<?php
$postID = intval($_GET['id'] ?? 0);
$showForm = isset($_GET['flag']);
?>

<?php if(!$showForm): ?>
    <p>Click the button below to report this post:</p>
    <a href="flagForm.php?id=<?= $postID ?>&flag=1"><button class="flag-btn">🚩 Flag</button></a>
<?php else: ?>
    <form method="post">
        <label>Reason</label>
        <select name="reportType" required>
            <option value="">Select Reason</option>
            <option value="Spam">Spam</option>
            <option value="Offensive">Offensive</option>
            <option value="Misleading">Misleading</option>
            <option value="Copyright">Copyright</option>
            <option value="Duplicate">Duplicate</option>
        </select>

        <label>Additional Details</label>
        <textarea name="description" placeholder="Describe the issue..."></textarea>

        <button type="submit" name="submitReport">Submit Report</button>
    </form>
<?php endif; ?>
