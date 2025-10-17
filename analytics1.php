<?php
session_start();
include "../rate.php";
header("Content-Type: text/html; charset=UTF-8");

$type = $_GET['type'] ?? '';

if ($type === 'users') {
    // --- 1. Registrations Over Time (last 6 months)
    $sql = "
        SELECT DATE_FORMAT(activeDate, '%Y-%m') AS month, COUNT(*) AS total
        FROM users
        WHERE deleted = 0
        GROUP BY DATE_FORMAT(activeDate, '%Y-%m')
        ORDER BY month ASC
    ";
    $registrations = $conn->query($sql);
    $months = [];
    $counts = [];
    while ($row = $registrations->fetch_assoc()) {
        $months[] = $row['month'];
        $counts[] = $row['total'];
    }

    // --- 2. Role Distribution
    $sql2 = "SELECT role, COUNT(*) AS total FROM users WHERE deleted=0 GROUP BY role";
    $roles = $conn->query($sql2);
    $roleLabels = [];
    $roleCounts = [];
    while ($r = $roles->fetch_assoc()) {
        $roleLabels[] = $r['role'];
        $roleCounts[] = $r['total'];
    }

    // --- 3. Account Status Breakdown
    $sql3 = "SELECT accountStatus, COUNT(*) AS total FROM users WHERE deleted=0 GROUP BY accountStatus";
    $statuses = $conn->query($sql3);
    $statusLabels = [];
    $statusCounts = [];
    while ($s = $statuses->fetch_assoc()) {
        $statusLabels[] = $s['accountStatus'];
        $statusCounts[] = $s['total'];
    }
    ?>
    <h3 style="color:#bdc007;">👥 User Analytics</h3>

    <div style="display:grid; gap:2rem; margin-top:1rem;">
      <div style="background:#1f1f1f; border-radius:12px; padding:1rem;">
        <h4>Registrations Over Time</h4>
        <canvas id="userRegistrations"></canvas>
      </div>

      <div style="background:#1f1f1f; border-radius:12px; padding:1rem;">
        <h4>User Role Distribution</h4>
        <canvas id="roleDistribution"></canvas>
      </div>

      <div style="background:#1f1f1f; border-radius:12px; padding:1rem;">
        <h4>Account Status Breakdown</h4>
        <canvas id="statusBreakdown"></canvas>
      </div>
    </div>

    <script>
    // Registration chart
    new Chart(document.getElementById('userRegistrations').getContext('2d'), {
      type: 'line',
      data: {
        labels: <?= json_encode($months) ?>,
        datasets: [{
          label: 'New Users',
          data: <?= json_encode($counts) ?>,
          borderColor: '#bdc007',
          backgroundColor: 'rgba(189,192,7,0.3)',
          fill: true,
          tension: 0.3
        }]
      },
      options: { scales: { y: { beginAtZero: true } } }
    });

    // Role distribution chart
    new Chart(document.getElementById('roleDistribution').getContext('2d'), {
      type: 'pie',
      data: {
        labels: <?= json_encode($roleLabels) ?>,
        datasets: [{
          data: <?= json_encode($roleCounts) ?>,
          backgroundColor: ['#e91e63', '#03a9f4', '#8bc34a']
        }]
      },
      options: { plugins: { legend: { position: 'bottom' } } }
    });

    // Account status chart
    new Chart(document.getElementById('statusBreakdown').getContext('2d'), {
      type: 'doughnut',
      data: {
        labels: <?= json_encode($statusLabels) ?>,
        datasets: [{
          data: <?= json_encode($statusCounts) ?>,
          backgroundColor: ['#4caf50', '#ffc107', '#f44336', '#9e9e9e']
        }]
      },
      options: { plugins: { legend: { position: 'bottom' } } }
    });
    </script>
    <?php
}
else {
    echo "<p>No analytics available for this category.</p>";
}
?>
