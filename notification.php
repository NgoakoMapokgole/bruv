<?php
include "../rate.php";
session_start();
$user = $_SESSION['userID'];
$result = $conn->query("
    SELECT * 
    FROM notification 
    WHERE deleted = 0
    AND userID = $user
    ORDER BY dateCreated DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Notification | Rate It All</title>
    <link rel="icon" type = "image/svg+xml" href="http://cs3-dev.ict.ru.ac.za/practicals/4a2/logo.png">
    <link rel="stylesheet" href="../mainStyle.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="../mainStyle.css">
    <style>
        /* Notification-specific styles that work with main theme */
        .notifications-container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .notifications-header {
            text-align: center;
            margin-bottom: 2rem;
            color: var(--text);
        }

        .notifications-header h1 {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .notifications-header .subtitle {
            color: var(--accent);
            font-size: 1.1rem;
            opacity: 0.9;
        }

        .notification {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 1.5rem;
            margin: 1.5rem 0;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .notification:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
            background: rgba(255, 255, 255, 0.08);
        }

        .notification::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
        }

        .notification .title {
            font-weight: 600;
            color: var(--text);
            font-size: 1.3rem;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .notification .meta {
            color: var(--accent);
            font-size: 0.9rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .notification .content {
            color: var(--text);
            line-height: 1.6;
            opacity: 0.9;
        }

        /* Category-specific styles using your theme colors */
        .category-System::before { background: var(--primary); }
        .category-System .meta { color: var(--primary); }

        .category-Alerts::before { background: #ff4444; }
        .category-Alerts .meta { color: #ff4444; }

        .category-UserBased::before { background: var(--secondary); }
        .category-UserBased .meta { color: var(--secondary); }

        .category-Promo::before { background: var(--accent); }
        .category-Promo .meta { color: var(--accent); }

        /* Empty state */
        .no-notifications {
            text-align: center;
            padding: 3rem 2rem;
            color: var(--text);
            opacity: 0.7;
        }

        .no-notifications .icon {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }

        .no-notifications h3 {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
            color: var(--accent);
        }

        /* Category badges */
        .category-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .category-System .category-badge { 
            background: rgba(242, 31, 7, 0.2); 
            border-color: rgba(242, 31, 7, 0.3);
            color: var(--primary);
        }

        .category-Alerts .category-badge { 
            background: rgba(255, 68, 68, 0.2); 
            border-color: rgba(255, 68, 68, 0.3);
            color: #ff4444;
        }

        .category-UserBased .category-badge { 
            background: rgba(133, 105, 5, 0.2); 
            border-color: rgba(133, 105, 5, 0.3);
            color: var(--secondary);
        }

        .category-Promo .category-badge { 
            background: rgba(189, 192, 7, 0.2); 
            border-color: rgba(189, 192, 7, 0.3);
            color: var(--accent);
        }

        /* Responsive design */
        @media (max-width: 768px) {
            .notifications-container {
                margin: 1rem auto;
                padding: 0 0.5rem;
            }

            .notifications-header h1 {
                font-size: 2rem;
            }

            .notification {
                padding: 1.25rem;
                margin: 1rem 0;
            }

            .notification .title {
                font-size: 1.1rem;
            }

            .notification .meta {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }
        }

        @media (max-width: 480px) {
            .notification {
                padding: 1rem;
            }

            .notifications-header h1 {
                font-size: 1.75rem;
            }
        }
    </style>
</head>
<body>
    <?php include "../nav.php"; ?>

    <div class="notifications-container">
        <div class="notifications-header">
            <h1>Notifications</h1>
            <div class="subtitle">Stay updated with your latest activities</div>
        </div>

        <?php if ($result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
                <div class="notification category-<?php echo htmlspecialchars($row['category']); ?>">
                    <div class="title">
                        <?php echo htmlspecialchars($row['title']); ?>
                    </div>
                    <div class="meta">
                        <span class="category-badge"><?php echo htmlspecialchars($row['category']); ?></span>
                        <span><?php echo date("d M Y, H:i", strtotime($row['dateCreated'])); ?></span>
                    </div>
                    <div class="content"><?php echo nl2br(htmlspecialchars($row['content'])); ?></div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="no-notifications">
                <div class="icon">🔔</div>
                <h3>No notifications yet</h3>
                <p>When you get notifications, they'll appear here.</p>
            </div>
        <?php endif; ?>
    </div>

</body>
</html>
