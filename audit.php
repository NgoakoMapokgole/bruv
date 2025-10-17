<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function logAudit($userID, $action, $tableAffected, $previousValue = NULL, $currentValue = NULL) {
    global $conn;  // Use the global database connection

    // Get the user's IP address
    $userIP = $_SERVER['REMOTE_ADDR'];

    // Prepare SQL statement for inserting audit log
    $sql = $conn->prepare("INSERT INTO audit (userID, tableAffected, Action, PreviousValue, CurrentValue, time, IP) 
                           VALUES (?, ?, ?, ?, ?, NOW(), ?)");

    // Bind parameters for the query
    $sql->bind_param('isssss', $userID, $tableAffected, $action, $previousValue, $currentValue, $userIP);

    // Execute the query and check for success
    if ($sql->execute()) {
        return true;  // Log success
    } else {
        return false;  // Log failure
    }
}
?>

