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

// Get article ID from URL
$article_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Get article details
$article = null;
try {
    $stmt = $conn->prepare("SELECT * FROM articles WHERE article_id = :id");
    $stmt->execute(['id' => $article_id]);
    $article = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $article = null;
}

// Fallback if article not found
if (!$article) {
    $article = [
        'article_id' => $article_id,
        'title' => 'Protecting your dog from ticks in humid Laguna weather',
        'description' => 'Learn how to check for ticks and prevent infestations during the rainy season.',
        'content' => '<h2>Why ticks are a problem in Laguna</h2>
<p>Laguna\'s humid climate creates the perfect environment for ticks to thrive year-round. Unlike colder regions, pets in Laguna are exposed to ticks in every season, especially during and after the rainy season when grass and vegetation stay wet for longer periods.</p>
<div class="wagura-tip"><strong>Wagura tip</strong> Log any scratching or skin irritation in your pet\'s health log so you can track patterns over time.</div>
<h2>How to check your dog for ticks</h2>
<ul>
<li>Run your fingers through your dog\'s coat slowly, feeling for small bumps.</li>
<li>Pay extra attention to the ears, neck, between toes, and under the collar.</li>
<li>Check after every outdoor walk or playtime in the grass.</li>
<li>Use a fine tooth comb for dogs with thick or long coats like Shih Tzus.</li>
</ul>
<h2>Prevention tips</h2>
<p>Use vet-approved tick prevention products such as topical treatments or tick collars. Ask your local vet in Laguna for brands available in your area. Keep your yard trimmed and avoid letting your dog play in tall grass or near drainage areas where ticks commonly hide.</p>
<div class="wagura-tip" style="border-color: var(--tag-symptoms);"><strong style="color: var(--tag-symptoms);">Important</strong> Never remove a tick with your bare hands. Use tweezers and pull straight out without twisting to avoid leaving the head embedded in the skin.</div>',
        'category_id' => 1,
        'icon' => 'bug',
        'created_at' => '2026-03-10'
    ];
}
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Wagura | Article Reader</title>
    <link rel="stylesheet" href="../template.css" />
    <link rel="stylesheet" href="../css/article_single_page.css" />
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
                <a href="articles_page.php" class="back-link">← Back to Articles</a>
            </header>

            <div class="article-reader-grid">
                <article class="article-main-cave">
                    <div class="article-hero-img">
                        <i class="fa-solid fa-<?php echo htmlspecialchars($article['icon'] ?? 'book'); ?>"></i>
                    </div>
                    <div class="article-content-body">
                        <div class="article-meta-row">
                            <span style="font-size: 10px; padding: 4px 10px; border-radius: 4px; background: rgba(151, 196, 89, 0.1); color: var(--tag-ph);">PH Guide</span>
                            <span style="font-size: 10px; padding: 4px 10px; border-radius: 4px; background: rgba(127, 142, 160, 0.1); color: var(--text-secondary);">All breeds</span>
                        </div>
                        
                        <h1><?php echo htmlspecialchars($article['title']); ?></h1>
                        
                        <div class="article-sub-meta">
                            General • 3 min read • Posted <?php echo date('M d, Y', strtotime($article['created_at'])); ?>
                        </div>

                        <div class="article-text">
                            <?php echo $article['content']; ?>
                        </div>
                    </div>
                </article>

                <aside class="article-sidebar">
                    <div class="toc-panel">
                        <h3>In this article</h3>
                        <ul class="toc-list">
                            <li><a href="#" class="toc-link"><span>1</span> Overview</a></li>
                            <li><a href="#" class="toc-link"><span>2</span> Details</a></li>
                            <li><a href="#" class="toc-link"><span>3</span> Tips</a></li>
                        </ul>
                    </div>

                    <div class="related-articles">
                        <h3>Related articles</h3>
                        <ul style="list-style: none; padding: 0;">
                            <li style="padding: 12px 0; border-bottom: 1px solid var(--border-subtle);">
                                <a href="articles_page.php" style="color: var(--text-secondary); text-decoration: none; font-size: 13px;">
                                    ← More articles
                                </a>
                            </li>
                        </ul>
                    </div>
                </aside>
            </div>
        </main>
    </div>
</body>
</html>
