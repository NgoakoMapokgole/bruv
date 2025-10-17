<?php
session_start();
require "editPostStuff.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rate It All ! | Edit Post</title>
    <link rel="icon" type = "image/svg+xml" href="\\cs3-dev.ict.ru.ac.za\practicals\4A2\logo.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../mainStyle.css"/>
    <link rel="stylesheet" href="editPost.css"/>
</head>
<body>
<header>
    <?php include "../nav.php"?>
</header>

<!-- review modal form -->
<?php include "../review.php"?>

<main>
    <h1>Edit Post</h1>

    <form id = "bruh" method="post">
        <!-- === POST DETAILS === -->
        <fieldset>
            <legend>Post Details</legend>

            <!-- Title -->
            <section class="form-group">
                <label for="Title">Title</label>
                <input class ="bruv" type="text" id="Title" name="Title" 
                value="<?= htmlspecialchars($post['Title'] ?? '') ?>" required>
            </section>

            <!-- Content -->
            <section class="form-group">
                <label for="Content">Content</label>
                <textarea id="Content" name="Content" required><?= htmlspecialchars($post['Content'] ?? '') ?></textarea>
            </section>

            <!-- Rating -->
            <section class="form-group">
                <label for="rating">Rating</label>
                <section class="star-rating">
                    <?php
                    $currentRating = $post['rating'] ?? 0;
                    for ($i = 5; $i >= 1; $i--) {
                        $checked = $currentRating == $i ? 'checked' : '';
                        echo "<input class ='bruv'  type='radio' id='star$i' name='rating' value='$i' $checked />
                              <label for='star$i' title='$i stars'>★</label>";
                    }
                    ?>
                </section>
            </section>

            <!-- Category -->
            <section class="form-group">
                <label for="category">Category</label>
                <select id="category" name="category">
                    <?php
                    $categories = ['Media', 'Concept', 'Food', 'Place', 'EverythingElse'];
                    foreach ($categories as $cat) {
                        $selected = ($post['category'] ?? '') === $cat ? 'selected' : '';
                        echo "<option value='$cat' $selected>$cat</option>";
                    }
                    ?>
                </select>
            </section>

            <!-- Tags -->
            <section class="form-group">
                <label for="tags">Tags (comma-separated)</label>
                <input class="bruv"  type="text" id="tags" name="tags" 
                       value="<?= htmlspecialchars($post['tags'] ?? '') ?>" 
                       placeholder="Add tags separated by commas...">
            </section>

            <!-- Reply Permission -->
            <section class="form-group">
                <label for="replyPermission">Who Can Reply</label>
                <select id="replyPermission" name="replyPermission">
                    <?php
                    $replyOptions = ['anyone', 'friends', 'author'];
                    foreach ($replyOptions as $option) {
                        $selected = ($post['replyPermission'] ?? '') === $option ? 'selected' : '';
                        echo "<option value='$option' $selected>$option</option>";
                    }
                    ?>
                </select>
            </section>

            <!-- Date Created (readonly) -->
            <section class="form-group">
                <label for="dateCreated">Date Created</label>
                <input class ="bruv"  type="text" id="dateCreated" 
                       value="<?= htmlspecialchars($post['dateCreated'] ?? '') ?>" readonly>
            </section>
        </fieldset>

        <!-- MEDIA SECTION -->
        <fieldset>
            <legend>Media</legend>

            <!-- Type -->
            <p>
                <label for="mediaType<?= $media['mediaID'] ?? 0 ?>">Type</label>
                <select id="mediaType<?= $media['mediaID'] ?? 0 ?>" name="mediaType[<?= $media['mediaID'] ?? 0 ?>]">
                    <?php
                    $types = ['Video','Audio','Images'];
                    foreach ($types as $type) {
                        $selected = ($media['typeMedia'] ?? '') === $type ? 'selected' : '';
                        echo "<option value='$type' $selected>$type</option>";
                    }
                    ?>
                </select>
            </p>

            <!-- Upload File -->
            <p>
                <label for="mediaFile<?= $media['mediaID'] ?? 0 ?>">Upload Media</label>
                <input type="file" id="mediaFile<?= $media['mediaID'] ?? 0 ?>" 
                       name="mediaFile[<?= $media['mediaID'] ?? 0 ?>]" accept="video/*,audio/*,image/*">
                <small>Current file: <?= htmlspecialchars($media['location'] ?? 'None') ?></small>
            </p>

            <!-- Archived -->
            <p>
                <label>
                    <input type="checkbox" name="mediaArchived[<?= $media['mediaID'] ?? 0 ?>]" 
                           value="1" <?= ($media['archived'] ?? 0) ? 'checked' : '' ?>>
                    Archived
                </label>
            </p>
        </fieldset>


        <footer>
            <button id = "yuh" type="submit">Save Changes</button>
            <a href="profile.php?postID=<?= $postID ?>">Cancel</a>
        </footer>
    </form>
</main>

<script src="../mainScript.js"></script>
</body>
</html>