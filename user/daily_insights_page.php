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

$user_id = $_SESSION["user_id"] ?? 0;
$first_name = $_SESSION["first_name"] ?? "User";

// Get all daily insights
$insights = [];
$total_insights = 0;
$featured_insight = null;

try {
    $stmt = $conn->prepare("SELECT * FROM daily_insights ORDER BY created_at DESC");
    if ($stmt instanceof PDOStatement && $stmt->execute()) {
        $insights = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $total_insights = count($insights);

        // Get featured (most recent)
        if (!empty($insights)) {
            $featured_insight = $insights[0];
            $insights = array_slice($insights, 1);
        }
    } else {
        $insights = [];
        $total_insights = 0;
    }
} catch (PDOException $e) {
    // Fallback data
    $featured_insight = [
        'insight_id' => 1,
        'title' => 'Did you know? Cats sleep 12 to 16 hours a day to conserve energy for their natural hunting instincts.',
        'category_id' => 2,
        'category_name' => 'Cats',
        'created_at' => date('Y-m-d')
    ];
    
    $insights = [
        ['insight_id' => 2, 'title' => 'Aspins need extra deworming every 3 months due to outdoor exposure', 'category_name' => 'Dogs', 'created_at' => '2026-03-19'],
        ['insight_id' => 3, 'title' => 'High humidity in Laguna can cause skin issues in pets', 'category_name' => 'General', 'created_at' => '2026-03-18'],
    ];
    $total_insights = 1 + count($insights);
}

// Get categories
$categories = [];
try {
    $stmt = $conn->prepare("SELECT * FROM categories");
    if ($stmt instanceof PDOStatement && $stmt->execute()) {
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $categories = [];
    }
} catch (PDOException $e) {
    $categories = [];
}
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Wagura | Daily Pet Insights</title>
    <link rel="stylesheet" href="../template.css" />
    <link rel="stylesheet" href="../css/daily_insights_page.css" />
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
                <li><a href="daily_insights_page.php" class="active"><i class="fa-solid fa-star"></i></a></li>
                <li><a href="health_log_page.php"><i class="fa-solid fa-clipboard-list"></i></a></li>
            </ul>
            <div class="sidebar-bottom" style="margin-top: auto; padding-top: 20px;">
                <a href="../logout.php" title="Logout" style="color: var(--text-muted); font-size: 18px; display: flex; align-items: center; justify-content: center;"><i class="fa-solid fa-right-from-bracket"></i></a>
            </div>
        </aside>

        <main class="main-content">
            <header class="page-header">
                <div class="title-group">
                    <h1>Daily Pet Insights</h1>
                    <p>Did-you-know facts for Laguna pet owners</p>
                </div>
                <div style="width: 35px; height: 35px; background: #3d4f5e; color: #fff; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 500;"><?php echo substr($first_name, 0, 2); ?></div>
            </header>

            <div class="toolbar">
                <div class="search-bar">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" id="searchInput" placeholder="Search insights..." />
                </div>
                <span class="results-info"><span id="resultsInfo"><?php echo $total_insights; ?></span> insights total</span>
            </div>

            <div class="filter-row">
                <button class="pill active" data-filter="all">All</button>
                <?php foreach ($categories as $cat): ?>
                    <button class="pill" data-filter="<?php echo $cat['category_id']; ?>">
                        <i class="fa-solid fa-<?php echo $cat['name'] === 'Dogs' ? 'dog' : ($cat['name'] === 'Cats' ? 'cat' : 'paw'); ?>"></i>
                        <?php echo htmlspecialchars($cat['name']); ?>
                    </button>
                <?php endforeach; ?>
            </div>

            <?php if ($featured_insight): ?>
            <div class="featured-insight-card">
                <div class="featured-content">
                    <div class="today-badge">
                        <i class="fa-solid fa-star"></i> Today's Insight — <?php echo date('M d, Y', strtotime($featured_insight['created_at'])); ?>
                    </div>
                    <h2><?php echo htmlspecialchars($featured_insight['title']); ?></h2>
                    <div class="featured-meta"><?php echo htmlspecialchars($featured_insight['category_name'] ?? 'General'); ?> • Posted by Admin</div>
                </div>
                <div class="featured-illustration">
                    <i class="fa-solid fa-<?php echo ($featured_insight['category_name'] ?? '') === 'Dogs' ? 'dog' : (($featured_insight['category_name'] ?? '') === 'Cats' ? 'cat' : 'paw'); ?>"></i>
                </div>
            </div>
            <?php endif; ?>

            <div class="insights-section-title">PREVIOUS INSIGHTS</div>
            <div class="insights-grid" id="insightsGrid">
                <?php foreach ($insights as $insight): ?>
                    <div class="insight-card" data-category="<?php echo $insight['category_id'] ?? 0; ?>" data-title="<?php echo strtolower($insight['title']); ?>">
                        <div class="insight-icon">
                            <i class="fa-solid fa-<?php 
                                $cat = $insight['category_name'] ?? '';
                                echo $cat === 'Dogs' ? 'dog' : ($cat === 'Cats' ? 'cat' : 'paw'); 
                            ?>"></i>
                        </div>
                        <div class="insight-tag <?php echo strtolower($insight['category_name'] ?? 'general'); ?>">
                            <?php echo htmlspecialchars($insight['category_name'] ?? 'General'); ?>
                        </div>
                        <h3><?php echo htmlspecialchars($insight['title']); ?></h3>
                        <div class="insight-footer">
                            <span><?php echo date('M d', strtotime($insight['created_at'])); ?></span>
                            <a href="#" class="read-link">Read →</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </main>
    </div>

    <script>
        const searchInput = document.getElementById('searchInput');
        const filterPills = document.querySelectorAll('.pill');
        const insightsGrid = document.getElementById('insightsGrid');
        const resultsInfo = document.getElementById('resultsInfo');
        const insightCards = insightsGrid.querySelectorAll('.insight-card');

        let currentFilter = 'all';

        function filterAndSearch() {
            const searchTerm = searchInput.value.toLowerCase();
            let visibleCount = 0;

            insightCards.forEach(card => {
                const title = card.dataset.title;
                const category = card.dataset.category;
                const matchesSearch = title.includes(searchTerm);
                const matchesFilter = currentFilter === 'all' || category === currentFilter;

                if (matchesSearch && matchesFilter) {
                    card.style.display = '';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });

            resultsInfo.textContent = visibleCount;
        }

        searchInput.addEventListener('input', filterAndSearch);

        filterPills.forEach(pill => {
            pill.addEventListener('click', function() {
                filterPills.forEach(p => p.classList.remove('active'));
                this.classList.add('active');
                currentFilter = this.dataset.filter;
                filterAndSearch();
            });
        });
    </script>
</body>
</html>
