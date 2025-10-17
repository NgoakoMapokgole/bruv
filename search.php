<?php
session_start();
include "../rate.php"; // Database connection


$query = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';
$sort = $_GET['sort'] ?? 'newest';
$minRating = $_GET['minRating'] ?? 1; // Default: show all


// Base SQL
$sql = "SELECT p.postID, p.Title, p.Content, p.tags, p.dateCreated, p.rating, p.likes, p.dislikes, p.category, p.userID, u.userName, u.profPic
        FROM post p
        JOIN users u ON p.userID = u.userID
        WHERE p.deleted = 0
          AND (p.Title LIKE ? OR p.tags LIKE ?)
          AND p.rating >= ?";

if (!empty($category)) {
    $sql .= " AND p.category = ?";
}

// Sorting options
switch ($sort) {
    case 'liked':
        $sql .= " ORDER BY p.likes DESC";
        break;
    case 'rated':
        $sql .= " ORDER BY p.rating DESC";
        break;
    default:
        $sql .= " ORDER BY p.dateCreated DESC";
        break;
}

$likeQuery = "%$query%";

if (!empty($category)) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssis", $likeQuery, $likeQuery, $minRating, $category);
} else {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $likeQuery, $likeQuery, $minRating);
}

$stmt->execute();
$result = $stmt->get_result();


if (!isset($_SESSION['userID'])) {
    header("Location: login.php");
    exit;
}

$userID = $_SESSION['userID'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['postID'])) {
    $postID = intval($_POST['postID']);
    $action = $_POST['action'];

    if ($action === 'like') {
        // Check if user already liked
        $stmt = $conn->prepare("SELECT id FROM likes WHERE user_id=? AND review_id=?");
        $stmt->bind_param("ii", $userID, $postID);
        $stmt->execute();
        $liked = $stmt->get_result()->num_rows > 0;

        if ($liked) {
            // Unlike
            $stmt = $conn->prepare("DELETE FROM likes WHERE user_id=? AND review_id=?");
            $stmt->bind_param("ii", $userID, $postID);
            $stmt->execute();
            $conn->query("UPDATE post SET likes = likes - 1 WHERE postID = $postID AND likes > 0");
        } else {
            // Like
            $stmt = $conn->prepare("INSERT INTO likes (user_id, review_id) VALUES (?, ?)");
            $stmt->bind_param("ii", $userID, $postID);
            $stmt->execute();
            $conn->query("UPDATE post SET likes = likes + 1 WHERE postID = $postID");

            // Only remove dislike if exists
            $stmt = $conn->prepare("SELECT id FROM dislikes WHERE user_id=? AND review_id=?");
            $stmt->bind_param("ii", $userID, $postID);
            $stmt->execute();
            if ($stmt->get_result()->num_rows > 0) {
                $stmt = $conn->prepare("DELETE FROM dislikes WHERE user_id=? AND review_id=?");
                $stmt->bind_param("ii", $userID, $postID);
                $stmt->execute();
                $conn->query("UPDATE post SET dislikes = dislikes - 1 WHERE postID = $postID AND dislikes > 0");
            }
        }
    }

    if ($action === 'dislike') {
        // Check if user already disliked
        $stmt = $conn->prepare("SELECT id FROM dislikes WHERE user_id=? AND review_id=?");
        $stmt->bind_param("ii", $userID, $postID);
        $stmt->execute();
        $disliked = $stmt->get_result()->num_rows > 0;

        if ($disliked) {
            // Remove dislike
            $stmt = $conn->prepare("DELETE FROM dislikes WHERE user_id=? AND review_id=?");
            $stmt->bind_param("ii", $userID, $postID);
            $stmt->execute();
            $conn->query("UPDATE post SET dislikes = dislikes - 1 WHERE postID = $postID AND dislikes > 0");
        } else {
            // Dislike
            $stmt = $conn->prepare("INSERT INTO dislikes (user_id, review_id) VALUES (?, ?)");
            $stmt->bind_param("ii", $userID, $postID);
            $stmt->execute();
            $conn->query("UPDATE post SET dislikes = dislikes + 1 WHERE postID = $postID");

            // Only remove like if exists
            $stmt = $conn->prepare("SELECT id FROM likes WHERE user_id=? AND review_id=?");
            $stmt->bind_param("ii", $userID, $postID);
            $stmt->execute();
            if ($stmt->get_result()->num_rows > 0) {
                $stmt = $conn->prepare("DELETE FROM likes WHERE user_id=? AND review_id=?");
                $stmt->bind_param("ii", $userID, $postID);
                $stmt->execute();
                $conn->query("UPDATE post SET likes = likes - 1 WHERE postID = $postID AND likes > 0");
            }
        }
    }

    // Redirect to refresh the page
    header("Location: " . $_SERVER['REQUEST_URI']);
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Search Results | Rate It All</title>
  <link rel="icon" type = "image/svg+xml" href="http://cs3-dev.ict.ru.ac.za/practicals/4a2/logo.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../mainStyle.css"/>
</head>
<style>
    /* ==================== LETTERBOXD-STYLE SEARCH RESULTS ==================== */

.mainSearch {
  text-align: center;
  margin: 2rem auto 1.5rem;
  border-radius: 20px 0 0 20px;
}

.mainSearch h1 {
  font-size: 1.8rem;
  font-weight: 600;
  color: #fff;
  margin-bottom: 1rem;
}

.main-search {
  display: flex;
  justify-content: center;
  margin-bottom: 2rem;
  border-radius: 20px 0 0 20px;
}

.main-search input {
  background: #2c3440;
  border: 1px solid #3c4450;
  padding: 10px 12px;
  width: 350px;
  color: #fff;
  font-size: 0.95rem;
  border-radius: 4px 0 0 4px;
  border-radius: 20px 0 0 20px;
}

.main-search button {
  background: #bdc007;
  border: none;
  padding: 10px 16px;
  font-size: 0.95rem;
  color: #000;
  cursor: pointer;
  border-radius: 0 20px 20px 0;
  transition: background 0.2s ease;
}

.main-search button:hover {
  background: #bdc007;
}

/* === Results List Styling === */
.all-reviews {
  max-width: 800px;
  margin: 0 auto;
}

.reviews-grid {
  display: flex;
  flex-direction: column;
  gap: 0;
}

/* Each result row */
.review-card {
  display: flex;
  flex-direction: column;
  padding: 1rem 0;
  border-bottom: 1px solid #2c3440;
}

.review-header {
  display: flex;
  align-items: center;
}

.review-title {
  font-size: 1.1rem;
  font-weight: 600;
  color: #fff;
  margin-bottom: 0.3rem;
  transition: color 0.2s ease;
}

.review-title:hover {
  color: #bdc007;
}

.review-meta {
  font-size: 0.85rem;
  color: #9ab;
}

.review-content {
  margin: 0.3rem 0 0.6rem;
  color: #ccd;
  font-size: 0.9rem;
  line-height: 1.4;
}

.review-tags {
  display: flex;
  gap: 0.4rem;
  flex-wrap: wrap;
  margin-bottom: 0.5rem;
}

.review-tag {
  font-size: 0.75rem;
  color: #9ab;
  background: #2c3440;
  padding: 2px 6px;
  border-radius: 3px;
}

.review-actions {
  display: flex;
  gap: 1rem;
  font-size: 0.9rem;
  color: #9ab;
}

.comment-submit {
  display: inline-block;
  margin-top: 0.8rem;
  font-size: 0.85rem;
  color: #bdc007;
  text-decoration: none;
}

.comment-submit:hover {
  text-decoration: underline;
}
.reviewer-profPic {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
    margin-right: 0.8rem;
    box-shadow: 0 2px 5px rgba(0,0,0,0.3);
}

.review-author .author-name {
    color: #bdc007;
    text-decoration: none;
    font-weight: 500;
    font-size: 0.85rem;
}

.review-author .author-name:hover {
    color: #bdc007;
}

.review-image-small {
    width: 300px;        /* fixed width */
    height: 200px;       /* fixed height */
    overflow: hidden;    /* crop overflow */
    border-radius: 6px;
    margin: 0.8rem 0;
    box-shadow: 0 3px 8px rgba(0,0,0,0.4);
}

.review-image-small img {
    width: 100%;
    height: 100%;
    object-fit: cover;   /* maintain aspect ratio, crop excess */
    display: block;
}


/* === MAIN + SIDEBAR FLEX LAYOUT === */
.search-layout {
    display: flex;
    gap: 2rem;
    max-width: 1200px;
    margin: 0 auto 3rem;
    align-items: flex-start; /* Top-align sidebar and main content */
}

/* Main section: 70% width */
.main-content {
    flex: 7;
    padding-right:5rem;
}
/* === COMPACT SIDEBAR === */
.sidebar {
    flex: 3;
    position: sticky;
    top: 1rem;
    font-size: 0.8rem; /* smaller font */
    line-height: 1.2;
    margin: 0.5rem auto 2rem  0;
}

.sidebar h3 {
    font-size: 0.85rem;
    margin: 0.5rem 0;
    color: #bdc007;
    text-transform: uppercase;
}

.sidebar ul {
  list-style: none;
  padding: 0;
  margin: 0 0 1rem 0;
  display: block;        /* ✅ ensure it's vertical, not flex */
}

.sidebar li {
  display: block;        /* ✅ stack each item */
  margin-bottom: 0.4rem; /* space between items */
  word-wrap: break-word; /* handle long titles nicely */
}

.sidebar li a {
  display: block;        /* ✅ makes each link take full width */
  text-decoration: none;
  color: #fff;
  font-size: 0.8rem;
  line-height: 1.3;
  transition: color 0.2s;
}

.sidebar li a:hover {
  color: #bdc007;
}

.filter{
  margin-bottom: 01rem;
  max-width:300px;
}

.filter-bubbles {
  display: flex;
  flex-wrap: wrap;
  gap: 0.4rem;
  margin-top: 0.6rem;
}

.filter-bubble {
  display: inline-block;
  padding: 6px 10px;
  background: #2c3440;
  color: #fff;
  border-radius: 999px;
  text-decoration: none;
  font-size: 0.8rem;
  transition: background 0.2s, transform 0.1s;
}

.filter-bubble:hover {
  background: #bdc007;
  color: #000;
  transform: scale(1.05);
}

.filter-bubble.active {
  background: #bdc007;
  color: #000;
  font-weight: 600;
  box-shadow: 0 0 6px rgba(0, 224, 84, 0.4);
}

.filter label {
  display: block;
  font-size: 0.8rem;
  color: #9ab;
  margin-bottom: 0.2rem;
}

.filter span {
  color: #bdc007;
  font-weight: 600;
  margin-left: 0.4rem;
}
.more{
  max-width: 300px;
}



</style>
<body>
    <header>
        <?php include "../nav.php"; ?>
    </header>
    <!-- review modal form -->
    <?php include "../review.php"?>
    <section id="flashMessage" class="flash-message"></section>

<!-- Search Section -->
<section class="mainSearch">
  <h1>
    Search Results for "<?php echo htmlspecialchars($query); ?>"
    <?php if (!empty($category)) echo " in " . htmlspecialchars($category); ?>
  </h1>
  
  <form class="main-search" method="get" action="search.php">
    <input type="text" name="search" value="<?php echo htmlspecialchars($query); ?>" placeholder="Search reviews..." />
    
    <!-- Category Dropdown -->
    <select name="category">
        <option value="">All Categories</option>
        <option value="Place" <?php if($category==="Place") echo "selected"; ?>>Places</option>
        <option value="Food" <?php if($category==="Food") echo "selected"; ?>>Food</option>
        <option value="Media" <?php if($category==="Media") echo "selected"; ?>>Media</option>
        <option value="Concept" <?php if($category==="Concept") echo "selected"; ?>>Concepts</option>
        <option value="EverythingElse" <?php if($category==="EverythingElse") echo "selected"; ?>>Everything Else</option>
    </select>

    <button type="submit"><i class="fa fa-search"></i></button>
  </form>
</section>

<!-- Results -->
 <section class="search-layout">
<section class="main-content">
  <h2>Results</h2>
  <div class="reviews-grid">
    <?php if ($result->num_rows > 0): ?>
      <?php while ($row = $result->fetch_assoc()): ?>
<article class="review-card">

  <div class="review-header">
    <?php if (!empty($row['profPic'])): ?>
      <img src="<?php echo htmlspecialchars($row['profPic']); ?>" alt="<?php echo htmlspecialchars($row['userName']); ?>" class="reviewer-profPic">
    <?php endif; ?>
    <div>
      <h3 class="review-title"><?php echo htmlspecialchars($row['Title']); ?></h3>
      <div class="review-author">
        <span class="review-meta">Review by </span><a href="../personProfile.php?userID=<?php echo $row['userID']; ?>" class="author-name">
          <?php echo htmlspecialchars($row['userName']); ?>
        </a>
      </div>
      <div class="review-meta">
        <span class="review-date"><?php echo date("M d, Y", strtotime($row['dateCreated'])); ?></span>
        <span class="review-rating">⭐ <?php echo $row['rating']; ?>/5</span>
      </div>
    </div>
  </div>

  <!-- Review image (optional) -->
  <?php
  // Check if review has media image
  $mediaSql = "SELECT location FROM media WHERE postID = ? AND typeMedia = 'Images' AND archived = 0 ORDER BY orderAppearance ASC LIMIT 1";
  $mediaStmt = $conn->prepare($mediaSql);
  $mediaStmt->bind_param("i", $row['postID']);
  $mediaStmt->execute();
  $mediaResult = $mediaStmt->get_result();
  if ($mediaRow = $mediaResult->fetch_assoc()): ?>
    <div class="review-image-small">
      <img src="<?php echo "../".htmlspecialchars($mediaRow['location']); ?>" alt="Review image">
    </div>
  <?php endif; ?>

  <div class="review-content">
    <?php echo substr(strip_tags($row['Content']), 0, 150) . "..."; ?>
  </div>

  <!-- Tags -->
  <div class="review-tags">
    <?php foreach (explode(",", $row['tags']) as $tag): 
        $trimmedTag = trim($tag);
        if (!empty($trimmedTag)): ?>
      <a href="search.php?query=<?php echo urlencode($trimmedTag); ?>" class="review-tag">
        <?php echo htmlspecialchars($trimmedTag); ?>
      </a>
    <?php endif; endforeach; ?>
  </div>

<div class="review-actions">
    <form method="POST">
        <input type="hidden" name="postID" value="<?= $row['postID'] ?>">
        <button type="submit" name="action" value="like"><i class="fa fa-thumbs-up"></i> <?= $row['likes'] ?></button>
        <button type="submit" name="action" value="dislike"><i class="fa fa-thumbs-down"></i> <?= $row['dislikes'] ?></button>
    </form>
</div>


  <a href="viewPost.php?id=<?php echo $row['postID']; ?>" class="comment-submit" style="margin-top: 1rem;">Read Full Review</a>

</article>
<?php endwhile; ?>

    <?php else: ?>
      <p class="no-comments">No results found for "<?php echo htmlspecialchars($query); ?>"</p>
    <?php endif; ?>
  </div>
</section>
<!-- Right: Sidebar -->

  <aside class="sidebar">
    <section>
    <h3>Filters</h3>
    <div class="filter">
      <label>Category:</label>
      <section class="filter-bubbles">
  <a href="search.php?search=<?php echo urlencode($query); ?>" class="filter-bubble <?php if ($category == '') echo 'active'; ?>">All</a>
  <a href="search.php?search=<?php echo urlencode($query); ?>&category=Food" class="filter-bubble <?php if ($category == 'Food') echo 'active'; ?>">Food</a>
  <a href="search.php?search=<?php echo urlencode($query); ?>&category=Place" class="filter-bubble <?php if ($category == 'Place') echo 'active'; ?>">Places</a>
  <a href="search.php?search=<?php echo urlencode($query); ?>&category=Media" class="filter-bubble <?php if ($category == 'Media') echo 'active'; ?>">Media</a>
  <a href="search.php?search=<?php echo urlencode($query); ?>&category=Concept" class="filter-bubble <?php if ($category == 'Concept') echo 'active'; ?>">Concepts</a>
  <a href="search.php?search=<?php echo urlencode($query); ?>&category=EverythingElse" class="filter-bubble <?php if ($category == 'EverythingElse') echo 'active'; ?>">Everything Else</a>
  <a href="search.php" class="filter-bubble" style="background:#3c4450;">Clear Filters ✖</a>

</section>

    </div>
    <div class="filter">
  <label>Sort by:</label>
  <form method="get" action="search.php">
    <input type="hidden" name="search" value="<?php echo htmlspecialchars($query); ?>">
    <input type="hidden" name="category" value="<?php echo htmlspecialchars($category); ?>">
    <input type="hidden" name="minRating" value="<?php echo htmlspecialchars($minRating); ?>">
    <select name="sort" onchange="this.form.submit()">
      <option value="newest" <?php if($sort==='newest') echo 'selected'; ?>>Newest</option>
      <option value="liked" <?php if($sort==='liked') echo 'selected'; ?>>Most Liked</option>
      <option value="rated" <?php if($sort==='rated') echo 'selected'; ?>>Top Rated</option>
    </select>
  </form>
</div>

   <div class="filter">
  <label>Minimum Rating:</label>
  <form method="get" action="search.php" class="rating-form">
    <input type="hidden" name="search" value="<?php echo htmlspecialchars($query); ?>">
    <input type="hidden" name="category" value="<?php echo htmlspecialchars($category); ?>">
    <input type="hidden" name="sort" value="<?php echo htmlspecialchars($sort); ?>">
    <input type="range" name="minRating" min="1" max="5" value="<?php echo htmlspecialchars($minRating); ?>" 
           oninput="this.nextElementSibling.innerText = this.value" 
           onchange="this.form.submit()">
    <span><?php echo htmlspecialchars($minRating); ?></span>
  </form>
</div>
</section>

    <section class="more">
  
    <h3>Top Rated</h3>
    <ul>
      <?php
      $topRated = $conn->query("SELECT postID, Title, rating FROM post WHERE deleted=0 ORDER BY rating DESC LIMIT 5");
      while($tr = $topRated->fetch_assoc()): ?>
        <li><a href="viewPost.php?id=<?php echo $tr['postID']; ?>"><?php echo htmlspecialchars($tr['Title']); ?> ⭐ <?php echo $tr['rating']; ?></a></li>
      <?php endwhile; ?>
    </ul>

    <h3>Recent Reviews</h3>
    <ul>
      <?php
      $recent = $conn->query("SELECT postID, Title, dateCreated FROM post WHERE deleted=0 ORDER BY dateCreated DESC LIMIT 5");
      while($rc = $recent->fetch_assoc()): ?>
        <li><a href="viewPost.php?id=<?php echo $rc['postID']; ?>"><?php echo htmlspecialchars($rc['Title']); ?> (<?php echo date("Y-m-d", strtotime($rc['dateCreated'])); ?>)</a></li>
      <?php endwhile; ?>
    </ul>

    <!-- Optional: Most Commented -->
    <h3>Most Reviewed</h3>
    <ul>
      <?php
      $mostC = $conn->query("SELECT p.postID, p.Title, COUNT(c.commentID) AS cnt FROM post p LEFT JOIN comments c ON p.postID=c.postID WHERE p.deleted=0 GROUP BY p.postID ORDER BY cnt DESC LIMIT 5");
      while($mc = $mostC->fetch_assoc()): ?>
        <li><a href="viewPost.php?id=<?php echo $mc['postID']; ?>"><?php echo htmlspecialchars($mc['Title']); ?> (<?php echo $mc['cnt']; ?>)</a></li>
      <?php endwhile; ?>
    </ul>
  </section>
  </aside>
</section>

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
    <script src="home.js"></script>>
</body>
</html>
