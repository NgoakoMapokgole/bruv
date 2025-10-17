<?php
session_start();
include "../rate.php"; // Database connection

// --- Only Admins allowed ---
if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../homepage/login.php");
    exit();
}

// Get post ID
$postID = $_GET['id'] ?? null;
if (!$postID || !is_numeric($postID)) {
    die("Invalid post ID.");
}

// Fetch deleted post
$postSql = "SELECT * FROM post WHERE deleted = 1 AND postID = ?";
$postStmt = $conn->prepare($postSql);
$postStmt->bind_param("i", $postID);
$postStmt->execute();
$postResult = $postStmt->get_result();

if ($postResult->num_rows === 0) {
    die("Deleted post not found.");
}
$post = $postResult->fetch_assoc();

// Fetch author
$authorSql = "SELECT userName, profPic FROM users WHERE userID = ?";
$authorStmt = $conn->prepare($authorSql);
$authorStmt->bind_param("i", $post['userID']);
$authorStmt->execute();
$authorResult = $authorStmt->get_result();
$author = $authorResult->fetch_assoc();

// Fetch media
$mediaSql = "SELECT * FROM media WHERE postID = ? AND archived = 0 ORDER BY orderAppearance ASC";
$mediaStmt = $conn->prepare($mediaSql);
$mediaStmt->bind_param("i", $postID);
$mediaStmt->execute();
$mediaResult = $mediaStmt->get_result();

$mediaItems = [];
while ($row = $mediaResult->fetch_assoc()) {
    $mediaItems[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Deleted Post - <?= htmlspecialchars($post['Title']); ?></title>
<link rel="stylesheet" href="../mainStyle.css">
<style>
body { background:#1a1d26; color:#fff; font-family:'Lexend',sans-serif; }
main.deleted-post { max-width:900px; margin:2rem auto; padding:1rem; border:2px solid #f44336; border-radius:8px; background:rgba(244,67,54,0.05); }
h1 { color:#f44336; }
.review-author a { color:#00e054; text-decoration:none; }
.review-content { margin-top:1rem; line-height:1.6; color:#ddd; }
.review-media img, .review-media video, .review-media audio { border-radius:8px; margin-top:1rem; max-width:100%; }
.restore-btn { margin-top:1rem; padding:6px 12px; background:#4caf50; border:none; border-radius:6px; color:#fff; cursor:pointer; }
</style>
</head>
<body>

<main class="deleted-post">
    <h1>Deleted Review: <?= htmlspecialchars($post['Title']); ?></h1>
    <div class="review-author">
        <?php if(!empty($author['profPic'])): ?>
            <img src="<?= htmlspecialchars($author['profPic']); ?>" alt="<?= htmlspecialchars($author['userName']); ?>" style="width:40px;height:40px;border-radius:50%;object-fit:cover;">
        <?php endif; ?>
        <a href="../personProfile.php?userID=<?= $post['userID']; ?>"><?= htmlspecialchars($author['userName']); ?></a>
    </div>

    <div class="review-meta">
        <span>Date: <?= date("M d, Y", strtotime($post['dateCreated'])); ?></span> |
        <span>⭐ <?= $post['rating']; ?>/5</span> |
        <span>Category: <?= htmlspecialchars($post['category']); ?></span>
    </div>

    <div class="review-content">
        <?= nl2br(htmlspecialchars($post['Content'])); ?>
    </div>

    <div class="review-media">
        <?php foreach($mediaItems as $media): ?>
            <?php if($media['typeMedia'] === 'Images'): ?>
                <img src="<?= htmlspecialchars($media['location']); ?>" alt="">
            <?php elseif($media['typeMedia'] === 'Video'): ?>
                <video controls>
                    <source src="<?= htmlspecialchars($media['location']); ?>" type="video/mp4">
                    Your browser does not support the video tag.
                </video>
            <?php elseif($media['typeMedia'] === 'Audio'): ?>
                <audio controls>
                    <source src="<?= htmlspecialchars($media['location']); ?>" type="audio/mpeg">
                    Your browser does not support the audio element.
                </audio>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>

    <!-- Restore button -->
    <form action="reviewAction.php" method="POST">
        <input type="hidden" name="postID" value="<?= $postID; ?>">
        <input type="hidden" name="action" value="restore">
        <button type="submit" class="restore-btn">Restore Review</button>
    </form>
</main>

</body>
</html>
