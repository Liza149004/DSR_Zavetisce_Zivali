<?php
session_start();

// Če je admin prijavljen in pride na glavno stran, ga odjavi
if (isset($_SESSION['admin_id'])) {
    session_unset();
    session_destroy();
    // Opcijsko: osvežimo stran, da se seja popolnoma izbriše iz brskalnika
    header("Location: index.php");
    exit();
}
// 1. POVEZAVA IN PRIDOBIVANJE PODATKOV ZA FILTRE
require 'db_connect.php';

$vrste_zivali = [];
$statusi_zivali = [];
$animal_count = 0;

try {
    // Pridobitev vrst za spustni seznam
    $stmt_vrsta = $pdo->query("SELECT ID_vrsta, imeVrste FROM Vrsta");
    $vrste_zivali = $stmt_vrsta->fetchAll();

    // Pridobitev statusov za spustni seznam
    $stmt_status = $pdo->query("SELECT ID_status, vrstaStatusa FROM Status ORDER BY vrstaStatusa");
    $statusi_zivali = $stmt_status->fetchAll();
} catch (PDOException $e) {
    error_log("Napaka pri pridobivanju filtrov: " . $e->getMessage());
}

// 2. PRIDOBIVANJE PARAMETROV IZ URL (GET)
$search_query = $_GET['search'] ?? '';
$filter_vrsta = $_GET['vrsta'] ?? '';
$filter_spol  = $_GET['spol'] ?? '';
$filter_starost_rang = $_GET['starost_rang'] ?? '';
$filter_status = $_GET['status'] ?? '';

// 3. SESTAVLJANJE DINAMIČNE SQL POIZVEDBE
$sql = "SELECT 
            Z.ID_zival, 
            Z.ime AS ime_zivali, 
            Z.starost, 
            Z.barvaKozuha,
            V.imeVrste AS vrsta, 
            S.vrstaStatusa AS status, 
            MIN(F.potDoDatoteke) AS pot_do_slike
        FROM Zival Z
        LEFT JOIN Vrsta V ON Z.TK_vrsta = V.ID_vrsta
        LEFT JOIN Status S ON Z.TK_status = S.ID_status
        LEFT JOIN Fotografija F ON Z.ID_zival = F.TK_zival
        WHERE 1=1"; 

$params = [];

// RAZŠIRJENO ISKANJE: Ime, Barva kožuha ali Ime vrste
if (!empty($search_query)) {
    $sql .= " AND (Z.ime LIKE ? OR Z.barvaKozuha LIKE ? OR V.imeVrste LIKE ?)";
    $like_query = "%$search_query%";
    $params[] = $like_query;
    $params[] = $like_query;
    $params[] = $like_query;
}

// FILTRIRANJE PO VRSTI, SPOLU IN STATUSU
if (!empty($filter_vrsta)) {
    $sql .= " AND Z.TK_vrsta = ?";
    $params[] = $filter_vrsta;
}
if (!empty($filter_spol)) {
    $sql .= " AND Z.spol = ?";
    $params[] = $filter_spol;
}
if (!empty($filter_status)) {
    $sql .= " AND Z.TK_status = ?";
    $params[] = $filter_status;
}

// LOGIKA ZA STAROSTNE RAZPONE
if (!empty($filter_starost_rang)) {
    switch ($filter_starost_rang) {
        case 'under1':
            $sql .= " AND CAST(Z.starost AS UNSIGNED) < 1";
            break;
        case '1-2':
            $sql .= " AND CAST(Z.starost AS UNSIGNED) BETWEEN 1 AND 2";
            break;
        case '2-3':
            $sql .= " AND CAST(Z.starost AS UNSIGNED) BETWEEN 2 AND 3";
            break;
        case '3-6':
            $sql .= " AND CAST(Z.starost AS UNSIGNED) BETWEEN 3 AND 6";
            break;
        case '6-10':
            $sql .= " AND CAST(Z.starost AS UNSIGNED) BETWEEN 6 AND 10";
            break;
        case '10plus':
            $sql .= " AND CAST(Z.starost AS UNSIGNED) > 10";
            break;
    }
}

$sql .= " GROUP BY Z.ID_zival, Z.ime, Z.starost, Z.barvaKozuha, V.imeVrste, S.vrstaStatusa
          ORDER BY CASE WHEN S.vrstaStatusa = 'Aktiven' THEN 0 ELSE 1 END, Z.ime ASC";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $animals = $stmt->fetchAll();
    $animal_count = count($animals);
} catch (PDOException $e) {
    $animals = [];
}
?>

<!DOCTYPE html>
<html lang="sl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ShelterCompass - Poišči Svojega Popolnega Spremljevalca</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body>

    <?php include 'header.php';?>

    <main>
        <section class="hero">
            <h1>Poišči svojega popolnega spremljevalca!</h1>
            <p class="tagline">Iščite po imenu, barvi kožuha ali vrsti živali.</p>
            
            <form method="GET" action="" class="search-form">
                <div class="search-bar">
                    <span type="submit" class="material-icons">search</span>
                    <input type="text" name="search" placeholder="Npr. ime, barva kožuha ali vrsta..." value="<?= htmlspecialchars($search_query) ?>">
                </div>
        </section>

        <section class="filters-container">
            <div class="filters-header">
                <span class="material-icons">tune</span>
                <h3>Filtri</h3>
                <?php if($search_query || $filter_vrsta || $filter_spol || $filter_starost_rang || $filter_status): ?>
                    <a href="index.php" style="font-size: 0.8rem; margin-left: 15px; color: #ff6b6b;">Počisti vse</a>
                <?php endif; ?>
            </div>
            
            <div class="filters-grid">
                <div class="filter-group">
                    <label>Vrsta</label>
                    <select name="vrsta" onchange="this.form.submit()">
                        <option value="">Vse vrste</option>
                        <?php foreach ($vrste_zivali as $v): ?>
                            <option value="<?= $v['ID_vrsta'] ?>" <?= $filter_vrsta == $v['ID_vrsta'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($v['imeVrste']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-group">
                    <label>Spol</label>
                    <select name="spol" onchange="this.form.submit()">
                        <option value="">Vsi spoli</option>
                        <option value="Samec" <?= $filter_spol == 'Samec' ? 'selected' : '' ?>>Samec</option>
                        <option value="Samička" <?= $filter_spol == 'Samička' ? 'selected' : '' ?>>Samička</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label>Starost</label>
                    <select name="starost_rang" onchange="this.form.submit()">
                        <option value="">Vse starosti</option>
                        <option value="under1" <?= $filter_starost_rang == 'under1' ? 'selected' : '' ?>>Pod 1 leto</option>
                        <option value="1-2" <?= $filter_starost_rang == '1-2' ? 'selected' : '' ?>>1 - 2 leti</option>
                        <option value="2-3" <?= $filter_starost_rang == '2-3' ? 'selected' : '' ?>>2 - 3 leta</option>
                        <option value="3-6" <?= $filter_starost_rang == '3-6' ? 'selected' : '' ?>>3 - 6 let</option>
                        <option value="6-10" <?= $filter_starost_rang == '6-10' ? 'selected' : '' ?>>6 - 10 let</option>
                        <option value="10plus" <?= $filter_starost_rang == '10plus' ? 'selected' : '' ?>>Nad 10 let</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label>Status</label>
                    <select name="status" onchange="this.form.submit()">
                        <option value="">Vsi statusi</option>
                        <?php foreach ($statusi_zivali as $st): ?>
                            <option value="<?= $st['ID_status'] ?>" <?= $filter_status == $st['ID_status'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($st['vrstaStatusa']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            </form>

            <p class="showing-info">Prikazujem <?= $animal_count ?> živali</p>
        </section>
            
        <section class="animal-gallery">
            <?php if ($animal_count > 0): ?>
                <?php foreach ($animals as $animal): 
                    $status = $animal['status'] ?? 'Neznano';
                    $status_lower = mb_strtolower(trim($status), 'UTF-8');
                    
                    $status_class = 'in-care';
                    $icon_emoji = 'ℹ️';

                    if ($status_lower === 'aktiven') {
                        $status_class = 'available'; $icon_emoji = '🐾';
                    } elseif ($status_lower === 'posvojen') {
                        $status_class = 'adopted'; $icon_emoji = '🏡';
                    } elseif ($status_lower === 'rezerviran') {
                        $status_class = 'reserved'; $icon_emoji = '🔒';
                    } elseif ($status_lower === 'neaktiven') {
                        $status_class = 'inactive'; $icon_emoji = '🚫';
                    }
                ?>
                    <a href="profil_zivali.php?id=<?= $animal['ID_zival'] ?>" class="animal-card-link">
                        <div class="animal-card">
                            <div class="image-placeholder" style="background-image: url('<?= !empty($animal['pot_do_slike']) ? htmlspecialchars($animal['pot_do_slike']) : 'images/placeholder.jpg' ?>')">
                                <span class="status-badge <?= $status_class ?>"><?= htmlspecialchars($status) ?></span>
                            </div>
                            <div class="card-details">
                                <h4><?= htmlspecialchars($animal['ime_zivali']) ?></h4>
                                <p><?= htmlspecialchars($animal['vrsta']) ?> • <?= htmlspecialchars($animal['barvaKozuha']) ?></p>
                                <p class="meta">Starost: <?= htmlspecialchars($animal['starost']) ?></p>
                                <span class="animal-icon"><?= $icon_emoji ?></span>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-results">
                    <p>Žal ni zadetkov, ki bi ustrezali vašim kriterijem.</p>
                </div>
            <?php endif; ?>
        </section>
    </main>
            
    <?php include 'footer.php';?>

    <script>
        // DARK MODE LOGIKA
        const toggleButton = document.getElementById('darkModeToggle');
        const body = document.body;
        
        if (localStorage.getItem('darkMode') === 'enabled') {
            body.classList.add('dark-mode');
            if(toggleButton) toggleButton.textContent = 'light_mode';
        }

        function toggleDarkMode() {
            body.classList.toggle('dark-mode');
            const mode = body.classList.contains('dark-mode') ? 'enabled' : 'disabled';
            localStorage.setItem('darkMode', mode);
            if(toggleButton) toggleButton.textContent = (mode === 'enabled') ? 'light_mode' : 'dark_mode';
        }

        if (toggleButton) {
            toggleButton.addEventListener('click', toggleDarkMode);
        }
    </script>
</body>
</html>