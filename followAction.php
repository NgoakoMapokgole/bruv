<?php
session_start();
include "rate.php"; 


// 2. Authentication and Input Validation
if (!isset($_SESSION['userID'])) {
    http_response_code(401);
    die(json_encode(['status' => 'error', 'message' => 'Not logged in']));
}

$loggedInID = $_SESSION['userID'];
$profileID = isset($_POST['userID']) ? intval($_POST['userID']) : 0;

if ($profileID <= 0) {
    http_response_code(400);
    die(json_encode(['status' => 'error', 'message' => 'Invalid user ID']));
}

// ------------------------------------
// 3. Core Logic (Follow/Unfollow)
// ------------------------------------

// Get current followers/following
$stmt = $conn->prepare("SELECT followers, following FROM users WHERE userID = ?");
$stmt->bind_param("i", $profileID);
$stmt->execute();
$result = $stmt->get_result();
$profile = $result->fetch_assoc();

$stmtUser = $conn->prepare("SELECT followers, following FROM users WHERE userID = ?");
$stmtUser->bind_param("i", $loggedInID);
$stmtUser->execute();
$userResult = $stmtUser->get_result();
$loggedInUser = $userResult->fetch_assoc();

$profileFollowers = !empty($profile['followers']) ? explode(',', $profile['followers']) : [];
$loggedInFollowing = !empty($loggedInUser['following']) ? explode(',', $loggedInUser['following']) : [];

if (in_array($loggedInID, $profileFollowers)) {
    // Unfollow
    $profileFollowers = array_diff($profileFollowers, [$loggedInID]);
    $loggedInFollowing = array_diff($loggedInFollowing, [$profileID]);
    $action = 'unfollow';
} else {
    // Follow
    $profileFollowers[] = $loggedInID;
    $loggedInFollowing[] = $profileID;
    $action = 'follow';
}

// ------------------------------------
// 4. Update Database (Using Prepared Statements)
// ------------------------------------

// Update profile followers
$stmtUpdate = $conn->prepare("UPDATE users SET followers = ? WHERE userID = ?");
$followersStr = implode(',', $profileFollowers);
$stmtUpdate->bind_param("si", $followersStr, $profileID);
$stmtUpdate->execute();

// Update logged-in user following
$stmtUpdate2 = $conn->prepare("UPDATE users SET following = ? WHERE userID = ?");
$followingStr = implode(',', $loggedInFollowing);
$stmtUpdate2->bind_param("si", $followingStr, $loggedInID);
$stmtUpdate2->execute();

// ------------------------------------
// 5. Success Response
// ------------------------------------
echo json_encode([
    'status' => 'success',
    'action' => $action,
    'followersCount' => count($profileFollowers)
]);
?>