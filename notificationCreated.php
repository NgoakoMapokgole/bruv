<?php

function addNotification($conn, $title, $content, $userID, $category = 'System') {
    $stmt = $conn->prepare("
        INSERT INTO notification (title, content, userID, category, dateCreated, deleted)
        VALUES (?, ?, ?, ?, NOW(), 0)
    ");
    $stmt->bind_param("ssis", $title, $content, $userID, $category);
    $success = $stmt->execute();
    $stmt->close();

    return $success;
}
?>
