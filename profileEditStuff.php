<?php
require "../rate.php";
session_start();

if (!isset($_SESSION['userID'])) {
    header("Location: login.php");
    exit();
}

$userID = $_SESSION['userID'];

$stmt = $conn->prepare("SELECT userName, fullName, userEmail, phone, age, bio, profPic FROM users WHERE userID = ?");
$stmt->bind_param("i", $userID);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) die("User not found.");

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Trim inputs
    $userName = trim($_POST['userName'] ?? '');
    $fullName = trim($_POST['fullName'] ?? '');
    $userEmail = trim($_POST['userEmail'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $age = intval($_POST['age'] ?? 0);
    $bio = trim($_POST['bio'] ?? '');
    $profilePicPath = $user['profPic'];

    // === Server-side validation ===
    if ($userName === '') $errors[] = "Username is required.";
    elseif (strlen($userName) > 50) $errors[] = "Username cannot exceed 50 characters.";

    if ($fullName === '') $errors[] = "Full name is required.";
    elseif (strlen($fullName) > 100) $errors[] = "Full name cannot exceed 100 characters.";

    if ($userEmail === '') $errors[] = "Email is required.";
    elseif (!filter_var($userEmail, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email format.";

    if ($phone !== '' && !preg_match('/^\+?[0-9]{7,15}$/', $phone)) {
        $errors[] = "Phone number must be 7-15 digits and can start with +.";
    }

    if ($age <= 0 || $age > 120) $errors[] = "Age must be between 1 and 120.";

    if (strlen($bio) > 500) $errors[] = "Bio cannot exceed 500 characters.";

    // === Profile picture upload ===
    if (!empty($_FILES['profPic']['name'])) {
        $uploadDir = "uploads/profile_pics/";
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

        $fileName = basename($_FILES["profPic"]["name"]);
        $targetFile = $uploadDir . time() . "_" . $fileName;
        $allowedTypes = ['jpg','jpeg','png','gif'];
        $ext = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

        if (!in_array($ext, $allowedTypes)) $errors[] = "Profile picture must be JPG, PNG, or GIF.";
        elseif (!move_uploaded_file($_FILES["profPic"]["tmp_name"], $targetFile)) {
            $errors[] = "Failed to upload profile picture.";
        } else {
            $profilePicPath = $targetFile;
        }
    }

    // === Update DB if no errors ===
    if (empty($errors)) {
        $update = $conn->prepare("
            UPDATE users 
            SET userName=?, fullName=?, userEmail=?, phone=?, age=?, bio=?, profPic=?
            WHERE userID=?
        ");
        $update->bind_param("ssssissi", $userName, $fullName, $userEmail, $phone, $age, $bio, $profilePicPath, $userID);

        if ($update->execute()) {
            echo "<script>alert('Profile updated successfully!'); window.location='profile.php';</script>";
            exit();
        } else {
            $errors[] = "Error updating profile in database.";
        }
    }
}
?>
