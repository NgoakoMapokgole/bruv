<?php
include "../rate.php";
session_start();

// restrict access
if(!isset($_SESSION['userID']) || $_SESSION['role'] !== 'Admin'){
    header("Location: ../homepage/login.php");
    exit();
}

if(!isset($_GET['id'])){
    header("Location: users.php");
    exit();
}

$userID = intval($_GET['id']);

// fetch user info
$stmt = $conn->prepare("SELECT userID, userName, userEmail, role, accountStatus, deleted FROM users WHERE userID=? LIMIT 1");
$stmt->bind_param("i", $userID);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if(!$user){
    header("Location: users.php");
    exit();
}

// handle update
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $userName = trim($_POST['userName']);
    $userEmail = trim($_POST['userEmail']);
    $role = $_POST['role'];
    $status = $_POST['accountStatus'];

    $update = $conn->prepare("UPDATE users SET userName=?, userEmail=?, role=?, accountStatus=? WHERE userID=?");
    $update->bind_param("ssssi", $userName, $userEmail, $role, $status, $userID);
    $update->execute();

    header("Location: userControl.php");
    exit();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit User</title>
<link rel="stylesheet" href="styles.css">
<style>
body { background:#121212; color:#fff; font-family:Segoe UI, sans-serif; }
.container { margin-left:240px; padding:2rem; max-width:600px; }
h1 { margin-bottom:1.5rem; }
form { display:flex; flex-direction:column; gap:1rem; }
label { font-weight:bold; }
input, select { padding:0.6rem; border-radius:6px; border:none; font-size:1rem; }
button { padding:0.6rem 1rem; border:none; border-radius:6px; background:#03a9f4; color:#fff; cursor:pointer; font-size:1rem; }
button:hover { background:#0288d1; }
</style>
</head>
<body>

<div class="container">
<h1>Edit User: <?= htmlspecialchars($user['userName']) ?></h1>

<form method="post">
    <label for="userName">Username</label>
    <input type="text" id="userName" name="userName" value="<?= htmlspecialchars($user['userName']) ?>" required>

    <label for="userEmail">Email</label>
    <input type="email" id="userEmail" name="userEmail" value="<?= htmlspecialchars($user['userEmail']) ?>" required>

    <label for="role">Role</label>
    <select id="role" name="role" required>
        <option value="User" <?= $user['role']=='User' ? 'selected' : '' ?>>User</option>
        <option value="Admin" <?= $user['role']=='Admin' ? 'selected' : '' ?>>Admin</option>
        <option value="Mod" <?= $user['role']=='Moderator' ? 'selected' : '' ?>>Moderator</option>
    </select>

    <label for="accountStatus">Account Status</label>
    <select id="accountStatus" name="accountStatus" required>
        <option value="Active" <?= $user['accountStatus']=='Active' ? 'selected' : '' ?>>Active</option>
        <option value="Suspended" <?= $user['accountStatus']=='Suspended' ? 'selected' : '' ?>>Suspended</option>
        <option value="De-Activated" <?= $user['accountStatus']=='De-Activated' ? 'selected' : '' ?>>De-Activated</option>
    </select>

    <button type="submit">Save Changes</button>
</form>

</div>

</body>
</html>
