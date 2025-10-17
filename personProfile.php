<?php
session_start();
require "rate.php";
include "personProfileStuff.php"; // This should define $user and $posts
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rate It All | <?= htmlspecialchars($user['userName'] ?? 'Unknown') ?></title>
    <link rel="icon" type = "image/svg+xml" href="http://cs3-dev.ict.ru.ac.za/practicals/4a2/logo.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="mainStyle.css"/>
    <link rel="stylesheet" href="personProfile.css"/>
</head>
<body>
    <header>
        <?php include "nav.php"; ?>
    </header>

    <?php include "review.php"; ?>

    <main>
        <!-- Profile Header -->
        <section class="profile-header">
            <section class="profile-banner"></section>
            <figure class="profile-avatar">
                <?php if (!empty($user['profPic'])): ?>
                    <img src="<?= "homepage/".htmlspecialchars($user['profPic']) ?>" alt="Profile picture of <?= htmlspecialchars($user['userName']) ?>">
                <?php else: ?>
                    <img src="default-avatar.png" alt="Default profile picture">
                <?php endif; ?>
            </figure>

            <section class="profile-info">
                <h1><?= htmlspecialchars($user['userName'] ?? 'Unknown') ?></h1>
                <p class="profile-bio"><?= htmlspecialchars($user['bio'] ?? '') ?></p>

                <section class="profile-actions">
                    <button id="btn-follow" data-userid="<?= $user['userID'] ?>" class="btn-follow">
                        <?php
                        // Check if logged-in user is following this profile
                        $loggedInID = $_SESSION['userID'] ?? 0;
                        $followersList = !empty($user['followers']) ? explode(',', $user['followers']) : [];
                        echo in_array($loggedInID, $followersList) ? 'Unfollow' : 'Follow';
                        ?>
                    </button>

                    <button class="btn-message" onclick="window.location.href='message.php?userID=<?= $user['userID'] ?>'">
                        Message
                    </button>

                </section>

                <section class="profile-stats">
                    <figure class="stat">
                        <p class="stat-number"><?= count($posts) ?></p>
                        <figcaption class="stat-label">REVIEWS</figcaption>
                    </figure>
                    <figure class="stat">
                        <p class="stat-number"><?= number_format($avgRating, 1) ?></p>
                        <figcaption class="stat-label">AVG RATING</figcaption>
                    </figure>
                    <figure class="stat">
                        <p class="stat-number"><?= count($followers) ?></p>
                        <figcaption class="stat-label">FOLLOWERS</figcaption>
                    </figure>
                    <figure class="stat">
                        <p class="stat-number"><?= count($following) ?></p>
                        <figcaption class="stat-label">FOLLOWING</figcaption>
                    </figure>
                </section>
            </section>
        </section>

        <!-- Profile Navigation -->
        <nav class="profile-nav">
            <button class="active" data-section="reviews">Reviews</button>
            <button data-section="lists">Liked Reviews</button>
            <button data-section="social">Activity</button>
            <button data-section="badges">Socials</button>
            <button data-section="stats">Stats for Nerds</button>
        </nav>

        <!-- Reviews Section -->
        <section class="reviews-grid" id="reviews">
            <header class="section-header">
                <h2><?= htmlspecialchars($user['userName'] ?? 'Unknown') ?>'s Reviews</h2>
            </header>
            <?php foreach ($posts as $post): ?>
                <article class="review-card">
                    <header class="review-header">
                        <section class="review-media">
                            <figure class="review-cover"><?= strtoupper(substr($post['Title'],0,1)) ?></figure>
                            <header>
                                <h3 class="review-title"><?= htmlspecialchars($post['Title']) ?></h3>
                                <time class="review-date" datetime="<?= $post['dateCreated'] ?>">
                                    <?= date("M d, Y", strtotime($post['dateCreated'])) ?>
                                </time>
                                
                            </header>
                        </section>
                    </header>

                    <p class="review-rating">
                        <?= str_repeat('★', $post['rating']) . str_repeat('☆', 5 - $post['rating']) ?>
                    </p>
                    <p class="review-content"><?= htmlspecialchars($post['Content']) ?></p>
                    <?php
                              
                                if (gettype($post['Media'])!="integer") echo '<img style="width:30%;" src="'.htmlspecialchars($post['Media']).'" alt=review-picture>';
                                ?>
                    <section class="review-tags">
                        <?php 
                        if(!empty($post['tags'])):
                            $tags = explode(',', $post['tags']);
                            foreach($tags as $tag): ?>
                                <span class="review-tag"><?= htmlspecialchars(trim($tag)) ?></span>
                        <?php endforeach; endif; ?>
                        
                    </section>

                    <footer class="review-actions">
                        <button class="like-btn"><i class="fas fa-heart"></i> <?= $post['totalLikes'] ?? 0 ?></button>
                        <button class="comment-btn"><i class="fas fa-comment"></i> <?= $post['totalComments'] ?? 0 ?></button>
                    </footer>
                </article>
            <?php endforeach; ?>
        </section>

        <!-- Liked Reviews Section -->
        <section id="lists" class="profile-section">
            <header class="section-header">
                <h2><?= htmlspecialchars($user['userName'] ?? 'Unknown') ?>'s Liked Reviews</h2>
            </header>

            <section class="lists-grid">
                <?php if(!empty($likedReviews)): ?>
                    <?php foreach($likedReviews as $post): ?>
                        <article class="list-card">
                            <header class="list-header">
                                <h3 class="list-title"><?= htmlspecialchars($post['Title']) ?></h3>
                                <p class="list-count"><?= $post['rating'] ?>★</p>
                            </header>
                            <p>by <?= htmlspecialchars($post['userName']) ?></p>
                            <p><?= htmlspecialchars(substr($post['Content'],0,100)) ?>...</p>
                            <footer class="review-actions">
                                <button class="like-btn"><i class="fas fa-heart"></i> <?= $post['totalLikes'] ?></button>
                                <button class="comment-btn"><i class="fas fa-comment"></i> <?= $post['totalComments'] ?></button>
                            </footer>
                        </article>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>This user hasn't liked any reviews yet.</p>
                <?php endif; ?>
            </section>
        </section>


        <section id="social" class="profile-section">
            <header class="section-header">
                <h2><?= htmlspecialchars($user['userName'] ?? 'Unknown') ?>'s Activity</h2>
            </header>

            <section class="activity-feed">
                <?php if(!empty($activities)): ?>
                    <?php foreach($activities as $act): ?>
                        <article class="activity-item">
                            <figure class="activity-avatar">
                                <?= strtoupper(substr($act['userName'] ?: $act['targetUser'],0,2)) ?>
                            </figure>
                            <section class="activity-content">
                                <p class="activity-text">
                                    <?php
                                    switch($act['type']){
                                        case 'review':
                                            echo "<strong>{$act['userName']}</strong> wrote a new review: <em>{$act['title']}</em>";
                                            break;
                                        case 'like':
                                            echo "<strong>{$act['userName']}</strong> liked a review: <em>{$act['title']}</em>";
                                            break;
                                        case 'comment':
                                            echo "<strong>{$act['userName']}</strong> commented on <em>{$act['title']}</em>: \"{$act['content']}\"";
                                            break;
                                        case 'follow':
                                            echo "<strong>{$act['userName']}</strong> followed <strong>{$act['targetUser']}</strong>";
                                            break;
                                    }
                                    ?>
                                </p>
                                <time class="activity-time" datetime="<?= $act['created_at'] ?>">
                                    <?= date("M d, Y H:i", strtotime($act['created_at'])) ?>
                                </time>
                            </section>
                        </article>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No activity yet.</p>
                <?php endif; ?>
            </section>
        </section>


        <section id="badges" class="profile-section">
            <header class="section-header">
                <h2>Who <?= htmlspecialchars($user['userName'] ?? 'Unknown') ?>'s Following</h2>
            </header>

            <section class="followers-grid">
                <?php if(!empty($followingList)): ?>
                    <?php foreach($followingList as $f): ?>
                        <article class="follower-card">
                            <figure class="follower-avatar">
                                <?php if (!empty($f['profPic'])): ?>
                                    <img src="<?= "homepage/".htmlspecialchars($f['profPic']) ?>" alt="<?= htmlspecialchars($f['userName']) ?>'s avatar">
                                <?php else: ?>
                                    <i class="fas fa-user avatar-placeholder"></i>
                                <?php endif; ?>
                            </figure>
                            <p class="follower-name"><?= htmlspecialchars($f['userName']) ?></p>
                        </article>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>This user isn’t following anyone yet.</p>
                <?php endif; ?>
                </section>
        </section>

        <section id="stats" class="profile-section">
            <header class="section-header">
                <h2><?= htmlspecialchars($user['userName'] ?? 'Unknown') ?>'s Stats for the Nerds</h2>
            </header>

            <section class="stats-grid">
                <section class="stat-card">
                    <h3>Total Reviews</h3>
                    <p><?= $stats['totalReviews'] ?></p>
                </section>

                <section class="stat-card">
                    <h3>Average Rating</h3>
                    <p><?= $stats['avgRating'] ?> ★</p>
                </section>

                <section class="stat-card">
                    <h3>Total Likes</h3>
                    <p><?= $stats['totalLikes'] ?></p>
                </section>

                <section class="stat-card">
                    <h3>Total Dislikes</h3>
                    <p><?= $stats['totalDislikes'] ?></p>
                </section>

                <section class="stat-card">
                    <h3>Posts with Media</h3>
                    <p><?= $stats['postsWithMedia'] ?></p>
                </section>

                <section class="stat-card">
                    <h3>Comments Received</h3>
                    <p><?= $stats['totalComments'] ?></p>
                </section>

                <section class="stat-card">
                    <h3>Followers</h3>
                    <p><?= $stats['followers'] ?></p>
                </section>

                <section class="stat-card">
                    <h3>Following</h3>
                    <p><?= $stats['following'] ?></p>
                </section>

                <section class="stat-card">
                    <h3>Category Breakdown</h3>
                    <ul>
                        <?php foreach($stats['categories'] as $cat => $count): ?>
                            <li><?= htmlspecialchars($cat) ?>: <?= $count ?></li>
                        <?php endforeach; ?>
                    </ul>
                </section>
            </section>
        </section>

    </main>

    <?php include "foot.php"; ?>
    <script src="mainScript.js"></script>
    <script src="personProfile.js"></script>
</body>
</html>
