<?php
session_start();
include "../rate.php"; // Database connection

// Only allow Admins or Mods
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['Admin', 'Mod'])) {
    die("Access denied.");
}

// Get filters
$search = $_GET['search'] ?? '';
$statusFilter = $_GET['status'] ?? '';

// Base SQL
$sql = "
SELECT r.*, u.userName, p.Title AS postTitle
FROM reports r
JOIN users u ON r.userID = u.userID
JOIN post p ON r.postID = p.postID
WHERE r.contentType = 'Post'
";

$params = [];
$types = "";

// Apply search filter
if (!empty($search)) {
    $sql .= " AND (u.userName LIKE ? OR p.Title LIKE ? OR r.reportType LIKE ? OR r.description LIKE ?)";
    $searchTerm = "%" . $search . "%";
    $params = [$searchTerm, $searchTerm, $searchTerm, $searchTerm];
    $types = "ssss";
}

// Apply status filter
if (!empty($statusFilter)) {
    $sql .= " AND r.status = ?";
    $params[] = $statusFilter;
    $types .= "s";
}

$sql .= " ORDER BY r.createdAt DESC";

// Prepare and execute safely
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
<title>Admin - Post Reports</title>
<link rel="icon" type="image/svg+xml" href="http://cs3-dev.ict.ru.ac.za/practicals/4a2/logo.png">
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
</style>
</head>

<body>
<header>
  <?php include "../adminSidebar.php"; ?>
</header>

<div class="main">
  <div class="topbar">
    <h1>Reports Management</h1>
  </div>

  <!-- 🔍 Search & Filter Form -->
  <form method="GET" class="filter-bar">
    <input type="text" name="search" placeholder="Search reporter, post, or reason..." 
           value="<?= htmlspecialchars($search) ?>">

    <select name="status">
      <option value="">All Statuses</option>
      <option value="Pending" <?= $statusFilter == 'Pending' ? 'selected' : '' ?>>Pending</option>
      <option value="Viewed" <?= $statusFilter == 'Viewed' ? 'selected' : '' ?>>Viewed</option>
      <option value="Cleared" <?= $statusFilter == 'Cleared' ? 'selected' : '' ?>>Cleared</option>
      <option value="Blocked" <?= $statusFilter == 'Blocked' ? 'selected' : '' ?>>Blocked</option>
    </select>

    <button type="submit">Filter</button>
    <a href="adminReports.php" style="text-decoration:none;">
      <button type="button" style="background-color:#6c757d;">Reset</button>
    </a>
  </form>

  <?php if ($result->num_rows === 0): ?>
    <p>No post reports found.</p>
  <?php else: ?>
    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Reporter</th>
          <th>Post</th>
          <th>Reason</th>
          <th>Description</th>
          <th>Status</th>
          <th>Created At</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php while($row = $result->fetch_assoc()): ?>
        <tr>
          <td>#<?= $row['reportID'] ?></td>
          <td><?= htmlspecialchars($row['userName']) ?></td>
          <td>
            <a href="../Homepage/viewPost.php?id=<?= $row['postID'] ?>" target="_blank">
              <?= htmlspecialchars($row['postTitle']) ?>
            </a>
          </td>
          <td><?= htmlspecialchars($row['reportType']) ?></td>
          <td><?= nl2br(htmlspecialchars($row['description'])) ?></td>
          <td class="status-<?= $row['status'] ?>"><?= htmlspecialchars($row['status']) ?></td>
          <td><?= date("Y-m-d H:i", strtotime($row['createdAt'])) ?></td>
          <td>
            <form action="updateReport.php" method="POST" style="display:flex; flex-direction:column;">
              <input type="hidden" name="reportID" value="<?= $row['reportID'] ?>">
              <select name="status" required style="margin-bottom:6px; padding:4px; border-radius:6px; border:1px solid #ccc;">
                <option value="">Change Status</option>
                <option value="Viewed">Viewed</option>
                <option value="Cleared">Cleared</option>
                <option value="Blocked">Blocked</option>
              </select>
              <button type="submit" class="action-btn edit">Update</button>
            </form>
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>

</body>
</html>
