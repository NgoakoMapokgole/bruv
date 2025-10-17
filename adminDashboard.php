<?php

session_start();
include "../rate.php";


if(!isset($_SESSION['userID']) || $_SESSION['role']!=='Admin'){
    header("Location: ../homepage/login.php");
    exit();
}

$userName = $_SESSION['userName'];

// --- Summary stats ---
$usersCount = $conn->query("SELECT COUNT(*) AS cnt FROM users")->fetch_assoc()['cnt'];
$reviewsCount = $conn->query("SELECT COUNT(*) AS cnt FROM post WHERE deleted=0")->fetch_assoc()['cnt'];
$commentsCount = $conn->query("SELECT COUNT(*) AS cnt FROM comments")->fetch_assoc()['cnt'];
$reportsCount = $conn->query("SELECT COUNT(*) AS cnt FROM reports")->fetch_assoc()['cnt'] ?? 0;

// --- Recent reviews ---
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
<link rel="icon" type = "image/svg+xml" href="http://cs3-dev.ict.ru.ac.za/practicals/4a2/logo.png">
<title>Admin Dashboard</title>
<link rel="stylesheet" href="admin.css"/> 
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

</head>
<style>

  #details {
    margin-top: 20px;
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    justify-content: space-evenly;
    align-items: flex-start;
  }

  #details canvas {
    flex: 1 1 30%;
    min-width: 300px;
    max-width: 400px;
    height: 300px !important;
  }

  #details p {
    text-align: center;
    width: 100%;
  }
</style>

<body>

  <header>
        <?php include "../adminSidebar.php"?>
  </header>

<div class="main">
<div class="topbar">
<h1>Dashboard Overview</h1>
<h2>Welcome, <?= htmlspecialchars($userName) ?></h2>
</div>

<div class="cards">
  <div class="card" data-type="users">
    <h3>Users</h3>
    <p><?= number_format($usersCount) ?> Registered</p>
  </div>
  <div class="card" data-type="reviews">
    <h3>Reviews</h3>
    <p><?= number_format($reviewsCount) ?> Posted</p>
  </div>
  <div class="card" data-type="comments">
    <h3>Comments</h3>
    <p><?= number_format($commentsCount) ?> Total</p>
  </div>
  <div class="card" data-type="reports">
    <h3>Reports</h3>
    <p><?= number_format($reportsCount) ?> Total</p>
  </div>
</div>

<div id="details">
<h4>Select a card to view detailed analytics</h4>
</div>

<h2>Recent Reviews</h2>
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
<td><?= htmlspecialchars($row['userName']) ?></td>
<td><?= htmlspecialchars($row['Title']) ?></td>
<td class="status-<?= $row['deleted'] ? 'deleted' : 'approved' ?>">
<?= $row['deleted'] ? 'Deleted' : 'Approved' ?>
</td>
<td><?= date("Y-m-d", strtotime($row['dateCreated'])) ?></td>
</tr>
<?php endwhile; ?>
</tbody>
</table>

<script>
document.querySelectorAll('.card').forEach(card => {
    card.addEventListener('click', function() {
        const type = this.getAttribute('data-type');
        const details = document.getElementById('details');

        // Toggle visibility — hide if already showing this type
        if (details.getAttribute('data-active') === type) {
            details.innerHTML = '<h4>Select a card to view detailed analytics</h4>';
            details.removeAttribute('data-active');
            return;
        }

        details.setAttribute('data-active', type);
        details.innerHTML = "<p>Loading...</p>";

        fetch(`analytics2.php?type=${type}`)
        .then(res => res.json())
        .then(data => {
            details.innerHTML = ''; // clear

            // Helper function to create canvas with margin
            const makeCanvas = () => {
                const canvas = document.createElement('canvas');
                canvas.style.margin = "10px";
                canvas.style.flex = "1";
                return canvas;
            };

            // Container for inline layout
            const chartContainer = document.createElement('div');
            chartContainer.style.display = "flex";
            chartContainer.style.flexWrap = "wrap";
            chartContainer.style.gap = "20px";
            chartContainer.style.justifyContent = "center";
            details.appendChild(chartContainer);

            const commonOpts = title => ({
                plugins: {
                    title: {
                        display: true,
                        text: title,
                        font: { size: 14, weight: 'bold' }
                    },
                    legend: { display: true }
                },
                responsive: true,
                maintainAspectRatio: false
            });

            if (type === 'users') {
                const regCanvas = makeCanvas();
                const roleCanvas = makeCanvas();
                const statusCanvas = makeCanvas();
                chartContainer.append(regCanvas, roleCanvas, statusCanvas);

                new Chart(regCanvas, {
                    type: 'line',
                    data: {
                        labels: data.months,
                        datasets: [{
                            label: 'Registrations',
                            data: data.registrations,
                            borderColor: '#03a9f4',
                            backgroundColor: 'rgba(3,169,244,0.2)',
                            fill: true
                        }]
                    },
                    options: commonOpts('User Registrations Over Time')
                });

                new Chart(roleCanvas, {
                    type: 'doughnut',
                    data: {
                        labels: data.roleLabels,
                        datasets: [{
                            data: data.roleCounts,
                            backgroundColor: ['#ff6384', '#36a2eb', '#ffce56']
                        }]
                    },
                    options: commonOpts('Role Distribution')
                });

                new Chart(statusCanvas, {
                    type: 'bar',
                    data: {
                        labels: data.statusLabels,
                        datasets: [{
                            label: 'Account Status',
                            data: data.statusCounts,
                            backgroundColor: '#bdc007'
                        }]
                    },
                    options: commonOpts('Account Status Overview')
                });

            } else if (type === 'reviews') {
                const trendCanvas = makeCanvas();
                const statusCanvas = makeCanvas();
                chartContainer.append(trendCanvas, statusCanvas);

                new Chart(trendCanvas, {
                    type: 'line',
                    data: {
                        labels: data.months,
                        datasets: [{
                            label: 'Reviews per Month',
                            data: data.trend,
                            borderColor: '#e91e63',
                            backgroundColor: 'rgba(233,30,99,0.2)',
                            fill: true
                        }]
                    },
                    options: commonOpts('Reviews Over Time')
                });

                new Chart(statusCanvas, {
                    type: 'pie',
                    data: {
                        labels: data.statusLabels,
                        datasets: [{
                            data: data.statusCounts,
                            backgroundColor: ['#4caf50', '#f44336']
                        }]
                    },
                    options: commonOpts('Review Status Distribution')
                });

            } else if (type === 'comments') {
                const trendCanvas = makeCanvas();
                const topCanvas = makeCanvas();
                chartContainer.append(trendCanvas, topCanvas);

                new Chart(trendCanvas, {
                    type: 'bar',
                    data: {
                        labels: data.months,
                        datasets: [{
                            label: 'Comments per Month',
                            data: data.trend,
                            backgroundColor: '#03a9f4'
                        }]
                    },
                    options: commonOpts('Comments Activity Over Time')
                });

                new Chart(topCanvas, {
                    type: 'pie',
                    data: {
                        labels: data.topLabels,
                        datasets: [{
                            data: data.topCounts,
                            backgroundColor: ['#ff6384', '#36a2eb', '#ffce56', '#4caf50', '#9c27b0']
                        }]
                    },
                    options: commonOpts('Top Commented Posts')
                });

            } else if (type === 'reports') {
                const trendCanvas = makeCanvas();
                const statusCanvas = makeCanvas();
                const typeCanvas = makeCanvas();
                chartContainer.append(trendCanvas, statusCanvas, typeCanvas);

                new Chart(trendCanvas, {
                    type: 'line',
                    data: {
                        labels: data.months,
                        datasets: [{
                            label: 'Reports per Month',
                            data: data.trend,
                            borderColor: '#ff9800',
                            backgroundColor: 'rgba(255,152,0,0.2)',
                            fill: true
                        }]
                    },
                    options: commonOpts('Reports Over Time')
                });

                new Chart(statusCanvas, {
                    type: 'doughnut',
                    data: {
                        labels: data.statusLabels,
                        datasets: [{
                            data: data.statusCounts,
                            backgroundColor: ['#ff5722', '#ffc107', '#8bc34a', '#9c27b0']
                        }]
                    },
                    options: commonOpts('Report Status Overview')
                });

                new Chart(typeCanvas, {
                    type: 'pie',
                    data: {
                        labels: data.typeLabels,
                        datasets: [{
                            data: data.typeCounts,
                            backgroundColor: ['#03a9f4', '#f44336']
                        }]
                    },
                    options: commonOpts('Report Type Breakdown')
                });
            }
        });
    });
});
</script>


</div>
</body>
</html>
