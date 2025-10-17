<?php
session_start();
include "../rate.php"; // your DB connection

// Make sure user is logged in
if (!isset($_SESSION['userID'])) {
    die("You must be logged in to deactivate your account.");
}

$userID = $_SESSION['userID'];

// Handle deactivate action
if (isset($_POST['deactivate_account'])) {

    $stmt = $conn->prepare("
        UPDATE users 
        SET accountStatus = 'De-Activated', deleted = 1, statusChangeDate = NOW() 
        WHERE userID = ?
    ");
    $stmt->bind_param("i", $userID);

    if ($stmt->execute()) {
        // Optionally log the user out immediately
        session_destroy();
        header("Location: index.php"); // redirect to a goodbye page
        exit;
    } else {
        echo "Error deactivating account. Please try again.";
    }
}
?>
