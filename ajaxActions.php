<?php
session_start();
include "../rate.php"; // your DB connection

header('Content-Type: application/json');

if (!isset($_SESSION['userID'])) {
    echo json_encode(['error' => 'Not logged in']);
    exit;
}

$userID = $_SESSION['userID'];

if (!isset($_POST['action']) || !isset($_POST['postID'])) {
    echo json_encode(['error' => 'Invalid request']);
    exit;
}

$postID = intval($_POST['postID']);
$action = $_POST['action'];

switch ($action) {
   
// Assuming $conn, $userID, and $postID are already defined and sanitized/validated
// And assuming the logic is inside a switch statement where $action is 'like' or 'dislike'

// Start a transaction to ensure atomicity (all or nothing) for consistency

    case 'like':
        // Check if user already liked
        $stmt = $conn->prepare("SELECT id FROM likes WHERE user_id = ? AND review_id = ?");
        $stmt->bind_param("ii", $userID, $postID);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            // --- Action: Like (Insert) ---

            // 1. Insert like
            $stmt = $conn->prepare("INSERT INTO likes (user_id, review_id) VALUES (?, ?)");
            $stmt->bind_param("ii", $userID, $postID);
            $stmt->execute();

            // 2. Remove dislike if exists
            // Deleting the dislike record must happen BEFORE updating the counts
            $stmt = $conn->prepare("DELETE FROM dislikes WHERE user_id = ? AND review_id = ?");
            $stmt->bind_param("ii", $userID, $postID);
            $stmt->execute();
            $dislike_removed = $stmt->affected_rows > 0; // Check if a dislike was actually removed

            // 3. Increment like count and conditionally decrement dislike count
            // FIX: If a dislike was removed, we must decrement the dislikes counter
            $dislike_decrement = $dislike_removed ? ", dislikes = GREATEST(0, dislikes - 1)" : "";
            $stmt = $conn->prepare("UPDATE post SET likes = likes + 1" . $dislike_decrement . " WHERE postID = ?");
            
            // Note: Since $dislike_decrement might add a parameter, we must adjust bind_param if we used a prepared statement for the update.
            // Keeping it simple and safe by binding only the $postID as the SQL logic is now cleaner.
            $stmt->bind_param("i", $postID);
            $stmt->execute();

        } else {
            // --- Action: Unlike (Delete) ---

            // 1. Delete like
            $stmt = $conn->prepare("DELETE FROM likes WHERE user_id=? AND review_id=?");
            $stmt->bind_param("ii", $userID, $postID);
            $stmt->execute();

            // 2. Decrement like count (using prepared statement for consistency)
            $stmt = $conn->prepare("UPDATE post SET likes = GREATEST(0, likes - 1) WHERE postID = ?");
            $stmt->bind_param("i", $postID);
            $stmt->execute();
        }

        // Fetch updated like count
        $stmt = $conn->prepare("SELECT likes FROM post WHERE postID = ?");
        $stmt->bind_param("i", $postID);
        $stmt->execute();
        $likes = $stmt->get_result()->fetch_assoc()['likes'];

        // Commit transaction if everything succeeded
        $conn->commit();

        echo json_encode(['likes' => $likes]);
        exit;

    // -----------------------------------------------------------------------

    case 'dislike':
        // Check if user already disliked
        $stmt = $conn->prepare("SELECT id FROM dislikes WHERE user_id = ? AND review_id = ?");
        $stmt->bind_param("ii", $userID, $postID);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            // --- Action: Dislike (Insert) ---

            // 1. Insert dislike
            $stmt = $conn->prepare("INSERT INTO dislikes (user_id, review_id) VALUES (?, ?)");
            $stmt->bind_param("ii", $userID, $postID);
            $stmt->execute();
            
            // 2. Remove like if exists
            $stmt = $conn->prepare("DELETE FROM likes WHERE user_id = ? AND review_id = ?");
            $stmt->bind_param("ii", $userID, $postID);
            $stmt->execute();
            $like_removed = $stmt->affected_rows > 0; // Check if a like was actually removed

            // 3. Increment dislike count and conditionally decrement like count
            // If a like was removed, we must decrement the likes counter
            $like_decrement = $like_removed ? ", likes = GREATEST(0, likes - 1)" : "";
            $stmt = $conn->prepare("UPDATE post SET dislikes = dislikes + 1" . $like_decrement . " WHERE postID = ?");
            $stmt->bind_param("i", $postID);
            $stmt->execute();

        } else {
            // --- Action: Undislike (Delete) ---

            // 1. Delete dislike
            $stmt = $conn->prepare("DELETE FROM dislikes WHERE user_id=? AND review_id=?");
            $stmt->bind_param("ii", $userID, $postID);
            $stmt->execute();

            // 2. Decrement dislike count (using prepared statement for consistency)
            $stmt = $conn->prepare("UPDATE post SET dislikes = GREATEST(0, dislikes - 1) WHERE postID = ?");
            $stmt->bind_param("i", $postID);
            $stmt->execute();
        }

        // Fetch updated dislike count
        $stmt = $conn->prepare("SELECT dislikes FROM post WHERE postID = ?");
        $stmt->bind_param("i", $postID);
        $stmt->execute();
        $dislikes = $stmt->get_result()->fetch_assoc()['dislikes'];

        // Commit transaction if everything succeeded
        $conn->commit();

        echo json_encode(['dislikes' => $dislikes]);
        exit;


    case 'comment':
        $content = trim($_POST['content'] ?? '');
        if (!$content) {
            echo json_encode(['error' => 'Empty comment']);
            exit;
        }

        // Insert comment
        $stmt = $conn->prepare("INSERT INTO comments (userID, postID, content, dateCreated) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("iis", $userID, $postID, $content);
        $stmt->execute();

        $commentID = $conn->insert_id;

        // Fetch username from users table
        $stmt = $conn->prepare("SELECT userName FROM users WHERE userID = ?");
        $stmt->bind_param("i", $userID);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        $username = $res['userName'];

        echo json_encode([
            'id' => $commentID,
            'userName' => $username,
            'content' => htmlspecialchars($content),
            'date' => date("M j, g:i a")
        ]);
        exit;

    default:
        echo json_encode(['error' => 'Invalid action']);
        exit;
}
?>
