<?php
// fetchMessages.php - Fetch new messages for polling

header('Content-Type: application/json');

session_start();
include "rate.php";

// Set error status by default
http_response_code(400);

// 1. Validate session
if (!isset($_SESSION['userID']) || empty($_SESSION['userID'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized', 'messages' => []]);
    exit;
}

$loggedInID = $_SESSION['userID'];

// 2. Validate receiver ID
$receiverID = intval($_GET['receiverID'] ?? 0);
if (!$receiverID) {
    echo json_encode(['success' => false, 'error' => 'Invalid recipient', 'messages' => []]);
    exit;
}

// 3. Prevent self-messaging
if ($loggedInID == $receiverID) {
    echo json_encode(['success' => false, 'error' => 'Invalid request', 'messages' => []]);
    exit;
}

// 4. Validate and sanitize lastID
$lastID = intval($_GET['lastID'] ?? 0);
if ($lastID < 0) {
    $lastID = 0;
}

// 5. Verify receiver exists
$stmt = $conn->prepare("SELECT userID FROM users WHERE userID = ?");
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error', 'messages' => []]);
    exit;
}

$stmt->bind_param("i", $receiverID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $stmt->close();
    echo json_encode(['success' => false, 'error' => 'User not found', 'messages' => []]);
    exit;
}
$stmt->close();

// 6. Fetch messages since lastID
$stmt = $conn->prepare("
    SELECT messageID, senderID, messageText, sentAt
    FROM messages 
    WHERE ((senderID = ? AND receiverID = ?) OR (senderID = ? AND receiverID = ?))
      AND messageID > ? 
    ORDER BY sentAt ASC
    LIMIT 100
");

if (!$stmt) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error', 'messages' => []]);
    exit;
}

$stmt->bind_param("iiiii", $loggedInID, $receiverID, $receiverID, $loggedInID, $lastID);

if (!$stmt->execute()) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Query failed', 'messages' => []]);
    $stmt->close();
    exit;
}

$result = $stmt->get_result();
$messages = [];

while ($msg = $result->fetch_assoc()) {
    $messages[] = [
        'messageID' => intval($msg['messageID']),
        'messageText' => htmlspecialchars($msg['messageText']),
        'sentAt' => date('M j, g:i A', strtotime($msg['sentAt'])),
        'isMe' => intval($msg['senderID']) == $loggedInID
    ];
}

$stmt->close();

// 7. Return success response with messages
http_response_code(200);
echo json_encode([
    'success' => true,
    'messages' => $messages
]);
exit;
?>