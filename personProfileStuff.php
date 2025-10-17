<?php
include "rate.php"; // Database connection

// --- Get userID from query string or session ---
$userID = isset($_GET['userID']) ? intval($_GET['userID']) : $_SESSION['userID'] ;
if ($userID <= 0) die("Invalid user ID.");

// --- Fetch user ---
$stmt = $conn->prepare("SELECT * FROM users WHERE userID = ?");
$stmt->bind_param("i", $userID);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
if (!$user) die("User not found.");

// --- Followers / Following arrays ---
$followers = !empty($user['followers']) ? explode(',', $user['followers']) : [];
$following = !empty($user['following']) ? explode(',', $user['following']) : [];

// --- Fetch user posts ---
$posts = [];
$stmtPosts = $conn->prepare("SELECT * FROM post WHERE userID = ? AND deleted = 0 ORDER BY dateCreated DESC");
$stmtPosts->bind_param("i", $userID);
$stmtPosts->execute();
$postsResult = $stmtPosts->get_result();
while($post = $postsResult->fetch_assoc()){
    $postID = $post['postID'];

    // Total comments
    $stmtComments = $conn->prepare("SELECT COUNT(*) AS totalComments FROM comments WHERE postID = ? AND deleted = 0");
    $stmtComments->bind_param("i", $postID);
    $stmtComments->execute();
    $post['totalComments'] = $stmtComments->get_result()->fetch_assoc()['totalComments'] ?? 0;

    $stmtMedia = $conn->prepare("SELECT location FROM media WHERE postID = ? AND archived = 0");
    $stmtMedia->bind_param("i", $postID);
    $stmtMedia->execute();
    $post['Media'] = $stmtMedia->get_result()->fetch_assoc()['location'] ?? 0;
    // Total likes
    $stmtLikes = $conn->prepare("SELECT COUNT(*) AS totalLikes FROM likes WHERE review_id = ?");
    $stmtLikes->bind_param("i", $postID);
    $stmtLikes->execute();
    $post['totalLikes'] = $stmtLikes->get_result()->fetch_assoc()['totalLikes'] ?? 0;

    $posts[] = $post;
}

// --- Average rating ---
$ratings = array_column($posts, 'rating');
$avgRating = count($ratings) ? array_sum($ratings)/count($ratings) : 0;

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

// --- Activities Feed ---
$activities = [];
// Reviews
foreach($posts as $p){
    $activities[] = [
        'type'=>'review',
        'title'=>$p['Title'],
        'postID'=>$p['postID'],
        'userName'=>$user['userName'],
        'created_at'=>$p['dateCreated']
    ];
}
// Liked Reviews
foreach($likedReviews as $lr){
    $activities[] = [
        'type'=>'like',
        'title'=>$lr['Title'],
        'postID'=>$lr['postID'],
        'userName'=>$lr['userName'],
        'created_at'=>date("Y-m-d H:i:s")
    ];
}
// Comments
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

// Sort newest first
usort($activities, function($a,$b){
    return strtotime($b['created_at']) - strtotime($a['created_at']);
});

// --- Fetch users with posts for "Following" tab ---
$followingList = [];

// Only proceed if this user is following anyone
if (!empty($user['following'])) {
    $followingIDs = explode(',', $user['following']);
    
    // Prepare placeholders for the IN clause
    $placeholders = implode(',', array_fill(0, count($followingIDs), '?'));
    $types = str_repeat('i', count($followingIDs));

    $stmt = $conn->prepare("SELECT userID, userName, profPic FROM users WHERE userID IN ($placeholders) ORDER BY userName ASC");

    // Bind parameters dynamically
    $stmt->bind_param($types, ...$followingIDs);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $followingList[] = $row;
    }

    $stmt->close();
}
// --- Stats for Nerds ---
$stats = [];
$stats['totalReviews'] = count($posts);
$stats['avgRating'] = round($avgRating,2);
$stats['totalWords'] = array_sum(array_map(function($p){ return str_word_count($p['Content']); }, $posts));
$stats['totalLikes'] = array_sum(array_column($posts,'totalLikes'));
$stats['totalDislikes'] = array_sum(array_column($posts,'dislikes') ?? [0]);
$stats['postsWithMedia'] = $conn->query("SELECT COUNT(DISTINCT postID) AS c FROM media WHERE postID IN (SELECT postID FROM post WHERE userID = $userID AND deleted = 0) AND archived = 0")->fetch_assoc()['c'] ?? 0;
$stats['totalComments'] = $conn->query("SELECT COUNT(*) AS c FROM comments WHERE postID IN (SELECT postID FROM post WHERE userID = $userID AND deleted = 0)")->fetch_assoc()['c'] ?? 0;
$stats['categories'] = [];
$catResult = $conn->query("SELECT category, COUNT(*) AS count FROM post WHERE userID = $userID AND deleted = 0 GROUP BY category");
while($row = $catResult->fetch_assoc()) $stats['categories'][$row['category']] = $row['count'];
$stats['followers'] = count($followers);
$stats['following'] = count($following);
?>
