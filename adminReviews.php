<?php
include "../rate.php";
session_start();

// Restrict access to Admins only
if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../homepage/login.php");
    exit();
}

// Get filters
$search = $_GET['search'] ?? '';
$statusFilter = $_GET['status'] ?? '';

// Base SQL
$sql = "SELECT p.postID, p.Title, p.dateCreated, p.deleted, u.userName
        FROM post p
        JOIN users u ON p.userID = u.userID
        WHERE 1=1";

$params = [];
$types = "";

// Search filter (title or author)
if (!empty($search)) {
    $sql .= " AND (p.Title LIKE ? OR u.userName LIKE ?)";
    $searchTerm = "%" . $search . "%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $types .= "ss";
}

// Status filter
if (!empty($statusFilter)) {
    if ($statusFilter == 'Active') {
        $sql .= " AND p.deleted = 0";
    } elseif ($statusFilter == 'Deleted') {
        $sql .= " AND p.deleted = 1";
    }
}

$sql .= " ORDER BY p.dateCreated DESC";

// Prepare and execute
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<link rel="icon" type="image/svg+xml" href="http://cs3-dev.ict.ru.ac.za/practicals/4a2/logo.png">
<title>Manage Reviews</title>
<link rel="stylesheet" href="admin.css"/> 
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
  .filter-bar {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 20px;
  }
  .filter-bar input, .filter-bar select, .filter-bar button {
    padding: 6px 10px;
    border-radius: 6px;
    border: 1px solid #ccc;
  }
  .filter-bar button {
    background-color: #007bff;
    color: white;
    cursor: pointer;
  }
  .filter-bar button:hover {
    background-color: #0056b3;
  }
  tr.deleted {
    opacity: 0.6;
  
  }
</style>
</head>

<body>
<header>
  <?php include "../adminSidebar.php"?>
</header>

<div class="main">
  <div class="topbar">
    <h1>Review Management</h1>
  </div>

  <!-- Filter/Search Form -->
  <form method="GET" class="filter-bar">
    <input type="text" name="search" placeholder="Search title or author..." value="<?= htmlspecialchars($search) ?>">

    <select name="status">
      <option value="">All Statuses</option>
      <option value="Active" <?= $statusFilter == 'Active' ? 'selected' : '' ?>>Active</option>
      <option value="Deleted" <?= $statusFilter == 'Deleted' ? 'selected' : '' ?>>Deleted</option>
    </select>

    <button type="submit">Filter</button>
    <a href="adminReviews.php" style="text-decoration:none;">
      <button type="button" style="background-color:#6c757d;">Reset</button>
    </a>
  </form>

  <table>
    <thead>
      <tr>
        <th>Review ID</th>
        <th>Title</th>
        <th>Author</th>
        <th>Date</th>
        <th>Status</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php if ($result->num_rows > 0): ?>
        <?php while($row = $result->fetch_assoc()): ?>
          <tr class="<?= $row['deleted'] ? 'deleted' : '' ?>">
            <td>#<?= $row['postID'] ?></td>
            <td><?= htmlspecialchars($row['Title']) ?></td>
            <td>@<?= htmlspecialchars($row['userName']) ?></td>
            <td><?= date("Y-m-d", strtotime($row['dateCreated'])) ?></td>
            <td><?= $row['deleted'] ? 'Deleted' : 'Active' ?></td>
            <td>
              <?php if($row['deleted']): ?>
                <a href="viewDeletedPost.php?id=<?= $row['postID'] ?>" style="text-decoration:none;">
                  <button class="action-btn btn-view">View Deleted</button>
                </a>
                <form action="reviewAction.php" method="POST" style="display:inline;">
                  <input type="hidden" name="postID" value="<?= $row['postID'] ?>">
                  <input type="hidden" name="action" value="restore">
                  <button class="action-btn btn-restore" type="submit">Restore</button>
                </form>
              <?php else: ?>
                <a href="../HomePage/viewPost.php?id=<?= $row['postID'] ?>" style="text-decoration:none;">
                  <button class="action-btn btn-view">View</button>
                </a>
                <form action="reviewAction.php" method="POST" style="display:inline;">
                  <input type="hidden" name="postID" value="<?= $row['postID'] ?>">
                  <input type="hidden" name="action" value="delete">
                  <button class="action-btn btn-delete" type="submit">Delete</button>
                </form>
              <?php endif; ?>
            </td>
          </tr>
        <?php endwhile; ?>
      <?php else: ?>
        <tr><td colspan="6" style="text-align:center;">No reviews found</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>
</body>
</html>
