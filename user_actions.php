<?php
include "../rate.php";
include "../audit.php";
include "../homepage/notificationCreated.php"; // make sure this file has addNotification()

if(!isset($_SESSION['userID']) || $_SESSION['role'] !== 'Admin'){
    header("Location: ../homepage/login.php");
    exit();
}

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $id = intval($_POST['id']);
    $action = $_POST['action'];

    switch($action){
        case 'edit':
            // redirect to edit page
            header("Location: edit_user.php?id=$id");
            exit();

        case 'ban':
            $stmt = $conn->prepare("UPDATE users SET accountStatus='Suspended' WHERE userID=?");
            $stmt->bind_param("i",$id);
            $stmt->execute();
           
logAudit($_SESSION['userID'], 'EDITPROFILE', 'users', "Active", "Account :" . $id." is banned");


            break;
         case 'unban':
            $stmt = $conn->prepare("UPDATE users SET accountStatus='Active' WHERE userID=?");
            $stmt->bind_param("i",$id);
            $stmt->execute();
            logAudit($_SESSION['userID'], 'EDITPROFILE', 'users', NULL, "usersID: " . $id);

 addNotification($conn, "Ban Has been lifted", "Admin has removed ban on your account", $id, 'Alerts');
            break;
         case 'recover':
            $stmt = $conn->prepare("UPDATE users SET deleted=0, accountStatus='Active' WHERE userID=?");
            $stmt->bind_param("i",$id);
            $stmt->execute();
            logAudit($_SESSION['userID'], 'EDITPROFILE', 'users', NULL, "usersID: " . $id);

 addNotification($conn, "Account Recovered", "Your Account has been recovered by Admin", $id, 'Alerts');
            break;

        case 'soft_delete':
            $stmt = $conn->prepare("UPDATE users SET deleted=1, accountStatus='De-Activated' WHERE userID=?");
            $stmt->bind_param("i",$id);
            $stmt->execute();
            logAudit($_SESSION['userID'], 'DELETE', 'users', NULL, "usersID: " . $id);

 
            break;
    }

    header("Location: userControl.php");
    exit();
}
?>
