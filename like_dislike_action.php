<?php
session_start();
include "../rate.php";  // Ensure this contains the connection and necessary functions.

if (!isset($_SESSION['userID'])) {
    die("You need to be logged in to like or dislike.");
}

$userID = $_SESSION['userID'];
$postID = $_POST['postID'] ?? null;
$action = $_POST['action'] ?? null;

if (!$postID || !is_numeric($postID)) {
    die("Invalid post ID.");
}

if ($action === 'like') {
    // Check if user already liked
    $stmt = $conn->prepare("SELECT id FROM likes WHERE user_id=? AND review_id=?");
    $stmt->bind_param("ii", $userID, $postID);
    $stmt->execute();
    $liked = $stmt->get_result()->num_rows > 0;

    if ($liked) {
        // Unlike
        $stmt = $conn->prepare("DELETE FROM likes WHERE user_id=? AND review_id=?");
        $stmt->bind_param("ii", $userID, $postID);
        $stmt->execute();
        $conn->query("UPDATE post SET likes = likes - 1 WHERE postID = $postID AND likes > 0");
    } else {
        // Like
        $stmt = $conn->prepare("INSERT INTO likes (user_id, review_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $userID, $postID);
        $stmt->execute();
        $conn->query("UPDATE post SET likes = likes + 1 WHERE postID = $postID");

        // Only remove dislike if exists
        $stmt = $conn->prepare("SELECT id FROM dislikes WHERE user_id=? AND review_id=?");
        $stmt->bind_param("ii", $userID, $postID);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $stmt = $conn->prepare("DELETE FROM dislikes WHERE user_id=? AND review_id=?");
            $stmt->bind_param("ii", $userID, $postID);
            $stmt->execute();
            $conn->query("UPDATE post SET dislikes = dislikes - 1 WHERE postID = $postID AND dislikes > 0");
        }
    }
} elseif ($action === 'dislike') {
    // Check if user already disliked
    $stmt = $conn->prepare("SELECT id FROM dislikes WHERE user_id=? AND review_id=?");
    $stmt->bind_param("ii", $userID, $postID);
    $stmt->execute();
    $disliked = $stmt->get_result()->num_rows > 0;

    if ($disliked) {
        // Remove dislike
        $stmt = $conn->prepare("DELETE FROM dislikes WHERE user_id=? AND review_id=?");
        $stmt->bind_param("ii", $userID, $postID);
        $stmt->execute();
        $conn->query("UPDATE post SET dislikes = dislikes - 1 WHERE postID = $postID AND dislikes > 0");
    } else {
        // Dislike
        $stmt = $conn->prepare("INSERT INTO dislikes (user_id, review_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $userID, $postID);
        $stmt->execute();
        $conn->query("UPDATE post SET dislikes = dislikes + 1 WHERE postID = $postID");

        // Only remove like if exists
        $stmt = $conn->prepare("SELECT id FROM likes WHERE user_id=? AND review_id=?");
        $stmt->bind_param("ii", $userID, $postID);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $stmt = $conn->prepare("DELETE FROM likes WHERE user_id=? AND review_id=?");
            $stmt->bind_param("ii", $userID, $postID);
            $stmt->execute();
            $conn->query("UPDATE post SET likes = likes - 1 WHERE postID = $postID AND likes > 0");
        }
    }
}

// Redirect back to the post page
header("Location: viewPost.php?id=" . $postID);
exit();
