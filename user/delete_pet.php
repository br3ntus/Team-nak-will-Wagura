<?php
/**
 * Delete Pet API Controller
 * 
 * Securely deletes a pet matching the given pet_code belonging to the logged-in user.
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
$pet_code = trim($_GET['pet_code'] ?? '');

if (empty($pet_code)) {
    echo json_encode(['success' => false, 'error' => 'Pet code is required.']);
    exit();
}

try {
    // 1. Verify owner of the pet before deleting
    $stmt = $pdo->prepare("SELECT pet_id, photo FROM pets WHERE pet_code = :pet_code AND user_id = :user_id");
    $stmt->execute(['pet_code' => $pet_code, 'user_id' => $user_id]);
    $pet = $stmt->fetch();

    if (!$pet) {
        echo json_encode(['success' => false, 'error' => 'Pet not found or unauthorized deletion.']);
        exit();
    }

    $pet_id = $pet['pet_id'];
    $photo_path = $pet['photo'];

    // 2. Delete child health logs first (to avoid FK constraints if any, or let cascading handle it)
    $stmt = $pdo->prepare("DELETE FROM health_logs WHERE pet_id = :pet_id");
    $stmt->execute(['pet_id' => $pet_id]);

    // 3. Delete pet
    $stmt = $pdo->prepare("DELETE FROM pets WHERE pet_id = :pet_id");
    $stmt->execute(['pet_id' => $pet_id]);

    // Delete photo file from disk if exists
    if (!empty($photo_path) && file_exists(__DIR__ . '/../' . ltrim($photo_path, './'))) {
        @unlink(__DIR__ . '/../' . ltrim($photo_path, './'));
    }

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
