<?php
$currentPage = basename($_SERVER['PHP_SELF']); // get current page filename
?>

<div class="sidebar">
    <center><h1 class="logo">Rate It All !</h1></center>
    <center><h2><i class="fa fa-shield"></i> Admin</h2></center>
    <div>
        <ul>
            <li>
                <a href="adminDashboard.php" class="<?= $currentPage === 'adminDashboard.php' ? 'active' : '' ?>">
                    <i class="fa fa-tachometer"></i> Dashboard
                </a>
            </li>
            <li>
                <a href="userControl.php" class="<?= $currentPage === 'userControl.php' ? 'active' : '' ?>">
                    <i class="fa fa-users"></i> Users
                </a>
            </li>
            <li>
                <a href="adminReviews.php" class="<?= $currentPage === 'adminReviews.php' ? 'active' : '' ?>">
                    <i class="fa fa-star"></i> Reviews
                </a>
            </li>
            <li>
                <a href="adminComments.php" class="<?= $currentPage === 'adminComments.php' ? 'active' : '' ?>">
                    <i class="fa fa-comments"></i> Comments
                </a>
            </li>
            <li>
                <a href="adminReports.php" class="<?= $currentPage === 'adminReports.php' ? 'active' : '' ?>">
                    <i class="fa fa-flag"></i> Reports
                </a>
            </li>
            <li>
                <a href="adminAudits.php" class="<?= $currentPage === 'adminAudits.php' ? 'active' : '' ?>">
                    <i class="fa fa-search"></i> Audits
                </a>
            </li>
            <li>
                <a href="../Homepage/index.php" class="<?= $currentPage === 'index.php' ? 'active' : '' ?>">
                    <i class="fa fa-home"></i> HomePage
                </a>
            </li>
        </ul>
    </div>
    <div class="logout">
        <ul>
            <li>
                <a href="../Homepage/Logout.php" onclick="return confirm('Are you sure you want to log out?');">
                    <i class="fa fa-sign-out"></i> Log Out
                </a>
            </li>
        </ul>
    </div>
</div>
