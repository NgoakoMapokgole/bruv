<?php
session_start();
require "../rate.php"; 
include "home.php";
?>

<!doctype html> 
<html> 
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rate It All ! | Home Page</title>
    <link rel="icon" type = "image/svg+xml" href="http://cs3-dev.ict.ru.ac.za/practicals/4a2/logo.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../mainStyle.css"/>
    <link rel="stylesheet" href="home.css"/>
</head>

<body>
    <header>
        <?php include "../nav.php"?>
    </header>
    
    <!-- review modal form -->
    <?php include "../review.php"?>
    <section id="flashMessage" class="flash-message"></section>


    <main>
        <section class="mainSearch">

            
            <h1>Discover, Rate, Enjoy, Share and Explore reviews on Places, Food, Media, Concepts etc- for insight, fun and everything in between.</h1>
            <form action="search.php" method="get" class="main-search">
                <input type="text" name="search" placeholder="Search..." value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
                    
                <select name="category">
                        <option value="">All Categories</option>
                        <option value="Place">Places</option>
                        <option value="Food">Food</option>
                        <option value="Media">Media</option>
                        <option value="Concept">Concepts</option>
                        <option value="EverythingElse">Everything Else</option>
                </select>
                
                <button type="submit"><i class="fa fa-search"></i></button>
            </form>
        </section>
        
        <section class="looking">
            <h2>What reviews are you looking for?</h2>
            <section class="options">
                <a href="http://cs3-dev.ict.ru.ac.za/practicals/4a2/HomePage/search.php?search=&category=Place">
                    <article>
                        <i class="fa fa-utensils"></i>
                        <h3>Places</h3>
                    </article>
                </a>
                <a href="http://cs3-dev.ict.ru.ac.za/practicals/4a2/HomePage/search.php?search=&category=Food">
                    <article>
                        <i class="fa fa-utensils"></i>
                        <h3>Food</h3>
                    </article>
                </a>

                <a href="http://cs3-dev.ict.ru.ac.za/practicals/4a2/HomePage/search.php?search=&category=Media">
                    <article>
                        <i class="fa fa-film"></i>
                        <h3>Media</h3>
                    </article>
                </a>

                <a href="http://cs3-dev.ict.ru.ac.za/practicals/4a2/HomePage/search.php?search=&category=Concept">
                    <article>
                        <i class="fa fa-lightbulb"></i>
                        <h3>Concepts</h3>
                    </article>
                </a>

                <a href="http://cs3-dev.ict.ru.ac.za/practicals/4a2/HomePage/search.php?search=&category=EverythingElse">
                    <article>
                        <i class="fa fa-random"></i>
                        <h3>Literally anything else</h3>
                    </article>
                </a>
            </section>
        </section>

        <!-- Featured Review Section -->
        <?php if($featuredReview): ?>
        <section class="featured-review">
            <h2>Featured Review of the Day</h2>
            <article class="review-card featured" data-category="<?= htmlspecialchars($featuredReview['category']) ?>">
                <header class="review-header">
                    <?php 
                    $userProfilePic = getUserProfilePic($featuredReview['userID'], $conn);
                    ?>
                    <img src="<?= $userProfilePic ? htmlspecialchars($userProfilePic) : "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='50' height='50' viewBox='0 0 50 50'%3E%3Ccircle cx='25' cy='25' r='25' fill='%23bdc007'/%3E%3Ctext x='25' y='30' text-anchor='middle' fill='%2314181c' font-size='18' font-weight='bold'%3E".substr($featuredReview['userName'],0,1)."%3C/text%3E%3C/svg%3E" ?>" 
                        alt="Profile Picture" class="reviewer-profPic">
                    
                    <section class="review-header-content">
                        <a href="viewPost.php?id=<?php echo $featuredReview['postID']; ?>"><h3 class="review-title"><?= htmlspecialchars($featuredReview['Title']) ?></h3></a>
                        <section class="review-meta">
                            <address class="review-author">
                                By: 
                                <a href="../personProfile.php?userID=<?= urlencode($featuredReview['userID']) ?>" class="author-name">
                                    <?= htmlspecialchars($featuredReview['userName']) ?>
                                </a>
                            </address>
                            <time class="review-date" datetime="<?= $featuredReview['dateCreated'] ?>">
                                <?= date("F j, Y", strtotime($featuredReview['dateCreated'])) ?>
                            </time>
                        </section>
                    </section>
                </header>
                
                <p class="review-rating"><?= str_repeat('★', $featuredReview['rating']) ?></p>
                <p class="review-content"><?= nl2br(htmlspecialchars($featuredReview['Content'])) ?></p>
                <button class="read-more-btn"></button>


                <?php 
                    $media = getMedia($featuredReview['postID'], $conn);
                    if ($media): ?>
                    <section class="review-media">
                        <?php if ($media['typeMedia'] === 'Images'): ?>
                            <img src="<?= "../".htmlspecialchars($media['location']) ?>" alt="Review Image" class="media-item">
                        <?php elseif ($media['typeMedia'] === 'Video'): ?>
                            <video controls class="media-item">
                                <source src="<?= "../".htmlspecialchars($media['location']) ?>" type="video/mp4">
                            </video>
                        <?php elseif ($media['typeMedia'] === 'Audio'): ?>
                            <audio controls class="media-item">
                                <source src="<?= "../".htmlspecialchars($media['location']) ?>" type="audio/mpeg">
                            </audio>
                        <?php endif; ?>
                    </section>
                    <?php endif; ?>


                

                <?php if(!empty($featuredReview['tags'])): ?>
                <section class="review-tags">
                    <?php 
                    $tags = explode(",", $featuredReview['tags']);
                    foreach($tags as $tag): 
                        $trimmedTag = trim($tag);
                        if(!empty($trimmedTag)):
                    ?>
                        <span class="review-tag"><?= htmlspecialchars($trimmedTag) ?></span>
                    <?php 
                        endif;
                    endforeach; 
                    ?>
                </section>
                <?php endif; ?>
                
                <section class="review-actions">
    <button class="like-btn" data-postid="<?= $featuredReview['postID'] ?>">
        <i class="fa fa-thumbs-up"></i> <span class="like-count"><?= $featuredReview['likes'] ?></span>
    </button>
    <button class="dislike-btn" data-postid="<?= $featuredReview['postID'] ?>">
        <i class="fa fa-thumbs-down"></i> <span class="dislike-count"><?= $featuredReview['dislikes'] ?></span>
    </button>
</section>

                

                <section class="comments">
                    <button class="toggle-comments">
                        Show Comments (<?= getComments($featuredReview['postID'], $conn)->num_rows ?>)
                    </button>
                    
                    <section class="comment-section" style="display: none;">
                        <section class="comment-list">
                            <?php 
                            $commentsResult = getComments($featuredReview['postID'], $conn);
                            if ($commentsResult->num_rows > 0):
                                while ($comment = $commentsResult->fetch_assoc()):
                            ?>
                                <article class="comment">
                                    <strong><?= htmlspecialchars($comment['userName']) ?>:</strong>
                                    <span><?= htmlspecialchars($comment['content']) ?></span>
                                    <time class="comment-date">
                                        <?= date("M j, g:i a", strtotime($comment['datecCreated'])) ?>
                                    </time>
                                </article>
                            <?php 
                                endwhile;
                            else:
                            ?>
                                <p class="no-comments">No comments yet.</p>
                            <?php endif; ?>
                        </section>
                        

                        <?php if (isset($_SESSION['userID'])): ?>
                        <form class="comment-form" data-postid="<?= $featuredReview['postID'] ?>">
                            <input type="text" name="new_comment" placeholder="Add a comment..." required>
                            <button type="submit">Comment</button>
                        </form>
                        <?php else: ?>
                        <p class="login-prompt"><a href="Login.php">Log in</a> to comment</p>
                        <?php endif; ?>
                    </section>
                </section>
            </article>
        </section>
        <?php endif; ?>


        <!----------------------------------------------------------------------------------------------------- All Reviews Section ----------------------------------->
        <section class="all-reviews">
            <h2>Recent Reviews</h2>
            <section class="reviews-grid">
                <?php foreach ($reviews as $rev): ?>
                <article class="review-card" data-category="<?= htmlspecialchars($rev['category']) ?>">

                    <!-- Header -->
                    <header class="review-header">
                        <?php 
                        // Get user profile picture
                        $userProfilePic = getUserProfilePic($rev['userID'], $conn);
                        ?>
                        <?php if ($userProfilePic): ?>
                            <img src="<?= htmlspecialchars($userProfilePic) ?>" alt="Profile Picture" class="reviewer-profPic">
                        <?php else: ?>
                            <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='50' height='50' viewBox='0 0 50 50'%3E%3Ccircle cx='25' cy='25' r='25' fill='%23bdc007'/%3E%3Ctext x='25' y='30' text-anchor='middle' fill='%2314181c' font-size='18' font-weight='bold'%3E<?= substr(htmlspecialchars($rev['userName']), 0, 1) ?>%3C/text%3E%3C/svg%3E" 
                                alt="Profile Picture" class="reviewer-profPic placeholder">
                        <?php endif; ?>

                        <section class="review-header-content">
                            <a href="viewPost.php?id=<?php echo $rev['postID']; ?>"><h3 class="review-title"><?= htmlspecialchars($rev['Title']) ?></h3></a>
                            <section class="review-meta">
                                <address class="review-author">
                                    By: 
                                    <a href="../personProfile.php?userID=<?= urlencode($rev['userID']) ?>" class="author-name">
                                        <?= htmlspecialchars($rev['userName']) ?>
                                    </a>
                                </address>
                                <time class="review-date" datetime="<?= htmlspecialchars($rev['dateCreated']) ?>">
                                    <?= date("F j, Y", strtotime($rev['dateCreated'])) ?>
                                </time>
                            </section>
                        </section>
                    </header>

                    <!-- Rating & Content -->
                    <p class="review-rating"><?= str_repeat('★', (int)$rev['rating']) ?></p>
                    <p class="review-content"><?= nl2br(htmlspecialchars($rev['Content'])) ?></p>
                    <button class="read-more-btn"></button>

                    <!-- Media Section -->
                    <?php 
                    $mediaResult = getMedia($rev['postID'], $conn);
                    if ($mediaResult): 
                    ?>
                    <section class="review-media">
                        
                            <?php 
                            $filePath = ($mediaResult['location']); 
                            $fileExt = strtolower(pathinfo($filePath, PATHINFO_EXTENSION)); 
                            ?>

                            <?php if (in_array($fileExt, ['jpg', 'jpeg', 'png', 'gif'])): ?>
                                <img src="<?= "../".$filePath ?>" alt="Review Image" class="media-item">

                            <?php elseif (in_array($fileExt, ['mp4', 'mov', 'avi', 'mkv'])): ?>
                                <video controls class="media-item">
                                    <source src="<?= "../".$filePath ?>" type="video/<?= $fileExt ?>">
                                    Your browser does not support the video tag.
                                </video>

                            <?php elseif (in_array($fileExt, ['mp3', 'wav', 'ogg'])): ?>
                                <audio controls class="media-item">
                                    <source src="<?= "../".$filePath ?>" type="audio/<?= $fileExt ?>">
                                    Your browser does not support the audio element.
                                </audio>
                            <?php endif; ?>
                        
                    </section>
                    <?php endif; ?>

                    <!-- Tags -->
                    <?php if (!empty($rev['tags'])): ?>
                    <section class="review-tags">
                        <?php 
                        $tags = explode(",", $rev['tags']);
                        foreach ($tags as $tag): 
                            $trimmedTag = trim($tag);
                            if (!empty($trimmedTag)):
                        ?>
                            <span class="review-tag"><?= htmlspecialchars($trimmedTag) ?></span>
                        <?php endif; endforeach; ?>
                    </section>
                    <?php endif; ?>

                    <!-- Like/Dislike -->
                    <section class="review-actions">
<button class="like-btn" data-postid="<?= $rev['postID'] ?>">
        <i class="fa fa-thumbs-up"></i> <span class="like-count"><?= $rev['likes'] ?></span>
    </button>
    <button class="dislike-btn" data-postid="<?= $rev['postID'] ?>">
        <i class="fa fa-thumbs-down"></i> <span class="dislike-count"><?= $rev['dislikes'] ?></span>
    </button>
                    </section>

                    <!-- Comments -->
                    <section class="comments">
                        <?php $commentsResult = getComments($rev['postID'], $conn); ?>
                        <button class="toggle-comments">
                            Show Comments (<?= $commentsResult->num_rows ?>)
                        </button>

                        <section class="comment-section" style="display: none;">
                            <section class="comment-list">
                                <?php if ($commentsResult->num_rows > 0): ?>
                                    <?php while ($comment = $commentsResult->fetch_assoc()): ?>
                                        <article class="comment">
                                            <strong><?= htmlspecialchars($comment['userName']) ?>:</strong>
                                            <span><?= htmlspecialchars($comment['content']) ?></span>
                                            <time class="comment-date">
                                                <?= date("M j, g:i a", strtotime($comment['datecCreated'])) ?>
                                            </time>
                                        </article>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <p class="no-comments">No comments yet.</p>
                                <?php endif; ?>
                            </section>

                            <!-- Add Comment -->
                            <?php if (isset($_SESSION['userID'])): ?>
                            <form class="comment-form" data-postid="<?= $rev['postID'] ?>">
                                <input type="text" name="new_comment" placeholder="Add a comment..." required>
                                <button type="submit">Comment</button>
                            </form>
                            <?php else: ?>
                            <p class="login-prompt"><a href="Login.php">Log in</a> to comment</p>
                            <?php endif; ?>
                        </section>
                    </section>

                </article>
                <?php endforeach; ?>
            </section>
        </section>


    </main>
    <?php include "../foot.php"?>

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