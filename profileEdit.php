<?php

require "profileEditStuff.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rate It All ! | Edit Profile</title>
    <link rel="icon" type = "image/svg+xml" href="\\cs3-dev.ict.ru.ac.za\practicals\4A2\logo.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../mainStyle.css"/>
    <link rel="stylesheet" href="profileEdit.css"/>
</head>
<body>

<header>
      <?php include "../nav.php"?>
    </header>
    <!-- review modal form -->
    <?php include "../review.php"?>

<main>
    <article>
        <section>
            <h2 class="profile-edit-h2">Profile Information</h2>
            <form method="POST" action= "profileEditStuff.php" enctype="multipart/form-data" class="profile-edit-form">
                <figure class="profile-pic-container">
                    <?php if (!empty($user['profPic'])): ?>
                        <img src="<?= htmlspecialchars($user['profPic']) ?>" alt="Profile Picture">
                    <?php else: ?>
                        <img src="default-avatar.png" alt="Default Profile Picture">
                    <?php endif; ?>
                    <figcaption class="profile-pic-caption">Current Profile Picture</figcaption>
                </figure>

                <fieldset class="profile-fieldset">
                    <legend class="profile-legend">Basic Info</legend>
                    
                    <p>
                        <label for="userName" class="profile-label">Username</label>
                        <input type="text" id="userName" name="userName" value="<?= htmlspecialchars($user['userName']) ?>" required class="profile-input">
                    </p>
                    
                    <p>
                        <label for="fullName" class="profile-label">Full Name</label>
                        <input type="text" id="fullName" name="fullName" value="<?= htmlspecialchars($user['fullName']) ?>" required class="profile-input">
                    </p>
                    
                    <p>
                        <label for="userEmail" class="profile-label">Email</label>
                        <input type="email" id="userEmail" name="userEmail" value="<?= htmlspecialchars($user['userEmail']) ?>" required class="profile-input">
                    </p>
                    
                    <p>
                        <label for="phone" class="profile-label">Phone</label>
                        <input type="text" id="phone" name="phone" value="<?= htmlspecialchars($user['phone']) ?>" class="profile-input">
                    </p>
                    
                    <p>
                        <label for="age" class="profile-label">Age</label>
                        <input type="number" id="age" name="age" value="<?= htmlspecialchars($user['age']) ?>" class="profile-input">
                    </p>
                </fieldset>

                <fieldset class="profile-fieldset">
                    <legend class="profile-legend">About You</legend>
                    <p>
                        <label for="bio" class="profile-label">Bio</label>
                        <textarea id="bio" name="bio" class="profile-textarea"><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>
                    </p>
                </fieldset>

                <fieldset class="profile-fieldset">
                    <legend class="profile-legend">Profile Picture</legend>
                    <p>
                        <label for="profPic" class="profile-label">Upload New Picture</label>
                        <input type="file" id="profPic" name="profPic" accept="image/*" class="profile-file-input">
                    </p>
                </fieldset>

                <button type="submit" class="profile-submit-btn">Save Changes</button>
            </form>
        </section>
    </article>
</main>
<?php include "../foot.php"?>
    
    <script>
      const isLoggedIn = <?php echo isset($_SESSION['userID']) ? 'true' : 'false'; ?>;
    </script>
    <script src="../mainScript.js"></script>
    <script src="profileEdit.js"></script>

</body>
</html>
