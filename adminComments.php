<?php
include "../rate.php";
session_start();

// restrict access
if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../homepage/login.php");
    exit();
}

// Get filters
$search = $_GET['search'] ?? '';
$statusFilter = $_GET['status'] ?? '';

// Base SQL
$sql = "SELECT c.commentID, c.content, c.deleted, c.datecCreated, 
               u.userName, p.Title AS postTitle, p.postID
        FROM comments c
        JOIN users u ON c.userID = u.userID
        JOIN post p ON c.postID = p.postID
        WHERE 1=1";

$params = [];
$types = "";

// Apply search filter
if (!empty($search)) {
    $sql .= " AND (c.content LIKE ? OR u.userName LIKE ? OR p.Title LIKE ?)";
    $searchTerm = "%" . $search . "%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $types .= "sss";
}

// Apply status filter
if (!empty($statusFilter)) {
    if ($statusFilter === "Active") {
        $sql .= " AND c.deleted = 0";
    } elseif ($statusFilter === "Deleted") {
        $sql .= " AND c.deleted = 1";
    }
}

$sql .= " ORDER BY c.datecCreated DESC";

// Prepare and bind
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
<title>Manage Comments</title>
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
    background-color: #f8d7da;
  }
</style>
</head>
<body>

<header>
  <?php include "../adminSidebar.php"?>
</header>

<div class="main">
  <div class="topbar">
    <h1>Manage Comments</h1>
  </div>

  <!-- Filter/Search Form -->
  <form method="GET" class="filter-bar">
    <input type="text" name="search" placeholder="Search comments, author, or post..." 
           value="<?= htmlspecialchars($search) ?>">

    <select name="status">
      <option value="">All Statuses</option>
      <option value="Active" <?= $statusFilter == 'Active' ? 'selected' : '' ?>>Active</option>
      <option value="Deleted" <?= $statusFilter == 'Deleted' ? 'selected' : '' ?>>Deleted</option>
    </select>

    <button type="submit">Filter</button>
    <a href="adminComments.php" style="text-decoration:none;">
      <button type="button" style="background-color:#6c757d;">Reset</button>
    </a>
  </form>

  <table>
    <thead>
      <tr>
        <th>ID</th>
        <th>Author</th>
        <th>Post</th>
        <th>Content</th>
        <th>Date</th>
        <th>Status</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php if ($result->num_rows > 0): ?>
        <?php while($row = $result->fetch_assoc()): ?>
          <tr class="<?= $row['deleted'] ? 'deleted' : '' ?>">
            <td>#<?= $row['commentID'] ?></td>
            <td>@<?= htmlspecialchars($row['userName']) ?></td>
            <td><a href="../HomePage/viewPost.php?id=<?= $row['postID'] ?>">
                <?= htmlspecialchars($row['postTitle']) ?></a></td>
            <td><?= htmlspecialchars(substr($row['content'], 0, 80)) ?><?= strlen($row['content']) > 80 ? '...' : '' ?></td>
            <td><?= date("Y-m-d H:i", strtotime($row['datecCreated'])) ?></td>
            <td><?= $row['deleted'] ? 'Deleted' : 'Active' ?></td>
            <td>
              <?php if($row['deleted']): ?>
                <a href="viewDeletedComment.php?id=<?= $row['commentID'] ?>" style="text-decoration:none;">
                  <button class="action-btn btn-view">View</button>
                </a>
                <form action="commentAction.php" method="POST" style="display:inline;">
                  <input type="hidden" name="commentID" value="<?= $row['commentID'] ?>">
                  <input type="hidden" name="action" value="restore">
                  <button class="action-btn btn-restore" type="submit">Restore</button>
                </form>
              <?php else: ?>
                <form action="commentAction.php" method="POST" style="display:inline;">
                  <input type="hidden" name="commentID" value="<?= $row['commentID'] ?>">
                  <input type="hidden" name="action" value="delete">
                  <button class="action-btn btn-delete" type="submit" onclick="return confirm('Delete this comment?')">Delete</button>
                </form>
              <?php endif; ?>
            </td>
          </tr>
        <?php endwhile; ?>
      <?php else: ?>
        <tr><td colspan="7" style="text-align:center;">No comments found</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

</body>
</html>
