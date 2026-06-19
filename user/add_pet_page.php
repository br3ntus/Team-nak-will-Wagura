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

$message = '';
$message_type = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pet_type = isset($_POST['pet_type']) ? htmlspecialchars($_POST['pet_type']) : '';
    $pet_name = isset($_POST['pet_name']) ? htmlspecialchars($_POST['pet_name']) : '';
    $breed = isset($_POST['breed']) ? htmlspecialchars($_POST['breed']) : '';
    $age = isset($_POST['age']) ? htmlspecialchars($_POST['age']) : '';
    $weight = isset($_POST['weight']) ? floatval($_POST['weight']) : null;
    $color = isset($_POST['color']) ? htmlspecialchars($_POST['color']) : '';
    $status = isset($_POST['status']) ? htmlspecialchars($_POST['status']) : 'Healthy';

    // Validate required fields
    if (!$pet_type || !$pet_name || !$breed || !$age) {
        $message = 'Please fill in all required fields';
        $message_type = 'error';
    } else {
        // Generate pet ID
        $pet_id = 'WGR-' . strtoupper(substr($pet_type, 0, 3)) . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

        try {
            $stmt = $conn->prepare("
                INSERT INTO pets (pet_id, user_id, name, type, breed, age, weight, color, status, created_at)
                VALUES (:pet_id, :user_id, :name, :type, :breed, :age, :weight, :color, :status, NOW())
            ");
            
            $stmt->execute([
                ':pet_id' => $pet_id,
                ':user_id' => $user_id,
                ':name' => $pet_name,
                ':type' => $pet_type,
                ':breed' => $breed,
                ':age' => $age,
                ':weight' => $weight,
                ':color' => $color,
                ':status' => $status
            ]);

            $message = 'Pet enrolled successfully! ID: ' . $pet_id;
            $message_type = 'success';
            
            // Redirect after 2 seconds
            header('Refresh: 2; URL=my_pet_page.php');
        } catch (PDOException $e) {
            $message = 'Error enrolling pet. Please try again.';
            $message_type = 'error';
        }
    }
}
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Wagura | Enroll New Pet</title>
    <link rel="stylesheet" href="../template.css" />
    <link rel="stylesheet" href="../css/add_pet_page.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
</head>
<body>
    <div class="dashboard-wrapper">
        <aside class="sidebar">
            <a href="../landing_page.html"><img src="../images/Wagura Logo 60x60.png" alt="Wagura" class="sidebar-logo"></a>
            <ul class="sidebar-links">
                <li><a href="dashboard_page.php"><i class="fa-solid fa-house"></i></a></li>
                <li><a href="my_pet_page.php" class="active"><i class="fa-solid fa-user"></i></a></li>
                <li><a href="articles_page.php"><i class="fa-solid fa-table-cells-large"></i></a></li>
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
                    <a href="my_pet_page.php" class="back-link">← Back to My Pets</a>
                    <h1>Enroll New Pet</h1>
                    <p>Fill in your pet's details below</p>
                </div>
                <div style="width: 35px; height: 35px; background: #3d4f5e; color: #fff; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 500;"><?php echo substr($first_name, 0, 2); ?></div>
            </header>

            <?php if ($message): ?>
                <div style="margin: 20px 0; padding: 15px; border-radius: 8px; background: <?php echo $message_type === 'success' ? '#d4edda' : '#f8d7da'; ?>; color: <?php echo $message_type === 'success' ? '#155724' : '#721c24'; ?>; border: 1px solid <?php echo $message_type === 'success' ? '#c3e6cb' : '#f5c6cb'; ?>;">
                    <i class="fa-solid fa-<?php echo $message_type === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="enroll-grid">
                <div class="upload-panel">
                    <div class="photo-upload-box">
                        <i class="fa-solid fa-image"></i>
                        <span>Click to upload pet photo</span>
                    </div>
                    <p class="upload-info">JPG or PNG only. Max 2MB. Square photos work best.</p>
                    
                    <div class="id-box">
                        <label>PET ID</label>
                        <span id="petIdDisplay">WG-AUTO</span>
                        <small>Auto-generated on save</small>
                    </div>
                </div>

                <div class="form-panel">
                    <span class="section-label">BASIC INFORMATION</span>
                    
                    <div class="form-group" style="margin-bottom: 2rem;">
                        <label>Pet type <span>*</span></label>
                        <div class="type-selector">
                            <div class="type-option active" data-type="Dog" onclick="selectType(this)">
                                <i class="fa-solid fa-dog"></i> Dog
                            </div>
                            <div class="type-option" data-type="Cat" onclick="selectType(this)">
                                <i class="fa-solid fa-cat"></i> Cat
                            </div>
                        </div>
                        <input type="hidden" name="pet_type" id="petType" value="Dog" required />
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Pet name <span>*</span></label>
                            <input type="text" name="pet_name" placeholder="e.g. Coco" required />
                        </div>
                        <div class="form-group">
                            <label>Breed <span>*</span></label>
                            <input type="text" name="breed" placeholder="e.g. Aspin, Shih Tzu" required />
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Age <span>*</span></label>
                            <input type="text" name="age" placeholder="e.g. 2 years" required />
                        </div>
                        <div class="form-group">
                            <label>Weight (kg)</label>
                            <input type="number" step="0.1" name="weight" placeholder="e.g. 8.5" />
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Color/Markings</label>
                            <input type="text" name="color" placeholder="e.g. Brown and white" />
                        </div>
                        <div class="form-group">
                            <label>Status</label>
                            <select name="status" style="background: var(--bg-panel); border: 1px solid var(--border-subtle); color: #fff; padding: 10px; border-radius: 6px;">
                                <option value="Healthy">Healthy</option>
                                <option value="Under Care">Under Care</option>
                                <option value="Observation">Observation</option>
                            </select>
                        </div>
                    </div>

                    <span class="section-label" style="margin-top: 2rem;">ADDITIONAL DETAILS</span>

                    <div class="form-group">
                        <label>Microchip ID (optional)</label>
                        <input type="text" placeholder="e.g. 123456789" />
                    </div>

                    <div class="form-group">
                        <label>Notes</label>
                        <textarea placeholder="Any special notes about your pet..." rows="3" style="background: var(--bg-panel); border: 1px solid var(--border-subtle); color: #fff; padding: 10px; border-radius: 6px; resize: vertical;"></textarea>
                    </div>

                    <div style="display: flex; gap: 10px; margin-top: 2rem;">
                        <button type="submit" style="background: var(--primary); color: white; border: none; padding: 12px 24px; border-radius: 6px; cursor: pointer; font-weight: 500;">
                            <i class="fa-solid fa-check"></i> Save Pet
                        </button>
                        <a href="my_pet_page.php" style="padding: 12px 24px; border: 1px solid var(--border-subtle); border-radius: 6px; text-decoration: none; color: var(--text-secondary); display: flex; align-items: center;">
                            Cancel
                        </a>
                    </div>
                </div>
            </form>
        </main>
    </div>

    <script>
        function selectType(element) {
            document.querySelectorAll('.type-option').forEach(el => el.classList.remove('active'));
            element.classList.add('active');
            document.getElementById('petType').value = element.dataset.type;
        }
    </script>
</body>
</html>
