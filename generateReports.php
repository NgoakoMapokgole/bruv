<?php
// adminDashboard.php
session_start();
include "../rate.php";

// --- Check login & role ---
if(!isset($_SESSION['userID']) || $_SESSION['role'] !== 'Admin'){
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
<title>Admin Dashboard</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
body { font-family:'Segoe UI',sans-serif; background:#121212; color:#fff; margin:0; display:flex;}
.sidebar { width:240px; background:#1f1f1f; height:100vh; padding:1rem; position:fixed; top:0; left:0; overflow-y:auto;}
.sidebar h2 { text-align:center; margin-bottom:2rem; color:#bdc007;}
.sidebar ul { list-style:none; padding:0;}
.sidebar ul li { margin:1rem 0;}
.sidebar ul li a { text-decoration:none; color:#fff; display:block; padding:0.6rem 1rem; border-radius:8px; transition:0.3s;}
.sidebar ul li a:hover { background:#333; color:#bdc007;}
.main { margin-left:240px; flex:1; padding:2rem;}
.topbar { background:#1f1f1f; padding:1rem 2rem; border-radius:8px; margin-bottom:2rem; display:flex; justify-content:space-between; align-items:center;}
.cards { display:grid; grid-template-columns:repeat(auto-fit, minmax(220px,1fr)); gap:1.5rem; margin-bottom:2rem;}
.card { background:rgba(255,255,255,0.05); padding:1.5rem; border-radius:12px; cursor:pointer; transition:0.3s;}
.card:hover { transform:translateY(-5px); border:1px solid #bdc007;}
.card h3 { margin:0 0 0.5rem 0; color:#bdc007;}
.card p { margin:0;}
#details { margin-top:2rem;}
table { width:100%; border-collapse:collapse; margin-top:1rem;}
th, td { border:1px solid rgba(255,255,255,0.1); padding:0.8rem; text-align:left;}
th { background:#1f1f1f;}
tr:hover { background: rgba(189,192,7,0.1);}
.status-approved { color:#0f0; font-weight:600;}
.status-deleted { color:#f00; font-weight:600;}
canvas { max-width: 600px; margin-top:1rem; }
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

<script>
document.querySelectorAll('.card').forEach(card=>{
    card.addEventListener('click', function(){
        const id = this.getAttribute('data-id');
        const details = document.getElementById('details');
        details.innerHTML = "<p>Loading analytics...</p>";

        fetch(`analytics2.php?type=${id}`)
        .then(res => res.json())
        .then(data => {
            details.innerHTML = "";

            // USERS CHARTS
            if(id==='users'){
                const regCanvas = document.createElement('canvas');
                regCanvas.id='regChart';
                details.appendChild(regCanvas);
                new Chart(regCanvas,{type:'line',data:{labels:data.months,datasets:[{label:'Registrations',data:data.registrations,borderColor:'#03a9f4',backgroundColor:'rgba(3,169,244,0.2)',fill:true}]}});
                
                const roleCanvas = document.createElement('canvas');
                roleCanvas.id='roleChart';
                details.appendChild(roleCanvas);
                new Chart(roleCanvas,{type:'doughnut',data:{labels:data.roleLabels,datasets:[{data:data.roleCounts,backgroundColor:['#ff6384','#36a2eb','#ffce56']}]}});
                
                const statusCanvas = document.createElement('canvas');
                statusCanvas.id='statusChart';
                details.appendChild(statusCanvas);
                new Chart(statusCanvas,{type:'bar',data:{labels:data.statusLabels,datasets:[{data:data.statusCounts,backgroundColor:'#bdc007'}]},options:{scales:{y:{beginAtZero:true}}}});
                
                // User table
                const table = document.createElement('table');
                table.innerHTML = `<thead><tr>
                <th>ID</th><th>Username</th><th>Email</th><th>Role</th><th>Status</th><th>Active Since</th>
                </tr></thead><tbody></tbody>`;
                details.appendChild(table);

                fetch('userData.php')
                .then(res=>res.json())
                .then(users=>{
                    const tbody = table.querySelector('tbody');
                    users.forEach(u=>{
                        const tr = document.createElement('tr');
                        tr.innerHTML=`<td>${u.userID}</td><td>${u.userName}</td><td>${u.userEmail}</td><td>${u.role}</td><td>${u.accountStatus}</td><td>${u.activeDate}</td>`;
                        tbody.appendChild(tr);
                    });
                });
            }
            else {
                // Other cards just show analytics charts returned by analytics2.php
                Object.keys(data).forEach(k=>{
                    const canvas = document.createElement('canvas');
                    details.appendChild(canvas);
                    new Chart(canvas,{type:'bar',data:{labels:data[k].labels||[],datasets:[{label:k,data:data[k].counts||[],backgroundColor:'#03a9f4'}]}});
                });
            }
        }).catch(err=>{details.innerHTML="<p>Error loading analytics.</p>";console.error(err);});
    });
});
</script>

</div>
</body>
</html>
