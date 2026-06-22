<?php
/**
 * User Dashboard Portal
 * 
 * Fetches user profile, pets, recent logs, PH guides, and insights from DB,
 * converts to JSON, and injects into frontend mock storage override.
 */
session_start();

// Security check: Redirect to login if user session is not active
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login_page.php");
    exit();
}

// Include connection
require_once __DIR__ . '/../db_connection.php';

// Helper to calculate pet age from date of birth
function getAgeString($dob) {
    if (!$dob) return 'Unknown age';
    try {
        $birthdate = new DateTime($dob);
        $today = new DateTime('today');
        $age = $birthdate->diff($today);
        
        if ($age->y > 0) {
            return $age->y . ($age->y == 1 ? ' yr' : ' yrs');
        } elseif ($age->m > 0) {
            return $age->m . ($age->m == 1 ? ' mo' : ' mos');
        } else {
            return $age->d . ($age->d == 1 ? ' day' : ' days');
        }
    } catch (Exception $e) {
        return 'Unknown age';
    }
}

// Prepare backend data payload
$backendData = [
    'profile' => [
        'name'        => $_SESSION['first_name'],
        'initials'    => $_SESSION['initials'],
        'petCapacity' => 5
    ],
    'pets'     => [],
    'logs'     => [],
    'articles' => [],
    'insights' => []
];

try {
    $user_id = $_SESSION['user_id'];

    // 1. Fetch user's enrolled pets
    $stmt = $pdo->prepare("
        SELECT p.*, b.breed_name, b.pet_type 
        FROM pets p
        JOIN breeds b ON p.breed_id = b.breed_id
        WHERE p.user_id = :user_id
        ORDER BY p.pet_id DESC
    ");
    $stmt->execute(['user_id' => $user_id]);
    $db_pets = $stmt->fetchAll();

    foreach ($db_pets as $pet) {
        // Query the last log date/time for this pet to display as status
        $log_stmt = $pdo->prepare("
            SELECT log_date 
            FROM health_logs 
            WHERE pet_id = :pet_id 
            ORDER BY log_date DESC, log_time DESC 
            LIMIT 1
        ");
        $log_stmt->execute(['pet_id' => $pet['pet_id']]);
        $last_log_date = $log_stmt->fetchColumn();
        
        $last_log_str = "No logs yet";
        if ($last_log_date) {
            $log_time = strtotime($last_log_date);
            if (date('Y-m-d', $log_time) === date('Y-m-d')) {
                $last_log_str = "Today";
            } elseif (date('Y-m-d', $log_time) === date('Y-m-d', strtotime('-1 day'))) {
                $last_log_str = "Yesterday";
            } else {
                $last_log_str = date('M d', $log_time);
            }
        }

        $backendData['pets'][] = [
            'id'      => $pet['pet_code'],
            'name'    => $pet['name'],
            'type'    => $pet['pet_type'],
            'breed'   => $pet['breed_name'],
            'age'     => getAgeString($pet['date_of_birth']),
            'weight'  => $pet['weight'] ? $pet['weight'] . " kg" : "-",
            'status'  => "Healthy", // Default status, customizable
            'lastLog' => $last_log_str
        ];
    }

    // 2. Fetch health logs for all user's pets
    $stmt = $pdo->prepare("
        SELECT h.*, p.name AS pet_name, p.pet_code,
               f.food_description,
               s.symptoms_description,
               v.clinic_name, v.doctor_notes,
               w.weight_kg
        FROM health_logs h
        JOIN pets p ON h.pet_id = p.pet_id
        LEFT JOIN feeding_logs f ON h.log_id = f.log_id
        LEFT JOIN symptom_logs s ON h.log_id = s.log_id
        LEFT JOIN vet_logs v ON h.log_id = v.log_id
        LEFT JOIN weight_logs w ON h.log_id = w.log_id
        WHERE p.user_id = :user_id
        ORDER BY h.log_date DESC, h.log_time DESC
    ");
    $stmt->execute(['user_id' => $user_id]);
    $db_logs = $stmt->fetchAll();

    foreach ($db_logs as $log) {
        // Map details based on subclass log table
        $details = "";
        $categoryIcon = "fa-bowl-food";
        $type = $log['log_type'];
        
        switch ($type) {
            case 'Feeding':
                $details = $log['food_description'] ?? '';
                $categoryIcon = "fa-bowl-food";
                break;
            case 'Weight':
                $details = ($log['weight_kg'] ? $log['weight_kg'] . " kg" : "0") . " recorded";
                $categoryIcon = "fa-weight-scale";
                break;
            case 'Vet Visit':
                $details = ($log['clinic_name'] ?? '') . ($log['doctor_notes'] ? " — " . $log['doctor_notes'] : "");
                $categoryIcon = "fa-hospital";
                break;
            case 'Symptoms':
                $details = $log['symptoms_description'] ?? '';
                $categoryIcon = "fa-face-frown";
                break;
        }

        // Format dates
        $date_formatted = date('M d, Y', strtotime($log['log_date']));
        $time_formatted = $log['log_time'] ? date('g:i A', strtotime($log['log_time'])) : '';

        $backendData['logs'][] = [
            'id'           => "LOG-" . str_pad($log['log_id'], 4, '0', STR_PAD_LEFT),
            'petId'        => $log['pet_code'],
            'petName'      => $log['pet_name'],
            'type'         => $type,
            'title'        => $type . " — " . $log['pet_name'],
            'details'      => $details,
            'date'         => $date_formatted,
            'time'         => $time_formatted,
            'categoryIcon' => $categoryIcon
        ];
    }

    // 3. Fetch global articles/guides
    $stmt = $pdo->query("
        SELECT a.*, c.category_name, b.breed_name
        FROM articles a
        JOIN categories c ON a.category_id = c.category_id
        LEFT JOIN breeds b ON a.breed_id = b.breed_id
        WHERE a.status = 'Published'
        ORDER BY a.created_at DESC
    ");
    $db_articles = $stmt->fetchAll();

    foreach ($db_articles as $art) {
        $backendData['articles'][] = [
            'id'       => "ART-" . str_pad($art['article_id'], 4, '0', STR_PAD_LEFT),
            'title'    => $art['title'],
            'category' => $art['category_name'],
            'breed'    => $art['breed_name'] ?? 'All breeds',
            'summary'  => $art['content'],
            'readTime' => $art['read_time'] ?? '3 min',
            'posted'   => date('M d, Y', strtotime($art['created_at'])),
            'icon'     => ($art['icon'] === 'fa-temp' ? 'fa-temperature-high' : ($art['icon'] ?? 'fa-book-open'))
        ];
    }

    // 4. Fetch daily insights
    $stmt = $pdo->query("
        SELECT d.*, c.category_name
        FROM daily_insights d
        JOIN categories c ON d.category_id = c.category_id
        ORDER BY d.post_date DESC
    ");
    $db_insights = $stmt->fetchAll();

    foreach ($db_insights as $ins) {
        $is_today = (date('Y-m-d', strtotime($ins['post_date'])) === date('Y-m-d'));
        $backendData['insights'][] = [
            'id'       => "INS-" . str_pad($ins['insight_id'], 4, '0', STR_PAD_LEFT),
            'text'     => $ins['insight_text'],
            'category' => $ins['category_name'],
            'posted'   => date('M d, Y', strtotime($ins['post_date'])),
            'status'   => $is_today ? 'today' : 'published'
        ];
    }

} catch (PDOException $e) {
    error_log("Dashboard query failure: " . $e->getMessage());
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
    
    <!-- Inject Real Database Data for Frontend JS Interception -->
    <script>
      window.WaguraBackendData = <?php echo json_encode($backendData); ?>;
    </script>
  </head>
  <body>
    <!-- This is the main wrapper for the whole dashboard layout -->
    <div class="dashboard-wrapper">
      <!-- The sidebar on the left handles all the main navigation links -->
      <aside class="sidebar">
        <a href="../landing_page.html"
          ><img
            src="../images/Wagura Logo 60x60.png"
            alt="Wagura"
            class="sidebar-logo"
        /></a>
        <ul class="sidebar-links">
          <li>
            <a href="dashboard_page.php" class="active"
              ><i class="fa-solid fa-house"></i
            ></a>
          </li>
          <li>
            <a href="my_pet_page.html"><i class="fa-solid fa-user"></i></a>
          </li>
          <li>
            <a href="articles_page.html"
              ><i class="fa-solid fa-table-cells-large"></i
            ></a>
          </li>
          <li>
            <a href="daily_insights_page.html"
              ><i class="fa-solid fa-star"></i
            ></a>
          </li>
          <li>
            <a href="health_log_page.html"
              ><i class="fa-solid fa-clipboard-list"></i
            ></a>
          </li>
        </ul>
      </aside>

      <!-- This is where all the actual pet info and updates live -->
      <main class="main-content">
        <header class="top-header">
          <div class="date-text"><?php echo date('F d, Y'); ?></div>
          <div class="header-right">
            <div class="search-box">
              <i class="fa-solid fa-magnifying-glass"></i>
              <input type="text" placeholder="Search articles..." />
            </div>
            <div class="user-profile"><?php echo htmlspecialchars($_SESSION['initials']); ?></div>
            <!-- Logout link for convenience -->
            <a href="../logout.php" style="color: #ef4444; margin-left: 15px; text-decoration: none; font-size: 14px; font-weight: 500;">
              <i class="fa-solid fa-right-from-bracket"></i> Logout
            </a>
          </div>
        </header>

        <div class="dashboard-grid">
          <!-- LEFT COLUMN -->
          <div class="content-left">
            <section class="greeting-card">
              <h5>Good morning, <?php echo htmlspecialchars($_SESSION['first_name']); ?></h5>
              <h1>Keep your pets healthy with Wagura</h1>
              <div class="enrolled-badge">
                <i class="fa-solid fa-star"></i> <?php echo count($backendData['pets']); ?> pets enrolled
              </div>
            </section>

            <section class="my-pets-section">
              <div class="section-title-row">
                <h2>MY PETS</h2>
              </div>
              <div class="pet-list">
                <!-- Dynamically loaded via dashboard_page.js -->
              </div>
            </section>

            <section class="articles-section">
              <div class="section-title-row">
                <h2>ARTICLES & GUIDES</h2>
              </div>
              <div class="articles-grid">
                <!-- Dynamically loaded via dashboard_page.js -->
              </div>
            </section>
          </div>

          <!-- RIGHT COLUMN -->
          <div class="content-right">
            <section class="side-panel">
              <div class="panel-header">
                <h2>Daily Pet Insights</h2>
                <a href="daily_insights_page.html" class="see-all-link"
                  >See all</a
                >
              </div>
              <div class="insight-list">
                <!-- Dynamically loaded via dashboard_page.js -->
              </div>
            </section>

            <section class="side-panel">
              <div class="panel-header">
                <h2>Recent Health Logs</h2>
                <a href="add_log_page.html" class="see-all-link">Log new</a>
              </div>
              <div class="log-list">
                <!-- Dynamically loaded via dashboard_page.js -->
              </div>
              <a href="add_log_page.html" class="log-action-btn"
                >+ Log health entry</a
              >
            </section>
          </div>
        </div>
      </main>
    </div>
    <script src="../js/user/user_data.js"></script>
    <script src="../js/user/common.js"></script>
    <script src="../js/user/dashboard_page.js"></script>
  </body>
</html>
