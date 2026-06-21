<?php
/**
 * Edit Pet API Controller
 * 
 * Receives FormData from edit_pet_page.js, resolves breed references,
 * parses age strings into dates, handles file upload for photo, and updates the database.
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
$pet_code = trim($_POST['pet_code'] ?? '');
$type = trim($_POST['type'] ?? 'Dog');
$name = trim($_POST['name'] ?? '');
$breed_name = trim($_POST['breed'] ?? '');
$age = trim($_POST['age'] ?? '');
$weight = trim(str_replace(' kg', '', $_POST['weight'] ?? ''));
$sex = trim($_POST['sex'] ?? 'Select sex');
$color = trim($_POST['color'] ?? '');
$notes = trim($_POST['notes'] ?? '');

// 1. Validate fields
if (empty($pet_code)) {
    echo json_encode(['success' => false, 'error' => 'Pet code is required.']);
    exit();
}
if (empty($name) || empty($breed_name) || empty($age)) {
    echo json_encode(['success' => false, 'error' => 'Name, breed, and age are required fields.']);
    exit();
}

// Ensure weight is numeric or null
$weight_numeric = is_numeric($weight) ? floatval($weight) : null;
$sex_enum = in_array($sex, ['Male', 'Female']) ? $sex : null;

try {
    // Check if pet exists and belongs to user
    $stmt = $pdo->prepare("SELECT photo FROM pets WHERE pet_code = :pet_code AND user_id = :user_id");
    $stmt->execute(['pet_code' => $pet_code, 'user_id' => $user_id]);
    $result = $stmt->fetch();
    
    if ($result === false) {
        echo json_encode(['success' => false, 'error' => 'Pet not found or unauthorized.']);
        exit();
    }
    
    $existing_photo = $result['photo'];

    // 2. Resolve breed_id
    $stmt = $pdo->prepare("SELECT breed_id FROM breeds WHERE LOWER(breed_name) = LOWER(:name) AND pet_type = :type");
    $stmt->execute(['name' => $breed_name, 'type' => $type]);
    $breed_id = $stmt->fetchColumn();

    if (!$breed_id) {
        $stmt = $pdo->prepare("INSERT INTO breeds (breed_name, pet_type) VALUES (:name, :type)");
        $stmt->execute(['name' => $breed_name, 'type' => $type]);
        $breed_id = $pdo->lastInsertId();
    }

    // 3. Parse age to date_of_birth
    $years = 0;
    if (preg_match('/(\d+)\s*(yr|year|yrs|years)/i', $age, $matches)) {
        $years = intval($matches[1]);
    }
    $dob = date('Y-m-d', strtotime("-$years years"));

    // 4. Handle photo upload
    $photo_path = $existing_photo;
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
            // Delete old photo if it exists
            if (!empty($existing_photo) && file_exists(__DIR__ . '/../' . ltrim($existing_photo, './'))) {
                @unlink(__DIR__ . '/../' . ltrim($existing_photo, './'));
            }
            $photo_path = '../uploads/pets/' . $filename;
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to save uploaded photo.']);
            exit();
        }
    }

    // 5. Update pet
    $stmt = $pdo->prepare("
        UPDATE pets 
        SET breed_id = :breed_id,
            name = :name,
            date_of_birth = :dob,
            weight = :weight,
            sex = :sex,
            color = :color,
            medical_notes = :notes,
            photo = :photo
        WHERE pet_code = :pet_code AND user_id = :user_id
    ");
    
    $stmt->execute([
        'breed_id'  => $breed_id,
        'name'      => $name,
        'dob'       => $dob,
        'weight'    => $weight_numeric,
        'sex'       => $sex_enum,
        'color'     => empty($color) ? null : $color,
        'notes'     => empty($notes) ? null : $notes,
        'photo'     => empty($photo_path) ? null : $photo_path,
        'pet_code'  => $pet_code,
        'user_id'   => $user_id
    ]);

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
