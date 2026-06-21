<?php
/**
 * Add Pet API Controller
 * 
 * Receives JSON request from add_pet_page.js, resolves breed references,
 * parses age strings into dates, enforces user pet limits, and saves to database.
 */
session_start();

// Security check
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized access.']);
    exit();
}

header('Content-Type: application/json');

// Include connection
require_once __DIR__ . '/../db_connection.php';

$user_id = $_SESSION['user_id'];

// Resolve input source: support both FormData and raw JSON
if (!empty($_POST)) {
    $type = trim($_POST['type'] ?? 'Dog');
    $name = trim($_POST['name'] ?? '');
    $breed_name = trim($_POST['breed'] ?? '');
    $age = trim($_POST['age'] ?? '');
    $weight = trim(str_replace(' kg', '', $_POST['weight'] ?? ''));
    $sex = trim($_POST['sex'] ?? 'Select sex');
    $color = trim($_POST['color'] ?? '');
    $notes = trim($_POST['notes'] ?? '');
} else {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        echo json_encode(['success' => false, 'error' => 'Invalid input data.']);
        exit();
    }
    $type = trim($input['type'] ?? 'Dog');
    $name = trim($input['name'] ?? '');
    $breed_name = trim($input['breed'] ?? '');
    $age = trim($input['age'] ?? '');
    $weight = trim(str_replace(' kg', '', $input['weight'] ?? ''));
    $sex = trim($input['sex'] ?? 'Select sex');
    $color = trim($input['color'] ?? '');
    $notes = trim($input['notes'] ?? '');
}

// 1. Validate fields
if (empty($name) || empty($breed_name) || empty($age)) {
    echo json_encode(['success' => false, 'error' => 'Name, breed, and age are required fields.']);
    exit();
}

// Ensure weight is numeric or null
$weight_numeric = is_numeric($weight) ? floatval($weight) : null;
$sex_enum = in_array($sex, ['Male', 'Female']) ? $sex : null;

try {
    // 2. Validate pet capacity (max 5)
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM pets WHERE user_id = ?");
    $stmt->execute([$user_id]);
    if ($stmt->fetchColumn() >= 5) {
        echo json_encode(['success' => false, 'error' => 'You have reached the maximum enrollment limit of 5 pets.']);
        exit();
    }

    // 3. Resolve breed_id
    // Query if breed exists
    $stmt = $pdo->prepare("SELECT breed_id FROM breeds WHERE LOWER(breed_name) = LOWER(:name) AND pet_type = :type");
    $stmt->execute(['name' => $breed_name, 'type' => $type]);
    $breed_id = $stmt->fetchColumn();

    // If breed does not exist, insert it!
    if (!$breed_id) {
        $stmt = $pdo->prepare("INSERT INTO breeds (breed_name, pet_type) VALUES (:name, :type)");
        $stmt->execute(['name' => $breed_name, 'type' => $type]);
        $breed_id = $pdo->lastInsertId();
    }

    // 4. Generate next pet code (e.g. WG-DOG-0004)
    $prefix = (strtolower($type) === 'cat') ? 'WG-CAT' : 'WG-DOG';
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM pets WHERE pet_code LIKE :prefix");
    $stmt->execute(['prefix' => $prefix . '-%']);
    $count = $stmt->fetchColumn();
    $pet_code = $prefix . '-' . str_pad($count + 1, 4, '0', STR_PAD_LEFT);

    // 5. Parse age to date_of_birth
    // E.g. "2 yrs" -> 2 years ago
    $years = 0;
    if (preg_match('/(\d+)\s*(yr|year|yrs|years)/i', $age, $matches)) {
        $years = intval($matches[1]);
    }
    $dob = date('Y-m-d', strtotime("-$years years"));

    // 5.5. Handle photo upload
    $photo_path = null;
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['photo'];
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mime_type, $allowed_types)) {
            echo json_encode(['success' => false, 'error' => 'Invalid file type. Only JPG, PNG, GIF, and WebP are allowed.']);
            exit();
        }

        if ($file['size'] > 2 * 1024 * 1024) {
            echo json_encode(['success' => false, 'error' => 'File size exceeds 2MB limit.']);
            exit();
        }

        $target_dir = __DIR__ . '/../uploads/pets/';
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        if (empty($extension)) {
            $extensions_map = [
                'image/jpeg' => 'jpg',
                'image/jpg'  => 'jpg',
                'image/png'  => 'png',
                'image/gif'  => 'gif',
                'image/webp' => 'webp',
            ];
            $extension = $extensions_map[$mime_type] ?? 'jpg';
        }

        $filename = $pet_code . '_' . time() . '.' . $extension;
        $target_path = $target_dir . $filename;

        if (move_uploaded_file($file['tmp_name'], $target_path)) {
            $photo_path = '../uploads/pets/' . $filename;
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to save uploaded photo.']);
            exit();
        }
    }

    // 6. Insert new pet
    $stmt = $pdo->prepare("
        INSERT INTO pets (user_id, breed_id, pet_code, name, date_of_birth, weight, sex, color, medical_notes, photo, created_at)
        VALUES (:user_id, :breed_id, :pet_code, :name, :dob, :weight, :sex, :color, :notes, :photo, NOW())
    ");
    $stmt->execute([
        'user_id'   => $user_id,
        'breed_id'  => $breed_id,
        'pet_code'  => $pet_code,
        'name'      => $name,
        'dob'       => $dob,
        'weight'    => $weight_numeric,
        'sex'       => $sex_enum,
        'color'     => empty($color) ? null : $color,
        'notes'     => empty($notes) ? null : $notes,
        'photo'     => $photo_path
    ]);

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
