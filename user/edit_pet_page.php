<?php
/**
 * Edit Pet Page
 * 
 * Loads existing pet data from DB and prefills the form.
 * Submits via fetch to edit_pet_logic.php.
 */
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login_page.php");
    exit();
}

require_once __DIR__ . '/../db_connection.php';

$user_id = $_SESSION['user_id'];
$pet_code = trim($_GET['pet_code'] ?? '');
$pet = null;

if (empty($pet_code)) {
    header("Location: my_pet_page.php");
    exit();
}

try {
    // Fetch the pet making sure the owner matches the session
    $stmt = $pdo->prepare("
        SELECT p.*, b.breed_name, b.pet_type
        FROM pets p
        LEFT JOIN breeds b ON p.breed_id = b.breed_id
        WHERE p.pet_code = :pet_code AND p.user_id = :user_id
    ");
    $stmt->execute(['pet_code' => $pet_code, 'user_id' => $user_id]);
    $pet = $stmt->fetch();
} catch (PDOException $e) {
    error_log("Edit Pet fetch failure: " . $e->getMessage());
}

// If pet not found in DB, fallback to empty array so frontend script can try localStorage
if (!$pet) {
    $pet = [
        'pet_code'     => $pet_code,
        'name'         => '',
        'pet_type'     => 'Dog',
        'breed_name'   => '',
        'date_of_birth'=> '',
        'weight'       => '',
        'sex'          => '',
        'color'        => '',
        'medical_notes'=> '',
        'photo'        => ''
    ];
}

// Helper to calculate age string from DOB for prefilling
function dob_to_age($dob) {
    if (!$dob) return '';
    $birthdate = new DateTime($dob);
    $today = new DateTime('today');
    $age = $birthdate->diff($today);
    if ($age->y > 0) return $age->y . ($age->y == 1 ? ' yr' : ' yrs');
    if ($age->m > 0) return $age->m . ($age->m == 1 ? ' mo' : ' mos');
    return $age->d . ' days';
}
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Wagura | Edit <?php echo htmlspecialchars($pet['name']); ?></title>

    <link rel="stylesheet" href="../template.css" />
    <link rel="stylesheet" href="../css/add_pet_page.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    
    <!-- Pass pet data to the frontend script -->
    <script>
      window.WaguraBackendData = { profile: { name: "<?php echo $_SESSION['first_name']; ?>", initials: "<?php echo $_SESSION['initials']; ?>", petCapacity: 5 }, pets: [] };
      window.WaguraEditPet = <?php echo json_encode([
        'pet_code'     => $pet['pet_code'],
        'name'         => $pet['name'],
        'type'         => $pet['pet_type'],
        'breed'        => $pet['breed_name'],
        'age'          => dob_to_age($pet['date_of_birth']),
        'weight'       => $pet['weight'] ?? '',
        'sex'          => $pet['sex'] ?? '',
        'color'        => $pet['color'] ?? '',
        'medical_notes'=> $pet['medical_notes'] ?? '',
        'photo'        => $pet['photo'] ?? ''
      ]); ?>;
    </script>
  </head>
  <body>
    <div class="dashboard-wrapper">
      <!-- SIDEBAR -->
      <aside class="sidebar">
        <a href="../landing_page.html"><img src="../images/Wagura Logo 60x60.png" alt="Wagura" class="sidebar-logo" /></a>
        <ul class="sidebar-links">
          <li><a href="dashboard_page.php"><i class="fa-solid fa-house"></i></a></li>
          <li><a href="my_pet_page.php" class="active"><i class="fa-solid fa-user"></i></a></li>
          <li><a href="articles_page.html"><i class="fa-solid fa-table-cells-large"></i></a></li>
          <li><a href="daily_insights_page.html"><i class="fa-solid fa-star"></i></a></li>
          <li><a href="health_log_page.html"><i class="fa-solid fa-clipboard-list"></i></a></li>
        </ul>
      </aside>

      <!-- MAIN CONTENT -->
      <main class="main-content">
        <header class="page-header">
          <div class="title-group">
            <a href="my_pet_page.php" class="back-link">← Back to My Pets</a>
            <h1>Edit <?php echo htmlspecialchars($pet['name']); ?></h1>
            <p>Update your pet's details below</p>
          </div>
          <div style="width:35px;height:35px;background:#3d4f5e;color:#fff;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:500;">
            <?php echo htmlspecialchars($_SESSION['initials']); ?>
          </div>
        </header>

        <div class="enroll-grid">
          <!-- LEFT PANEL: PHOTO & ID -->
          <div class="upload-panel">
            <div class="photo-upload-box" id="photo-upload-box" style="cursor:pointer; position: relative; overflow: hidden;">
              <?php if (!empty($pet['photo'])): ?>
                <img id="photo-preview" src="<?php echo htmlspecialchars($pet['photo']); ?>" alt="Pet photo" style="width:100%;height:100%;object-fit:cover;border-radius:inherit;" />
              <?php else: ?>
                <i class="fa-solid fa-image" id="photo-icon"></i>
                <span id="photo-label">Click to upload pet photo</span>
                <img id="photo-preview" style="display:none;width:100%;height:100%;object-fit:cover;border-radius:inherit;" />
              <?php endif; ?>
              <!-- Hidden file input -->
              <input type="file" id="pet-photo-input" accept="image/jpeg,image/png" style="position:absolute;top:0;left:0;width:100%;height:100%;opacity:0;cursor:pointer;" />
            </div>
            <p class="upload-info">JPG or PNG only. Max 2MB. Square photos work best.</p>
            <div class="id-box">
              <label>PET ID</label>
              <span><?php echo htmlspecialchars($pet['pet_code']); ?></span>
              <small>Cannot be changed</small>
            </div>
          </div>

          <!-- RIGHT PANEL: FORM -->
          <div class="form-panel">
            <span class="section-label">BASIC INFORMATION</span>

            <div class="form-group" style="margin-bottom: 2rem">
              <label>Pet type <span>*</span></label>
              <div class="type-selector">
                <div class="type-option <?php echo strtolower($pet['pet_type']) === 'dog' ? 'active' : ''; ?>">
                  <i class="fa-solid fa-dog"></i> Dog
                </div>
                <div class="type-option <?php echo strtolower($pet['pet_type']) === 'cat' ? 'active' : ''; ?>">
                  <i class="fa-solid fa-cat"></i> Cat
                </div>
              </div>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label>Pet name <span>*</span></label>
                <input type="text" id="edit-name" placeholder="e.g. Coco" value="<?php echo htmlspecialchars($pet['name']); ?>" />
              </div>
              <div class="form-group">
                <label>Breed <span>*</span></label>
                <input type="text" id="edit-breed" placeholder="e.g. Aspin, Shih Tzu" value="<?php echo htmlspecialchars($pet['breed_name']); ?>" />
              </div>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label>Age <span>*</span></label>
                <input type="text" id="edit-age" placeholder="e.g. 2 years" value="<?php echo htmlspecialchars(dob_to_age($pet['date_of_birth'])); ?>" />
              </div>
              <div class="form-group">
                <label>Weight (kg)</label>
                <input type="text" id="edit-weight" placeholder="e.g. 8.5" value="<?php echo htmlspecialchars($pet['weight'] ?? ''); ?>" />
              </div>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label>Sex</label>
                <select id="edit-sex">
                  <option value="Select sex" <?php echo empty($pet['sex']) ? 'selected' : ''; ?>>Select sex</option>
                  <option value="Male" <?php echo $pet['sex'] === 'Male' ? 'selected' : ''; ?>>Male</option>
                  <option value="Female" <?php echo $pet['sex'] === 'Female' ? 'selected' : ''; ?>>Female</option>
                </select>
              </div>
              <div class="form-group">
                <label>Color / Markings</label>
                <input type="text" id="edit-color" placeholder="e.g. Brown with white spots" value="<?php echo htmlspecialchars($pet['color'] ?? ''); ?>" />
              </div>
            </div>

            <span class="section-label" style="margin-top: 2rem">MEDICAL NOTES</span>
            <div class="form-group">
              <label>Medical history or special notes</label>
              <textarea id="edit-notes" placeholder="e.g. Vaccinated in March 2025..."><?php echo htmlspecialchars($pet['medical_notes'] ?? ''); ?></textarea>
            </div>

            <div class="form-footer">
              <button class="cancel-btn" onclick="window.location.href='my_pet_page.php'">Cancel</button>
              <button class="save-btn" id="edit-save-btn">Save changes</button>
            </div>
          </div>
        </div>
      </main>
    </div>

    <script src="../js/user/user_data.js"></script>
    <script src="../js/user/common.js"></script>
    <script src="../js/user/edit_pet_page.js"></script>
  </body>
</html>
