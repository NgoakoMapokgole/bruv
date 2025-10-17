<?php
include "../../rate.php"; // include DB connection
session_start();

header('Content-Type: application/json');

if(!isset($_SESSION['userID'])){
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

if(!isset($data['review_id'])){
    echo json_encode(['success' => false, 'message' => 'No review ID provided']);
    exit;
}

$review_id = intval($data['review_id']);
$user_id = $_SESSION['userID'];

// OPTIONAL: Prevent user from liking twice
$check = $conn->prepare("SELECT * FROM likes WHERE review_id = ? AND user_id = ?");
$check->bind_param("ii", $review_id, $user_id);
$check->execute();
$res = $check->get_result();
if($res->num_rows > 0){
    echo json_encode(['success' => false, 'message' => 'You already liked this review']);
    exit;
}

// Increment like count
$update = $conn->prepare("UPDATE post SET likes = likes + 1 WHERE id = ?");
$update->bind_param("i", $review_id);
$update->execute();

// Insert into likes table to track users
$insert = $conn->prepare("INSERT INTO likes (user_id, review_id) VALUES (?, ?)");
$insert->bind_param("ii", $user_id, $review_id);
$insert->execute();

// Get new like count
$stmt = $conn->prepare("SELECT likes FROM post WHERE id = ?");
$stmt->bind_param("i", $review_id);
$stmt->execute();
$result = $stmt->get_result();
$new_like_count = $result->fetch_assoc()['likes'];

echo json_encode(['success' => true, 'new_like_count' => $new_like_count]);
?>
