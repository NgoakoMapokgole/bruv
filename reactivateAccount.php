<?php
session_start();
include "../rate.php"; // your database connection

// Ensure user is logged in
$userID = $_SESSION['userID'] ?? 0;
if (!$userID) {
    die("You must be logged in to reactivate your account.");
}

// Update account status to Active
$stmt = $conn->prepare("UPDATE users SET accountStatus = 'Active' WHERE userID = ?");
$stmt->bind_param("i", $userID);
if ($stmt->execute()) {
    $_SESSION['accountStatus'] = 'Active'; // optional: update session info
    header("Location: profile.php?message=account_reactivated");
    exit;
} else {
    die("Failed to reactivate account.");
}
?>