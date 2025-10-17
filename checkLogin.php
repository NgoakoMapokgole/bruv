<?php
session_start();
echo json_encode([
    'isLoggedIn' => isset($_SESSION['userID']),
    'userId' => $_SESSION['userID'] ?? null
]);
?>