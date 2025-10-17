<?php
// comment.php (Admin Management)
session_start();
require '../rate.php'; // $conn = new mysqli(...)

// --- POST: Add comment ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_comment'])) {
    if (!isset($_SESSION['userID'])) {
        die("You must be logged in to comment.");
    }

    $review_id = $_POST['review_id'];
    $content   = trim($_POST['content']);
    $user_id   = $_SESSION['userID'];

    if ($content === '') {
        die("Comment cannot be empty.");
    }

    // INSERT (replyID and image left NULL, likes/dislikes set 0, deleted=0)
    $stmt = $conn->prepare("
        INSERT INTO comments (replyID, userID, postID, content, image, `like`, dislike, datecCreated, deleted)
        VALUES (NULL, ?, ?, ?, NULL, 0, 0, NOW(), 0)
    ");
    $stmt->bind_param("iis", $user_id, $review_id, $content);
    $stmt->execute();
    $stmt->close();

    header("Location: comment.php?review_id=" . $review_id);
    exit;
}

// --- GET: Display review and comments ---
$review_id = $_GET['review_id'] ?? null;
if (!$review_id) {
    die("No review selected.");
}

// Fetch the review itself
$stmt = $conn->prepare("
    SELECT r.postID, r.content, r.rating, u.username
    FROM post r
    JOIN users u ON r.userID = u.userID
    WHERE r.postID = ?
");
$stmt->bind_param("i", $review_id);
$stmt->execute();
$review_result = $stmt->get_result();
$review = $review_result->fetch_assoc();
$stmt->close();

if (!$review) {
    die("Review not found.");
}

// Fetch comments linked to this review
$stmt = $conn->prepare("
    SELECT c.commentID, c.content, c.datecCreated, c.deleted, u.username
    FROM comments c
    JOIN users u ON c.userID = u.userID
    WHERE c.postID = ?
    ORDER BY c.datecCreated ASC
");
$stmt->bind_param("i", $review_id);
$stmt->execute();
$comments_result = $stmt->get_result();
$comments = [];
while ($row = $comments_result->fetch_assoc()) {
    $comments[] = $row;
}
$stmt->close();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin: Manage Comments</title>
</head>
<body>
<h3>Review by <?= htmlspecialchars($review['username']) ?> (<?= htmlspecialchars($review['rating']) ?>/5)</h3>
<p><?= nl2br(htmlspecialchars($review['content'])) ?></p>
<hr>

<form method="post">
    <input type="hidden" name="review_id" value="<?= htmlspecialchars($review_id) ?>">
    <textarea name="content" placeholder="Write a comment..." required></textarea><br>
    <button type="submit" name="add_comment">Post Comment</button>
</form>

<h4>Comments (Admin View)</h4>
<?php if (count($comments) > 0): ?>
    <?php foreach ($comments as $comment): ?>
        <div>
            <strong><?= htmlspecialchars($comment['username']) ?></strong>:
            <?= nl2br(htmlspecialchars($comment['content'])) ?><br>
            <small><?= htmlspecialchars($comment['datecCreated']) ?></small>
            <?php if ($comment['deleted'] == 0): ?>
                | <a href="delete_comment.php?id=<?= $comment['commentID'] ?>&review_id=<?= $review_id ?>">Delete</a>
            <?php else: ?>
                | <em>Deleted</em>
            <?php endif; ?>
        </div>
        <hr>
    <?php endforeach; ?>
<?php else: ?>
    <p><em>No comments yet.</em></p>
<?php endif; ?>
</body>
</html>
