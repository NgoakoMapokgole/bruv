<?php
include "../rate.php"; // database connection

$userID = $_SESSION['userID'] ?? 0;
if ($userID <= 0) {
    die("You must be logged in.");
}

// --- Fetch user info ---
$stmt = $conn->prepare("SELECT * FROM users WHERE userID = ?");
$stmt->bind_param("i", $userID);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
if (!$user) die("User not found.");

// --- Followers / Following ---
$followers = !empty($user['followers']) ? explode(',', $user['followers']) : [];
$following = !empty($user['following']) ? explode(',', $user['following']) : [];

// --- Fetch user reviews with one media per post ---
$reviews = [];
$reviewStmt = $conn->prepare("
    SELECT p.*, m.typeMedia, m.location
    FROM post p
    LEFT JOIN media m ON p.postID = m.postID AND m.archived = 0
    WHERE p.userID = ? AND p.deleted = 0
    GROUP BY p.postID
    ORDER BY p.dateCreated DESC
");
$reviewStmt->bind_param("i", $userID);
$reviewStmt->execute();
$reviewResult = $reviewStmt->get_result();
while($row = $reviewResult->fetch_assoc()){
    $postID = $row['postID'];

    // Total likes
    $stmtLikes = $conn->prepare("SELECT COUNT(*) AS totalLikes FROM likes WHERE review_id = ?");
    $stmtLikes->bind_param("i", $postID);
    $stmtLikes->execute();
    $row['totalLikes'] = $stmtLikes->get_result()->fetch_assoc()['totalLikes'] ?? 0;

    // Total comments
    $stmtComments = $conn->prepare("SELECT COUNT(*) AS totalComments FROM comments WHERE postID = ? AND deleted = 0");
    $stmtComments->bind_param("i", $postID);
    $stmtComments->execute();
    $row['totalComments'] = $stmtComments->get_result()->fetch_assoc()['totalComments'] ?? 0;

    $reviews[] = $row;
}

// --- Calculate stats ---
$ratings = array_column($reviews, 'rating');
$avgRating = count($ratings) ? array_sum($ratings)/count($ratings) : 0;
$totalWords = array_sum(array_map(fn($p) => str_word_count($p['Content']), $reviews));
$totalLikes = array_sum(array_column($reviews, 'totalLikes'));
$totalDislikes = array_sum(array_column($reviews, 'dislikes') ?? [0]);
$postsWithMedia = $conn->query("
    SELECT COUNT(DISTINCT postID) AS c 
    FROM media 
    WHERE postID IN (SELECT postID FROM post WHERE userID = $userID AND deleted = 0) 
    AND archived = 0
")->fetch_assoc()['c'] ?? 0;
$totalComments = $conn->query("
    SELECT COUNT(*) AS c 
    FROM comments 
    WHERE postID IN (SELECT postID FROM post WHERE userID = $userID AND deleted = 0)
")->fetch_assoc()['c'] ?? 0;

// Categories count
$categories = [];
$catResult = $conn->query("SELECT category, COUNT(*) AS count FROM post WHERE userID = $userID AND deleted = 0 GROUP BY category");
while($row = $catResult->fetch_assoc()) $categories[$row['category']] = $row['count'];

// --- Fetch liked reviews ---
$likedReviews = [];
$stmtLiked = $conn->prepare("
    SELECT p.*, u.userName
    FROM likes l
    JOIN post p ON l.review_id = p.postID
    JOIN users u ON p.userID = u.userID
    WHERE l.user_id = ? AND p.deleted = 0
    ORDER BY l.id DESC
");
$stmtLiked->bind_param("i", $userID);
$stmtLiked->execute();
$resultLiked = $stmtLiked->get_result();
while($review = $resultLiked->fetch_assoc()){
    $postID = $review['postID'];

    // Likes and comments for liked review
    $stmtLikes = $conn->prepare("SELECT COUNT(*) as totalLikes FROM likes WHERE review_id = ?");
    $stmtLikes->bind_param("i", $postID);
    $stmtLikes->execute();
    $review['totalLikes'] = $stmtLikes->get_result()->fetch_assoc()['totalLikes'] ?? 0;

    $stmtComments = $conn->prepare("SELECT COUNT(*) as totalComments FROM comments WHERE postID = ? AND deleted = 0");
    $stmtComments->bind_param("i", $postID);
    $stmtComments->execute();
    $review['totalComments'] = $stmtComments->get_result()->fetch_assoc()['totalComments'] ?? 0;

    $likedReviews[] = $review;
}

// --- Activities feed (reviews, likes, comments) ---
$activities = [];

// Reviews
foreach($reviews as $p){
    $activities[] = [
        'type'=>'review',
        'title'=>$p['Title'],
        'postID'=>$p['postID'],
        'userName'=>$user['userName'],
        'created_at'=>$p['dateCreated']
    ];
}

// Liked reviews
foreach($likedReviews as $lr){
    $activities[] = [
        'type'=>'like',
        'title'=>$lr['Title'],
        'postID'=>$lr['postID'],
        'userName'=>$lr['userName'],
        'created_at'=>date("Y-m-d H:i:s")
    ];
}

// User comments
$stmtComments = $conn->prepare("
    SELECT c.commentID, c.postID, c.content, c.datecCreated, u.userName, p.Title AS postTitle
    FROM comments c
    JOIN users u ON c.userID = u.userID
    JOIN post p ON c.postID = p.postID
    WHERE c.userID = ? AND c.deleted = 0
");
$stmtComments->bind_param("i", $userID);
$stmtComments->execute();
$resultComments = $stmtComments->get_result();
while($row = $resultComments->fetch_assoc()){
    $activities[] = [
        'type'=>'comment',
        'title'=>$row['postTitle'],
        'content'=>$row['content'],
        'postID'=>$row['postID'],
        'userName'=>$row['userName'],
        'created_at'=>$row['datecCreated']
    ];
}

// Sort activities newest first
usort($activities, fn($a,$b) => strtotime($b['created_at']) - strtotime($a['created_at']));

        // Fetch deleted posts for the logged-in user
        $userID = $_SESSION['userID'] ?? 0;
        $stmt = $conn->prepare("SELECT * FROM post WHERE userID = ? AND deleted = 1 ORDER BY dateCreated DESC");
        $stmt->bind_param("i", $userID);
        $stmt->execute();
        $deletedPostsResult = $stmt->get_result();
?>
