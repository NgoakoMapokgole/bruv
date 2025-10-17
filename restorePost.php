<?php
include "../rate.php"; 
session_start();

if(!isset($_POST['postID'])) exit("No post specified.");
$postID = intval($_POST['postID']);
$userID = $_SESSION['userID'] ?? 0;

// Restore post (set deleted = 0)
$stmt = $conn->prepare("UPDATE post SET deleted = 0 WHERE postID = ? AND userID = ?");
$stmt->bind_param("ii", $postID, $userID);
$stmt->execute();

header("Location: " . $_SERVER['HTTP_REFERER']);
exit;
?>
