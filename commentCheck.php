<!DOCTYPE html>
<?php
session_start();
include "../rate.php";

// --- Access control (Admin or Mod only) ---
if (!isset($_SESSION['userID']) || ($_SESSION['role'] != "Admin" && $_SESSION['role'] != "Mod")) {
    echo "<script>alert('Access denied. Please log in as Admin or Mod.'); window.location='../../homepage/login.php';</script>";
    exit;
}

// --- Validate post ID ---
if (!isset($_GET['review_id'])) {
    die("No post selected.");
}
$postID = intval($_GET['review_id']);

// --- Fetch the post itself ---
$stmt = $conn->prepare("
    SELECT p.postID, p.content AS postContent, p.dateCreated, u.username
    FROM post p
    JOIN users u ON p.userID = u.userID
    WHERE p.postID = ?
");
$stmt->bind_param("i", $postID);
$stmt->execute();
$postResult = $stmt->get_result();
$post = $postResult->fetch_assoc();
$stmt->close();

if (!$post) {
    die("Post not found.");
}

// --- Fetch all comments for the post (with report info if any) ---
$stmt = $conn->prepare("
    SELECT c.commentID, c.content AS commentContent, c.datecCreated, c.deleted, u.username,
           r.reportID
    FROM comments c
    JOIN users u ON c.userID = u.userID
    LEFT JOIN reports r ON r.commentID = c.commentID
    WHERE c.postID = ?
    ORDER BY c.datecCreated ASC
");
$stmt->bind_param("i", $postID);
$stmt->execute();
$commentsResult = $stmt->get_result();
$comments = [];
while ($row = $commentsResult->fetch_assoc()) {
    $comments[] = $row;
}
$stmt->close();
?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin: View Comments per Post</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 30px;
        }
        .post {
            border: 2px solid #333;
            padding: 15px;
            background: #f8f8f8;
            border-radius: 10px;
        }
        .comment {
            border-left: 4px solid #007bff;
            margin: 15px 0;
            padding: 10px 15px;
            background: #fff;
            border-radius: 8px;
        }
        .comment.deleted {
            opacity: 0.6;
            font-style: italic;
        }
        .flagged {
            border-left-color: red;
            background: #ffe5e5;
        }
        .flagged::before {
            content: "⚠️ ";
            color: red;
            font-weight: bold;
        }
        .username {
            font-weight: bold;
        }
        .timestamp {
            font-size: 0.9em;
            color: #777;
        }
    </style>
</head>
<body>

    <h2>Post #<?= htmlspecialchars($postID) ?></h2>
    <div class="post">
        <p><strong>Posted by:</strong> <?= htmlspecialchars($post['username']) ?></p>
        <p><em><?= htmlspecialchars($post['dateCreated']) ?></em></p>
        <p><?= nl2br(htmlspecialchars($post['postContent'])) ?></p>
    </div>

    <h3>Comments</h3>
    <?php if (count($comments) > 0): ?>
        <?php foreach ($comments as $comment): ?>
            <div class="comment <?= $comment['deleted'] ? 'deleted' : '' ?> <?= $comment['reportID'] ? 'flagged' : '' ?>">
                <div class="username"><?= htmlspecialchars($comment['username']) ?></div>
                <div class="timestamp"><?= htmlspecialchars($comment['datecCreated']) ?></div>
                <p><?= nl2br(htmlspecialchars($comment['commentContent'])) ?></p>

                <?php if ($comment['reportID']): ?>
                    <p><strong>Report reason:</strong> <?= htmlspecialchars($comment['reason']) ?></p>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p><em>No comments yet for this post.</em></p>
    <?php endif; ?>

</body>
</html>
