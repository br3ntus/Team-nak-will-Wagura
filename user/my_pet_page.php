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
$pets_count = 0;
$max_pets = 5;

try {
    $stmt = $conn->prepare("SELECT * FROM pets WHERE user_id = :user_id ORDER BY created_at DESC");
    if ($stmt instanceof PDOStatement && $stmt->execute(['user_id' => $user_id])) {
        $user_pets = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $pets_count = count($user_pets);
    } else {
        $user_pets = [];
        $pets_count = 0;
    }
} catch (PDOException $e) {
    // Fallback data with all necessary keys
    $user_pets = [
        ['pet_id' => 'WGR-001', 'name' => 'Coco', 'type' => 'Dog', 'breed' => 'Aspin', 'age' => '3 years old', 'status' => 'Healthy'],
        ['pet_id' => 'WGR-002', 'name' => 'Mochi', 'type' => 'Cat', 'breed' => 'Persian', 'age' => '2 years old', 'status' => 'Healthy'],
        ['pet_id' => 'WGR-003', 'name' => 'Bruno', 'type' => 'Dog', 'breed' => 'Shih Tzu', 'age' => '1 year old', 'status' => 'Healthy'],
    ];
    $pets_count = count($user_pets);
}

$remaining_slots = $max_pets - $pets_count;
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Wagura | My Pets</title>
    <link rel="stylesheet" href="../template.css" />
    <link rel="stylesheet" href="../css/my_pet_page.css" />
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
                    <h1>My Pets</h1>
                    <p>Manage your enrolled pets</p>
                </div>
                <div class="user-profile"><?php echo substr($first_name, 0, 2); ?></div>
            </header>

            <div class="filter-bar">
                <div class="filters-left">
                    <div class="search-input-wrapper">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <input type="text" id="petSearch" placeholder="Search pets..." />
                    </div>
                    <select id="typeFilter" class="filter-select">
                        <option value="">All types</option>
                        <option value="Dog">Dogs</option>
                        <option value="Cat">Cats</option>
                    </select>
                    <select id="statusFilter" class="filter-select">
                        <option value="">All status</option>
                        <option value="Healthy">Healthy</option>
                        <option value="Under Care">Under Care</option>
                    </select>
                </div>
                <a href="add_pet_page.php" class="enroll-btn">+ Enroll new pet</a>
            </div>

            <div class="slot-status-card">
                <div class="slot-text">
                    Pet slots: <strong><?php echo $pets_count; ?> of <?php echo $max_pets; ?></strong> used. You can enroll <strong><?php echo $remaining_slots; ?> more pet<?php echo $remaining_slots !== 1 ? 's' : ''; ?></strong>.
                </div>
                <div class="slot-dots">
                    <?php for ($i = 0; $i < $max_pets; $i++): ?>
                        <div class="dot <?php echo $i < $pets_count ? 'active' : ''; ?>"></div>
                    <?php endfor; ?>
                </div>
            </div>

            <div class="pet-count-label"><span id="petCount"><?php echo $pets_count; ?></span> pets enrolled</div>
            <div class="pets-grid" id="petsGrid">
                <?php foreach ($user_pets as $pet): ?>
                    <div class="pet-card" data-name="<?php echo strtolower($pet['name']); ?>" data-type="<?php echo htmlspecialchars($pet['type']); ?>" data-status="<?php echo htmlspecialchars($pet['status']); ?>">
                        <div class="pet-header">
                            <i class="fa-solid fa-<?php echo (isset($pet['type']) && $pet['type'] === 'Cat') ? 'cat' : 'dog'; ?>"></i>
                            <span class="status-badge <?php echo strtolower(htmlspecialchars($pet['status'])); ?>"><?php echo htmlspecialchars($pet['status']); ?></span>
                        </div>
                        <div class="pet-body">
                            <h3><?php echo htmlspecialchars($pet['name']); ?></h3>
                            <div class="pet-details">
                                <span class="detail-item"><strong><?php echo htmlspecialchars($pet['breed']); ?></strong></span>
                                <span class="detail-item"><?php echo htmlspecialchars($pet['age']); ?></span>
                                <span class="detail-item"><?php echo htmlspecialchars($pet['type']); ?></span>
                            </div>
                        </div>
                        <div class="pet-footer">
                            <small>ID: <?php echo htmlspecialchars($pet['pet_id']); ?></small>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php if ($pets_count === 0): ?>
                <div style="text-align: center; padding: 40px; color: var(--text-secondary);">
                    <i class="fa-solid fa-paw" style="font-size: 48px; margin-bottom: 20px; opacity: 0.5;"></i>
                    <p>No pets enrolled yet. <a href="add_pet_page.php" style="color: var(--primary);">Enroll your first pet</a></p>
                </div>
            <?php endif; ?>
        </main>
    </div>

    <script>
        const petSearch = document.getElementById('petSearch');
        const typeFilter = document.getElementById('typeFilter');
        const statusFilter = document.getElementById('statusFilter');
        const petsGrid = document.getElementById('petsGrid');
        const petCount = document.getElementById('petCount');
        const petCards = petsGrid.querySelectorAll('.pet-card');

        function filterPets() {
            const searchTerm = petSearch.value.toLowerCase();
            const selectedType = typeFilter.value;
            const selectedStatus = statusFilter.value;
            let visibleCount = 0;

            petCards.forEach(card => {
                const name = card.dataset.name;
                const type = card.dataset.type;
                const status = card.dataset.status;

                const matchesSearch = name.includes(searchTerm);
                const matchesType = !selectedType || type === selectedType;
                const matchesStatus = !selectedStatus || status === selectedStatus;

                if (matchesSearch && matchesType && matchesStatus) {
                    card.style.display = '';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });

            petCount.textContent = visibleCount;
        }

        petSearch.addEventListener('input', filterPets);
        typeFilter.addEventListener('change', filterPets);
        statusFilter.addEventListener('change', filterPets);
    </script>
</body>
</html>
