<?php
// admin.php – Dynamic Admin Dashboard
session_start();
include "../rate.php";



// --- Check login & role ---
if(!isset($_SESSION['userID']) || $_SESSION['role'] !== 'Admin'){
    header("Location: ../homepage/login.php");
    exit();
}

$userName = $_SESSION['userName'];

// --- Fetch summary stats ---
$usersCount = $conn->query("SELECT COUNT(*) AS cnt FROM users")->fetch_assoc()['cnt'];
$reviewsCount = $conn->query("SELECT COUNT(*) AS cnt FROM post WHERE deleted=0")->fetch_assoc()['cnt'];
$commentsCount = $conn->query("SELECT COUNT(*) AS cnt FROM comments")->fetch_assoc()['cnt'];
$reportsCount = $conn->query("SELECT COUNT(*) AS cnt FROM reports")->fetch_assoc()['cnt'] ?? 0; // optional reports table

// --- Fetch recent reviews ---
$recentReviews = $conn->query("
    SELECT p.postID, p.Title, p.dateCreated, u.userName, p.deleted
    FROM post p
    JOIN users u ON p.userID = u.userID
    ORDER BY p.dateCreated DESC
    LIMIT 10
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard</title>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <link rel="stylesheet" href="styles.css"> <!-- optional external CSS -->
  <style>

  </style>
</head>
<body>

  <div class="sidebar">
    <h2>Admin</h2>
    <ul>
      <li><a href="#">Dashboard</a></li>
      <li><a href="#">Users</a></li>
      <li><a href="#">Reviews</a></li>
      <li><a href="#">Comments</a></li>
      <li><a href="#">Reports</a></li>
      <li><a href="#">Settings</a></li>
      <li><a href="logout.php">Logout</a></li>
    </ul>
  </div>

  <div class="main">
    <div class="topbar">
      <h1>Dashboard Overview</h1>
      <span>Welcome, <?= htmlspecialchars($userName) ?></span>
    </div>

    <div class="cards">
  <div class="card" data-id="users">
    <h3>Users</h3>
    <p><?= number_format($usersCount) ?> registered</p>
  </div>
  <div class="card" data-id="reviews">
    <h3>Reviews</h3>
    <p><?= number_format($reviewsCount) ?> posted</p>
  </div>
  <div class="card" data-id="comments">
    <h3>Comments</h3>
    <p><?= number_format($commentsCount) ?> total</p>
  </div>
  <div class="card" data-id="reports">
    <h3>Reports</h3>
    <p><?= number_format($reportsCount) ?> pending</p>
  </div>
</div>

<div id="details">
  <h4>Select a card to view detailed analytics</h4>
</div>

<script>
// Handle card clicks
document.querySelectorAll('.card').forEach(card => {
  card.addEventListener('click', function() {
    const id = this.getAttribute('data-id');
    fetch(`analytics2.php?type=${id}`)
      .then(res => res.text())
      .then(html => {
        document.getElementById('details').innerHTML = html;
      })
      .catch(err => {
        document.getElementById('details').innerHTML = "<p>Error loading data.</p>";
      });
  });
});
</script>

    <h2 style="margin-top:2rem;">Recent Reviews</h2>
    <table>
      <thead>
        <tr>
          <th>User</th>
          <th>Review</th>
          <th>Status</th>
          <th>Date</th>
        </tr>
      </thead>
      <tbody>
        <?php while($row = $recentReviews->fetch_assoc()): ?>
      <tr>
          <td><a href="viewReview.php?id=<?= $row['postID'] ?>">@<?= htmlspecialchars($row['userName']) ?></a></td>
          <td><?= htmlspecialchars($row['Title']) ?></td>
          <td class="status-<?= $row['deleted'] ? 'deleted' : 'approved' ?>">
            <?= $row['deleted'] ? 'Deleted' : 'Approved' ?>
          </td>
          <td><?= date("Y-m-d", strtotime($row['dateCreated'])) ?></td>
      </tr>

        <?php endwhile; ?>
      </tbody>
    </table>
  </div>

</body>
</html>
