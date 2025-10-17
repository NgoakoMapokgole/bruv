<?php
session_start();
include "../rate.php";

// --- Only Admins allowed ---
if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../homepage/login.php");
    exit();
}

$userName = $_SESSION['userName'];

// --- Get filters ---
$search = $_GET['search'] ?? '';
$tableFilter = $_GET['table'] ?? '';

// --- Base SQL ---
$sql = "
    SELECT a.*, u.userName
    FROM audit a
    JOIN users u ON a.userID = u.userID
    WHERE 1=1
";

$params = [];
$types = "";

// Apply search
if (!empty($search)) {
    $sql .= " AND (u.userName LIKE ? OR a.tableAffected LIKE ? OR a.Action LIKE ?)";
    $searchTerm = "%$search%";
    $params = [$searchTerm, $searchTerm, $searchTerm];
    $types = "sss";
}

// Apply table filter
if (!empty($tableFilter)) {
    $sql .= " AND a.tableAffected = ?";
    $params[] = $tableFilter;
    $types .= "s";
}

$sql .= " ORDER BY a.time DESC";

// --- Prepare & Execute ---
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$reviews = $stmt->get_result();

if (!$reviews) {
    die("Error fetching audit logs: " . $conn->error);
}

// --- Get distinct table names for filter dropdown ---
$tables = $conn->query("SELECT DISTINCT tableAffected FROM audit ORDER BY tableAffected");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <link rel="icon" type="image/svg+xml" href="http://cs3-dev.ict.ru.ac.za/practicals/4a2/logo.png">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin - View Audit Logs</title>
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
      <h1>View Audit Logs</h1>
    </div>

    <!-- 🔍 Search and Filter -->
    <form method="GET" class="filter-bar">
      <input type="text" name="search" placeholder="Search username, table, or action..." 
             value="<?= htmlspecialchars($search) ?>">

      <select name="table">
        <option value="">All Tables</option>
        <?php while($t = $tables->fetch_assoc()): ?>
          <option value="<?= htmlspecialchars($t['tableAffected']) ?>"
            <?= ($tableFilter == $t['tableAffected']) ? 'selected' : '' ?>>
            <?= htmlspecialchars($t['tableAffected']) ?>
          </option>
        <?php endwhile; ?>
      </select>

      <button type="submit">Filter</button>
      <a href="adminAudits.php" style="text-decoration:none;">
        <button type="button" style="background-color:#6c757d;">Reset</button>
      </a>
    </form>

    <?php if ($reviews->num_rows === 0): ?>
      <p>No audit records found.</p>
    <?php else: ?>
      <table>
        <thead>
          <tr>
            <th>User Name</th>
            <th>Table Affected</th>
            <th>Action</th>
            <th>Previous Value</th>
            <th>Current Value</th>
            <th>IP Address</th>
            <th>Date Captured</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($r = $reviews->fetch_assoc()): ?>
          <tr>
            <td><?= htmlspecialchars($r['userName']) ?></td>
            <td><?= htmlspecialchars($r['tableAffected']) ?></td>
            <td><?= htmlspecialchars($r['Action']) ?></td>
            <td><?= htmlspecialchars($r['PreviousValue'] ?? 'N/A') ?></td>
            <td><?= htmlspecialchars($r['CurrentValue'] ?? 'N/A') ?></td>
            <td><?= htmlspecialchars($r['IP']) ?></td>
            <td><?= date("Y-m-d H:i:s", strtotime($r['time'])) ?></td>
          </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>
</body>
</html>
