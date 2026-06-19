<?php
require_once 'db_connection_pdo.php';

echo "=== Inserting Dummy Data ===\n\n";

$sqls = [
    "INSERT IGNORE INTO categories (category_id, category_name) VALUES (1, 'Dogs'), (2, 'Cats'), (3, 'General'), (4, 'Health'), (5, 'Nutrition');",
    
    "INSERT IGNORE INTO articles (article_id, admin_id, category_id, title, breed, content, icon, read_time, status, created_at) VALUES
    (1, 1001, 1, 'Protecting your dog from ticks in humid Laguna weather', 'Aspin', 'Ticks are common...', 'bug', '3 min read', 'Published', NOW()),
    (2, 1001, 1, 'Rabies prevention tips for Aspin owners near strays', 'Aspin', 'Aspins in the Philippines...', 'shield', '4 min read', 'Published', NOW()),
    (3, 1001, 2, 'Keeping your cat cool during hot season in Laguna', 'Persian', 'Cats are sensitive to heat...', 'temp', '2 min read', 'Published', NOW()),
    (4, 1001, 3, 'What to do when your pet encounters a stray animal', 'Mixed', 'Stray encounters...', 'alert', '5 min read', 'Published', NOW());",
    
    "INSERT IGNORE INTO daily_insights (insight_id, admin_id, category_id, icon, insight_text, post_date, created_at) VALUES
    (1, 1001, 1, 'star', 'Did you know? Cats sleep 12-16 hours a day to conserve energy.', CURDATE(), NOW()),
    (2, 1001, 1, 'star', 'Tip for Aspins: Deworm every 3 months due to outdoor exposure.', CURDATE(), NOW()),
    (3, 1001, 3, 'star', 'Laguna weather: High humidity can cause skin issues.', CURDATE(), NOW()),
    (4, 1001, 2, 'star', 'Cat fact: A cat\\'s hearing is five times better than humans.', CURDATE(), NOW()),
    (5, 1001, 1, 'star', 'Dog care: Exercise your pet at least 30 minutes daily.', CURDATE(), NOW());",
    
    "INSERT IGNORE INTO pets (pet_id, user_id, pet_code, name, pet_type, breed, age, weight, sex, color, medical_notes, photo, created_at) VALUES
    (1, 1, 'WGR-001', 'Coco', 'Dog', 'Aspin', '3 years', 18.5, 'Female', 'Brown and white', 'Healthy', 'coco.jpg', NOW()),
    (2, 1, 'WGR-002', 'Mochi', 'Cat', 'Persian', '2 years', 4.2, 'Female', 'White', 'Healthy', 'mochi.jpg', NOW()),
    (3, 1, 'WGR-003', 'Bruno', 'Dog', 'Shih Tzu', '1 year', 5.8, 'Male', 'Black and tan', 'Puppy', 'bruno.jpg', NOW());",
    
    "INSERT IGNORE INTO health_logs (log_id, pet_id, log_type, log_date, log_time, notes, created_at) VALUES
    (1, 1, 'Feeding', '2026-06-19', '08:00:00', 'Morning meal - 1 cup of dog food', NOW()),
    (2, 2, 'Weight', '2026-06-18', '10:30:00', 'Weight recorded: 4.2 kg', NOW()),
    (3, 3, 'Vet Visit', '2026-06-15', '14:00:00', 'Annual checkup - All good', NOW()),
    (4, 1, 'Symptoms', '2026-06-17', '16:45:00', 'Minor itching observed', NOW()),
    (5, 2, 'Feeding', '2026-06-19', '09:00:00', 'Breakfast - cat food', NOW()),
    (6, 1, 'Feeding', '2026-06-19', '18:00:00', 'Evening meal - 1 cup', NOW()),
    (7, 3, 'Weight', '2026-06-19', '11:00:00', 'Weight recorded: 5.8 kg', NOW());",
];

try {
    foreach ($sqls as $i => $sql) {
        $conn->exec($sql);
        echo "✓ Query " . ($i + 1) . " executed\n";
    }
    echo "\n✓ All dummy data inserted successfully!\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
