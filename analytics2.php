<?php
include "../rate.php";
$type = $_GET['type'] ?? '';
header('Content-Type: application/json');

if($type==='users'){
    // Registrations per month
    $reg = $conn->query("
      SELECT DATE_FORMAT(activeDate,'%Y-%m') AS month, COUNT(*) AS cnt
      FROM users
      GROUP BY month
      ORDER BY month ASC
    ");
    $months = []; $counts = [];
    while($r = $reg->fetch_assoc()){
        $months[] = $r['month'];
        $counts[] = $r['cnt'];
    }

    // Role distribution
    $roles = $conn->query("SELECT role, COUNT(*) AS cnt FROM users GROUP BY role");
    $roleLabels = []; $roleCounts = [];
    while($r = $roles->fetch_assoc()){
        $roleLabels[] = $r['role'];
        $roleCounts[] = $r['cnt'];
    }

    // Account status
    $statuses = $conn->query("SELECT accountStatus, COUNT(*) AS cnt FROM users GROUP BY accountStatus");
    $statusLabels = []; $statusCounts = [];
    while($s = $statuses->fetch_assoc()){
        $statusLabels[] = $s['accountStatus'];
        $statusCounts[] = $s['cnt'];
    }

    echo json_encode([
        'months'=>$months,
        'registrations'=>$counts,
        'roleLabels'=>$roleLabels,
        'roleCounts'=>$roleCounts,
        'statusLabels'=>$statusLabels,
        'statusCounts'=>$statusCounts
    ]);
    exit();
}

if($type==='reviews'){
    // Reviews per month
    $trend = $conn->query("
        SELECT DATE_FORMAT(dateCreated,'%Y-%m') AS month, COUNT(*) AS cnt
        FROM post
        WHERE deleted=0
        GROUP BY month
        ORDER BY month ASC
    ");
    $months = []; $counts = [];
    while($r=$trend->fetch_assoc()){
        $months[] = $r['month'];
        $counts[] = $r['cnt'];
    }

    // Reviews by status
    $status = $conn->query("
        SELECT SUM(CASE WHEN deleted=0 THEN 1 ELSE 0 END) AS active,
               SUM(CASE WHEN deleted=1 THEN 1 ELSE 0 END) AS deleted
        FROM post
    ")->fetch_assoc();

    echo json_encode([
        'months'=>$months,
        'trend'=>$counts,
        'statusLabels'=>['Active','Deleted'],
        'statusCounts'=>[(int)$status['active'], (int)$status['deleted']]
    ]);
    exit();
}

if($type==='comments'){
    // Comments per month
    $trend = $conn->query("
        SELECT DATE_FORMAT(datecCreated,'%Y-%m') AS month, COUNT(*) AS cnt
        FROM comments
        WHERE deleted=0
        GROUP BY month
        ORDER BY month ASC
    ");
    $months = []; $counts = [];
    while($r=$trend->fetch_assoc()){
        $months[] = $r['month'];
        $counts[] = $r['cnt'];
    }

    // Top 5 commenters
    $topUsers = $conn->query("
        SELECT u.userName, COUNT(c.commentID) AS cnt
        FROM comments c
        JOIN users u ON c.userID=u.userID
        WHERE c.deleted=0
        GROUP BY u.userName
        ORDER BY cnt DESC
        LIMIT 5
    ");
    $topLabels = []; $topCounts = [];
    while($u=$topUsers->fetch_assoc()){
        $topLabels[] = $u['userName'];
        $topCounts[] = $u['cnt'];
    }

    echo json_encode([
        'months'=>$months,
        'trend'=>$counts,
        'topLabels'=>$topLabels,
        'topCounts'=>$topCounts
    ]);
    exit();
}

if($type==='reports'){
    // Reports per month
    $trend = $conn->query("
        SELECT DATE_FORMAT(createdAt,'%Y-%m') AS month, COUNT(*) AS cnt
        FROM reports
        GROUP BY month
        ORDER BY month ASC
    ");
    $months=[]; $num=[];
    while($t=$trend->fetch_assoc()){
        $months[]=$t['month'];
        $num[]=$t['cnt'];
    }

    // Reports by status
    $status = $conn->query("SELECT status, COUNT(*) AS cnt FROM reports GROUP BY status");
    $statusLabels=[]; $statusCounts=[];
    while($s=$status->fetch_assoc()){
        $statusLabels[]=$s['status'];
        $statusCounts[]=$s['cnt'];
    }

    // Reports by content type
    $typeData = $conn->query("SELECT contentType, COUNT(*) AS cnt FROM reports GROUP BY contentType");
    $typeLabels=[]; $typeCounts=[];
    while($c=$typeData->fetch_assoc()){
        $typeLabels[]=$c['contentType'];
        $typeCounts[]=$c['cnt'];
    }

    echo json_encode([
        'months'=>$months,
        'trend'=>$num,
        'statusLabels'=>$statusLabels,
        'statusCounts'=>$statusCounts,
        'typeLabels'=>$typeLabels,
        'typeCounts'=>$typeCounts
    ]);
    exit();
}

echo json_encode(['error'=>'Invalid type']);
?>
