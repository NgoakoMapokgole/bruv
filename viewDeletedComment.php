<?php
session_start();
include "../rate.php";

// --- Only Admins allowed ---
if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../homepage/login.php");
    exit();
}

$userName = $_SESSION['userName'];

// Get comment ID
$commentID = $_GET['id'] ?? null;
if (!$commentID || !is_numeric($commentID)) {
    die("Invalid comment ID.");
}

// Fetch comment
$sql = "
    SELECT c.*, u.userName AS authorName, u.profPic AS authorPic, p.Title AS postTitle, p.postID AS postID, p.content AS postContent
    FROM comments c
    JOIN users u ON c.userID = u.userID
    JOIN post p ON c.postID = p.postID
    WHERE c.commentID = ?
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $commentID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Comment not found.");
}

$comment = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin - View Deleted Comment</title>
<style>
body { font-family: 'Segoe UI', sans-serif; background:#121212; color:#fff; margin:0; padding:2rem; }
.container { max-width:900px; margin:0 auto; background:#1f1f1f; padding:2rem; border-radius:10px; }
h1, h2 { color:#bdc007; }
.comment-content, .post-content { background:#2c2c2c; padding:1rem; border-radius:8px; margin-bottom:1rem; }
.comment-content.deleted { border-left:4px solid #f44336; }
.comment-author, .post-author { font-weight:600; margin-bottom:0.5rem; }
.actions { margin-top:1rem; }
.actions form { display:inline-block; margin-right:0.5rem; }
.actions button { padding:6px 12px; border:none; border-radius:6px; cursor:pointer; font-weight:600; }
.btn-restore { background:#4caf50; color:#000; }
.btn-delete { background:#f44336; color:#fff; }
a { color:#03a9f4; text-decoration:none; }
a:hover { text-decoration:underline; }
</style>
</head>
<body>

<div class="container">
    <h1>Deleted Comment</h1>
    <div class="comment-content deleted">
        <div class="comment-author">
            Author: @<?= htmlspecialchars($comment['authorName']) ?>
        </div>
        <div class="comment-text">
            <?= nl2br(htmlspecialchars($comment['content'])) ?>
        </div>
        <div class="comment-meta">
            Date: <?= date("M d, Y H:i", strtotime($comment['datecCreated'])) ?> |
            Likes: <?= $comment['like'] ?> |
            Dislikes: <?= $comment['dislike'] ?>
        </div>
    </div>

    <h2>Associated Post</h2>
    <div class="post-content">
        <div class="post-title">
            <a href="../HomePage/viewPost.php?id=<?= $comment['postID'] ?>">
                <?= htmlspecialchars($comment['postTitle']) ?>
            </a>
        </div>
        <div class="post-text">
            <?= nl2br(htmlspecialchars(substr($comment['postContent'], 0, 300))) ?><?= strlen($comment['postContent']) > 300 ? '...' : '' ?>
        </div>
    </div>

    <div class="actions">
        <form action="commentAction.php" method="POST">
            <input type="hidden" name="commentID" value="<?= $comment['commentID'] ?>">
            <input type="hidden" name="action" value="restore">
            <button class="btn-restore" type="submit">Restore Comment</button>
        </form>
    </div>

    <p style="margin-top:1rem;"><a href="adminComments.php">Back to Comments List</a></p>
</div>

</body>
</html>
