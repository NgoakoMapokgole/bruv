<?php
// Get user profile picture
function getUserProfilePic($userID, $conn) {
    $stmt = $conn->prepare("SELECT profPic FROM users WHERE userID = ?");
    $stmt->bind_param("i", $userID);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        if (!empty($row['profPic'])) {
            return $row['profPic']; // stored path/url in DB
        }
    }
    return null; // no pic set
}

// Get single media (poster) for a post
function getPoster($postID, $conn) {
    $mediaResult = getMedia($postID, $conn);
    if ($mediaResult) {
        if ($mediaResult['typeMedia'] === 'Images') {
            return $mediaResult['location'];
        }
    }
    return "default-poster.jpg"; // fallback if no poster
}

// Get media for a post (only one, first by orderAppearance)
function getMedia($postID, $conn) {
    $mediaQuery = "SELECT * FROM media WHERE postID = ? AND archived = 0 ORDER BY orderAppearance ASC LIMIT 1";
    $stmt = $conn->prepare($mediaQuery);
    $stmt->bind_param("i", $postID);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc(); // single row or null
}

// Get comments for a post
function getComments($postID, $conn) {
    $commentsQuery = "
        SELECT c.*, u.userName 
        FROM comments c 
        JOIN users u ON c.userID = u.userID 
        WHERE c.postID = ? AND c.deleted = 0 
        ORDER BY c.datecCreated DESC
    ";
    $stmt = $conn->prepare($commentsQuery);
    $stmt->bind_param("i", $postID);
    $stmt->execute();
    return $stmt->get_result();
}

// ================= HANDLE LIKES =================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['review_id'])) {
    if (!isset($_SESSION['userID'])) {
        exit("You must be logged in to like a review.");
    }

    $reviewID = (int) $_POST['review_id'];
    $userID   = $_SESSION['userID'];
    $action   = $_POST['action']; // 'like' or 'unlike'

    if ($action === "like") {
        // Use INSERT IGNORE to prevent multiple likes
        $stmt = $conn->prepare("INSERT IGNORE INTO likes (user_id, review_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $userID, $reviewID);
        $stmt->execute();

        // Increment like count only if insert affected a row
        if ($stmt->affected_rows > 0) {
            $conn->query("UPDATE post SET likes = likes + 1 WHERE postID = $reviewID");
        }

    } elseif ($action === "unlike") {
        // Remove like if exists
        $stmt = $conn->prepare("DELETE FROM likes WHERE user_id = ? AND review_id = ?");
        $stmt->bind_param("ii", $userID, $reviewID);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            $conn->query("UPDATE post SET likes = likes - 1 WHERE postID = $reviewID");
        }
    }

    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// ================= HANDLE NEW COMMENT =================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_comment'], $_POST['postID'])) {
    if (isset($_SESSION['userID'])) {
        $userID = $_SESSION['userID'];
        $postID = (int) $_POST['postID'];
        $content = trim($_POST['new_comment']);

        if ($content !== "") {
            $stmt = $conn->prepare("
                INSERT INTO comments (userID, postID, content, datecCreated, deleted) 
                VALUES (?, ?, ?, NOW(), 0)
            ");
            $stmt->bind_param("iis", $userID, $postID, $content);
            $stmt->execute();
        }
    }

    // Redirect to avoid form resubmission
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// ================= FEATURED REVIEW =================
$featuredQuery = "
    SELECT p.*, u.userName,p.likes,p.dislikes 
    FROM post p 
    JOIN users u ON p.userID = u.userID 
    WHERE DATE(p.dateCreated) = CURDATE() 
    AND p.deleted = 0 
    ORDER BY p.likes DESC 
    LIMIT 1
";
$featuredResult = $conn->query($featuredQuery);
$featuredReview = $featuredResult->fetch_assoc();

// ================= ALL REVIEWS =================
$reviewsQuery = "
    SELECT p.*, u.userName 
    FROM post p 
    JOIN users u ON p.userID = u.userID 
    WHERE p.deleted = 0 
    ORDER BY p.dateCreated DESC 
    LIMIT 12
";
$reviewsResult = $conn->query($reviewsQuery);
$reviews = [];
while ($row = $reviewsResult->fetch_assoc()) {
    $reviews[] = $row;
}
?>
