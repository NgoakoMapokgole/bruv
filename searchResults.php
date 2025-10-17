<?php
session_start();
include "../rate.php"; // DB connection

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category = isset($_GET['category']) ? trim($_GET['category']) : '';

// Base query
$sql = "SELECT p.postID, p.Title, p.Content, p.tags, p.dateCreated, p.rating,
               p.likes, p.dislikes, p.category, u.userName, u.userID
        FROM post p
        JOIN users u ON p.userID = u.userID
        WHERE p.deleted = 0";

// Add filters
$params = [];
$types  = "";

if (!empty($search)) {
    $sql .= " AND (p.Title LIKE ? OR p.tags LIKE ?)";
    $like = "%$search%";
    $params[] = &$like;
    $params[] = &$like;
    $types .= "ss";
}

if (!empty($category)) {
    $sql .= " AND p.category = ?";
    $params[] = &$category;
    $types .= "s";
}

$sql .= " ORDER BY p.dateCreated DESC";

$stmt = $conn->prepare($sql);

// Bind parameters dynamically if needed
if (!empty($params)) {
    array_unshift($params, $types);
    call_user_func_array([$stmt, 'bind_param'], $params);
}

$stmt->execute();
$result = $stmt->get_result();
$reviews = $result->fetch_all(MYSQLI_ASSOC);
?>

<!doctype html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Search Results | Rate It All</title>
    <link rel="stylesheet" href="../mainStyle.css"/>
</head>
<body>
    <header>
        <?php include "../nav.php"; ?>
    </header>

    <main>
        <h1>Search Results</h1>
        <p>
            Showing results for: <strong><?= htmlspecialchars($search) ?></strong>
            <?php if (!empty($category)): ?>
                in <em><?= htmlspecialchars($category) ?></em>
            <?php endif; ?>
        </p>

        <section class="reviews-grid">
            <?php if (!empty($reviews)): ?>
                <?php foreach ($reviews as $rev): ?>
                    <article class="review-card">
                        <header class="review-header">
                            <h3><?= htmlspecialchars($rev['Title']) ?></h3>
                            <p>By <a href="../personProfile.php?id=<?= $rev['userID'] ?>">
                                <?= htmlspecialchars($rev['userName']) ?></a>
                                on <?= date("F j, Y", strtotime($rev['dateCreated'])) ?>
                            </p>
                        </header>

                        <p class="review-rating"><?= str_repeat('★', $rev['rating']) ?></p>
                        <p><?= nl2br(htmlspecialchars($rev['Content'])) ?></p>

                        <!-- Tags -->
                        <?php if (!empty($rev['tags'])): ?>
                            <section class="review-tags">
                                <?php foreach (explode(",", $rev['tags']) as $tag): ?>
                                    <?php $tag = trim($tag); if (!empty($tag)): ?>
                                        <span class="review-tag"><?= htmlspecialchars($tag) ?></span>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </section>
                        <?php endif; ?>

                        <!-- Actions -->
                        <section class="review-actions">
                            <form method="POST" action="../likeHandler.php">
                                <input type="hidden" name="postID" value="<?= $rev['postID'] ?>">
                                <button type="submit" name="action" value="like">
                                    👍 <?= $rev['likes'] ?>
                                </button>
                                <button type="submit" name="action" value="dislike">
                                    👎 <?= $rev['dislikes'] ?>
                                </button>
                            </form>
                        </section>
                    </article>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No results found.</p>
            <?php endif; ?>
        </section>
    </main>

    <?php include "../foot.php"; ?>
</body>
</html>
