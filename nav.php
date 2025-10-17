<?php
$currentPage = basename($_SERVER['PHP_SELF']);
$currentCategory = $_GET['category'] ?? '';
?>

<header>
    <nav class="navbar">
        <h1 class="logo">Rate It All !</h1>

        <button class="menu-toggle" id="menu-toggle" aria-label="Toggle navigation menu">
            <i class="fa fa-bars"></i>
        </button>

        <ul class="nav-links">
            <li>
                <a href="http://cs3-dev.ict.ru.ac.za/practicals/4a2/HomePage/index.php" class="<?= $currentPage === 'index.php' ? 'active' : '' ?>">Home</a>
            </li>
            <li>
                <a id="openReviewBtn" class="<?= $currentPage === 'writeReview.php' ? 'active' : '' ?>">Write a review</a>
            </li>
            <li class="dropdown <?= $currentPage === 'search.php' ? 'active' : '' ?>">
                <a href="#" class="dropdown-toggle">Categories</a>
                <ul class="dropdown-menu">
                    <li><a href="http://cs3-dev.ict.ru.ac.za/practicals/4a2/HomePage/search.php?search=&category=Place" class="<?= $currentCategory === 'Place' ? 'active' : '' ?>">Places</a></li>
                    <li><a href="http://cs3-dev.ict.ru.ac.za/practicals/4a2/HomePage/search.php?search=&category=Food" class="<?= $currentCategory === 'Food' ? 'active' : '' ?>">Food</a></li>
                    <li><a href="http://cs3-dev.ict.ru.ac.za/practicals/4a2/HomePage/search.php?search=&category=Media" class="<?= $currentCategory === 'Media' ? 'active' : '' ?>">Media</a></li>
                    <li><a href="http://cs3-dev.ict.ru.ac.za/practicals/4a2/HomePage/search.php?search=&category=Concept" class="<?= $currentCategory === 'Concept' ? 'active' : '' ?>">Concepts</a></li>
                    <li><a href="http://cs3-dev.ict.ru.ac.za/practicals/4a2/HomePage/search.php?search=&category=EverythingElse" class="<?= $currentCategory === 'EverythingElse' ? 'active' : '' ?>">Wild Card</a></li>
                </ul>
            </li>
            <li>
                <a href="http://cs3-dev.ict.ru.ac.za/practicals/4a2/AboutUs/AboutUs.php" class="<?= $currentPage === 'AboutUs.php' ? 'active' : '' ?>">About Us</a>
            <!-- </li>
            <li>
                <a href="http://cs3-dev.ict.ru.ac.za/practicals/4a2/Content/Other/Other.php" class="<?= $currentPage === 'Other.php' ? 'active' : '' ?>">Other</a>
            </li> -->

            <!-- Search form for desktop -->
            <li class="nav-search-desktop">
                <form action="http://cs3-dev.ict.ru.ac.za/practicals/4a2/HomePage/search.php" method="get" class="nav-search">
                    <input type="text" name="search" placeholder="Search..." value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
                    <select name="category">
                        <option value="">All Categories</option>
                        <option value="Place" <?= $currentCategory === 'Place' ? 'selected' : '' ?>>Places</option>
                        <option value="Food" <?= $currentCategory === 'Food' ? 'selected' : '' ?>>Food</option>
                        <option value="Media" <?= $currentCategory === 'Media' ? 'selected' : '' ?>>Media</option>
                        <option value="Concept" <?= $currentCategory === 'Concept' ? 'selected' : '' ?>>Concepts</option>
                        <option value="EverythingElse" <?= $currentCategory === 'EverythingElse' ? 'selected' : '' ?>>Everything Else</option>
                    </select>
                    <button type="submit"><i class="fa fa-search"></i></button>
                </form>
            </li>

            <?php if (isset($_SESSION['userID']) && isset($_SESSION['userName'])): ?>
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle"><?= htmlspecialchars($_SESSION['userName']) ?></a>
                    <ul class="dropdown-menu">
                        <?php if ($_SESSION['role'] === "Admin"): ?>
                            <li><a href="http://cs3-dev.ict.ru.ac.za/practicals/4a2/content/adminDashboard.php">AdminPage</a></li>
                        <?php elseif ($_SESSION['role'] === "Mod"): ?>
                            <li><a href="http://cs3-dev.ict.ru.ac.za/practicals/4a2/HomePage/profile.php">ModeratorPage</a></li>
                        <?php endif; ?>
                        <li><a href="http://cs3-dev.ict.ru.ac.za/practicals/4a2/HomePage/profile.php">Profile</a></li>
                        <li><a href="http://cs3-dev.ict.ru.ac.za/practicals/4a2/HomePage/settings.php">Settings</a></li>
                        <li><a href="http://cs3-dev.ict.ru.ac.za/practicals/4a2/homepage/notification.php">Notification</a></li>
                        <li><a href="http://cs3-dev.ict.ru.ac.za/practicals/4a2/HomePage/Logout.php" onclick="return confirm('Are you sure you want to log out?');">Log Out</a></li>
                    </ul>
                </li>
            <?php else: ?>
                <li><a href="http://cs3-dev.ict.ru.ac.za/practicals/4a2/HomePage/Login.php" class="<?= $currentPage === 'Login.php' ? 'active' : '' ?>">Log-in</a></li>
            <?php endif; ?>
        </ul>

        <!-- Search toggle and mobile search form -->
        <button class="search-toggle" id="search-toggle" aria-label="Toggle search">
            <i class="fa fa-search"></i>
        </button>

        <form action="http://cs3-dev.ict.ru.ac.za/practicals/4a2/HomePage/search.php" method="get" class="nav-search-mobile">
            <input type="text" name="search" placeholder="Search..." value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
            <select name="category">
                <option value="">All Categories</option>
                <option value="Place" <?= $currentCategory === 'Place' ? 'selected' : '' ?>>Places</option>
                <option value="Food" <?= $currentCategory === 'Food' ? 'selected' : '' ?>>Food</option>
                <option value="Media" <?= $currentCategory === 'Media' ? 'selected' : '' ?>>Media</option>
                <option value="Concept" <?= $currentCategory === 'Concept' ? 'selected' : '' ?>>Concepts</option>
                <option value="EverythingElse" <?= $currentCategory === 'EverythingElse' ? 'selected' : '' ?>>Everything Else</option>
            </select>
            <button type="submit"><i class="fa fa-search"></i></button>
        </form>
    </nav>
</header>