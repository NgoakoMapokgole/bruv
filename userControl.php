<?php
include "../rate.php";
session_start();

// restrict access
if(!isset($_SESSION['userID']) || $_SESSION['role'] !== 'Admin'){
    header("Location: ../homepage/login.php");
    exit();
}

// Get filters
$search = $_GET['search'] ?? '';
$roleFilter = $_GET['role'] ?? '';
$statusFilter = $_GET['status'] ?? '';

// Base SQL
$sql = "SELECT userID, userName, userEmail, role, accountStatus, activeDate, deleted 
        FROM users 
        WHERE 1=1";

// Add conditions dynamically
$params = [];
$types = "";

if (!empty($search)) {
    $sql .= " AND (userName LIKE ? OR userEmail LIKE ?)";
    $searchTerm = "%" . $search . "%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $types .= "ss";
}

if (!empty($roleFilter)) {
    $sql .= " AND role = ?";
    $params[] = $roleFilter;
    $types .= "s";
}

if (!empty($statusFilter)) {
    $sql .= " AND accountStatus = ?";
    $params[] = $statusFilter;
    $types .= "s";
}

$sql .= " ORDER BY activeDate DESC";

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
<title>Manage Users</title>
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
    <h1>User Management</h1>
  </div>

  <!-- Filter/Search Form -->
  <form method="GET" class="filter-bar">
    <input type="text" name="search" placeholder="Search username or email..." value="<?= htmlspecialchars($search) ?>">

    <select name="role">
      <option value="">All Roles</option>
      <option value="Admin" <?= $roleFilter == 'Admin' ? 'selected' : '' ?>>Admin</option>
      <option value="User" <?= $roleFilter == 'User' ? 'selected' : '' ?>>User</option>
    </select>

    <select name="status">
      <option value="">All Statuses</option>
      <option value="Active" <?= $statusFilter == 'Active' ? 'selected' : '' ?>>Active</option>
      <option value="Suspended" <?= $statusFilter == 'Suspended' ? 'selected' : '' ?>>Suspended</option>
    </select>

    <button type="submit">Filter</button>
    <a href="userControl.php" style="text-decoration:none;">
      <button type="button" style="background-color:#6c757d;">Reset</button>
    </a>
  </form>

  <table>
    <thead>
      <tr>
        <th>Username</th>
        <th>Email</th>
        <th>Role</th>
        <th>Status</th>
        <th>Joined</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php if ($result->num_rows > 0): ?>
        <?php while($row = $result->fetch_assoc()): ?>
          <tr class="<?= $row['deleted'] ? 'deleted' : '' ?>">
            <td><?= htmlspecialchars($row['userName']) ?></td>
            <td><?= htmlspecialchars($row['userEmail']) ?></td>
            <td><?= ucfirst($row['role']) ?></td>
            <td><?= ucfirst($row['accountStatus']) ?><?= $row['deleted'] ? " (Deleted)" : "" ?></td>
            <td><?= date("Y-m-d", strtotime($row['activeDate'])) ?></td>
            <td>
              <form method="post" action="user_actions.php" style="display:inline;">
                <input type="hidden" name="id" value="<?= $row['userID'] ?>">
                <button class="action-btn edit" name="action" value="edit">Edit</button>
                <?php if(!$row['deleted']): ?>
                  <button class="action-btn delete" name="action" value="soft_delete" onclick="return confirm('Soft delete this user?')">Soft Delete</button>
                <?php else: ?>
                  <button class="action-btn delete" name="action" value="recover" onclick="return confirm('Recover this user?')">Recover</button>
                <?php endif; ?>
                <?php if ($row['accountStatus'] != "Suspended"): ?>
                  <button class="action-btn ban" name="action" value="ban">Ban</button>
                <?php else: ?>
                  <button class="action-btn ban" name="action" value="unban">Unban</button>
                <?php endif; ?>
              </form>
            </td>
          </tr>
        <?php endwhile; ?>
      <?php else: ?>
        <tr><td colspan="6" style="text-align:center;">No users found</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

</body>
</html>
