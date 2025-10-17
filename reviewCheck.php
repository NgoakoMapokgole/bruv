<?php
// review.php (Admin Side)
session_start();
require '../rate.php'; // make sure $conn = mysqli_connect(...);

if (!isset($_SESSION['userID'])) {
    die("Not authorized.");
}

// Fetch posts with user info
$sql = "
    SELECT p.postID, p.title, p.content, p.rating, p.dateCreated, 
           p.likes, p.dislikes, p.category, p.deleted, u.userName
    FROM post p
    JOIN users u ON p.userID = u.userID
    ORDER BY p.dateCreated DESC
";
$result = mysqli_query($conn, $sql);

if (!$result) {
    die("Query failed: " . mysqli_error($conn));
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin: Manage Posts</title>
    <style>
        body { font-family: Arial, sans-serif; background: #121212; color: #fff; }
        table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
        th, td { border: 1px solid rgba(255,255,255,0.2); padding: 0.8rem; text-align: left; }
        th { background: #1f1f1f; }
        tr:hover { background: rgba(255,255,255,0.05); }
        a { color: #03a9f4; text-decoration: none; }
        a:hover { text-decoration: underline; }
        .deleted { color: red; }
        .active { color: lightgreen; }
    </style>
</head>
<body>
    <h2>Admin Panel – Manage Posts</h2>
    <table>
        <thead>
            <tr>
                <th>User</th>
                <th>Title</th>
                <th>Rating</th>
                <th>Category</th>
                <th>Likes</th>
                <th>Dislikes</th>
                <th>Date</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($post = mysqli_fetch_assoc($result)): ?>
            <tr>
                <td><?= htmlspecialchars($post['userName']) ?></td>
                <td><?= htmlspecialchars($post['title']) ?></td>
                <td><?= $post['rating'] ?>/5</td>
                <td><?= htmlspecialchars($post['category']) ?></td>
                <td><?= $post['likes'] ?></td>
                <td><?= $post['dislikes'] ?></td>
                <td><?= $post['dateCreated'] ?></td>
                <td class="<?= $post['deleted'] ? 'deleted' : 'active' ?>">
                    <?= $post['deleted'] ? 'Deleted' : 'Active' ?>
                </td>
                <td>
                    <a href="commentManagement.php?review_id=<?= $post['postID'] ?>">POST</a> | 
                    <a href="commentCheck.php?review_id=<?= $post['postID'] ?>">Comments</a> | 
                    <?php if ($post['deleted'] == 0): ?>
                        <a href="review_action.php?action=delete&id=<?= $post['postID'] ?>">Delete</a>
                    <?php else: ?>
                        <a href="review_action.php?action=restore&id=<?= $post['postID'] ?>">Restore</a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</body>
</html>
