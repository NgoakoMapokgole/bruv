<?php
session_start();
require "../rate.php";
include "profileStuff.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rate It All ! | My Profile</title>
    <link rel="icon" type = "image/svg+xml" href="http://cs3-dev.ict.ru.ac.za/practicals/4a2/logo.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../mainStyle.css"/>
    <link rel="stylesheet" href="profile.css"/>
</head>
<body>
    <header>
      <?php include "../nav.php"?>
      
    </header>
    <!-- review modal form -->
    <?php include "../review.php";
    
    if (isset($_GET['msg'])) {
    echo "<p style='padding:6px auto; background:#00e054; color:#000; border:none; border-radius:4px; text-align:center'>".htmlspecialchars($_GET['msg'])."</p>";
}?>

    

    <!-- Main Content -->
    <main>
        <!-- Profile Header -->
        <section class="profile-header">
    <figure class="profile-avatar">
        <?php if (!empty($user['profPic'])): ?>
            <img src="<?= htmlspecialchars($user['profPic']) ?>" alt="Profile picture of <?= htmlspecialchars($user['userName']) ?>">
        <?php else: ?>
            <img src="default-avatar.png" alt="Default profile picture">
        <?php endif; ?>
    </figure>

    <section class="profile-info">
        <header>
            <h1><?= htmlspecialchars($user['userName'] ?? 'Unknown User') ?></h1>

            <!-- Account status -->
            <p class="profile-status">
                Status: 
                <span class="<?= strtolower($user['accountStatus'] ?? 'unknown') ?>">
                    <?= htmlspecialchars($user['accountStatus'] ?? 'Unknown') ?>
                </span>
            </p>

            <p class="profile-bio"><?= htmlspecialchars($user['bio'] ?? 'No bio available.') ?></p>
            <a href="settings.php" class="button">EDIT PROFILE</a>
        </header>

        <section class="profile-stats">
            <figure class="stat">
                <p class="stat-number"><?= count($reviews) ?></p>
                <figcaption class="stat-label">REVIEWS</figcaption>
            </figure>
            <figure class="stat">
                <p class="stat-number"><?= substr_count($user['following'] ?? '', ',') ?></p>
                <figcaption class="stat-label">FOLLOWING</figcaption>
            </figure>
            <figure class="stat">
                <p class="stat-number"><?= substr_count($user['followers'] ?? '', ',') ?></p>
                <figcaption class="stat-label">FOLLOWERS</figcaption>
            </figure>
        </section>
    </section>
</section>


        <!-- Profile Navigation -->
        <nav class="profile-nav">
            
            <button class="active" data-section="reviews">Reviews</button>
            <button data-section="favorites"> Liked Reviews</button>
            <!-- <button data-section="watchlist">Deleted posts</button> -->
            <button data-section="settings">Settings</button>
            <!-- <button id="writeReview">Write Review</button> -->
        </nav>

        <!-- Reviews Section -->
         
        <section id="reviews" class="profile-section reviews-grid active">
    <header class="section-header">
        <h2>Your Reviews</h2>
    </header>

    <section class="lists-grid">
        <?php foreach($reviews as $rev): ?>
            <article class="review-card">
                <!-- Title & Date -->
                <h3 class="review-title"><?= htmlspecialchars($rev['Title']) ?></h3>
                <time class="review-date" datetime="<?= htmlspecialchars($rev['dateCreated']) ?>">
                    <?= date("F j, Y", strtotime($rev['dateCreated'])) ?>
                </time>

                <!-- Rating & Content -->
                <p class="review-rating"><?= str_repeat('★', (int)$rev['rating']) ?></p>
                <p class="review-content"><?= nl2br(htmlspecialchars($rev['Content'])) ?></p>

                <!-- Display media if exists -->
                <?php if (!empty($rev['location'])): ?>
                    <section class="review-media">
                        <?php if ($rev['typeMedia'] === 'Images'): ?>
                            <img src="uploads/<?= htmlspecialchars($rev['location']) ?>" alt="Review Image" class="media-item">
                        <?php elseif ($rev['typeMedia'] === 'Video'): ?>
                            <video controls class="media-item">
                                <source src="uploads/<?= htmlspecialchars($rev['location']) ?>" type="video/mp4">
                            </video>
                        <?php elseif ($rev['typeMedia'] === 'Audio'): ?>
                            <audio controls class="media-item">
                                <source src="uploads/<?= htmlspecialchars($rev['location']) ?>" type="audio/mpeg">
                            </audio>
                        <?php endif; ?>
                    </section>
                <?php endif; ?>

                <!-- Tags -->
                <?php if(!empty($rev['tags'])): ?>
                    <section class="review-tags">
                        <?php 
                            $tags = explode(",", $rev['tags']);
                            foreach($tags as $tag) {
                                $trimmedTag = trim($tag);
                                if(!empty($trimmedTag)) {
                                    echo "<span class='review-tag'>" . htmlspecialchars($trimmedTag) . "</span>";
                                }
                            }
                        ?>
                    </section>
                <?php endif; ?>

                <!-- Actions -->
                <footer class="review-actions">
                    <a href="editPost.php?postID=<?= $rev['postID'] ?>" class="edit-btn">Edit</a>

                    <form method="post" action="deletePost.php" style="display:inline;" 
                          onsubmit="return confirm('Are you sure you want to delete this post?');">
                        <input type="hidden" name="postID" value="<?= $rev['postID'] ?>">
                        <button type="submit" class="delete-btn">Delete</button>
                    </form>
                </footer>
            </article>
        <?php endforeach; ?>
    </section>
</section>

        



        <!-- Favorites Section -->
        <section id="favorites" class="profile-section">
    <header class="section-header">
        <h2>Your Liked Reviews</h2>
    </header>

    <section class="lists-grid">
        <?php if(!empty($likedReviews)): ?>
            <?php foreach($likedReviews as $post): ?>
                <article class="list-card">
                    <!-- Media -->
                    <?php if(!empty($post['typeMedia']) && !empty($post['location'])): ?>
                        <?php if($post['typeMedia'] === 'Images'): ?>
                            <img src="uploads/<?= htmlspecialchars($post['location']) ?>" alt="Review Image" class="media-item">
                        <?php elseif($post['typeMedia'] === 'Video'): ?>
                            <video controls class="media-item">
                                <source src="uploads/<?= htmlspecialchars($post['location']) ?>" type="video/mp4">
                            </video>
                        <?php elseif($post['typeMedia'] === 'Audio'): ?>
                            <audio controls class="media-item">
                                <source src="uploads/<?= htmlspecialchars($post['location']) ?>" type="audio/mpeg">
                            </audio>
                        <?php endif; ?>
                    <?php endif; ?>

                    <!-- Header -->
                    <header class="list-header">
                        <h3 class="list-title"><?= htmlspecialchars($post['Title']) ?></h3>
                        <p class="list-count"><?= (int)$post['rating'] ?>★</p>
                    </header>

                    <p>by <?= htmlspecialchars($post['userName']) ?></p>
                    <p><?= htmlspecialchars(substr($post['Content'],0,100)) ?>...</p>

                    <!-- Likes / Comments -->
                    <footer class="review-actions">
                        <button class="like-btn"><i class="fas fa-heart"></i> <?= (int)$post['totalLikes'] ?></button>
                        <button class="comment-btn"><i class="fas fa-comment"></i> <?= (int)$post['totalComments'] ?></button>
                    </footer>
                </article>
            <?php endforeach; ?>
        <?php else: ?>
            <p>You haven't liked any reviews yet.</p>
        <?php endif; ?>
    </section>
</section>


        <!-- Watchlist Section -->
        <section id="watchlist" class="profile-section">
        <header class="section-header">
        <h2>Your Deleted Posts</h2>
    </header>

    <section class="lists-grid">

<?php
        if($deletedPostsResult->num_rows > 0):
            while($post = $deletedPostsResult->fetch_assoc()):
        ?>
            <article class="list-card deleted-post">
                <!-- Media -->
                <?php
                $mediaStmt = $conn->prepare("SELECT * FROM media WHERE postID = ? AND archived = 0 ORDER BY orderAppearance");
                $mediaStmt->bind_param("i", $post['postID']);
                $mediaStmt->execute();
                $mediaResult = $mediaStmt->get_result();
                if($mediaResult->num_rows > 0):
                    while($media = $mediaResult->fetch_assoc()):
                        if($media['typeMedia'] === 'Images'): ?>
                            <img src="uploads/<?= htmlspecialchars($media['location']) ?>" alt="Review Image" class="media-item">
                        <?php elseif($media['typeMedia'] === 'Video'): ?>
                            <video controls class="media-item">
                                <source src="uploads/<?= htmlspecialchars($media['location']) ?>" type="video/mp4">
                            </video>
                        <?php elseif($media['typeMedia'] === 'Audio'): ?>
                            <audio controls class="media-item">
                                <source src="uploads/<?= htmlspecialchars($media['location']) ?>" type="audio/mpeg">
                            </audio>
                        <?php endif;
                    endwhile;
                endif;
                ?>

                <header class="list-header">
                    <h3 class="list-title"><?= htmlspecialchars($post['Title']) ?></h3>
                    <p class="list-count"><?= str_repeat('★', (int)$post['rating']) ?></p>
                    <time class="review-date"><?= date("F j, Y", strtotime($post['dateCreated'])) ?></time>
                </header>

                <p><?= htmlspecialchars(substr($post['Content'],0,150)) ?>...</p>
                <p>Category: <?= htmlspecialchars($post['category']) ?></p>

                <footer class="review-actions">
                    <!-- Restore Form -->
                    <form method="post" action="restorePost.php" style="display:inline;" 
                          onsubmit="return confirm('Do you want to restore this post?');">
                        <input type="hidden" name="postID" value="<?= $post['postID'] ?>">
                        <button type="submit" class="restore-btn">Restore Post</button>
                    </form>
                </footer>
            </article>
        <?php 
            endwhile;
        else: 
        ?>
            <p>No deleted posts.</p>
        <?php endif; ?>
    </section>
</section>

        <!-- Settings Section -->
        <section id="settings" class="profile-section">
            <h2>Account Settings</h2>
            <form method="post" action="deactivate.php" onsubmit="return confirm('Are you sure you want to deactivate your account?');">
                <button type="submit" name="deactivate_account" class="button deactivate-btn">Deactivate Account</button>
            </form>

            <?php if ($user['accountStatus'] !== 'Active'): ?>
    <form method="post" action="reactivateAccount.php">
        <input type="hidden" name="userID" value="<?= $user['userID'] ?>">
        <button type="submit" class="button reactivate-btn">Reactivate Account</button>
    </form>
<?php endif; ?>
        </section>
    </main>

    <?php include "../foot.php"?>
    
    <script>
      const isLoggedIn = <?php echo isset($_SESSION['userID']) ? 'true' : 'false'; ?>;
    </script>
    <script src="../mainScript.js"></script>
    <script src="profile.js"></script>
  </body>
</body>
</html>