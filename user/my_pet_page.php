<?php
/**
 * User My Pets Portal View
 * 
 * Secure portal view for managing enrolled pets. Integrates dynamic DB lists
 * into user_data.js via script injection.
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
    'pets'     => []
];

try {
    $user_id = $_SESSION['user_id'];

    // Fetch user's enrolled pets
    $stmt = $pdo->prepare("
        SELECT p.*, b.breed_name, b.pet_type 
        FROM pets p
        LEFT JOIN breeds b ON p.breed_id = b.breed_id
        WHERE p.user_id = :user_id
        ORDER BY p.pet_id DESC
    ");
    $stmt->execute(['user_id' => $user_id]);
    $db_pets = $stmt->fetchAll();

    foreach ($db_pets as $pet) {
        // Query the last log date/time for this pet
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
            'type'    => $pet['pet_type'] ?? 'Dog',
            'breed'   => $pet['breed_name'] ?? 'Unknown Breed',
            'age'     => getAgeString($pet['date_of_birth']),
            'weight'  => $pet['weight'] ? $pet['weight'] . " kg" : "-",
            'status'  => "Healthy",
            'lastLog' => $last_log_str,
            'photo'   => $pet['photo'] ? $pet['photo'] : ''
        ];
    }
} catch (PDOException $e) {
    error_log("My Pets query failure: " . $e->getMessage());
}
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Wagura | My Pets</title>

    <!-- CSS Links -->
    <link rel="stylesheet" href="../template.css" />
    <link rel="stylesheet" href="../css/my_pet_page.css" />

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
    <!-- This wrapper keeps the sidebar and the main content lined up correctly -->
    <div class="dashboard-wrapper">
      <!-- The sidebar on the left lets you jump between different parts of the app -->
      <aside class="sidebar">
        <a href="../landing_page.html"
          ><img
            src="../images/Wagura Logo 60x60.png"
            alt="Wagura"
            class="sidebar-logo"
        /></a>
        <ul class="sidebar-links">
          <li>
            <a href="dashboard_page.php"><i class="fa-solid fa-house"></i></a>
          </li>
          <li>
            <a href="my_pet_page.php" class="active"
              ><i class="fa-solid fa-user"></i
            ></a>
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
          <li>
            <a
              href="../register_page.php"
              class="temp-register-link"
              title="Back to register"
              ><i class="fa-solid fa-user-plus"></i
            ></a>
          </li>
        </ul>
      </aside>

      <!-- This is the main section where you manage your pets -->
      <main class="main-content">
        <header class="page-header">
          <div class="title-group">
            <h1>My Pets</h1>
            <p>Manage your enrolled pets</p>
          </div>
          <div class="user-profile"><?php echo htmlspecialchars($_SESSION['initials']); ?></div>
        </header>

        <!-- FILTER BAR -->
        <div class="filter-bar">
          <div class="filters-left">
            <div class="search-input-wrapper">
              <i class="fa-solid fa-magnifying-glass"></i>
              <input type="text" placeholder="Search pets..." />
            </div>
            <select class="filter-select">
              <option>All breeds</option>
            </select>
            <select class="filter-select">
              <option>All types</option>
            </select>
          </div>
          <a href="add_pet_page.php" class="enroll-btn">+ Enroll new pet</a>
        </div>

        <!-- SLOT STATUS -->
        <div class="slot-status-card">
          <!-- Dynamically loaded via my_pet_page.js -->
          <div class="slot-text">
            Loading pet slots...
          </div>
          <div class="slot-dots">
            <!-- Loaded via JS -->
          </div>
        </div>

        <!-- PETS GRID -->
        <div class="pet-count-label">0 pets enrolled</div>
        <div class="pets-grid">
          <!-- Dynamically loaded via my_pet_page.js -->
        </div>
      </main>
    </div>
    <script src="../js/user/user_data.js"></script>
    <script src="../js/user/common.js"></script>
    <script src="../js/user/my_pet_page.js"></script>
  </body>
</html>
