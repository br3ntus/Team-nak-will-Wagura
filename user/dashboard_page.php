<?php
session_start();
// If not logged in, redirect to login page
if (!isset($_SESSION["user_id"])) {
    header("Location: ../login_page.html");
    exit();
}

require_once "../db_connection_pdo.php";

$user_id = $_SESSION["user_id"];
$first_name = $_SESSION["first_name"] ?? "User";

// Count pets belonging to this user
$pets_count = 0;
$user_pets = [];
try {
    $stmtCount = $conn->prepare("SELECT COUNT(*) FROM pets WHERE user_id = :user_id");
    $stmtCount->execute(['user_id' => $user_id]);
    if ($stmtCount instanceof PDOStatement) {
        $pets_count = (int)$stmtCount->fetchColumn();
    }

    $stmtPets = $conn->prepare("SELECT * FROM pets WHERE user_id = :user_id");
    $stmtPets->execute(['user_id' => $user_id]);
    if ($stmtPets instanceof PDOStatement) {
        $user_pets = $stmtPets->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    // Fallback if table or columns don't exist
    $pets_count = 3;
    $user_pets = [
        ['name' => 'Coco', 'breed' => 'Aspin', 'age' => '3 years old', 'pet_id' => 'WGR-001', 'status' => 'Healthy', 'type' => 'Dog'],
        ['name' => 'Mochi', 'breed' => 'Persian', 'age' => '2 years old', 'pet_id' => 'WGR-002', 'status' => 'Healthy', 'type' => 'Cat'],
        ['name' => 'Bruno', 'breed' => 'Shih Tzu', 'age' => '1 year old', 'pet_id' => 'WGR-003', 'status' => 'Healthy', 'type' => 'Dog']
    ];
}

// Health logs for this user's pets
$recent_logs = [];
try {
    $stmtLogs = $conn->prepare("SELECT l.*, p.name as pet_name FROM health_logs l JOIN pets p ON l.pet_id = p.pet_id WHERE p.user_id = :user_id ORDER BY l.log_id DESC LIMIT 3");
    $stmtLogs->execute(['user_id' => $user_id]);
    if ($stmtLogs instanceof PDOStatement) {
        $recent_logs = $stmtLogs->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    try {
        $stmtLogs2 = $conn->prepare("SELECT * FROM health_logs LIMIT 3");
        $stmtLogs2->execute();
        if ($stmtLogs2 instanceof PDOStatement) {
            $recent_logs = $stmtLogs2->fetchAll(PDO::FETCH_ASSOC);
        }
    } catch (PDOException $e2) {
        $recent_logs = [
            ['type' => 'Feeding', 'pet_name' => 'Coco', 'details' => 'Morning meal logged', 'time' => 'Today', 'icon' => 'fa-bowl-food', 'color' => '#F09595'],
            ['type' => 'Weight', 'pet_name' => 'Mochi', 'details' => '3.2 kg recorded', 'time' => 'Yesterday', 'icon' => 'fa-weight-scale', 'color' => '#85B7EB'],
            ['type' => 'Vet Visit', 'pet_name' => 'Bruno', 'details' => 'Annual checkup done', 'time' => 'Mar 10', 'icon' => 'fa-hospital', 'color' => '#FAC775']
        ];
    }
}
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Wagura | Dashboard</title>

    <!-- CSS Links -->
    <link rel="stylesheet" href="../template.css" />
    <link rel="stylesheet" href="../css/dashboard_page.css" />

    <!-- Font Awesome -->
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  </head>
  <body>
    <!-- This is the main wrapper for the whole dashboard layout -->
    <div class="dashboard-wrapper">
      <!-- The sidebar on the left handles all the main navigation links -->
      <aside class="sidebar">
        <a href="../landing_page.html"><img src="../images/Wagura Logo 60x60.png" alt="Wagura" class="sidebar-logo"></a>
        <ul class="sidebar-links">
          <li><a href="dashboard_page.php" class="active"><i class="fa-solid fa-house"></i></a></li>
          <li><a href="my_pet_page.html"><i class="fa-solid fa-user"></i></a></li>
          <li><a href="articles_page.html"><i class="fa-solid fa-table-cells-large"></i></a></li>
          <li><a href="daily_insights_page.html"><i class="fa-solid fa-star"></i></a></li>
          <li><a href="health_log_page.html"><i class="fa-solid fa-clipboard-list"></i></a></li>
        </ul>
        <div class="sidebar-bottom" style="margin-top: auto; padding-top: 20px;">
          <a href="../logout.php" title="Logout" style="color: var(--text-muted); font-size: 18px; display: flex; align-items: center; justify-content: center;"><i class="fa-solid fa-right-from-bracket"></i></a>
        </div>
      </aside>

      <!-- This is where all the actual pet info and updates live -->
      <main class="main-content">
        <header class="top-header">
          <div class="date-text"><?php echo date("F j, Y"); ?></div>
          <div class="header-right">
            <div class="search-box">
              <i class="fa-solid fa-magnifying-glass"></i>
              <input type="text" placeholder="Search articles..." />
            </div>
            <div class="user-profile">
                <?php 
                $initials = "";
                $parts = explode(" ", $first_name);
                foreach ($parts as $part) {
                    if (!empty($part)) $initials .= strtoupper($part[0]);
                }
                echo htmlspecialchars(substr($initials, 0, 2));
                ?>
            </div>
          </div>
        </header>

        <div class="dashboard-grid">
          <!-- LEFT COLUMN -->
          <div class="content-left">
            <section class="greeting-card">
              <h5>Good morning, <?php echo htmlspecialchars($first_name); ?></h5>
              <h1>Keep your pets healthy with Wagura</h1>
              <div class="enrolled-badge">
                <i class="fa-solid fa-star"></i> <?php echo (int)$pets_count; ?> pets enrolled
              </div>
            </section>

            <section class="my-pets-section">
              <div class="section-title-row">
                <h2>MY PETS</h2>
              </div>
              <div class="pet-list">
                <?php foreach ($user_pets as $pet): ?>
                <a href="add_pet_page.html" class="pet-card">
                  <div class="pet-avatar">
                    <i class="fa-solid <?php echo (isset($pet['type']) && strtolower($pet['type']) === 'cat') ? 'fa-cat' : 'fa-dog'; ?>"></i>
                  </div>
                  <div class="pet-info">
                    <h3><?php echo htmlspecialchars($pet['name']); ?></h3>
                    <p><?php echo htmlspecialchars($pet['breed']); ?> • <?php echo htmlspecialchars($pet['age'] ?? '1 year old'); ?></p>
                  </div>
                  <div class="pet-id"><?php echo htmlspecialchars($pet['pet_id'] ?? 'WGR-001'); ?></div>
                  <span class="pet-status"><?php echo htmlspecialchars($pet['status'] ?? 'Healthy'); ?></span>
                </a>
                <?php endforeach; ?>
                <!-- ADD PET -->
                <a href="add_pet_page.html" class="add-pet-btn">
                  <i class="fa-solid fa-plus"></i>
                  <span>Add a new pet (<?php echo max(0, 5 - (int)$pets_count); ?> slots remaining)</span>
                </a>
              </div>
            </section>

            <section class="articles-section">
              <div class="section-title-row">
                <h2>ARTICLES & GUIDES</h2>
              </div>
              <div class="articles-grid">
                <div class="article-card">
                  <div>
                    <span class="article-tag ph">PH Guide</span>
                    <h3>Protecting your dog from ticks in humid Laguna weather</h3>
                  </div>
                  <span class="article-meta">General • 3 min read</span>
                </div>
                <div class="article-card">
                  <div>
                    <span class="article-tag dogs">Dogs</span>
                    <h3>Rabies prevention tips for Aspin owners near strays</h3>
                  </div>
                  <span class="article-meta">Dogs • 4 min read</span>
                </div>
                <div class="article-card">
                  <div>
                    <span class="article-tag cats">Cats</span>
                    <h3>Keeping your cat cool during hot season in Laguna</h3>
                  </div>
                  <span class="article-meta">Cats • 2 min read</span>
                </div>
                <div class="article-card">
                  <div>
                    <span class="article-tag ph">PH Guide</span>
                    <h3>What to do when your pet encounters a stray animal</h3>
                  </div>
                  <span class="article-meta">General • 5 min read</span>
                </div>
              </div>
            </section>
          </div>

          <!-- RIGHT COLUMN -->
          <div class="content-right">
            <section class="side-panel">
              <div class="panel-header">
                <h2>Daily Pet Insights</h2>
                <a href="daily_insights_page.html" class="see-all-link">See all</a>
              </div>
              <div class="insight-list">
                <div class="insight-item">
                  <i class="fa-solid fa-star"></i>
                  <p><strong>Did you know?</strong> Cats sleep 12-16 hours a day to conserve energy.</p>
                </div>
                <div class="insight-item">
                  <i class="fa-solid fa-star"></i>
                  <p><strong>Tip for Aspins:</strong> Deworm every 3 months due to outdoor exposure.</p>
                </div>
                <div class="insight-item">
                  <i class="fa-solid fa-star"></i>
                  <p><strong>Laguna weather:</strong> High humidity can cause skin issues. Check weekly.</p>
                </div>
              </div>
            </section>

            <section class="side-panel">
              <div class="panel-header">
                <h2>Recent Health Logs</h2>
                <a href="add_log_page.html" class="see-all-link">Log new</a>
              </div>
              <div class="log-list">
                <?php foreach ($recent_logs as $log): ?>
                <div class="log-item">
                  <div class="log-icon"><i class="fa-solid <?php echo htmlspecialchars($log['icon'] ?? 'fa-bowl-food'); ?>" style="color: <?php echo htmlspecialchars($log['color'] ?? '#F09595'); ?>;"></i></div>
                  <div class="log-details">
                    <h4><?php echo htmlspecialchars($log['type'] ?? 'Log'); ?> — <?php echo htmlspecialchars($log['pet_name'] ?? ''); ?></h4>
                    <p><?php echo htmlspecialchars($log['details'] ?? ''); ?></p>
                  </div>
                  <span class="log-time"><?php echo htmlspecialchars($log['time'] ?? 'Today'); ?></span>
                </div>
                <?php endforeach; ?>
              </div>
              <a href="add_log_page.html" class="log-action-btn">+ Log health entry</a>
            </section>
          </div>
        </div>
      </main>
    </div>
  </body>
</html>
