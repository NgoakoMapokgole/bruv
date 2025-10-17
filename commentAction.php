<?php
include "../audit.php";
include "../homepage/notificationCreated.php"; // Ensure this file has addNotification()
include "../rate.php"; // Database connection

// --- Only Admins allowed ---
if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../homepage/login.php");
    exit();
}

// Check POST data
if (!isset($_POST['commentID'], $_POST['action']) || !in_array($_POST['action'], ['delete', 'restore'])) {
    die("Invalid request.");
}

$commentID = intval($_POST['commentID']);  // Safely cast to integer
$action = $_POST['action'];  // Validate action against allowed values

// Prepare SQL to fetch userID from comment
$sql = $conn->prepare("SELECT userID FROM comments WHERE commentID = ?");
$sql->bind_param("i", $commentID);
$sql->execute();
$jj = $sql->get_result()->fetch_assoc();  // Fetch the result once
$sql->close();

if (!$jj) {
    die("Comment not found.");
}

// Prepare the update query based on action
if ($action === 'delete') {
    $sql = "UPDATE comments SET deleted = 1 WHERE commentID = ?";
    $msg = "Comment deleted successfully.";
} elseif ($action === 'restore') {
    $sql = "UPDATE comments SET deleted = 0 WHERE commentID = ?";
    $msg = "Comment restored successfully.";
}

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $commentID);

if ($stmt->execute()) {
    // Log audit and create notifications
    if ($action === 'restore') {
        logAudit($_SESSION['userID'], 'COMMENT', 'comments', "Active", "commentID: " . $commentID . " RECOVERED");
        addNotification($conn, "Comment Restoration", "Your comment $commentID has been restored by admin", $jj['userID'], 'Alerts');
    } else {
        logAudit($_SESSION['userID'], 'COMMENT', 'comments', "Deleted", "commentID: " . $commentID . " DELETION");
        addNotification($conn, "Comment Deletion", "Your comment $commentID has been deleted by admin", $jj['userID'], 'Alerts');
    }

    // Redirect with success message
    header("Location: adminComments.php?msg=" . urlencode($msg));
    exit();
} else {
    die("Database error: " . $conn->error);
}
?>
