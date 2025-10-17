<?php
session_start();
require "../rate.php";

if (!isset($_SESSION['userID'])) {
    die("Not logged in.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['postID'])) {
    $postID = intval($_POST['postID']);
    $userID = $_SESSION['userID'];

    // Only allow the owner to delete
    $stmt = $conn->prepare("UPDATE post SET deleted = 1 WHERE postID = ? AND userID = ?");
    $stmt->bind_param("ii", $postID, $userID);
    $stmt->execute();

    header("Location: profile.php?userID=$userID"); // redirect back to profile
    exit();
}

die("Invalid request.");
