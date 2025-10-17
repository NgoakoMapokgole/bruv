<?php
include "../audit.php";
include "../homepage/notificationCreated.php"; // make sure this file has addNotification()
include "../rate.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    die("Access denied.");
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Invalid request method.");
}

$postID = $_POST['postID'] ?? null;
$action = $_POST['action'] ?? '';

if (!$postID || !$action) {
    die("Missing parameters.");
}

$sql = $conn->prepare("SELECT userID FROM post WHERE postID = ?");
$sql->bind_param("i", $postID);
$sql->execute();
$jj = $sql->get_result()->fetch_assoc();  // Fetch the result once
$sql->close();

if (!$jj) {
    die("Post not found.");
}

if ($action === 'delete') {
    $stmt = $conn->prepare("UPDATE post SET deleted = 1 WHERE postID = ?");
} elseif ($action === 'restore') {
    $stmt = $conn->prepare("UPDATE post SET deleted = 0 WHERE postID = ?");
} else {
    die("Invalid action.");
}

$stmt->bind_param("i", $postID);

if ($stmt->execute()) {
    if ($action === 'restore') {
        logAudit($_SESSION['userID'], 'POST', 'post', "Active", "PostID: " . $postID . " RECOVERED");

        // Use $jj['userID'] here
        addNotification($conn, "Post Restoration", "Your post $postID has been restored by admin", $jj['userID'], 'Alerts');
    } else {
        logAudit($_SESSION['userID'], 'POST', 'post', "Deleted", "PostID: " . $postID . " DELETION");

        // Use $jj['userID'] here
        addNotification($conn, "Post Deletion", "Your post $postID has been deleted by admin", $jj['userID'], 'Alerts');
    }

    header("Location: adminReviews.php?msg=" . urlencode("Review " . ($action === 'delete' ? "deleted" : "restored") . " successfully."));
    exit();
} else {
    die("Database error: " . $conn->error);
}
?>
