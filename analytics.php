<?php
include "../rate.php";

$type = $_GET['type'] ?? '';

echo "<script src='https://cdn.jsdelivr.net/npm/chart.js'></script>
<style>
h3 { font-family: Arial, sans-serif; margin-top: 25px; }
.chart-box { background: rgba(255,255,255,0.05); padding: 20px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.3); margin-top: 20px; }
canvas { margin-top: 15px; max-height: 400px; }
</style>";

if ($type === 'users') {
    // ───────────── USER REGISTRATION TREND ─────────────
    $data = $conn->query("SELECT DATE(activeDate) AS d, COUNT(*) AS c FROM users GROUP BY DATE(activeDate) ORDER BY d ASC");
    $labels = []; $values = [];
    while ($r = $data->fetch_assoc()) { $labels[] = $r['d']; $values[] = $r['c']; }

    // ───────────── USER ROLE DISTRIBUTION ─────────────
    $roles = $conn->query("SELECT role, COUNT(*) AS c FROM users GROUP BY role");
    $role_labels = []; $role_values = [];
    while ($r = $roles->fetch_assoc()) { $role_labels[] = $r['role']; $role_values[] = $r['c']; }

    // ───────────── ACCOUNT STATUS DISTRIBUTION ─────────────
    $status = $conn->query("SELECT accountStatus, COUNT(*) AS c FROM users GROUP BY accountStatus");
    $status_labels = []; $status_values = [];
    while ($r = $status->fetch_assoc()) { $status_labels[] = $r['accountStatus']; $status_values[] = $r['c']; }

    echo "
    <div class='chart-box'>
        <h3>👤 User Registrations Over Time</h3>
        <canvas id='chart1'></canvas>
    </div>
    <div class='chart-box'>
        <h3>📊 User Role Distribution</h3>
        <canvas id='chart2'></canvas>
    </div>
    <div class='chart-box'>
        <h3>🧩 Account Status Breakdown</h3>
        <canvas id='chart3'></canvas>
    </div>

    <script>
    new Chart(document.getElementById('chart1'), {
        type: 'line',
        data: {
            labels: " . json_encode($labels) . ",
            datasets: [{
                label: 'New Users per Day',
                data: " . json_encode($values) . ",
                fill: true,
                backgroundColor: 'rgba(0, 123, 255, 0.2)',
                borderColor: '#007bff',
                tension: 0.3
            }]
        },
        options: { 
            responsive: true,
            maintainAspectRatio: true,
            scales: { 
                y: { 
                    beginAtZero: true,
                    ticks: { color: '#fff' },
                    grid: { color: 'rgba(255,255,255,0.1)' }
                },
                x: {
                    ticks: { color: '#fff' },
                    grid: { color: 'rgba(255,255,255,0.1)' }
                }
            },
            plugins: {
                legend: { labels: { color: '#fff' } }
            }
        }
    });

    new Chart(document.getElementById('chart2'), {
        type: 'pie',
        data: {
            labels: " . json_encode($role_labels) . ",
            datasets: [{
                data: " . json_encode($role_values) . ",
                backgroundColor: ['#007bff', '#28a745', '#ffc107']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: { labels: { color: '#fff' } }
            }
        }
    });

    new Chart(document.getElementById('chart3'), {
        type: 'doughnut',
        data: {
            labels: " . json_encode($status_labels) . ",
            datasets: [{
                data: " . json_encode($status_values) . ",
                backgroundColor: ['#17a2b8', '#6c757d', '#dc3545', '#28a745']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: { labels: { color: '#fff' } }
            }
        }
    });
    </script>";

} elseif ($type === 'reviews') {
    // ───────────── REVIEWS PER DAY ─────────────
    $data = $conn->query("SELECT DATE(dateCreated) AS d, COUNT(*) AS c FROM post GROUP BY DATE(dateCreated) ORDER BY d ASC");
    $labels = []; $values = [];
    while ($r = $data->fetch_assoc()) { $labels[] = $r['d']; $values[] = $r['c']; }

    // ───────────── REVIEWS PER CATEGORY ─────────────
    $cat = $conn->query("SELECT category, COUNT(*) AS c FROM post GROUP BY category");
    $cat_labels = []; $cat_values = [];
    while ($r = $cat->fetch_assoc()) { $cat_labels[] = $r['category']; $cat_values[] = $r['c']; }

    // ───────────── AVERAGE RATING PER CATEGORY ─────────────
    $rating = $conn->query("SELECT category, AVG(rating) AS avg_rating FROM post GROUP BY category");
    $rating_labels = []; $rating_values = [];
    while ($r = $rating->fetch_assoc()) { $rating_labels[] = $r['category']; $rating_values[] = round($r['avg_rating'], 2); }

    echo "
    <div class='chart-box'>
        <h3>📝 Reviews Posted Over Time</h3>
        <canvas id='chart1'></canvas>
    </div>
    <div class='chart-box'>
        <h3>📚 Reviews by Category</h3>
        <canvas id='chart2'></canvas>
    </div>
    <div class='chart-box'>
        <h3>⭐ Average Rating per Category</h3>
        <canvas id='chart3'></canvas>
    </div>

    <script>
    new Chart(document.getElementById('chart1'), {
        type: 'bar',
        data: {
            labels: " . json_encode($labels) . ",
            datasets: [{
                label: 'Reviews per Day',
                data: " . json_encode($values) . ",
                backgroundColor: '#28a745'
            }]
        },
        options: { 
            responsive: true,
            maintainAspectRatio: true,
            scales: { 
                y: { 
                    beginAtZero: true,
                    ticks: { color: '#fff' },
                    grid: { color: 'rgba(255,255,255,0.1)' }
                },
                x: {
                    ticks: { color: '#fff' },
                    grid: { color: 'rgba(255,255,255,0.1)' }
                }
            },
            plugins: {
                legend: { labels: { color: '#fff' } }
            }
        }
    });

    new Chart(document.getElementById('chart2'), {
        type: 'pie',
        data: {
            labels: " . json_encode($cat_labels) . ",
            datasets: [{
                data: " . json_encode($cat_values) . ",
                backgroundColor: ['#ff6384', '#36a2eb', '#ffce56', '#4bc0c0', '#9966ff']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: { labels: { color: '#fff' } }
            }
        }
    });

    new Chart(document.getElementById('chart3'), {
        type: 'bar',
        data: {
            labels: " . json_encode($rating_labels) . ",
            datasets: [{
                label: 'Average Rating',
                data: " . json_encode($rating_values) . ",
                backgroundColor: '#f39c12'
            }]
        },
        options: { 
            responsive: true,
            maintainAspectRatio: true,
            scales: { 
                y: { 
                    min: 0, 
                    max: 5,
                    ticks: { color: '#fff' },
                    grid: { color: 'rgba(255,255,255,0.1)' }
                },
                x: {
                    ticks: { color: '#fff' },
                    grid: { color: 'rgba(255,255,255,0.1)' }
                }
            },
            plugins: {
                legend: { labels: { color: '#fff' } }
            }
        }
    });
    </script>";

} elseif ($type === 'comments') {
    // ───────────── COMMENTS PER POST ─────────────
    $data = $conn->query("SELECT p.postID, COUNT(c.commentID) AS count FROM post p LEFT JOIN comments c ON p.postID=c.postID GROUP BY p.postID ORDER BY p.postID ASC");
    $labels = []; $values = [];
    while ($r = $data->fetch_assoc()) { $labels[] = 'Post #' . $r['postID']; $values[] = $r['count']; }

    // ───────────── ACTIVE COMMENTERS ─────────────
    $users = $conn->query("SELECT u.userName, COUNT(c.commentID) AS total FROM comments c JOIN users u ON c.userID=u.userID GROUP BY c.userID ORDER BY total DESC LIMIT 5");
    $u_labels = []; $u_values = [];
    while ($r = $users->fetch_assoc()) { $u_labels[] = $r['userName']; $u_values[] = $r['total']; }

    echo "<script>alert('alll');</script>
    <div class='chart-box'>
        <h3>💬 Comments per Post</h3>
        <canvas id='chart1'></canvas>
    </div>
    <div class='chart-box'>
        <h3>🏆 Top 5 Most Active Commenters</h3>
        <canvas id='chart2'></canvas>
    </div>

    <script>
    new Chart(document.getElementById('chart1'), {
        type: 'bar',
        data: {
            labels: " . json_encode($labels) . ",
            datasets: [{
                label: 'Comments per Post',
                data: " . json_encode($values) . ",
                backgroundColor: '#ffc107'
            }]
        },
        options: { 
            responsive: true,
            maintainAspectRatio: true,
            scales: { 
                y: { 
                    beginAtZero: true,
                    ticks: { color: '#fff' },
                    grid: { color: 'rgba(255,255,255,0.1)' }
                },
                x: {
                    ticks: { color: '#fff' },
                    grid: { color: 'rgba(255,255,255,0.1)' }
                }
            },
            plugins: {
                legend: { labels: { color: '#fff' } }
            }
        }
    });

    new Chart(document.getElementById('chart2'), {
        type: 'bar',
        data: {
            labels: " . json_encode($u_labels) . ",
            datasets: [{
                label: 'Total Comments',
                data: " . json_encode($u_values) . ",
                backgroundColor: '#007bff'
            }]
        },
        options: { 
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: true,
            scales: { 
                x: { 
                    beginAtZero: true,
                    ticks: { color: '#fff' },
                    grid: { color: 'rgba(255,255,255,0.1)' }
                },
                y: {
                    ticks: { color: '#fff' },
                    grid: { color: 'rgba(255,255,255,0.1)' }
                }
            },
            plugins: {
                legend: { labels: { color: '#fff' } }
            }
        }
    });
    </script>";

} elseif ($type === 'reports') {
    // ───────────── REPORTS OVER TIME ─────────────
    $data = $conn->query("SELECT DATE(createdAt) AS d, COUNT(*) AS c FROM reports GROUP BY DATE(createdAt) ORDER BY d ASC");
    $labels = []; $values = [];
    while ($r = $data->fetch_assoc()) { $labels[] = $r['d']; $values[] = $r['c']; }

    // ───────────── REPORT STATUS DISTRIBUTION ─────────────
    $status = $conn->query("SELECT status, COUNT(*) AS c FROM reports GROUP BY status");
    $status_labels = []; $status_values = [];
    while ($r = $status->fetch_assoc()) { $status_labels[] = $r['status']; $status_values[] = $r['c']; }

    // ───────────── REPORT TYPES ─────────────
    $types = $conn->query("SELECT reportType, COUNT(*) AS c FROM reports GROUP BY reportType");
    $t_labels = []; $t_values = [];
    while ($r = $types->fetch_assoc()) { $t_labels[] = $r['reportType']; $t_values[] = $r['c']; }

    echo "
    <div class='chart-box'>
        <h3>🚨 Reports Filed Over Time</h3>
        <canvas id='chart1'></canvas>
    </div>
    <div class='chart-box'>
        <h3>⚖️ Report Status Distribution</h3>
        <canvas id='chart2'></canvas>
    </div>
    <div class='chart-box'>
        <h3>📑 Report Types Breakdown</h3>
        <canvas id='chart3'></canvas>
    </div>

    <script>
    new Chart(document.getElementById('chart1'), {
        type: 'line',
        data: {
            labels: " . json_encode($labels) . ",
            datasets: [{
                label: 'Reports per Day',
                data: " . json_encode($values) . ",
                fill: true,
                backgroundColor: 'rgba(255, 0, 0, 0.2)',
                borderColor: 'red',
                tension: 0.3
            }]
        },
        options: { 
            responsive: true,
            maintainAspectRatio: true,
            scales: { 
                y: { 
                    beginAtZero: true,
                    ticks: { color: '#fff' },
                    grid: { color: 'rgba(255,255,255,0.1)' }
                },
                x: {
                    ticks: { color: '#fff' },
                    grid: { color: 'rgba(255,255,255,0.1)' }
                }
            },
            plugins: {
                legend: { labels: { color: '#fff' } }
            }
        }
    });

    new Chart(document.getElementById('chart2'), {
        type: 'pie',
        data: {
            labels: " . json_encode($status_labels) . ",
            datasets: [{
                data: " . json_encode($status_values) . ",
                backgroundColor: ['#28a745', '#ffc107', '#17a2b8', '#dc3545']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: { labels: { color: '#fff' } }
            }
        }
    });

    new Chart(document.getElementById('chart3'), {
        type: 'bar',
        data: {
            labels: " . json_encode($t_labels) . ",
            datasets: [{
                label: 'Reports per Type',
                data: " . json_encode($t_values) . ",
                backgroundColor: '#6f42c1'
            }]
        },
        options: { 
            responsive: true,
            maintainAspectRatio: true,
            scales: { 
                y: { 
                    beginAtZero: true,
                    ticks: { color: '#fff' },
                    grid: { color: 'rgba(255,255,255,0.1)' }
                },
                x: {
                    ticks: { color: '#fff' },
                    grid: { color: 'rgba(255,255,255,0.1)' }
                }
            },
            plugins: {
                legend: { labels: { color: '#fff' } }
            }
        }
    });
    </script>";

} else {
    echo "<p style='color: #fff;'>Invalid type. Please select a valid card.</p>";

}
?>