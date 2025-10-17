<?php
session_start();
include "../rate.php"; 


// Get post ID
$postID = $_GET['id'] ?? null;
if (!$postID || !is_numeric($postID)) {
    die("Invalid post ID.");
}



$showComments = isset($_GET['showComments']) && $_GET['showComments'] == 1;

// Fetch post
$postSql = "SELECT * FROM post WHERE deleted = 0 AND postID = ?";
$postStmt = $conn->prepare($postSql);
$postStmt->bind_param("i", $postID);
$postStmt->execute();
$postResult = $postStmt->get_result();

if ($postResult->num_rows === 0) {
    die("Post not found.");
}
$post = $postResult->fetch_assoc();

$postLiked = false;
$postDisliked = false;

if (isset($_SESSION['userID'])) {
    $uid = $_SESSION['userID'];

    // Check like
    $stmt = $conn->prepare("SELECT id FROM likes WHERE user_id=? AND review_id=?");
    $stmt->bind_param("ii", $uid, $postID);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) $postLiked = true;

    // Check dislike
    $stmt = $conn->prepare("SELECT id FROM dislikes WHERE user_id=? AND review_id=?");
    $stmt->bind_param("ii", $uid, $postID);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) $postDisliked = true;
}



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


// ==================== COMMENTS HANDLING ====================


function fetchComments($conn, $postID, $parentID = NULL) {
    $sql = "
        SELECT c.*, u.userName, u.profPic 
        FROM comments c
        JOIN users u ON c.userID = u.userID
        WHERE c.postID = ? AND c.deleted = 0
    ";
    if (is_null($parentID)) {
        $sql .= " AND c.replyID IS NULL";
    } else {
        $sql .= " AND c.replyID = ?";
    }
    $sql .= " ORDER BY c.datecCreated ASC"; // oldest first

    $stmt = $conn->prepare($sql);
    if (is_null($parentID)) {
        $stmt->bind_param("i", $postID);
    } else {
        $stmt->bind_param("ii", $postID, $parentID);
    }
    $stmt->execute();
    return $stmt->get_result();
}
?>

<?php
// Check if current user already reported this post
$flagDisabled = false;
if (isset($_SESSION['userID'])) {
    $userID = $_SESSION['userID'];
    $checkFlagSql = "SELECT * FROM reports WHERE userID = ? AND postID = ? LIMIT 1";
    $checkFlagStmt = $conn->prepare($checkFlagSql);
    $checkFlagStmt->bind_param("ii", $userID, $postID);
    $checkFlagStmt->execute();
    $checkFlagResult = $checkFlagStmt->get_result();
    if ($checkFlagResult->num_rows > 0) {
        $flagDisabled = true;
    }
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($post['Title']); ?> | Rate It All</title>
    <link rel="icon" type = "image/svg+xml" href="http://cs3-dev.ict.ru.ac.za/practicals/4a2/logo.png">
    <link rel="stylesheet" href="../mainStyle.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>
        body { background: #14181c; font-family: 'Lexend', sans-serif; color: #ccd; }
        main.full-review { max-width: 900px; margin: 2rem auto; }

        /* Header with poster and title */
        .review-header { display: flex; gap: 2rem; align-items: flex-start; margin-bottom: 1rem; }
        .review-image img { width: 200px; border-radius: 8px; object-fit: cover; box-shadow: 0 4px 15px rgba(0,0,0,0.5); }
        .review-title { font-size: 2rem; font-weight: 600; margin: 0; }

        /* Author styling */
        .review-author { display: flex; align-items: center; gap: 0.5rem; margin-top: 0.3rem; font-size: 0.9rem; color: #9ab; }
        .review-author .author-profPic { width: 32px; height: 32px; border-radius: 50%; object-fit: cover; box-shadow: 0 2px 5px rgba(0,0,0,0.3); }
        .review-author .author-name { color: #bdc007; text-decoration: none; font-weight: 500; }
        .review-author .author-name:hover { color: #bdc007; }

        .review-meta { font-size: 0.9rem; color: #9ab; margin-top: 0.3rem; }

        /* Content & media */
        .review-content { margin-top: 1rem; line-height: 1.6; font-size: 1rem; color: #ddd; }
        .review-media { margin-top: 1.5rem; display: flex; flex-direction: column; gap: 1rem; }
        .review-media img, .review-media video, .review-media audio { border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.4); }

        /* Tags */
        .review-tags { display: flex; flex-wrap: wrap; gap: 0.4rem; margin-top: 1rem; }
        .review-tag { font-size: 0.8rem; color: #9ab; background: #2c3440; padding: 3px 8px; border-radius: 4px; text-decoration: none; }

        /* Actions */
        .review-actions { display: flex; gap: 1rem; margin-top: 1.5rem; font-size: 0.95rem; justify-content: space-between;color: #9ab}
        .like-btn, .dislike-btn { background: transparent; border: none; cursor: pointer; color: #9ab; font-size: 0.95rem; }
        .like-btn:hover, .dislike-btn:hover { color: #bdc007; }
        .comments-section h2 { color:#fff; margin-bottom:1rem; }

.add-comment textarea, .reply-form textarea {
    width: 100%; padding:8px; margin-bottom:5px; border-radius:8px; border:none; resize:none; background:#2c3440; color:#fff;
}

.add-comment button, .reply-form button {
    padding:6px 12px; background:#bdc007; color:#000; border:none; border-radius:6px; cursor:pointer;
}

.comment {
    background:#2c3440; border-radius:12px; padding:12px; margin-bottom:12px;
}

.comment.reply { margin-left:40px; background:#333a4a; }

.comment-header { display:flex; align-items:center; gap:0.5rem; margin-bottom:6px; }
.comment-avatar { width:32px; height:32px; border-radius:50%; object-fit:cover; }
.comment-timestamp { margin-left:auto; font-size:0.75rem; color:#9ab; }

.comment-content { margin-bottom:6px; color:#ddd; }

hr{border-color:#333a4a; }

.comment-actions { display:flex; gap:1rem; font-size:0.85rem; color:#9ab; }
.comment-actions button { background:transparent; border:none; cursor:pointer; color:#bdc007; }
.comment-actions button:hover { color:#00e054; }
.comment-actions a.reply-link { cursor:pointer; color:#00e054; text-decoration:none; }
.comment-actions a.reply-link:hover { text-decoration:underline; }

.replies { margin-top:8px; }
.flag-btn-wrapper {
    position: relative;
    display: inline-block;
}

.flag-btn-wrapper .flag-btn {
    background: transparent;
    border: none;
    font-size: 1.2rem;
    cursor: pointer;
    color: #9ab
}

.flag-btn-wrapper::after {
    content: "Report";
    position: absolute;
    bottom: 125%; /* show above the button */
    left: 50%;
    transform: translateX(-50%);
    background: #333;
    color: #fff;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 0.8rem;
    white-space: nowrap;
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.2s;
    z-index: 10;
}

.flag-btn-wrapper:hover::after {
    opacity: 1;
}
.like-btn.active { color: #00e054; }
.dislike-btn.active { color: #ff5252; }


    </style>
</head>
<body>
    <header>
        <?php include "../nav.php"; ?>

    </header>
    <!-- review modal form -->
    <?php include "../review.php"?>
    <section id="flashMessage" class="flash-message"></section>

<?php
if (isset($_GET['msg'])) {
    echo "<p style='padding:6px auto; background:#00e054; color:#000; border:none; border-radius:4px; text-align:center'>".htmlspecialchars($_GET['msg'])."</p>";
}?>

    <main class="full-review">
        <!-- Header with poster and title -->
        <div class="review-header">
            <?php
            // Display first image as poster if exists
            $posterShown = false;
            foreach ($mediaItems as $media) {
                if ($media['typeMedia'] === 'Images') {
                    echo '<div class="review-image"><img src="' . "../".htmlspecialchars($media['location']) . '" alt="' . htmlspecialchars($post['Title']) . '"></div>';
                    $posterShown = true;
                    break; // only first image
                }
            }
            ?>
            <div>
                <h1 class="review-title"><?php echo htmlspecialchars($post['Title']); ?></h1>

                <!-- Author -->
                <div class="review-author">
                    <?php if(!empty($author['profPic'])): ?>
                        <img src="<?php echo htmlspecialchars($author['profPic']); ?>" alt="<?php echo htmlspecialchars($author['userName']); ?>" class="author-profPic">
                    <?php endif; ?>
                    <a href="../personProfile.php?userID=<?php echo $post['userID']; ?>" class="author-name">
                        <?php echo htmlspecialchars($author['userName']); ?>
                    </a>
                </div>

                <!-- Meta -->
                <div class="review-meta">
                    <span><?php echo date("M d, Y", strtotime($post['dateCreated'])); ?></span> |
                    <span>⭐ <?php echo $post['rating']; ?>/5</span> |
                    <span><?php echo htmlspecialchars($post['category']); ?></span>
                </div>
            </div>
        </div>

        <!-- Full review content -->
        <div class="review-content">
            <?php echo nl2br(htmlspecialchars($post['Content'])); ?>
        </div>

        <!-- Additional media -->
        <div class="review-media">
            <?php foreach ($mediaItems as $media):
                if ($posterShown && $media['typeMedia'] === 'Images') continue; // skip poster again
                if ($media['typeMedia'] === 'Images'): ?>
                    <img src="<?php echo "../".htmlspecialchars($media['location']); ?>" alt="">
                <?php elseif ($media['typeMedia'] === 'Video'): ?>
                    <video controls>
                        <source src="<?php echo "../".htmlspecialchars($media['location']); ?>" type="video/mp4">
                        Your browser does not support the video tag.
                    </video>
                <?php elseif ($media['typeMedia'] === 'Audio'): ?>
                    <audio controls>
                        <source src="<?php echo "../".htmlspecialchars($media['location']); ?>" type="audio/mpeg">
                        Your browser does not support the audio element.
                    </audio>
                <?php endif;
            endforeach; ?>
        </div>

        <!-- Tags -->
        <div class="review-tags">
            <?php foreach (explode(",", $post['tags']) as $tag):
                $tag = trim($tag);
                if ($tag): ?>
                <a href="search.php?search=<?php echo urlencode($tag); ?>" class="review-tag"><?php echo htmlspecialchars($tag); ?></a>
            <?php endif; endforeach; ?>
        </div>

        <!-- Likes / Dislikes -->
        <div class="review-actions">
            <!-- Likes / Dislikes -->
<div class="review-actions">
    <form action="like_dislike_action.php" method="POST" style="display:inline;">
        <input type="hidden" name="action" value="like">
        <input type="hidden" name="postID" value="<?php echo $postID; ?>">
        <button class="like-btn <?php echo $postLiked ? 'active' : ''; ?>" type="submit">
            <i class="fa fa-thumbs-up"></i> <?php echo $post['likes']; ?>
        </button>
    </form>

    <form action="like_dislike_action.php" method="POST" style="display:inline;">
        <input type="hidden" name="action" value="dislike">
        <input type="hidden" name="postID" value="<?php echo $postID; ?>">
        <button class="dislike-btn <?php echo $postDisliked ? 'active' : ''; ?>" type="submit">
            <i class="fa fa-thumbs-down"></i> <?php echo $post['dislikes']; ?>
        </button>
    </form>
</div>


            <div class="flag-btn-wrapper">
                <?php
$showFlagForm = isset($_GET['flag']) && ($_GET['postID'] ?? null) == $postID;

?>

<div class="flag-btn-wrapper">
    <?php if ($flagDisabled): ?>
        <button class="flag-btn" disabled style="color:gray; cursor:not-allowed;">
            <i class="fab fa-font-awesome-flag"></i> Reported
        </button>
    <?php else: ?>
        <?php if (!$showFlagForm): ?>
            <a href="?id=<?= $postID ?>&flag=1&postID=<?= $postID ?>">
                <button class="flag-btn" style="color:red;"><i class="fab fa-font-awesome-flag"></i></button>
            </a>
        <?php else: ?>
            <form method="post" action="submitFlag.php" style="margin-top:0.5rem;">
                <input type="hidden" name="postID" value="<?= $postID ?>">
                <label for="reportType">Reason:</label>
                <select name="reportType" required style="margin-bottom:0.5rem;">
                    <option value="">Select Reason</option>
                    <option value="Spam">Spam</option>
                    <option value="Offensive content">Offensive</option>
                    <option value="Misleading">Misleading</option>
                    <option value="Copyright violation">Copyright</option>
                    <option value="Duplicate content">Duplicate</option>
                    <option value="Other">Other</option>
                </select>
                <textarea name="description" placeholder="Describe the issue..." required style="width:100%; padding:6px; margin-bottom:0.5rem; border-radius:6px; border:none; background:#2c3440; color:#fff;"></textarea>
                
                <div style="display:flex; gap:0.5rem;">
                    <button type="submit" style="padding:6px 12px; background:#00e054; color:#000; border:none; border-radius:6px;">Submit Report</button>
                    <a href="?id=<?= $postID ?>" style="text-decoration:none;">
                        <button type="button" style="padding:6px 12px; background:#ff5252; color:#fff; border:none; border-radius:6px; cursor:pointer;">Cancel</button>
                    </a>
                </div>
            </form>
        <?php endif; ?>
    <?php endif; ?>
</div>


</div>


            </div>
        </div>
        
    </main>

    <!-- Comments Section -->
<section class="comments-section" style="margin-top: 3rem; max-width: 900px; margin-left:auto; margin-right:auto;">
    <h2>Comments</h2>

    <?php if(isset($_SESSION['userID'])): ?>
<div class="add-comment" style="margin-bottom:2rem; max-width:900px; margin-left:auto; margin-right:auto;">
    <h3>Add a Comment</h3>
    <form action="addComment.php" method="POST">
        <input type="hidden" name="postID" value="<?php echo $postID; ?>">
        <textarea name="content" placeholder="Write your comment..." required style="width:100%; padding:8px; margin-bottom:5px;"></textarea>
        <button type="submit" style="padding:6px 12px; background:#00e054; color:#000; border:none; border-radius:4px;">Post Comment</button>
    </form>
</div>
<?php endif; ?>

<a href="#" id="toggle-comments" style="color:#00e054; text-decoration:none; margin-bottom:1rem; display:inline-block;">
    <?php echo $showComments ? 'Hide Comments' : 'View Comments...'; ?>
</a>


<div id="comments-container" style="display: <?php echo $showComments ? 'block' : 'none'; ?>;">
    <?php
    $topComments = fetchComments($conn, $postID);
    while ($comment = $topComments->fetch_assoc()):
    ?>
    <div class="review-card" style="margin-bottom: 1rem;">
        <div style="display:flex; gap:0.5rem; align-items:flex-start;">
            <?php if(!empty($comment['profPic'])): ?>
                <img src="<?php echo htmlspecialchars($comment['profPic']); ?>" class="reviewer-profPic" style="width:40px;height:40px;border-radius:50%;object-fit:cover;">
            <?php endif; ?>
            <div style="flex:1;">
                <div>
                    <strong><?php echo htmlspecialchars($comment['userName']); ?></strong>
                    <span style="color:#9ab; font-size:0.8rem; margin-left:0.5rem;"><?php echo date("M j, Y H:i", strtotime($comment['datecCreated'])); ?></span>
                </div>
                <p style="margin:0.3rem 0;"><?php echo nl2br(htmlspecialchars($comment['content'])); ?></p>
                <?php if(!empty($comment['image'])): ?>
                    <img src="<?php echo htmlspecialchars($comment['image']); ?>" style="max-width:200px; border-radius:6px; margin-top:0.5rem;">
                <?php endif; ?>
                <div class="comment-actions">
                    <button class="like-btn"><i class="fa fa-thumbs-up"></i> <?php echo $comment['like']; ?></button>
                    <button class="dislike-btn"><i class="fa fa-thumbs-down"></i> <?php echo $comment['dislike']; ?></button>
                    <?php if(isset($_SESSION['userID'])): ?>
                        <a href="#" class="reply-link" data-commentid="<?php echo $comment['commentID']; ?>">Reply</a>
                    <?php endif; ?>
                    
                </div>

                <!-- Replies -->
                <div class="replies" >
                    <?php
                    $replies = fetchComments($conn, $postID, $comment['commentID']);
                    while($reply = $replies->fetch_assoc()):
                    ?>
                    <div class="review-card reply-card" style="display:flex; gap:0.5rem; margin-bottom:0.5rem;">
                        <?php if(!empty($reply['profPic'])): ?>
                            <img src="<?php echo htmlspecialchars($reply['profPic']); ?>" class="reviewer-profPic" style="width:32px;height:32px;border-radius:50%;object-fit:cover;">
                        <?php endif; ?>
                        <div>
                            <div><strong><?php echo htmlspecialchars($reply['userName']); ?></strong>
                                <span style="color:#9ab; font-size:0.75rem; margin-left:0.3rem;"><?php echo date("M j, Y H:i", strtotime($reply['datecCreated'])); ?></span>
                            </div>
                            <p style="margin:0.2rem 0;"><?php echo nl2br(htmlspecialchars($reply['content'])); ?></p>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>

                <!-- Reply form (hidden) -->
                <?php if(isset($_SESSION['userID'])): ?>
                <form action="addComment.php" method="POST" class="reply-form" style="display:none; margin-top:0.5rem;">
                    <input type="hidden" name="postID" value="<?php echo $postID; ?>">
                    <input type="hidden" name="replyID" value="<?php echo $comment['commentID']; ?>">
                    <textarea name="content" placeholder="Write a reply..." required style="width:100%; padding:5px; margin-bottom:3px;"></textarea>
                    <button type="submit" style="padding:4px 8px; background:#00e054; color:#000; border:none; border-radius:4px;">Reply</button>
                </form>
                <?php endif; ?>
                
                <hr>
            </div>
        </div>
    </div>
    <?php endwhile; ?>
</div>
</section>



    <?php include "../foot.php"; ?>
</body>
<script>
document.querySelectorAll('.reply-link').forEach(link => {
    link.addEventListener('click', e => {
        e.preventDefault();
        const form = link.closest('.review-card').querySelector('.reply-form');
        form.style.display = form.style.display === 'none' ? 'block' : 'none';
    });
});

const toggleLink = document.getElementById('toggle-comments');
const commentsContainer = document.getElementById('comments-container');

toggleLink.addEventListener('click', (e) => {
    e.preventDefault();
    if (commentsContainer.style.display === 'none') {
        commentsContainer.style.display = 'block';
        toggleLink.textContent = 'Hide Comments';
    } else {
        commentsContainer.style.display = 'none';
        toggleLink.textContent = 'View Comments...';
    }
});

</script>

<script>
        const isLoggedIn = <?php echo isset($_SESSION['userID']) ? 'true' : 'false'; ?>;
        const userId = <?php echo isset($_SESSION['userID']) ? $_SESSION['userID'] : 'null'; ?>;

        const reviewStatus = <?php
        if(isset($_SESSION['reviewStatus'])) {
            echo json_encode($_SESSION['reviewStatus']);
            unset($_SESSION['reviewStatus']); // clear flash after reading
        } else {
            echo 'null';
        }
        ?>;
    </script>
    <script src="../mainScript.js"></script>
    <script src="home.js"></script>
</body>


</html>
