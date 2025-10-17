<?php
session_start();
include "../audit.php";  // Ensure this file has logAudit() function
include "../homepage/notificationCreated.php";  // Ensure this file has addNotification() function
include "../rate.php";  // Database connection

// Only Admins or Mods
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    die("Access denied.");
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Invalid request method.");
}

$reportID = $_POST['reportID'] ?? null;
$status = $_POST['status'] ?? null;

if (!$reportID || !$status) {
    die("All fields are required.");
}

// Get postID related to this report
$stmt = $conn->prepare("SELECT postID FROM reports WHERE reportID=?");
$stmt->bind_param("i", $reportID);
$stmt->execute();
$result = $stmt->get_result();
$report = $result->fetch_assoc();

if (!$report) {
    die("Report not found.");
}

$postID = $report['postID'];

// Get the userID associated with the post (for notifications)
$stmt2 = $conn->prepare("SELECT userID FROM post WHERE postID=?");
$stmt2->bind_param("i", $postID);
$stmt2->execute();
$postResult = $stmt2->get_result()->fetch_assoc();
$postUserID = $postResult['userID'];

// Update the report status
$stmt = $conn->prepare("UPDATE reports SET status=? WHERE reportID=?");
$stmt->bind_param("si", $status, $reportID);

if ($stmt->execute()) {
    // Log the action in the audit table
    logAudit($_SESSION['userID'], 'REPORT', 'reports', "Status", "ReportID: " . $reportID . " Status set to: " . $status);

    // Handle post visibility based on status
    if (!empty($postID)) {
        if ($status === 'Blocked') {
            // Block the post (hide it)
            $stmt2 = $conn->prepare("UPDATE post SET deleted=1 WHERE postID=?");
            $stmt2->bind_param("i", $postID);
            $stmt2->execute();
            
            // Create notification
            addNotification($conn, "Post Blocked", "Your post $postID has been blocked due to a report.", $postUserID, 'Alerts');
        } else {
            // Restore the post if status is anything other than 'Blocked'
            $stmt3 = $conn->prepare("UPDATE post SET deleted=0 WHERE postID=?");
            $stmt3->bind_param("i", $postID);
            $stmt3->execute();
            
            // Create notification
            addNotification($conn, "Post Restored", "Your post $postID has been restored after a report review.", $postUserID, 'Alerts');
        }
    }

    // Redirect with success message
    header("Location: adminReports.php?msg=" . urlencode("Report status updated."));
    exit();
} else {
    die("Error updating report: " . $conn->error);
}
?>
