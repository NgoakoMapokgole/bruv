<?php
require "../rate.php";

// --- GET postID ---
$postID = isset($_GET['postID']) ? intval($_GET['postID']) : 0;
if ($postID <= 0) die("Invalid Post ID.");

// --- FETCH POST ---
$stmt = $conn->prepare("SELECT * FROM post WHERE postID = ? AND deleted = 0 and userID=?");
$stmt->bind_param("ii", $postID,$_SESSION['userID']);
$stmt->execute();
$result = $stmt->get_result();
$post = $result->fetch_assoc();
if (!$post) die("Post not found.");

// --- FETCH MEDIA (single item) ---
$stmtMedia = $conn->prepare("SELECT * FROM media WHERE postID = ? ORDER BY orderAppearance ASC LIMIT 1");
$stmtMedia->bind_param("i", $postID);
$stmtMedia->execute();
$resultMedia = $stmtMedia->get_result();
$media = $resultMedia->fetch_assoc();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // --- POST FIELDS ---
    $title = trim($_POST['Title'] ?? '');
    $content = trim($_POST['Content'] ?? '');
    $rating = intval($_POST['rating'] ?? 0);
    $tags = trim($_POST['tags'] ?? '');
    $category = $_POST['category'] ?? 'EverythingElse';
    $replyPermission = $_POST['replyPermission'] ?? 'anyone';

    // --- VALIDATION ---
    if ($title === '') $errors[] = "Title is required.";
    elseif (strlen($title) > 200) $errors[] = "Title cannot exceed 200 characters.";
    if ($content === '') $errors[] = "Content is required.";
    if ($rating < 1 || $rating > 5) $errors[] = "Rating must be between 1 and 5.";

    $allowedCategories = ['Media','Concept','Food','Place','EverythingElse'];
    if (!in_array($category, $allowedCategories)) $errors[] = "Invalid category selected.";

    $allowedReplyPermissions = ['anyone','friends','author'];
    if (!in_array($replyPermission, $allowedReplyPermissions)) $errors[] = "Invalid reply permission selected.";

    // Sanitize tags
    $tags = implode(',', array_filter(array_map('trim', explode(',', $tags))));

    // --- MEDIA FIELDS ---
    $mediaID = $media['mediaID'] ?? 0;
    $mediaType = $_POST['mediaType'][$mediaID] ?? ($media['typeMedia'] ?? '');
    $mediaArchived = isset($_POST['mediaArchived'][$mediaID]) ? 1 : 0;
    $mediaLocation = $media['location'] ?? '';

    if ($mediaType && !in_array($mediaType, ['Video','Audio','Images'])) $errors[] = "Invalid media type.";

    // Handle file upload
    if (!empty($_FILES['mediaFile']['name'][$mediaID])) {
        $file = $_FILES['mediaFile'];
        $tmpName = $file['tmp_name'][$mediaID];
        $fileName = basename($file['name'][$mediaID]);
        $targetDir = "../uploads/";
        $targetFile = $targetDir . uniqid() . "_" . $fileName;

        $allowedMime = ['image/jpeg','image/png','image/gif','video/mp4','audio/mpeg','audio/mp3'];
        $fileMime = mime_content_type($tmpName);

        if (!in_array($fileMime, $allowedMime)) {
            $errors[] = "Invalid file type for media.";
        } else {
            if (move_uploaded_file($tmpName, $targetFile)) {
                $mediaLocation = $targetFile; // use new file
            } else {
                $errors[] = "Failed to upload media file.";
            }
        }
    }

    // --- UPDATE POST + MEDIA ---
    if (empty($errors)) {
        // Update post
        $stmtUpdate = $conn->prepare("
            UPDATE post
            SET Title = ?, Content = ?, rating = ?, tags = ?, category = ?, replyPermission = ?
            WHERE postID = ?
        ");
        $stmtUpdate->bind_param("ssisssi", $title, $content, $rating, $tags, $category, $replyPermission, $postID);
        $stmtUpdate->execute();

        // Update or insert media
        if ($mediaID > 0) {
            // Existing media: update
            $stmtUpdateMedia = $conn->prepare("
                UPDATE media
                SET typeMedia = ?, location = ?, archived = ?
                WHERE mediaID = ? AND postID = ?
            ");
            $stmtUpdateMedia->bind_param("ssiii", $mediaType, $mediaLocation, $mediaArchived, $mediaID, $postID);
            $stmtUpdateMedia->execute();
        } else if ($mediaType || !empty($mediaLocation)) {
            // New media: insert
            $stmtInsertMedia = $conn->prepare("
                INSERT INTO media (postID, typeMedia, location, orderAppearance, archived)
                VALUES (?, ?, ?, 1, ?)
            ");
            $stmtInsertMedia->bind_param("issi", $postID, $mediaType, $mediaLocation, $mediaArchived);
            $stmtInsertMedia->execute();
        }

        $message="Post and media updated successfully!";

        // Refresh data
        $stmt->execute();
        $post = $stmt->get_result()->fetch_assoc();

        $stmtMedia->execute();
        $media = $stmtMedia->get_result()->fetch_assoc();
        header("Location:profile.php?msg=$message");

    } else {
        foreach ($errors as $error) echo "<p style='color:red;'> $error</p>";
    }
}
?>