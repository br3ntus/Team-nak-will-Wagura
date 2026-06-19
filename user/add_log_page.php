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

// Get user's pets
$user_pets = [];
try {
    $stmt = $conn->prepare("SELECT * FROM pets WHERE user_id = :user_id ORDER BY name");
    if ($stmt instanceof PDOStatement) {
        if ($stmt->execute(['user_id' => $user_id])) {
            $user_pets = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $user_pets = [
                ['pet_id' => 'WGR-001', 'name' => 'Coco', 'type' => 'Dog'],
                ['pet_id' => 'WGR-002', 'name' => 'Mochi', 'type' => 'Cat'],
                ['pet_id' => 'WGR-003', 'name' => 'Bruno', 'type' => 'Dog'],
            ];
        }
    } else {
        $user_pets = [
            ['pet_id' => 'WGR-001', 'name' => 'Coco', 'type' => 'Dog'],
            ['pet_id' => 'WGR-002', 'name' => 'Mochi', 'type' => 'Cat'],
            ['pet_id' => 'WGR-003', 'name' => 'Bruno', 'type' => 'Dog'],
        ];
    }
} catch (PDOException $e) {
    $user_pets = [
        ['pet_id' => 'WGR-001', 'name' => 'Coco', 'type' => 'Dog'],
        ['pet_id' => 'WGR-002', 'name' => 'Mochi', 'type' => 'Cat'],
        ['pet_id' => 'WGR-003', 'name' => 'Bruno', 'type' => 'Dog'],
    ];
}

$message = '';
$message_type = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pet_id = isset($_POST['pet_id']) ? htmlspecialchars($_POST['pet_id']) : '';
    $log_type = isset($_POST['log_type']) ? htmlspecialchars($_POST['log_type']) : '';
    $log_date = isset($_POST['log_date']) ? htmlspecialchars($_POST['log_date']) : date('Y-m-d');
    $log_time = isset($_POST['log_time']) ? htmlspecialchars($_POST['log_time']) : '12:00';
    $description = isset($_POST['description']) ? htmlspecialchars($_POST['description']) : '';
    $notes = isset($_POST['notes']) ? htmlspecialchars($_POST['notes']) : '';

    // Validate
    if (!$pet_id || !$log_type || !$description) {
        $message = 'Please fill in all required fields';
        $message_type = 'error';
    } else {
        try {
            $datetime = $log_date . ' ' . $log_time . ':00';
            
            $stmt = $conn->prepare("
                INSERT INTO health_logs (pet_id, log_type, description, notes, created_at)
                VALUES (:pet_id, :log_type, :description, :notes, :created_at)
            ");
            
            $stmt->execute([
                ':pet_id' => $pet_id,
                ':log_type' => $log_type,
                ':description' => $description,
                ':notes' => $notes,
                ':created_at' => $datetime
            ]);

            $message = 'Health log added successfully!';
            $message_type = 'success';
            
            // Redirect after 2 seconds
            header('Refresh: 2; URL=health_log_page.php');
        } catch (PDOException $e) {
            $message = 'Error adding log. Please try again.';
            $message_type = 'error';
        }
    }
}

$today = date('Y-m-d');
$current_time = date('H:i');
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Wagura | Add New Log</title>
    <link rel="stylesheet" href="../template.css" />
    <link rel="stylesheet" href="../css/add_log_page.css" />
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
                <li><a href="daily_insights_page.php"><i class="fa-solid fa-star"></i></a></li>
                <li><a href="health_log_page.php" class="active"><i class="fa-solid fa-clipboard-list"></i></a></li>
            </ul>
            <div class="sidebar-bottom" style="margin-top: auto; padding-top: 20px;">
                <a href="../logout.php" title="Logout" style="color: var(--text-muted); font-size: 18px; display: flex; align-items: center; justify-content: center;"><i class="fa-solid fa-right-from-bracket"></i></a>
            </div>
        </aside>

        <main class="main-content">
            <header class="page-header">
                <div class="title-group">
                    <a href="health_log_page.php" class="back-link">← Back to Health Logs</a>
                    <h1>Add New Log</h1>
                    <p>Record a health entry for your pet</p>
                </div>
            </header>

            <?php if ($message): ?>
                <div style="margin: 20px 0; padding: 15px; border-radius: 8px; background: <?php echo $message_type === 'success' ? '#d4edda' : '#f8d7da'; ?>; color: <?php echo $message_type === 'success' ? '#155724' : '#721c24'; ?>; border: 1px solid <?php echo $message_type === 'success' ? '#c3e6cb' : '#f5c6cb'; ?>;">
                    <i class="fa-solid fa-<?php echo $message_type === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="log-form-container">
                <span class="section-label">SELECT PET</span>
                <div class="pet-selector">
                    <?php foreach ($user_pets as $index => $pet): ?>
                        <div class="pet-option <?php echo $index === 0 ? 'active' : ''; ?>" onclick="selectPet(this, '<?php echo htmlspecialchars($pet['pet_id']); ?>')">
                            <i class="fa-solid fa-<?php echo $pet['type'] === 'Dog' ? 'dog' : 'cat'; ?>"></i> <?php echo htmlspecialchars($pet['name']); ?>
                        </div>
                    <?php endforeach; ?>
                </div>
                <input type="hidden" name="pet_id" id="petId" value="<?php echo !empty($user_pets) ? $user_pets[0]['pet_id'] : ''; ?>" required />

                <span class="section-label">LOG TYPE</span>
                <div class="type-grid">
                    <div class="type-btn active" onclick="selectLogType(this, 'Feeding')">
                        <i class="fa-solid fa-bowl-food" style="color: #F09595;"></i>
                        <span>Feeding</span>
                    </div>
                    <div class="type-btn" onclick="selectLogType(this, 'Weight')">
                        <i class="fa-solid fa-weight-scale" style="color: #85B7EB;"></i>
                        <span>Weight</span>
                    </div>
                    <div class="type-btn" onclick="selectLogType(this, 'Vet visit')">
                        <i class="fa-solid fa-hospital" style="color: #FAC775;"></i>
                        <span>Vet visit</span>
                    </div>
                    <div class="type-btn" onclick="selectLogType(this, 'Symptoms')">
                        <i class="fa-solid fa-face-frown" style="color: #FFB800;"></i>
                        <span>Symptoms</span>
                    </div>
                </div>
                <input type="hidden" name="log_type" id="logType" value="Feeding" required />

                <span class="section-label">LOG DETAILS</span>
                <div class="form-row">
                    <div class="form-group">
                        <label>Date <span>*</span></label>
                        <input type="date" name="log_date" value="<?php echo $today; ?>" required />
                    </div>
                    <div class="form-group">
                        <label>Time</label>
                        <input type="time" name="log_time" value="<?php echo $current_time; ?>" />
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 1.5rem;">
                    <label id="descLabel">Food description <span>*</span></label>
                    <input type="text" name="description" id="description" placeholder="e.g. 200g dry food + water refilled" required />
                    <small style="color: var(--text-faint); font-size: 11px; margin-top: 4px;">This field changes based on the log type selected above.</small>
                </div>

                <div class="form-group">
                    <label>Additional notes</label>
                    <textarea name="notes" placeholder="Any additional details..." rows="3" style="background: var(--bg-panel); border: 1px solid var(--border-subtle); color: #fff; padding: 10px; border-radius: 6px; resize: vertical;"></textarea>
                </div>

                <div style="display: flex; gap: 10px; margin-top: 2rem;">
                    <button type="submit" style="background: var(--primary); color: white; border: none; padding: 12px 24px; border-radius: 6px; cursor: pointer; font-weight: 500;">
                        <i class="fa-solid fa-check"></i> Save Log
                    </button>
                    <a href="health_log_page.php" style="padding: 12px 24px; border: 1px solid var(--border-subtle); border-radius: 6px; text-decoration: none; color: var(--text-secondary); display: flex; align-items: center;">
                        Cancel
                    </a>
                </div>
            </form>
        </main>
    </div>

    <script>
        function selectPet(element, petId) {
            document.querySelectorAll('.pet-option').forEach(el => el.classList.remove('active'));
            element.classList.add('active');
            document.getElementById('petId').value = petId;
        }

        function selectLogType(element, type) {
            document.querySelectorAll('.type-btn').forEach(el => el.classList.remove('active'));
            element.classList.add('active');
            document.getElementById('logType').value = type;

            // Update label and placeholder based on type
            const labels = {
                'Feeding': 'Food description',
                'Weight': 'Weight (kg)',
                'Vet visit': 'Vet visit description',
                'Symptoms': 'Symptom description'
            };
            
            const placeholders = {
                'Feeding': 'e.g. 200g dry food + water refilled',
                'Weight': 'e.g. 5.5 kg',
                'Vet visit': 'e.g. Vaccination, check-up, etc.',
                'Symptoms': 'e.g. Coughing, vomiting, etc.'
            };

            document.getElementById('descLabel').innerHTML = labels[type] + ' <span>*</span>';
            document.getElementById('description').placeholder = placeholders[type];
        }
    </script>
</body>
</html>
