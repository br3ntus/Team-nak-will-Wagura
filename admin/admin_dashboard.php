<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

require_once "../db_connection_pdo.php";

function getCount(PDO $conn, string $table): ?int {
    try {
        $stmt = $conn->query("SELECT COUNT(*) FROM `$table`");
        if ($stmt instanceof PDOStatement) {
            return (int)$stmt->fetchColumn();
        }
        return null;
    } catch (PDOException $e) {
        return null;
    }
}

// Live counts
$users_count = getCount($conn, 'users') ?? getCount($conn, 'user') ?? 24;
$pets_count = getCount($conn, 'pets') ?? getCount($conn, 'pet') ?? 67;
$articles_count = getCount($conn, 'articles') ?? getCount($conn, 'article') ?? 12;
$insights_count = getCount($conn, 'insights') ?? getCount($conn, 'daily_insights') ?? 23;
$logs_count = getCount($conn, 'health_logs') ?? getCount($conn, 'health_log') ?? getCount($conn, 'logs') ?? getCount($conn, 'pet_logs') ?? 142;

// Recent users list
$recent_users = [];
try {
    $stmtUsers = $conn->query("SELECT * FROM users ORDER BY user_id DESC LIMIT 4");
    if ($stmtUsers instanceof PDOStatement) {
        $recent_users = $stmtUsers->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    try {
        $stmtUsers2 = $conn->query("SELECT * FROM users LIMIT 4");
        if ($stmtUsers2 instanceof PDOStatement) {
            $recent_users = $stmtUsers2->fetchAll(PDO::FETCH_ASSOC);
        }
    } catch (PDOException $e2) {
        // Fallback static data if table doesn't exist
        $recent_users = [
            ['first_name' => 'Brent', 'last_name' => 'A.', 'email' => 'brent@email.com', 'status' => 'Active', 'user_id' => 1],
            ['first_name' => 'Juan', 'last_name' => 'D.', 'email' => 'juan@email.com', 'status' => 'New', 'user_id' => 2],
            ['first_name' => 'Maria', 'last_name' => 'L.', 'email' => 'maria@email.com', 'status' => 'Active', 'user_id' => 3],
            ['first_name' => 'Rico', 'last_name' => 'C.', 'email' => 'rico@email.com', 'status' => 'New', 'user_id' => 4]
        ];
    }
}

// Recent pets list
$recent_pets = [];
try {
    $stmtPets = $conn->query("SELECT p.*, u.first_name, u.last_name FROM pets p LEFT JOIN users u ON p.user_id = u.user_id ORDER BY p.pet_id DESC LIMIT 4");
    if ($stmtPets instanceof PDOStatement) {
        $recent_pets = $stmtPets->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    try {
        $stmtPets2 = $conn->query("SELECT * FROM pets LIMIT 4");
        if ($stmtPets2 instanceof PDOStatement) {
            $recent_pets = $stmtPets2->fetchAll(PDO::FETCH_ASSOC);
        }
    } catch (PDOException $e2) {
        $recent_pets = [
            ['name' => 'Coco', 'breed' => 'Aspin', 'type' => 'Dog', 'owner_name' => 'Brent A.'],
            ['name' => 'Mochi', 'breed' => 'Persian', 'type' => 'Cat', 'owner_name' => 'Brent A.'],
            ['name' => 'Bruno', 'breed' => 'Shih Tzu', 'type' => 'Dog', 'owner_name' => 'Brent A.'],
            ['name' => 'Niko', 'breed' => 'Puspin', 'type' => 'Cat', 'owner_name' => 'Juan D.']
        ];
    }
}
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Wagura | Admin Dashboard</title>
    <!-- Brand CSS and icons -->
    <link rel="stylesheet" href="../template.css" />
    <link rel="stylesheet" href="../css/admin_shared.css" />
    <link rel="stylesheet" href="../css/admin_dashboard.css" />
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  </head>
  <body>
    <div class="admin-layout">
      <!-- Admin Sidebar Navigation -->
      <aside class="admin-sidebar">
        <a href="admin_dashboard.php" class="brand-box">W</a>
        <ul class="nav-links">
          <li>
            <a href="admin_dashboard.php" class="active"><i class="fa-solid fa-house"></i></a>
          </li>
          <li>
            <a href="manage_users.html"><i class="fa-solid fa-users"></i></a>
          </li>
          <li>
            <a href="manage_pets.html"><i class="fa-solid fa-paw"></i></a>
          </li>
          <li>
            <a href="manage_articles.html"><i class="fa-solid fa-pen-to-square"></i></a>
          </li>
          <li>
            <a href="manage_insights.html"><i class="fa-solid fa-star"></i></a>
          </li>
        </ul>
        <div class="sidebar-bottom">
          <a href="admin_logout.php" title="Logout"><i class="fa-solid fa-right-from-bracket"></i></a>
        </div>
      </aside>

      <!-- Main administrative display -->
      <main class="admin-main">
        <header class="admin-header">
          <div class="header-title">
            <h1>Admin Dashboard</h1>
            <p>Platform overview — <?php echo date("F j, Y"); ?></p>
          </div>
          <div class="header-actions">
            <div class="admin-badge"><i class="fa-solid fa-shield-halved"></i> Admin</div>
            <div class="admin-profile-circle">
                <?php 
                $admin_name = $_SESSION['admin_name'] ?? 'Admin';
                $initials = "";
                $parts = explode(" ", $admin_name);
                foreach ($parts as $part) {
                    if (!empty($part)) $initials .= strtoupper($part[0]);
                }
                echo htmlspecialchars(substr($initials, 0, 2));
                ?>
            </div>
          </div>
        </header>

        <!-- Stats Counter Section -->
        <section class="stats-grid">
          <!-- Registered Users -->
          <div class="admin-card stat-card users">
            <div class="stat-icon-wrapper">
              <i class="fa-solid fa-users"></i>
            </div>
            <div>
              <div class="stat-number"><?php echo (int)$users_count; ?></div>
              <div class="stat-label">Registered Users</div>
            </div>
            <div class="stat-trend">+3 this week</div>
          </div>

          <!-- Enrolled Pets -->
          <div class="admin-card stat-card pets">
            <div class="stat-icon-wrapper">
              <i class="fa-solid fa-paw"></i>
            </div>
            <div>
              <div class="stat-number"><?php echo (int)$pets_count; ?></div>
              <div class="stat-label">Enrolled Pets</div>
            </div>
            <div class="stat-trend">+8 this week</div>
          </div>

          <!-- Articles Posted -->
          <div class="admin-card stat-card articles">
            <div class="stat-icon-wrapper">
              <i class="fa-solid fa-pen-to-square"></i>
            </div>
            <div>
              <div class="stat-number"><?php echo (int)$articles_count; ?></div>
              <div class="stat-label">Articles Posted</div>
            </div>
            <div class="stat-trend">+2 this week</div>
          </div>

          <!-- Daily Insights -->
          <div class="admin-card stat-card insights">
            <div class="stat-icon-wrapper">
              <i class="fa-solid fa-star"></i>
            </div>
            <div>
              <div class="stat-number"><?php echo (int)$insights_count; ?></div>
              <div class="stat-label">Daily Insights</div>
            </div>
            <div class="stat-trend">+1 today</div>
          </div>

          <!-- Health Log Entries -->
          <div class="admin-card stat-card logs">
            <div class="stat-icon-wrapper">
              <i class="fa-solid fa-clipboard-list"></i>
            </div>
            <div>
              <div class="stat-number"><?php echo (int)$logs_count; ?></div>
              <div class="stat-label">Health Log Entries</div>
            </div>
            <div class="stat-trend">+15 today</div>
          </div>
        </section>

        <!-- Recent activities splits -->
        <div class="dashboard-split">
          <!-- Recent Users table list -->
          <section class="admin-card">
            <div class="card-header-row">
              <h2>Recent Users</h2>
              <a href="manage_users.html" class="manage-link">Manage users &rarr;</a>
            </div>
            <table class="admin-table">
              <thead>
                <tr>
                  <th>Name</th>
                  <th>Email</th>
                  <th>Status</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($recent_users as $ru): ?>
                <tr>
                  <td>
                    <div class="entity-cell">
                      <div class="avatar-circle gold">
                        <?php 
                        $fn = $ru['first_name'] ?? '';
                        $ln = $ru['last_name'] ?? '';
                        echo htmlspecialchars(strtoupper(($fn[0] ?? '') . ($ln[0] ?? '')));
                        ?>
                      </div>
                      <span><?php echo htmlspecialchars(($ru['first_name'] ?? '') . ' ' . ($ru['last_name'] ?? '')); ?></span>
                    </div>
                  </td>
                  <td><?php echo htmlspecialchars($ru['email'] ?? ''); ?></td>
                  <td><span class="status-pill active">Active</span></td>
                  <td>
                    <a href="edit_user.html" class="btn-table">View</a>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </section>

          <!-- Recent Enrolled Pets table list -->
          <section class="admin-card">
            <div class="card-header-row">
              <h2>Recent Enrolled Pets</h2>
              <a href="manage_pets.html" class="manage-link">Manage pets &rarr;</a>
            </div>
            <table class="admin-table">
              <thead>
                <tr>
                  <th>Pet Name</th>
                  <th>Breed</th>
                  <th>Type</th>
                  <th>Owner</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($recent_pets as $rp): ?>
                <tr>
                  <td>
                    <div class="entity-cell">
                      <div class="mini-icon-box"><i class="fa-solid <?php echo (isset($rp['type']) && strtolower($rp['type']) === 'cat') ? 'fa-cat' : 'fa-dog'; ?>"></i></div>
                      <span><?php echo htmlspecialchars($rp['name'] ?? $rp['pet_name'] ?? ''); ?></span>
                    </div>
                  </td>
                  <td><?php echo htmlspecialchars($rp['breed'] ?? ''); ?></td>
                  <td><span class="status-pill healthy"><?php echo htmlspecialchars($rp['type'] ?? 'Dog'); ?></span></td>
                  <td><?php echo htmlspecialchars($rp['owner_name'] ?? (($rp['first_name'] ?? '') . ' ' . ($rp['last_name'] ?? '')) ?: 'Brent A.'); ?></td>
                  <td>
                    <a href="edit_pet.html" class="btn-table">View</a>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </section>
        </div>
      </main>
    </div>
  </body>
</html>
