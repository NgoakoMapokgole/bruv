<?php
// CRITICAL: Ensure session is started before using $_SESSION
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// CHECK AUTHENTICATION: Ensure the user is logged in
if (!isset($_SESSION['userID'])) {
    // Redirect to login page or show error
    header("Location: login.php");
    exit;
}

include '../rate.php'; // Assuming this provides the $conn object

// Define upload directory and maximum file size (security best practice)
$upload_dir = 'uploads/';
$MAX_FILE_SIZE = 5 * 1024 * 1024; // 5 MB
$ALLOWED_MIME_TYPES = ['image/jpeg', 'image/png', 'image/gif'];

// --- 1. Fetch user information ---
$sql = $conn->prepare("SELECT fullName, userName, userEmail, phone, bio, profPic FROM users WHERE userID=?");
$sql->bind_param('i', $_SESSION['userID']);

$info = [];
$dom = []; // Original data array
if (!$sql->execute()) {
    // Log the error for system administrator
    error_log("Database error fetching user info: " . $conn->error);
    echo "<script>alert('System unable to retrieve information, contact system administrator');</script>";
    header("Location: index.php");
    exit;
} else {
    $result = $sql->get_result();
    $info = $result->fetch_assoc();
    $sql->close(); // Close the first statement

    if (!$info) {
        // User not found, should not happen if logged in
        header("Location: logout.php"); 
        exit;
    }
    
    // Original values for comparison (robustness improvement: use ternary operator for missing bio/pic)
    $dom = [
        $info['fullName'], 
        $info['userName'], 
        $info['userEmail'], 
        $info['phone'], 
        $info['bio'] ?? '', 
        $info['profPic'] ?? ''
    ];
}

// --- 2. Handle POST Request (Update) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])) {
    
    // Input Sanitization and Trimming
    $Fname = trim($_POST['fullName']);
    $Uname = trim($_POST['userName']);
    $Email = trim($_POST['userEmail']);
    $Phone = trim($_POST['phone']);
    $Bio = trim($_POST['bio']);
    $Pic = $info['profPic'] ?? ''; // Default to the current profile picture

    $update_error = false;
    
    // Input Validation (Security Improvement)
    if (!filter_var($Email, FILTER_VALIDATE_EMAIL)) {
        echo "<script>alert('Invalid email format.');</script>";
        $update_error = true;
    }
    // Add more validation checks (e.g., username length, phone regex) here...

    if (!$update_error) {
        // Handle file upload (CRITICAL SECURITY FIXES)
        if (isset($_FILES['profPic']) && $_FILES['profPic']['error'] === UPLOAD_ERR_OK) {
            $file_tmp = $_FILES['profPic']['tmp_name'];
            $file_size = $_FILES['profPic']['size'];
            
            // --- FILE TYPE DETECTION (FIX FOR mime_content_type() ERROR) ---
            $file_type = '';
            if (function_exists('mime_content_type')) {
                // Best option if available
                $file_type = mime_content_type($file_tmp);
            } elseif (class_exists('finfo')) {
                // Second best option (requires Fileinfo extension)
                $finfo = new finfo(FILEINFO_MIME_TYPE);
                $file_type = $finfo->file($file_tmp);
            } else {
                // Least secure fallback (user-supplied value)
                $file_type = $_FILES['profPic']['type'];
            }
            // -----------------------------------------------------------------
            
            // SECURITY CHECK 1: File size check
            if ($file_size > $MAX_FILE_SIZE) {
                echo "<script>alert('File size exceeds 5MB limit.');</script>";
                $update_error = true;
            }
            // SECURITY CHECK 2: MIME type whitelisting (check against actual content or provided type)
            elseif (!in_array($file_type, $ALLOWED_MIME_TYPES)) {
                echo "<script>alert('Only JPEG, PNG, and GIF images are allowed. Detected type: " . htmlspecialchars($file_type) . "');</script>";
                $update_error = true;
            } 
            // SECURITY CHECK 3: Ensure it is a valid image (depth defense)
            elseif (!getimagesize($file_tmp)) {
                echo "<script>alert('File is not a valid image.');</script>";
                $update_error = true;
            } else {
                // SECURITY FIX: Generate a unique, safe filename with a fixed extension
                $ext = match ($file_type) {
                    'image/jpeg' => '.jpg',
                    'image/png' => '.png',
                    'image/gif' => '.gif',
                    default => '.jpg', // Fallback
                };
                $new_file_name = uniqid('prof_', true) . $ext;
                $file_path = $upload_dir . $new_file_name;

                // Create directory if it doesn't exist
                if (!is_dir($upload_dir)) {
                    // 0755 is secure permission for web-writable directories
                    mkdir($upload_dir, 0755, true); 
                }

                if (move_uploaded_file($file_tmp, $file_path)) {
                    // Delete old profile picture if it's not the default and exists
                    if (!empty($info['profPic']) && file_exists($info['profPic']) && strpos($info['profPic'], $upload_dir) !== false) {
                         unlink($info['profPic']); 
                    }
                    $Pic = $file_path; // Update with the new, safe path
                } else {
                    echo "<script>alert('Failed to upload profile picture.');</script>";
                    $update_error = true;
                }
            }
        }
    }

    // Only proceed with DB update if no upload or validation error occurred
    if (!$update_error) {
        // Updated values for comparison
        $som = [$Fname, $Uname, $Email, $Phone, $Bio, $Pic];
        
        // Use a simple comparison to check for any difference before update
        $data_changed = (count(array_diff_assoc($dom, $som)) > 0);

        // Run the update if data changed (robustness improvement)
        if ($data_changed) { 
            
            try {
                // Prepared statement (already good)
                $sql_update = $conn->prepare("UPDATE users SET fullName=?, userName=?, userEmail=?, phone=?, bio=?, profPic=? WHERE userID=?");
                $sql_update->bind_param('ssssssi', $Fname, $Uname, $Email, $Phone, $Bio, $Pic, $_SESSION['userID']);
                
                // Execute the statement
                if (!$sql_update->execute()) {
                    // Fallback check for systems NOT configured to throw exceptions
                    $db_error_code = $conn->errno;
                    
                    if ($db_error_code == 1062) {
                         $error_message = "This email or username is already taken. Please choose a different one.";
                         echo "<script>alert('" . htmlspecialchars($error_message) . "');</script>";
                         error_log("Attempted non-exception duplicate entry by User ID " . $_SESSION['userID'] . ": " . $conn->error);
                    } else {
                        error_log("Database error updating user info (non-exception): " . $conn->error);
                        echo "<script>alert('Failed to update user information due to a server error. Please try again.');</script>";
                    }
                } else {
                    // Update successful logic
                    $info = [
                        'fullName' => $Fname, 
                        'userName' => $Uname, 
                        'userEmail' => $Email, 
                        'phone' => $Phone, 
                        'bio' => $Bio, 
                        'profPic' => $Pic
                    ];
                    echo "<script>alert('Profile updated successfully.');</script>";
                }
                $sql_update->close();

            } catch (mysqli_sql_exception $e) {
                // --- CATCH BLOCK HANDLES THE EXCEPTION (THE ROOT CAUSE OF YOUR DISPLAYED ERROR) ---
                
                $db_error_code = $e->getCode();
                
                if ($db_error_code == 1062) {
                    // Error 1062: Duplicate entry caught as an exception
                    $error_message = "This email or username is already taken. Please choose a different one.";
                    echo "<script>alert('" . htmlspecialchars($error_message) . "');</script>";
                    
                    // Log the detailed exception error internally
                    error_log("Duplicate entry exception caught (User ID " . $_SESSION['userID'] . "): " . $e->getMessage());
                    
                } else {
                    // Catch all other SQL exceptions
                    error_log("Unexpected SQL exception updating user info: " . $e->getMessage());
                    echo "<script>alert('A critical database error occurred. Please contact support.');</script>";
                }
            }
        } else {
            // No changes submitted
             echo "<script>alert('No changes detected.');</script>";
        }
    }
}
?>

<!doctype html> 
<html> 
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rate It All! | Settings</title>
    <link rel="icon" type="image/svg+xml" href="\\cs3-dev.ict.ru.ac.za\practicals\4A2\logo.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../mainStyle.css"/>
    <link rel="stylesheet" href="home.css"/>
</head>

<body>
    <header>
        <?php include "../nav.php"?>
    </header>
    
    <?php include "../review.php"?>

    <section class='reviewContent'>
        <h2>Account Settings</h2>
        <form action="" method="POST" enctype="multipart/form-data">
        
            <label>Full Name</label>
            <input type="text" name="fullName" value="<?php echo htmlspecialchars($info['fullName']); ?>">

            <label>Username</label>
            <input type="text" name="userName" value="<?php echo htmlspecialchars($info['userName']); ?>">

            <label>Email</label>
            <input type="email" name="userEmail" value="<?php echo htmlspecialchars($info['userEmail']); ?>">

            <label>Phone</label>
            <input type="text" name="phone" value="<?php echo htmlspecialchars($info['phone']); ?>">

            <label>Bio</label>
            <textarea name="bio" rows="4"><?php echo htmlspecialchars($info['bio'] ?? ''); ?></textarea>

            <label>Profile Picture</label>
            <?php if (!empty($info['profPic'])): ?>
                <img src="<?php echo htmlspecialchars($info['profPic']); ?>" width="100" height="100" style="border-radius:50%;">
            <?php endif; ?>
            <input type="file" name="profPic">

            <button type="submit" name="update">Save Changes</button>
        </form>
    </section>

</body>
</html>