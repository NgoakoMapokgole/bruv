<?php
session_start();
include "../rate.php"; 

if (!isset($_SESSION['userID'])) die("You must be logged in to comment.");

$userID = $_SESSION['userID'];
$postID = $_POST['postID'] ?? null;
$replyID = $_POST['replyID'] ?? null; 
$content = $_POST['content'] ?? null;

if (!$postID || !$content) die("Invalid input.");

$stmt = $conn->prepare("INSERT INTO comments (replyID, userID, postID, content) VALUES (?, ?, ?, ?)");
$stmt->bind_param("iiis", $replyID, $userID, $postID, $content);
$stmt->execute();
$msg = urlencode("Comment added successfully");
header("Location: viewPost.php?id=$postID&showComments=1&msg=$msg");
exit;
?>
