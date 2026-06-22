<?php
/**
 * User Add Pet Page View
 * 
 * Secure portal view for enrolling new pets. Integrates dynamic DB lists
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

    // Fetch user's enrolled pets (only need count/list for capacity check)
    $stmt = $pdo->prepare("SELECT pet_code FROM pets WHERE user_id = :user_id");
    $stmt->execute(['user_id' => $user_id]);
    $db_pets = $stmt->fetchAll();

    foreach ($db_pets as $pet) {
        $backendData['pets'][] = [
            'id' => $pet['pet_code']
        ];
    }
} catch (PDOException $e) {
    error_log("Add Pet count query failure: " . $e->getMessage());
}
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Wagura | Enroll New Pet</title>

    <!-- CSS Links -->
    <link rel="stylesheet" href="../template.css" />
    <link rel="stylesheet" href="../css/add_pet_page.css" />

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
    <div class="dashboard-wrapper">
      <!-- SIDEBAR -->
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
        </ul>
      </aside>

      <!-- MAIN CONTENT -->
      <main class="main-content">
        <header class="page-header">
          <div class="title-group">
            <a href="my_pet_page.php" class="back-link">← Back to My Pets</a>
            <h1>Enroll New Pet</h1>
            <p>Fill in your pet's details below</p>
          </div>
          <div
            style="
              width: 35px;
              height: 35px;
              background: #3d4f5e;
              color: #fff;
              border-radius: 50%;
              display: flex;
              align-items: center;
              justify-content: center;
              font-size: 12px;
              font-weight: 500;
            ">
            <?php echo htmlspecialchars($_SESSION['initials']); ?>
          </div>
        </header>

        <div class="enroll-grid">
          <!-- LEFT PANEL: PHOTO & ID -->
          <div class="upload-panel">
            <div class="photo-upload-box" id="photo-upload-box" style="cursor:pointer; position: relative; overflow: hidden;">
              <i class="fa-solid fa-image" id="photo-icon"></i>
              <span id="photo-label">Click to upload pet photo</span>
              <img id="photo-preview" style="display:none;width:100%;height:100%;object-fit:cover;border-radius:inherit;" />
              <!-- Hidden file input -->
              <input type="file" id="pet-photo-input" accept="image/jpeg,image/png" style="position:absolute;top:0;left:0;width:100%;height:100%;opacity:0;cursor:pointer;" />
            </div>
            <p class="upload-info">
              JPG or PNG only. Max 2MB. Square photos work best.
            </p>

            <div class="id-box">
              <label>PET ID</label>
              <span>WG-XXX-XXXX</span>
              <small>Auto-generated on save</small>
            </div>
          </div>

          <!-- RIGHT PANEL: FORM -->
          <div class="form-panel">
            <span class="section-label">BASIC INFORMATION</span>

            <div class="form-group" style="margin-bottom: 2rem">
              <label>Pet type <span>*</span></label>
              <div class="type-selector">
                <div class="type-option active">
                  <i class="fa-solid fa-dog"></i> Dog
                </div>
                <div class="type-option">
                  <i class="fa-solid fa-cat"></i> Cat
                </div>
              </div>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label>Pet name <span>*</span></label>
                <input type="text" placeholder="e.g. Coco" />
              </div>
              <div class="form-group">
                <label>Breed <span>*</span></label>
                <input type="text" placeholder="e.g. Aspin, Shih Tzu" />
              </div>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label>Age <span>*</span></label>
                <input type="text" placeholder="e.g. 2 years" />
              </div>
              <div class="form-group">
                <label>Weight (kg)</label>
                <input type="text" placeholder="e.g. 8.5" />
              </div>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label>Sex</label>
                <select>
                  <option>Select sex</option>
                  <option>Male</option>
                  <option>Female</option>
                </select>
              </div>
              <div class="form-group">
                <label>Color / Markings</label>
                <input type="text" placeholder="e.g. Brown with white spots" />
              </div>
            </div>

            <span class="section-label" style="margin-top: 2rem"
              >MEDICAL NOTES</span
            >
            <div class="form-group">
              <label>Medical history or special notes</label>
              <textarea
                placeholder="e.g. Vaccinated in March 2025, allergic to chicken..."></textarea>
              <small
                style="
                  color: var(--text-faint);
                  font-size: 11px;
                  margin-top: 8px;
                "
                >Optional. You can update this anytime from the health
                log.</small
              >
            </div>

            <div class="form-footer">
              <button class="cancel-btn">Cancel</button>
              <button class="save-btn">Save pet</button>
            </div>
          </div>
        </div>
      </main>
    </div>
    <script src="../js/user/user_data.js"></script>
    <script src="../js/user/common.js"></script>
    <script src="../js/user/add_pet_page.js"></script>
  </body>
</html>
