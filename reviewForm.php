<?php
include "rate.php";
include "audit.php";
include "homepage/notificationCreated.php"; // make sure this file has addNotification()

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: homepage/index.php");
    exit();
}

if (!isset($_SESSION['userID'])) {
    header("Location: /homepage/Login.php");
    exit();
}

$userID = $_SESSION['userID'];

// Check active account
$stmt = $conn->prepare("SELECT role, accountStatus FROM users WHERE userID = ?");
$stmt->bind_param("i", $userID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: homepage/Login.php");
    exit();
}

$user = $result->fetch_assoc();
if ($user['accountStatus'] !== 'Active') {
    header("Location: homepage/Login.php");
    exit();
}

// Get form inputs
$title   = trim($_POST['title'] ?? '');
$content = trim($_POST['content'] ?? '');
$rating  = intval($_POST['rating'] ?? 0);
$tags    = trim($_POST['tags'] ?? '');
$date    = date('Y-m-d H:i:s');

// === Basic server-side validation ===
$errors = [];
if (!$title)   $errors[] = "Title cannot be empty.";
if (!$content) $errors[] = "Content cannot be empty.";
if ($rating < 1 || $rating > 5) $errors[] = "Rating must be 1-5.";

if (!empty($errors)) {
    $_SESSION['reviewStatus'] = ['success' => false, 'message' => implode('<br>', $errors)];
    header("Location: homepage/index.php");
    exit();
}

// Determine post category
$entityToCategoryMap = [
    'Movie' => 'Media',
    'Book' => 'Media',
    'FoodItem' => 'Food',
    'Restaurant' => 'Place',
    'Concept' => 'Concept',
    'Other' => 'EverythingElse',
    'EverythingElse' => 'EverythingElse'
];

$postCategory = 'EverythingElse';
$relation = ''; // default relation if not using banned word logic

// === INSERT POST ===
$stmt = $conn->prepare("
    INSERT INTO post (userID, Title, Content, rating, tags, dateCreated, likes, dislikes, category, relation, deleted)
    VALUES (?, ?, ?, ?, ?, ?, 0, 0, ?, ?, 0)
");
$stmt->bind_param("ississss", $userID, $title, $content, $rating, $tags, $date, $postCategory, $relation);

if (!$stmt->execute()) {
    die("Error saving review.");
}

$postID = $stmt->insert_id;
$stmt->close();

// === AUDIT LOG ===
logAudit($userID, 'POST', 'post', NULL, "PostID: $postID");

// === NOTIFICATION ===
// Example: send to system admin or create a public “system alert”
$notifyTitle = "📝 New Post Added";
$notifyContent = $_SESSION['userName']." created a new post titled '$title'.";
addNotification($conn, $notifyTitle, $notifyContent, $userID, 'UserBased');

// === HANDLE MEDIA UPLOAD ===
if (!empty($_FILES['media']['name'][0])) {
    $uploadDir = "uploads/";
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

    foreach ($_FILES['media']['name'] as $index => $fileName) {
        $tmpName = $_FILES['media']['tmp_name'][$index];
        $error   = $_FILES['media']['error'][$index];

        if ($error === UPLOAD_ERR_OK) {
            // Get file extension
            $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

            // Detect media type
            $typeMedia = "Images";
            if (in_array($ext, ['mp4', 'mov', 'avi', 'mkv'])) $typeMedia = "Video";
            elseif (in_array($ext, ['mp3', 'wav', 'ogg'])) $typeMedia = "Audio";

            // Generate unique hashed filename
            $uniqueName = hash('sha256', uniqid('', true) . $fileName . microtime()) . '.' . $ext;
            $filePath = $uploadDir . $uniqueName;

            if (move_uploaded_file($tmpName, $filePath)) {
                $order = $index + 1;

                $stmt = $conn->prepare("
                    INSERT INTO media (postID, typeMedia, location, orderAppearance, archived)
                    VALUES (?, ?, ?, ?, 0)
                ");
                $stmt->bind_param("issi", $postID, $typeMedia, $filePath, $order);
                $stmt->execute();

                // Log media upload
                logAudit($userID, 'POST', 'media', NULL, "MediaID: " . $stmt->insert_id);

                // Optional: notify user about upload success
                addNotification($conn, "📸 Media Uploaded", "Your media file was uploaded successfully for post #$postID.", $userID, 'UserBased');

                $stmt->close();
            }
        }
    }
}

$_SESSION['reviewStatus'] = [
    'success' => true,
    'message' => 'Review & media uploaded successfully!'
];

// Redirect
echo "<script>window.location='homepage/index.php';</script>";
exit();
?>
