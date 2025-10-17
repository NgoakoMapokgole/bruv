<?php
include "rate.php"; // Database connection
 // your database connection

// 1️⃣ Check if user is logged in
$loggedInID = $_SESSION['userID'] ?? 0;
if ($loggedInID == 0) {
    die("You must be logged in to view messages.");
}

// 2️⃣ Get the user you want to message
$profileUserID = isset($_GET['userID']) ? intval($_GET['userID']) : 0;
if ($profileUserID == 0) {
    die("Invalid user.");
}

// Optional: fetch profile user info
$stmt = $conn->prepare("SELECT userName FROM users WHERE userID = ?");
$stmt->bind_param("i", $profileUserID);
$stmt->execute();
$profileResult = $stmt->get_result();
$profileUser = $profileResult->fetch_assoc();

// 3️⃣ Handle sending a new message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['messageText'])) {
    header('Content-Type: application/json');

// ------------------------------------
// 1. CSRF Token Validation (THE FIX)
// ------------------------------------
if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
    http_response_code(403);
    die(json_encode(['status' => 'error', 'message' => 'CSRF token validation failed. Action aborted.']));
}

   $loggedInID = $_SESSION['userID'] ?? 0;
$receiverID = intval($_POST['receiverID'] ?? 0);
$messageText = trim($_POST['messageText'] ?? '');

if ($loggedInID && $receiverID && $messageText !== '') {
    $stmt = $conn->prepare("INSERT INTO messages (senderID, receiverID, messageText) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $loggedInID, $receiverID, $messageText);
    $stmt->execute();

    echo json_encode([
        'success' => true,
        'message' => htmlspecialchars($messageText),
        'time' => date('M j, g:i A')
    ]);
} else {
    echo json_encode(['success' => false]);
}
}

// 4️⃣ Fetch previous messages between the two users
$stmt = $conn->prepare("
    SELECT * FROM messages 
    WHERE (senderID = ? AND receiverID = ?) 
       OR (senderID = ? AND receiverID = ?)
    ORDER BY sentAt ASC
");
$stmt->bind_param("iiii", $loggedInID, $profileUserID, $profileUserID, $loggedInID);
$stmt->execute();
$messages = $stmt->get_result();

?>