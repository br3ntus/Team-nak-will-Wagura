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

// Get all articles for display
$articles = [];
$total_articles = 0;

try {
    $stmt = $conn->prepare("SELECT * FROM articles ORDER BY created_at DESC");
    if ($stmt instanceof PDOStatement && $stmt->execute()) {
        $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $total_articles = count($articles);
    } else {
        $articles = [];
        $total_articles = 0;
    }
} catch (PDOException $e) {
    // Fallback data
    $articles = [
        ['article_id' => 1, 'title' => 'Protecting your dog from ticks in humid Laguna weather', 'description' => 'Learn how to check for ticks and prevent infestations during the rainy season.', 'category_id' => 1, 'icon' => 'bug', 'created_at' => '2026-03-10'],
        ['article_id' => 2, 'title' => 'Cat nutrition guide for Philippine climate', 'description' => 'Best practices for feeding your cat in tropical weather.', 'category_id' => 2, 'icon' => 'shield', 'created_at' => '2026-03-09'],
    ];
    $total_articles = count($articles);
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
    <title>Wagura | Articles & Guides</title>
    <link rel="stylesheet" href="../template.css" />
    <link rel="stylesheet" href="../css/articles_page.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
</head>
<body>
    <div class="dashboard-wrapper">
        <aside class="sidebar">
            <a href="../landing_page.html"><img src="../images/Wagura Logo 60x60.png" alt="Wagura" class="sidebar-logo"></a>
            <ul class="sidebar-links">
                <li><a href="dashboard_page.php"><i class="fa-solid fa-house"></i></a></li>
                <li><a href="my_pet_page.php"><i class="fa-solid fa-user"></i></a></li>
                <li><a href="articles_page.php" class="active"><i class="fa-solid fa-table-cells-large"></i></a></li>
                <li><a href="daily_insights_page.php"><i class="fa-solid fa-star"></i></a></li>
                <li><a href="health_log_page.php"><i class="fa-solid fa-clipboard-list"></i></a></li>
            </ul>
            <div class="sidebar-bottom" style="margin-top: auto; padding-top: 20px;">
                <a href="../logout.php" title="Logout" style="color: var(--text-muted); font-size: 18px; display: flex; align-items: center; justify-content: center;"><i class="fa-solid fa-right-from-bracket"></i></a>
            </div>
        </aside>

        <main class="main-content">
            <header class="page-header">
                <div class="title-group">
                    <h1>Articles & Guides</h1>
                    <p>PH-specific guides for Laguna pet owners</p>
                </div>
                <div style="width: 35px; height: 35px; background: #3d4f5e; color: #fff; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 500;"><?php echo substr($first_name, 0, 2); ?></div>
            </header>

            <div class="toolbar">
                <div class="search-bar">
                    <div class="search-input">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <input type="text" id="searchInput" placeholder="Search articles..." />
                    </div>
                </div>
                <span class="results-count"><span id="resultsCount"><?php echo $total_articles; ?></span> articles found</span>
            </div>

            <div class="filter-pill-row" id="filterPills">
                <button class="pill active" data-filter="all">All</button>
                <?php foreach ($categories as $cat): ?>
                    <button class="pill" data-filter="<?php echo $cat['category_id']; ?>">
                        <i class="fa-solid fa-<?php echo $cat['name'] === 'Dogs' ? 'dog' : ($cat['name'] === 'Cats' ? 'cat' : 'paw'); ?>"></i>
                        <?php echo htmlspecialchars($cat['name']); ?>
                    </button>
                <?php endforeach; ?>
            </div>

            <div class="articles-grid" id="articlesGrid">
                <?php foreach ($articles as $article): ?>
                    <a href="article_single_page.php?id=<?php echo $article['article_id']; ?>" class="article-card" data-category="<?php echo $article['category_id']; ?>" data-title="<?php echo strtolower($article['title']); ?>">
                        <div class="article-header">
                            <i class="fa-solid fa-<?php echo htmlspecialchars($article['icon']); ?>"></i>
                        </div>
                        <div class="article-body">
                            <div class="tag-row">
                                <span class="tag ph">PH Guide</span>
                                <span class="tag general">All breeds</span>
                            </div>
                            <h3><?php echo htmlspecialchars($article['title']); ?></h3>
                            <p><?php echo htmlspecialchars($article['description']); ?></p>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </main>
    </div>

    <script>
        const searchInput = document.getElementById('searchInput');
        const filterPills = document.querySelectorAll('.pill');
        const articlesGrid = document.getElementById('articlesGrid');
        const resultsCount = document.getElementById('resultsCount');
        const articleCards = articlesGrid.querySelectorAll('.article-card');

        let currentFilter = 'all';

        function filterAndSearch() {
            const searchTerm = searchInput.value.toLowerCase();
            let visibleCount = 0;

            articleCards.forEach(card => {
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

            resultsCount.textContent = visibleCount;
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
