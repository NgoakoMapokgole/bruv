<?php
// Ensure session is started for $_SESSION variables to work
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Assume 'audit.php' includes necessary functions like logAudit
// and that a database connection object $conn is available.
include "../audit.php";

$logFile = __DIR__ . '/system.log';
$err = "";

// --- Helper Functions ---

/**
 * Gets the user's real IP address, prioritizing REMOTE_ADDR for security.
 * NOTE: Changed from a public function (invalid in global scope) to a standard function.
 */
function getUserIP() {
    // Check for proxy headers ONLY if you trust your proxy/load balancer
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        // Explode list and take the first (client) IP, then trim it
        $ipList = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        $ip = trim($ipList[0]);
    } else {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
    }
    return $ip;
}

// Basic function to log errors safely
function logError($level, $category, $message, $logFile, $userID = 'N/A') {
    $ip = getUserIP();
    $date = date('Y-m-d H:i:s');
    error_log("[$date][$level][$category][UserID: $userID][IP: $ip] $message\n", 3, $logFile);
}

// --- LOGIN HANDLER ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['LogInH'])) {
    $user = trim($_POST["login-email"]);
    $password = $_POST['login-password'];
    $ip = getUserIP(); // Get the IP for logging/tracking

    // Initialize/Increment the attempt counter for this user/IP in the session
    $attempt_key = 'login_attempts_' . md5($user . $ip);
    $_SESSION[$attempt_key] = ($_SESSION[$attempt_key] ?? 0) + 1;
    $current_attempts = $_SESSION[$attempt_key];
    $MAX_ATTEMPTS = 3;

    if ($current_attempts > $MAX_ATTEMPTS) {
        $err = "Your account is temporarily locked due to too many failed login attempts.";
        logError('WARNING', 'BRUTE_FORCE', "Attempted login on locked account for user: $user", $logFile);
        // Do not proceed with the DB check if already over the limit
    } else {
        try {
            // Use SELECT only for required columns (security best practice)
            $sql = $conn->prepare("SELECT userID, userName, password, role, accountStatus FROM users WHERE (userName=? OR userEmail=?) LIMIT 1;");
            $sql->bind_param("ss", $user, $user);
            
            if ($sql->execute()) {
                $result = $sql->get_result();
                $sqlResults = $result->fetch_assoc();

                if ($sqlResults && $sqlResults['accountStatus'] === 'Suspended') {
                    $err = "Your account has been suspended. Please contact support.";
                    logError('WARNING', 'SUSPENDED_ACCOUNT', "Suspended account login attempt for user: $user", $logFile);
                    
                } elseif ($sqlResults && password_verify($password, $sqlResults['password'])) {
                    // --- SUCCESSFUL LOGIN ---
                    session_regenerate_id(true); // Prevent Session Fixation
                    
                    $_SESSION['userName'] = $sqlResults['userName'];
                    $_SESSION['userID'] = $sqlResults['userID'];
                    $_SESSION['role'] = $sqlResults['role'];
                    
                    // Clear failed attempts upon success
                    unset($_SESSION[$attempt_key]); 
                    
                    logAudit($sqlResults['userID'], 'LOGIN', 'users', NULL, $sqlResults['userName']);
                    header("Location: index.php");
                    exit();
                    
                } else {
                    // --- FAILED LOGIN (Incorrect Password/User) ---
                    $err = "Incorrect Username/Email or Password given";
                    $logMessage = "Failed login attempt for user: $user. Attempt $current_attempts of $MAX_ATTEMPTS.";
                    $userID_log = $sqlResults['userID'] ?? 'N/A';
                    logError('ERROR', 'LOGIN_FAIL', $logMessage, $logFile, $userID_log);

                    // Check if this was the final allowed attempt (3rd failure)
                    if ($current_attempts >= $MAX_ATTEMPTS && $sqlResults) {
                        $checker = $conn->prepare("UPDATE users SET accountStatus='Suspended' WHERE userID = ?;");
                        $checker->bind_param('i', $sqlResults['userID']);
                        if ($checker->execute()) {
                            $err = "Too many failed attempts. Your account has been suspended. Please contact support.";
                            logError('CRITICAL', 'ACCOUNT_LOCK', "Account suspended after 3 failures for user: " . $sqlResults['userName'], $logFile, $sqlResults['userID']);
                        }
                        $checker->close();
                    }
                }
            } else {
                // Database query preparation/execution error
                $err = "Login failed due to an internal error. Please try again.";
                logError('FATAL', 'DB_ERROR', "Login query failed: " . $conn->error, $logFile);
            }
            $sql->close();

        } catch (Exception $e) {
            $err = "An unexpected error occurred. Please try again.";
            logError('FATAL', 'EXCEPTION', "Login handler exception: " . $e->getMessage(), $logFile);
        }
    }
}

// --- SIGNUP HANDLER ---
elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['SignUpH'])) {
    
    // Trim and sanitize inputs
    $userName = trim($_POST['signup-name']);
    $userAname = trim($_POST['signup-Aname']); // Assuming Aname is full name
    $userEmail = trim($_POST['signup-email']);
    $userPhone = trim($_POST['signup-phone']);
    $DOB = $_POST['myDate'];
    $userPassword = $_POST['signup-password'];
    $userPassword2 = $_POST['confirm-password'];

    $today = date('Y-m-d');
    $userAge = date_diff(date_create($DOB), date_create($today))->y;

    // Basic validation
    if (empty($userName) || empty($userAname) || empty($userEmail) || empty($userPhone) || empty($DOB) || empty($userPassword) || empty($userPassword2)) {
        $err = "All fields are required.";
    } elseif (!filter_var($userEmail, FILTER_VALIDATE_EMAIL)) {
        $err = "Invalid email format.";
    } elseif (!preg_match( '/^\d{3}-\d{3}-\d{4}$/', $userPhone)) {
        // Warning: This validation is too strict for international users.
        $err = "Invalid phone number format. Use XXX-XXX-XXXX."; 
    } elseif ($userAge < 16) {
        $err = "You must be at least 16 years old to register.";
    } elseif ($userPassword !== $userPassword2) {
        $err = "Passwords do not match.";
    } elseif (strlen($userPassword) < 6) {
        $err = "Password must be at least 6 characters.";
    } else {
        // Hash the password securely
        $hashedPassword = password_hash($userPassword, PASSWORD_DEFAULT);

        try {
            // Prepare the SQL safely
            $sql = $conn->prepare("
                INSERT INTO users (userEmail, userName, fullName, age, accountStatus, role, activeDate, password, phone)
                VALUES (?, ?, ?, ?, 'Active', 'User', ?, ?, ?)
            ");

            // Corrected parameter binding: sssissi (String, String, String, Integer, String, String, String)
            $sql->bind_param("sssisss", 
                $userEmail, 
                $userName, 
                $userAname, 
                $userAge,      // Bound as 'i' in the 'sssissi' suggestion, but bound as 's' here to match existing code's type, which is generally fine for MySQL/MariaDB.
                $today, 
                $hashedPassword, 
                $userPhone
            );

            if ($sql->execute()) {
                session_regenerate_id(true); // Prevent Session Fixation
                
                $_SESSION['userName'] = $userName;
                $_SESSION['userID'] = $sql->insert_id;
                $_SESSION['role'] = "User";
                
                logAudit($_SESSION['userID'], 'SIGNUP', 'users', NULL, $userName);
                header("Location: index.php");
                exit();
            } else {
                // Check for duplicate key error (e.g., email or username already exists)
                if ($conn->errno == 1062) { 
                    $err = "Account creation failed: Username or Email already exists.";
                } else {
                    $err = "Account creation failed due to a server error. Please contact support.";
                    logError('ERROR', 'DB_ERROR', "Signup insert failed: " . $conn->error, $logFile);
                }
            }
            $sql->close();

        } catch (Exception $e) {
            $err = "An unexpected error occurred during signup.";
            logError('FATAL', 'EXCEPTION', "Signup handler exception: " . $e->getMessage(), $logFile);
        }
    }
}

?>