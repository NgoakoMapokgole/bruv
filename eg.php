<?php
include "../rate.php"; // Database connection

$query = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';

// Base SQL
$sql = "SELECT p.postID, p.Title, p.Content, p.tags, p.dateCreated, p.rating, p.likes, p.dislikes, p.category, p.userID, u.userName, u.profPic
        FROM post p
        JOIN users u ON p.userID = u.userID
        WHERE p.deleted = 0 
          AND (p.Title LIKE ? OR p.tags LIKE ?)";

// Add category filter if selected
if (!empty($category)) {
    $sql .= " AND p.category = ?";
    $stmt = $conn->prepare($sql . " ORDER BY p.dateCreated DESC");
    $likeQuery = "%$query%";
    $stmt->bind_param("sss", $likeQuery, $likeQuery, $category);
} else {
    $stmt = $conn->prepare($sql . " ORDER BY p.dateCreated DESC");
    $likeQuery = "%$query%";
    $stmt->bind_param("ss", $likeQuery, $likeQuery);
}

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Search Results - Rate It All</title>
  <link rel="icon" type="image/svg+xml" href="\\cs3-dev.ict.ru.ac.za\practicals\4A2\logo.png">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@100..900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="../mainStyle.css"/>
  <style>
    /* ====== IMDB-inspired wide-left, static-right sidebar (matches your theme variables) ====== */

    /* container that holds left results and right sidebar */
    .search-results-layout {
      max-width: 1300px;
      margin: 0 auto;
      padding: 1.5rem 1rem;
      display: flex;
      gap: 32px;
      align-items: flex-start;
      box-sizing: border-box;
    }

    /* left column: wide results */
    .results-column {
      flex: 1 1 0%;
      min-width: 0;
      max-width: calc(100% - 320px);
      background: transparent;
    }

    /* right column: narrow sidebar */
    .results-sidebar {
      width: 320px; /* fixed width for predictable alignment */
      flex-shrink: 0;
      background: linear-gradient(180deg, rgba(255,255,255,0.02), rgba(255,255,255,0.01));
      border-radius: 8px;
      padding: 14px;
      box-shadow: 0 6px 20px rgba(0,0,0,0.45);
      border: 1px solid rgba(255,255,255,0.03);
      color: var(--text);
    }

    .sidebar-section {
      margin-bottom: 1.1rem;
      padding-bottom: 0.6rem;
      border-bottom: 1px solid rgba(255,255,255,0.03);
    }

    .sidebar-section h4 {
      margin: 0 0 0.6rem 0;
      color: var(--accent);
      font-size: 0.98rem;
      font-weight: 600;
    }

    .sidebar-list {
      list-style: none;
      padding: 0;
      margin: 0;
    }

    .sidebar-list li {
      padding: 0.5rem 0;
      font-size: 0.92rem;
      color: rgba(255,255,255,0.9);
      display:flex;
      justify-content: space-between;
      gap: 10px;
      align-items: center;
      border-bottom: 1px dashed rgba(255,255,255,0.02);
    }

    .sidebar-list li:last-child {
      border-bottom: none;
    }

    .sidebar-small-meta {
      font-size: 0.82rem;
      color: rgba(255,255,255,0.6);
    }

    /* ====== Result rows (IMDB Find vibe) ====== */
    .imdb-find {
      width: 100%;
      background: transparent;
      padding: 0;
    }

    .find-header {
      font-size: 1.05rem;
      color: var(--text);
      margin: 0 0 0.8rem 0;
    }

    .results-list {
      width: 100%;
      background: transparent;
      border-radius: 6px;
      overflow: hidden;
    }

    .result-row {
      display: flex;
      gap: 14px;
      align-items: flex-start;
      padding: 12px 10px;
      border-bottom: 1px solid rgba(255,255,255,0.03);
      transition: background 0.12s ease, transform 0.08s ease;
    }

    .result-row:hover {
      background: rgba(255,255,255,0.01);
      transform: translateY(-2px);
    }

    .poster-thumb {
      width: 56px;
      height: 86px;
      flex-shrink: 0;
      overflow: hidden;
      border-radius: 4px;
      background: linear-gradient(180deg, rgba(255,255,255,0.02), rgba(0,0,0,0.35));
      box-shadow: 0 6px 14px rgba(0,0,0,0.6);
      display:flex;
      align-items:center;
      justify-content:center;
    }

    .poster-thumb img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }

    .result-info {
      display: flex;
      flex-direction: column;
      min-width: 0;
    }

    .title-link {
      color: var(--text);
      font-size: 1rem;
      font-weight: 700;
      text-decoration: none;
      letter-spacing: -0.2px;
    }

    .title-link:hover {
      color: var(--primary); /* small red pop on hover to fit your site's primary */
      text-decoration: underline;
    }

    .sub-meta {
      margin-top: 4px;
      font-size: 0.88rem;
      color: rgba(255,255,255,0.65);
      display:flex;
      gap: 12px;
      align-items:center;
      flex-wrap:wrap;
    }

    .sub-meta .rating {
      background: rgba(255,255,255,0.03);
      padding: 2px 6px;
      border-radius: 4px;
      font-weight: 600;
      color: var(--accent);
      display:inline-flex;
      align-items:center;
      gap:6px;
      font-size:0.88rem;
    }

    .sub-meta .date {
      font-size: 0.85rem;
      color: rgba(255,255,255,0.55);
    }

    /* optional short excerpt line to the right (kept subtle) */
    .row-excerpt {
      margin-top: 8px;
      font-size: 0.88rem;
      color: rgba(255,255,255,0.6);
      display: -webkit-box;
      -webkit-line-clamp: 2;
      -webkit-box-orient: vertical;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    /* author row (small avatar + name) */
    .row-author {
      display:flex;
      align-items:center;
      gap:10px;
      margin-top:8px;
    }

    .author-pic {
      width: 30px;
      height: 30px;
      border-radius: 50%;
      object-fit:cover;
      box-shadow: 0 3px 8px rgba(0,0,0,0.6);
    }

    .author-name {
      font-size: 0.88rem;
      color: var(--accent);
      text-decoration: none;
      font-weight:600;
    }

    /* small actions / likes */
    .row-actions {
      margin-left: auto;
      display:flex;
      gap:8px;
      align-items:center;
      color:rgba(255,255,255,0.75);
      font-size:0.95rem;
    }

    /* No results styling */
    .no-results {
      text-align:center;
      color: rgba(255,255,255,0.6);
      padding: 2rem 0;
    }

    /* ====== Responsive ====== */
    @media (max-width: 1000px) {
      .search-results-layout {
        padding: 1rem;
        gap: 16px;
      }
      .results-sidebar {
        display: none; /* hide sidebar on small screens (IMDB mobile tends to show list) */
      }
      .result-row {
        gap: 12px;
        padding: 10px 6px;
      }
      .poster-thumb {
        width: 48px;
        height: 74px;
      }
      .title-link { font-size: 0.98rem; }
    }

    @media (max-width: 600px) {
      .poster-thumb { display: none; } /* save space on tiny devices */
      .row-actions { display: none; }
      .title-link { font-size: 1rem; }
    }
  </style>
</head>
<body>
  <header>
    <?php include "../nav.php"; ?>
  </header>

  <!-- Search Section (top) -->
  <section class="mainSearch">
    <h1>
      Search Results for "<?php echo htmlspecialchars($query); ?>"
      <?php if (!empty($category)) echo " in " . htmlspecialchars($category); ?>
    </h1>

    <form class="main-search" method="get" action="search.php">
      <input type="text" name="search" value="<?php echo htmlspecialchars($query); ?>" placeholder="Search reviews..." />
      <select name="category">
          <option value="">All Categories</option>
          <option value="Place" <?php if($category==="Place") echo "selected"; ?>>Places</option>
          <option value="Food" <?php if($category==="Food") echo "selected"; ?>>Food</option>
          <option value="Media" <?php if($category==="Media") echo "selected"; ?>>Media</option>
          <option value="Concept" <?php if($category==="Concept") echo "selected"; ?>>Concepts</option>
          <option value="EverythingElse" <?php if($category==="EverythingElse") echo "selected"; ?>>Everything Else</option>
      </select>
      <button type="submit">Search</button>
    </form>
  </section>

  <!-- MAIN LAYOUT: wide left results + right sidebar -->
 <section class="search-container" style="display:flex; gap:2rem; max-width:1200px; margin:2rem auto;">
  
  <!-- Left: Main Search Results -->
  <div class="all-reviews" style="flex:3;">
    <h2>Results for "<?php echo htmlspecialchars($query); ?>"</h2>
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
                  <a href="../personProfile.php?userID=<?php echo $row['userID']; ?>" class="author-name"><?php echo htmlspecialchars($row['userName']); ?></a>
                </div>
                <div class="review-meta">
                  <span class="review-date"><?php echo date("M d, Y", strtotime($row['dateCreated'])); ?></span>
                  <span class="review-rating">⭐ <?php echo $row['rating']; ?>/5</span>
                  <span class="review-category"><?php echo htmlspecialchars($row['category']); ?></span>
                </div>
              </div>
            </div>
            <!-- Optional Review Image -->
            <?php
            $mediaSql = "SELECT location FROM media WHERE postID = ? AND typeMedia='Images' AND archived=0 ORDER BY orderAppearance ASC LIMIT 1";
            $mediaStmt = $conn->prepare($mediaSql);
            $mediaStmt->bind_param("i",$row['postID']);
            $mediaStmt->execute();
            $mediaResult = $mediaStmt->get_result();
            if ($mediaRow = $mediaResult->fetch_assoc()): ?>
              <div class="review-image-small"><img src="<?php echo htmlspecialchars($mediaRow['location']); ?>" alt="Review image"></div>
            <?php endif; ?>
            <div class="review-content"><?php echo substr(strip_tags($row['Content']),0,150) . "..."; ?></div>
            <div class="review-tags">
              <?php foreach(explode(",",$row['tags']) as $tag): 
                $trimmedTag = trim($tag); 
                if(!empty($trimmedTag)): ?>
                  <a href="search.php?query=<?php echo urlencode($trimmedTag); ?>" class="review-tag"><?php echo htmlspecialchars($trimmedTag); ?></a>
              <?php endif; endforeach; ?>
            </div>
            <div class="review-actions">
              <button class="like-btn">👍 <?php echo $row['likes']; ?></button>
              <button class="dislike-btn">👎 <?php echo $row['dislikes']; ?></button>
            </div>
            <a href="viewPost.php?id=<?php echo $row['postID']; ?>" class="comment-submit">Read Full Review</a>
          </article>
        <?php endwhile; ?>
      <?php else: ?>
        <p class="no-comments">No results found for "<?php echo htmlspecialchars($query); ?>"</p>
      <?php endif; ?>
    </div>
  </div>

  <!-- Right: Sidebar -->
  <aside class="sidebar" style="flex:1; position:sticky; top:2rem;">
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
  </aside>
</section>

  <?php include "../foot.php"; ?>
</body>
</html>
