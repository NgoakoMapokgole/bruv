<!-- FILE: message.php -->
<?php
session_start();
include "rate.php";

// Validate session
if (!isset($_SESSION['userID'])) {
    header("Location: login.php");
    exit;
}

$loggedInID = $_SESSION['userID'];
$profileUserID = intval($_GET['userID'] ?? 0);

// Validate profile user ID
if (!$profileUserID) {
    die("Invalid user.");
}

// Prevent messaging yourself
if ($loggedInID == $profileUserID) {
    die("You cannot message yourself.");
}

// Initialize CSRF token if not exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Fetch profile user
$stmt = $conn->prepare("SELECT userID, userName FROM users WHERE userID = ?");
if (!$stmt) {
    die("Database error: " . $conn->error);
}
$stmt->bind_param("i", $profileUserID);
$stmt->execute();
$result = $stmt->get_result();
$profileUser = $result->fetch_assoc();

if (!$profileUser) {
    die("User not found.");
}
$stmt->close();

// Fetch initial messages
$stmt = $conn->prepare("
    SELECT messageID, senderID, messageText, sentAt 
    FROM messages 
    WHERE (senderID=? AND receiverID=?) OR (senderID=? AND receiverID=?)
    ORDER BY sentAt ASC
    LIMIT 50
");

if (!$stmt) {
    die("Database error: " . $conn->error);
}

$stmt->bind_param("iiii", $loggedInID, $profileUserID, $profileUserID, $loggedInID);
$stmt->execute();
$messagesResult = $stmt->get_result();
$messages = [];

while ($msg = $messagesResult->fetch_assoc()) {
    $messages[] = $msg;
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages with <?= htmlspecialchars($profileUser['userName']) ?> - RateMySite</title>
    <meta name="description" content="Chat with <?= htmlspecialchars($profileUser['userName']) ?> on RateMySite">
    <link rel="icon" type="image/svg+xml" href="logo.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="mainStyle.css"/>
    <link rel="stylesheet" href="message.css">
</head>
<body>
    <?php include 'nav.php'; ?>
    <?php include 'review.php'; ?>
    
    <main class="messages-container">
        <header class="chat-header">
            <h1>Chat with <?= htmlspecialchars($profileUser['userName']) ?></h1>
        </header>

        <section class="chat-messages" id="chatMessages" aria-live="polite" aria-label="Conversation with <?= htmlspecialchars($profileUser['userName']) ?>">
            <?php if (count($messages) > 0): ?>
                <?php foreach ($messages as $msg): ?>
                    <?php $isMe = $msg['senderID'] == $loggedInID; ?>
                    <article class="message <?= $isMe ? 'my-message' : 'their-message' ?>" 
                             role="article" 
                             data-message-id="<?= intval($msg['messageID']) ?>"
                             aria-labelledby="message-<?= intval($msg['messageID']) ?>">
                        <p id="message-<?= intval($msg['messageID']) ?>"><?= htmlspecialchars($msg['messageText']) ?></p>
                        <section class="message-meta">
                            <time datetime="<?= htmlspecialchars($msg['sentAt']) ?>" class="message-time">
                                <?= date('M j, g:i A', strtotime($msg['sentAt'])) ?>
                            </time>
                            <?php if ($isMe): ?>
                                <span class="message-status sent" aria-label="Message sent">✓</span>
                            <?php endif; ?>
                        </section>
                    </article>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="no-messages" role="status">No messages yet. Start the conversation!</p>
            <?php endif; ?>
            
            <!-- Typing indicator -->
            <figure class="typing-indicator" id="typingIndicator" role="status" aria-label="<?= htmlspecialchars($profileUser['userName']) ?> is typing" hidden>
                <figcaption>Typing...</figcaption>
                <span class="typing-dots">
                    <span class="typing-dot"></span>
                    <span class="typing-dot"></span>
                    <span class="typing-dot"></span>
                </span>
            </figure>
        </section>

        <form method="POST" class="message-form" aria-label="Send a message" id="messageForm" data-receiver-id="<?= intval($profileUserID) ?>" data-csrf-token="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
            <label for="messageInput" class="visually-hidden">Type your message</label>
            <textarea 
            style="color:black";
            name="messageText" 
                      class="message-input" 
                      id="messageInput"
                      placeholder="Type your message..." 
                      maxlength="5000"
                      required></textarea>
            <button type="submit" class="send-btn">
                <span class="visually-hidden">Send message</span>
                <span aria-hidden="true">Send</span>
            </button>
        </form>
    </main>
    <script src="message.js"></script>
    <script src="mainScript.js"></script>
</body>
</html>