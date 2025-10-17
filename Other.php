<?php
include "../rate.php"; // DB connection
session_start();

if (!isset($_SESSION['userID'])) {
    echo "You have not logged in. Please log in to continue.";
    exit;
}

$user_id = $_SESSION['userID'];

// Handle Like
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['like_review_id'])) {
    $review_id = intval($_POST['like_review_id']);

    // Check if user already liked
    $check = $conn->prepare("SELECT * FROM likes WHERE review_id = ? AND user_id = ?");
    $check->bind_param("ii", $review_id, $user_id);
    $check->execute();
    $res = $check->get_result();

    if ($res->num_rows === 0) {
        // Add like
        $update = $conn->prepare("UPDATE post SET likes = likes + 1 WHERE postID = ?");
        $update->bind_param("i", $review_id);
        $update->execute();

        $insert = $conn->prepare("INSERT INTO likes (user_id, review_id) VALUES (?, ?)");
        $insert->bind_param("ii", $user_id, $review_id);
        $insert->execute();
    }

    echo json_encode(["success" => true]);
    exit;
}

// Handle Comment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment_review_id'], $_POST['comment_text'])) {
    $review_id = intval($_POST['comment_review_id']);
    $comment_text = htmlspecialchars(substr($_POST['comment_text'], 0, 500));

    $insert = $conn->prepare("INSERT INTO comments (userID, postID, content) VALUES (?, ?, ?)");
    $insert->bind_param("iis", $user_id, $review_id, $comment_text);
    $insert->execute();

    echo json_encode(["success" => true, "comment" => $comment_text]);
    exit;
}

// Fetch Top Rated (4+ stars)
$topRated = $conn->prepare("SELECT * FROM post WHERE rating >= 4 ORDER BY rating DESC, postID DESC LIMIT 5");
$topRated->execute();
$topRatedResult = $topRated->get_result();

// Fetch All Reviews
$sql = $conn->prepare("SELECT * FROM post ORDER BY rating DESC, postID DESC");
$sql->execute();
$result = $sql->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Rate It All! | Home Page</title>
  <link rel="stylesheet" href="../../mainStyle.css">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

  <style>
    /* Highlight Top Rated */
    .review-card.top-rated {
      border: 2px solid gold;
      background: rgba(255, 215, 0, 0.05);
      box-shadow: 0 0 10px rgba(255, 215, 0, 0.3);
    }

    .top-rated-section {
      padding: 1rem;
      background: rgba(255,255,255,0.03);
      border: 1px solid rgba(255,255,255,0.1);
      margin-bottom: 2rem;
    }

    .top-rated-section h2 {
      color: gold;
      margin-bottom: 1rem;
    }

    /* =========== COMMENTS SECTION =========== */
    .comments {
      margin-top: 1.5rem;
      background: rgba(255, 255, 255, 0.03);
      border-radius: 10px;
      padding: 1rem;
      border: 1px solid rgba(255, 255, 255, 0.08);
    }

    .comments h4 {
      font-size: 1.1rem;
      font-weight: 600;
      margin-bottom: 1rem;
      color: var(--text);
    }

    .comment-list {
      list-style: none;
      padding: 0;
      margin: 0 0 1rem;
    }

    .comment-list li {
      background: rgba(255, 255, 255, 0.05);
      border: 1px solid rgba(255, 255, 255, 0.1);
      border-radius: 8px;
      padding: 0.75rem 1rem;
      margin-bottom: 0.5rem;
      font-size: 0.9rem;
      color: var(--text);
    }

    .comment-form {
      display: flex;
      gap: 0.5rem;
      margin-top: 1rem;
      flex-wrap: wrap;
    }

    .comment-input {
      flex: 1;
      padding: 0.6rem 0.9rem;
      border: 1px solid rgba(255, 255, 255, 0.15);
      background: rgba(255, 255, 255, 0.05);
      color: var(--text);
      font-size: 1rem;
      border-radius: 6px;
      outline: none;
    }

    .comment-submit {
      background: var(--accent);
      color: var(--bgnav);
      border: none;
      padding: 0.6rem 1rem;
      border-radius: 6px;
      cursor: pointer;
      font-weight: 600;
      font-size: 1rem;
    }

    .comment-submit:hover {
      background: var(--navhover);
    }

    @media (max-width: 600px) {
      .comment-form {
        flex-direction: column;
      }

      .comment-input,
      .comment-submit {
        width: 100%;
      }
    }
  </style>
</head>

<body>
<header>
  <h1>Rate It All!</h1>
  <nav>
    <ul>
      <li><a href="../Homepage/index.html">Home</a></li>
      <li><a href="Places.html">Places</a></li>
      <li><a href="Food.html">Food</a></li>
      <li><a href="Media.html">Media</a></li>
      <li><a href="Concepts.html">Concepts</a></li>
      <li><a href="WildCard.html">Wild Card</a></li>
      <li><a href="Other.html">Other</a></li>
      <li><a href="../HomePage/Login.html">Log-in</a></li>
    </ul>
  </nav>
</header>

<main>
  <!-- ✅ Top Rated Section -->
  <section class="top-rated-section">
    <h2>🌟 Top Rated Reviews</h2>
    <?php while($row = $topRatedResult->fetch_assoc()):
      $reviewId = intval($row['postID']);
      $rating = intval($row['rating']);
      $title = htmlspecialchars($row['Title']);
      $content = htmlspecialchars($row['Content']);
      $likes = intval($row['likes']);
    ?>
    <article class="review-card top-rated" id="review-<?= $reviewId ?>">
      <h3><?= $title ?></h3>
      <p><strong>User:</strong> BurgerLover99</p>
      <p><?= $content ?></p>

      <section class="star-rating">
        <?php for($i = 1; $i <= 5; $i++): ?>
          <input type="radio" name="rating-<?= $reviewId ?>" <?= ($i <= $rating) ? 'checked' : '' ?> disabled>
        <?php endfor; ?>
      </section>

      <button class="like-btn" data-review-id="<?= $reviewId ?>">👍 Like <span class="like-count"><?= $likes ?></span></button>

      <section class="comments">
        <h4>Comments</h4>
        <ul class="comment-list" id="comments-<?= $reviewId ?>"></ul>

        <form class="comment-form" data-review-id="<?= $reviewId ?>">
          <input type="text" name="comment_text" placeholder="Add a comment..." class="comment-input" required>
          <button type="submit" class="comment-submit">Comment</button>
        </form>
      </section>
    </article>
    <?php endwhile; ?>
  </section>

  <!-- 🔽 All Reviews Section -->
  <section class="featured-reviews">
    <?php while($row = $result->fetch_assoc()):
      $reviewId = intval($row['postID']);
      $rating = intval($row['rating']);
      $title = htmlspecialchars($row['Title']);
      $content = htmlspecialchars($row['Content']);
      $likes = intval($row['likes']);
      $highlightClass = ($rating == 5) ? 'top-rated' : '';
    ?>
    <article class="review-card <?= $highlightClass ?>" id="review-<?= $reviewId ?>">
      <h3><?= $title ?></h3>
      <p><strong>User:</strong> BurgerLover99</p>
            <p><?= $content ?></p>

      <section class="star-rating">
        <?php for($i = 1; $i <= 5; $i++): ?>
          <input type="radio" name="rating-<?= $reviewId ?>" <?= ($i <= $rating) ? 'checked' : '' ?> disabled>
        <?php endfor; ?>
      </section>

      <button class="like-btn" data-review-id="<?= $reviewId ?>">
        👍 Like <span class="like-count"><?= $likes ?></span>
      </button>

      <section class="comments">
        <h4>Comments</h4>
        <ul class="comment-list" id="comments-<?= $reviewId ?>"></ul>

        <form class="comment-form" data-review-id="<?= $reviewId ?>">
          <input type="text" name="comment_text" placeholder="Add a comment..." class="comment-input" required>
          <button type="submit" class="comment-submit">Comment</button>
        </form>
      </section>
    </article>
    <?php endwhile; ?>
  </section>
</main>

<footer style="text-align:center; padding:1rem; background:#28a745; color:white; margin-top:2rem;">
  <p>
    Authors: Ngoako Mapokgole - CEO (<a href="mailto:g22m3828@campus.ru.ac.za" style="color:white;">Ngoako@gmail.com</a>)<br>
    Anesipho Nkonkobe - Back End Developer (<a href="mailto:g23n7615@campus.ru.ac.za" style="color:white;">Ane@gmail.com</a>)<br>
    Mathlo Lethabo - Front End Developer (<a href="mailto:g23m7135@campus.ru.ac.za" style="color:white;">xXGamerBoyX@yahoo.com</a>)
  </p>
  <small>&copy; 2025 Anesipho & Friends Corporation. All Rights Reserved.</small>
</footer>

<script>
$(document).ready(function() {
  // LIKE BUTTON AJAX
  $('.like-btn').on('click', function() {
    var reviewId = $(this).data('review-id');
    var likeCountEl = $('.like-count', `#review-${reviewId}`);

    $.post('', { like_review_id: reviewId }, function(response) {
      if (response.success) {
        // Increment the like count visually (can be improved by fetching updated count from DB)
        var currentLikes = parseInt(likeCountEl.text());
        likeCountEl.text(currentLikes + 1);
      }
    }, 'json');
  });

  // FETCH COMMENTS FUNCTION
  function fetchComments(reviewId) {
    $.post('comment-fetch.php', { review_id: reviewId }, function(comments) {
      var commentList = $('#comments-' + reviewId);
      commentList.empty();

      if (comments.length > 0) {
        comments.forEach(function(comment) {
          commentList.append(`<li><strong>${comment.username}:</strong> ${comment.content}</li>`);
        });
      } else {
        commentList.append('<li>No comments yet.</li>');
      }
    }, 'json');
  }

  // Load comments on page load
  $('.review-card').each(function() {
    var reviewId = $(this).attr('id').replace('review-', '');
    fetchComments(reviewId);
  });

  // COMMENT SUBMISSION AJAX
  $('.comment-form').on('submit', function(e) {
    e.preventDefault();

    var form = $(this);
    var reviewId = form.data('review-id');
    var commentInput = form.find('input[name="comment_text"]');
    var commentText = commentInput.val();

    if (commentText.trim().length === 0) return;

    $.post('', {
      comment_review_id: reviewId,
      comment_text: commentText
    }, function(response) {
      if (response.success) {
        $('#comments-' + reviewId).append(`<li><strong>You:</strong> ${response.comment}</li>`);
        commentInput.val('');
      }
    }, 'json');
  });
});
</script>

</body>
</html>

