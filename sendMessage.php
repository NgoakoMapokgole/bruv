<?php
// sendMessage.php - Handle sending messages

header('Content-Type: application/json');

session_start();
include "rate.php";

// Set appropriate status codes
http_response_code(400); // Default to error


// 1. Validate session
if (!isset($_SESSION['userID']) || empty($_SESSION['userID'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'You must be logged in.']);
    exit;
}

$loggedInID = $_SESSION['userID'];

// 2. Validate CSRF token
if (empty($_POST['csrf_token']) || empty($_SESSION['csrf_token'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Security token invalid.']);
    exit;
}

if (!hash_equals($_POST['csrf_token'], $_SESSION['csrf_token'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Security token does not match.']);
    exit;
}

// 3. Validate receiver ID
$receiverID = intval($_POST['receiverID'] ?? 0);
if (!$receiverID) {
    echo json_encode(['success' => false, 'error' => 'Invalid recipient.']);
    exit;
}

// 4. Prevent self-messaging
if ($loggedInID == $receiverID) {
    echo json_encode(['success' => false, 'error' => 'You cannot message yourself.']);
    exit;
}

// 5. Validate message text
$messageText = trim($_POST['messageText'] ?? '');
if (empty($messageText)) {
    echo json_encode(['success' => false, 'error' => 'Message cannot be empty.']);
    exit;
}

if (strlen($messageText) > 5000) {
    echo json_encode(['success' => false, 'error' => 'Message is too long (maximum 5000 characters).']);
    exit;
}

// 6. Rate limiting check (prevent spam)
$lastMessageKey = 'last_message_time_' . $receiverID;
$lastMessageTime = $_SESSION[$lastMessageKey] ?? 0;
$currentTime = time();

if ($currentTime - $lastMessageTime < 1) { // 1 second minimum between messages
    echo json_encode(['success' => false, 'error' => 'Please wait before sending another message.']);
    exit;
}

$_SESSION[$lastMessageKey] = $currentTime;

// 7. Verify receiver exists
$stmt = $conn->prepare("SELECT userID FROM users WHERE userID = ?");
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error.']);
    exit;
}

$stmt->bind_param("i", $receiverID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $stmt->close();
    echo json_encode(['success' => false, 'error' => 'Recipient does not exist.']);
    exit;
}
$stmt->close();

// 8. Insert message into database
$stmt = $conn->prepare("
    INSERT INTO messages (senderID, receiverID, messageText, sentAt) 
    VALUES (?, ?, ?, NOW())
");

if (!$stmt) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $conn->error]);
    exit;
}

$stmt->bind_param("iis", $loggedInID, $receiverID, $messageText);

if (!$stmt->execute()) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Failed to send message.']);
    $stmt->close();
    exit;
}

$messageID = $conn->insert_id;
$stmt->close();

// 9. Return success response
http_response_code(200);
echo json_encode([
    'success' => true,
    'messageID' => $messageID,
    'message' => htmlspecialchars($messageText),
    'time' => date('M j, g:i A')
]);
exit;
?>