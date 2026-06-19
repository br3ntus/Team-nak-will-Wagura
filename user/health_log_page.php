<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// If not logged in, redirect to login page
if (!isset($_SESSION["user_id"])) {
    header("Location: ../login_page.html");
    exit();
}

require_once "../db_connection_pdo.php";

$user_id = $_SESSION["user_id"];
$first_name = $_SESSION["first_name"] ?? "User";

// Get health logs for user's pets
$health_logs = [];
try {
    $stmt = $conn->prepare("SELECT l.*, p.name as pet_name, p.type as pet_type FROM health_logs l JOIN pets p ON l.pet_id = p.pet_id WHERE p.user_id = :user_id ORDER BY l.created_at DESC");
    if ($stmt instanceof PDOStatement && $stmt->execute(['user_id' => $user_id])) {
        $health_logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $health_logs = [];
    }
} catch (PDOException $e) {
    // Fallback data
    $health_logs = [
        ['log_id' => 1, 'pet_name' => 'Coco', 'log_type' => 'Feeding', 'description' => '200g dry food + water refilled', 'created_at' => '2026-03-20 07:30:00'],
        ['log_id' => 2, 'pet_name' => 'Mochi', 'log_type' => 'Weight', 'description' => '3.5 kg', 'created_at' => '2026-03-20 10:00:00'],
        ['log_id' => 3, 'pet_name' => 'Bruno', 'log_type' => 'Vet visit', 'description' => 'Vaccination', 'created_at' => '2026-03-19 14:30:00'],
    ];
}

// Get user's pets for pet name display
$user_pets = [];
try {
    $stmt = $conn->prepare("SELECT * FROM pets WHERE user_id = :user_id");
    if ($stmt instanceof PDOStatement && $stmt->execute(['user_id' => $user_id])) {
        $user_pets = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $user_pets = [];
    }
} catch (PDOException $e) {
    $user_pets = [];
}

function getLogIcon($type) {
    $icons = [
        'Feeding' => 'bowl-food',
        'Weight' => 'weight-scale',
        'Vet visit' => 'hospital',
        'Symptoms' => 'face-frown'
    ];
    return $icons[$type] ?? 'list';
}

function getLogColor($type) {
    $colors = [
        'Feeding' => '#F09595',
        'Weight' => '#85B7EB',
        'Vet visit' => '#FAC775',
        'Symptoms' => '#FFB800'
    ];
    return $colors[$type] ?? '#999';
}
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Wagura | Pet Health Logs</title>
    <link rel="stylesheet" href="../template.css" />
    <link rel="stylesheet" href="../css/health_log_page.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
</head>
<body>
    <div class="dashboard-wrapper">
        <aside class="sidebar">
            <a href="../landing_page.html"><img src="../images/Wagura Logo 60x60.png" alt="Wagura" class="sidebar-logo"></a>
            <ul class="sidebar-links">
                <li><a href="dashboard_page.php"><i class="fa-solid fa-house"></i></a></li>
                <li><a href="my_pet_page.php"><i class="fa-solid fa-user"></i></a></li>
                <li><a href="articles_page.php"><i class="fa-solid fa-table-cells-large"></i></a></li>
                <li><a href="daily_insights_page.php"><i class="fa-solid fa-star"></i></a></li>
                <li><a href="health_log_page.php" class="active"><i class="fa-solid fa-clipboard-list"></i></a></li>
            </ul>
            <div class="sidebar-bottom" style="margin-top: auto; padding-top: 20px;">
                <a href="../logout.php" title="Logout" style="color: var(--text-muted); font-size: 18px; display: flex; align-items: center; justify-content: center;"><i class="fa-solid fa-right-from-bracket"></i></a>
            </div>
        </aside>

        <main class="main-content">
            <header class="page-header">
                <div class="title-group">
                    <h1>Pet Health Logs</h1>
                    <p>All health entries across your pets</p>
                </div>
                <div class="user-profile"><?php echo substr($first_name, 0, 2); ?></div>
            </header>

            <div class="filter-row">
                <div class="log-filters">
                    <button class="filter-btn active" data-filter="all"><i class="fa-solid fa-list"></i> All logs</button>
                    <button class="filter-btn" data-filter="Feeding"><i class="fa-solid fa-bowl-food"></i> Feeding</button>
                    <button class="filter-btn" data-filter="Weight"><i class="fa-solid fa-weight-scale"></i> Weight</button>
                    <button class="filter-btn" data-filter="Vet visit"><i class="fa-solid fa-hospital"></i> Vet visit</button>
                    <button class="filter-btn" data-filter="Symptoms"><i class="fa-solid fa-face-frown"></i> Symptoms</button>
                </div>
                <a href="add_log_page.php" class="add-log-btn">+ Add new log</a>
            </div>

            <div class="log-page-grid">
                <div class="timeline-section" id="logsTimeline">
                    <?php if (empty($health_logs)): ?>
                        <div style="text-align: center; padding: 40px; color: var(--text-secondary);">
                            <i class="fa-solid fa-clipboard-list" style="font-size: 48px; margin-bottom: 20px; opacity: 0.5;"></i>
                            <p>No health logs yet. <a href="add_log_page.php" style="color: var(--primary);">Add your first log</a></p>
                        </div>
                    <?php else: ?>
                        <?php
                        $currentDate = '';
                        foreach ($health_logs as $log):
                            $logDate = date('M d, Y', strtotime($log['created_at']));
                            $logTime = date('g:i A', strtotime($log['created_at']));
                            
                            if ($currentDate !== $logDate):
                                if ($currentDate !== ''):
                        ?>
                                </div>
                            </div>
                        <?php
                                endif;
                                $currentDate = $logDate;
                        ?>
                        <div class="timeline-group">
                            <h2><?php echo strtoupper($logDate); ?></h2>
                            <div class="log-items-list">
                        <?php
                            endif;
                        ?>
                                <div class="log-entry-card" data-type="<?php echo htmlspecialchars($log['log_type']); ?>">
                                    <div class="log-icon-box"><i class="fa-solid fa-<?php echo getLogIcon($log['log_type']); ?>" style="color: <?php echo getLogColor($log['log_type']); ?>;"></i></div>
                                    <div class="log-info">
                                        <h3><?php echo htmlspecialchars($log['log_type']); ?></h3>
                                        <p><?php echo htmlspecialchars($log['description']); ?></p>
                                    </div>
                                    <div class="pet-tag-group">
                                        <span class="pet-name-label"><?php echo htmlspecialchars($log['pet_name']); ?></span>
                                        <span class="log-type-label"><?php echo htmlspecialchars($log['log_type']); ?></span>
                                        <span class="log-time-label"><?php echo $logTime; ?></span>
                                    </div>
                                </div>
                        <?php
                        endforeach;
                        if ($currentDate !== ''):
                        ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <script>
        const filterButtons = document.querySelectorAll('.filter-btn');
        const logCards = document.querySelectorAll('.log-entry-card');

        filterButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                filterButtons.forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                const filter = this.dataset.filter;

                logCards.forEach(card => {
                    if (filter === 'all' || card.dataset.type === filter) {
                        card.style.display = '';
                    } else {
                        card.style.display = 'none';
                    }
                });
            });
        });
    </script>
</body>
</html>
