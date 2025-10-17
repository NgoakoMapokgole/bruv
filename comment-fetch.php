<?php
include "../rate.php"; // DB connection

if (isset($_POST['review_id'])) {
    $review_id = intval($_POST['review_id']);
    
    // Fetch comments for the given review ID
    $sql = $conn->prepare("SELECT c.commentID, c.content, u.username FROM comments c JOIN users u ON c.userID = u.userID WHERE reviewID = ? ORDER BY c.commentID DESC");
    $sql->bind_param("i", $review_id);
    $sql->execute();
    $result = $sql->get_result();
    
    $comments = [];
    while ($row = $result->fetch_assoc()) {
        $comments[] = $row;
    }

    echo json_encode($comments); // Return JSON array of comments
}
?>
