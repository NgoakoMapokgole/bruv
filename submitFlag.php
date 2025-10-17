<?php
session_start();
include "../rate.php"; // Database connection

// Ensure user is logged in
if (!isset($_SESSION['userID'])) {
    die("You must be logged in to report a post.");
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Invalid request method.");
}

// Get input values
$userID = $_SESSION['userID'];
$postID = $_POST['postID'] ?? null;
$reportType = $_POST['reportType'] ?? '';
$description = trim($_POST['description'] ?? '');

if (!$postID || !$reportType || !$description) {
    die("All fields are required.");
}

// Insert into reports table
$sql = "INSERT INTO reports (postID, commentID, userID, reportType, contentType, description, status, createdAt)
        VALUES (?, NULL, ?, ?, 'Post', ?, 'New', NOW())";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iiss", $postID, $userID, $reportType, $description);

if ($stmt->execute()) {
    // Redirect back to the review page
    header("Location: viewPost.php?id=" . intval($postID) . "&msg=" . urlencode("Report submitted successfully."));
    exit();
} else {
    die("Error submitting report: " . $conn->error);
}
