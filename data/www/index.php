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

        <?php
        // Vključi PDO povezavo na bazo podatkov
        require 'db_connect.php';
        // Pridobitev podatkov za dinamično polnjenje filtrov (Vrsta)
        $vrste_zivali = [];
        $animal_count = 0;
        try {
            // Poizvedba za tipe ostane enaka
            $vrsta_sql = "SELECT ID_vrsta, imeVrste FROM Vrsta ORDER BY imeVrste";
            $stmt_vrsta = $pdo->prepare($vrsta_sql);
            $stmt_vrsta->execute();
            $vrste_zivali = $stmt_vrsta->fetchAll();
        } catch (PDOException $e) {
            // Obvladovanje napake
        }
        ?>
    <?php include 'header.php';?>

    <main>
        <section class="hero">
            <h1>Poišči svojega popolnega spremljevalca</h1>
            <p class="tagline">Vsaka žival si zasluži ljubeč dom. Prebrskajte po naših živalih in pomagajte spremeniti njihova življenja.</p>
            <div class="search-bar">
                <span class="material-icons">search</span>
                <input type="text" placeholder="Išči po imenu ali pasmi...">
            </div>
        </section>

    <section class="filters-container">
        <div class="filters-header">
            <span class="material-icons">tune</span>
            <h3>Filtri</h3>
        </div>
        <div class="filters-grid">
            <div class="filter-group">
                <label for="filter-tip">Tip</label>
                <select id="filter-tip">
                    <option value="">Vsi Tipi</option>
                    <?php foreach ($vrste_zivali as $vrsta): ?>
                        <option value="<?php echo htmlspecialchars($vrsta['ID_vrsta']); ?>">
                            <?php echo htmlspecialchars($vrsta['imeVrste']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="filter-group">
                <label>Spol</label>
                <select><option>Vsi Spoli</option></select>
            </div>
            <div class="filter-group">
                <label>Starost</label>
                <select><option>Vse Starosti</option></select>
            </div>
            <div class="filter-group">
                <label>Status</label>
                <select><option>Vsi Statusi</option></select>
            </div>
        </div>
        <p class="showing-info" id="showing-info">Prikazujem živali...</p>
    </section>
            
    <section class="animal-gallery">
        <?php
        $sql = "SELECT
                    Z.ID_zival,
                    Z.ime AS ime_zivali,
                    Z.starost,
                    V.imeVrste AS vrsta, 
                    S.vrstaStatusa AS status,
                    MAX(F.potDoDatoteke) AS pot_do_slike
                FROM
                    Zival Z
                LEFT JOIN 
                    Vrsta V ON Z.TK_vrsta = V.ID_vrsta
                LEFT JOIN
                    Status S ON Z.TK_status = S.ID_status
                LEFT JOIN
                    Fotografija F ON Z.ID_zival = F.TK_zival
                GROUP BY
                    Z.ID_zival, Z.ime, Z.starost, V.imeVrste, S.vrstaStatusa -- Vključimo vse neagregirane stolpce v GROUP BY
                ORDER BY 
                    CASE WHEN S.vrstaStatusa = 'Aktiven' THEN 0 ELSE 1 END,
                    Z.ime ASC"; 
        
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $animals = $stmt->fetchAll();
            
            // Povežemo število živali s spremenljivko v footerju
            $animal_count = count($animals);

            if ($animal_count > 0) {
                // Generiranje kartic za vsako žival
                foreach ($animals as $animal) {
                    $status = $animal['status'] ?? 'Neznano'; 
                    // KLJUČNA SPREMEMBA: Normaliziramo status! Odstranimo presledke in pretvorimo v male črke.
                    $status_lower = strtolower(trim($status));
                    // Initializacija za privzeti status (če ne ujamemo nobenega elseif)
                    $icon_emoji = 'ℹ️';
                    $status_class = 'in-care'; 

                    if ($status_lower === 'aktiven') {
                        $icon_emoji = '🐾'; 
                        $status_class = 'available';
                    } elseif ($status_lower === 'posvojen') {
                        $icon_emoji = '🏡';
                        $status_class = 'adopted';
                    } elseif ($status_lower === 'rezerviran') {
                        $icon_emoji = '🔒';
                        $status_class = 'reserved';
                    } elseif ($status_lower === 'neaktiven') {
                        $icon_emoji = '🚫';
                        $status_class = 'inactive';
                    }
                    $vrsta = $animal['vrsta'] ?? 'Neznana vrsta';

                    $animal_id = htmlspecialchars($animal['ID_zival']); // ID živali potrebujemo za povezavo
                    $image_path = !empty($animal['pot_do_slike']) ? htmlspecialchars($animal['pot_do_slike']) : 'images/placeholder.jpg';

                    // Uporabimo <a> oznako za celo kartico, ki vodi na stran z ID-jem
                    echo "<a href='profil_zivali.php?id={$animal_id}' class='animal-card-link'>"; // Dodajanje novega razreda
                    echo "<div class='animal-card'>";
                    echo "<div class='image-placeholder' style='background-image: url(\"{$image_path}\")'><span class='status-badge {$status_class}'>{$status}</span></div>";
                    echo "<div class='card-details'>";
                    echo "<h4>" . htmlspecialchars($animal['ime_zivali']) . "</h4>";
                    echo "<p>" . htmlspecialchars($vrsta) . "</p>";
                    echo "<p class='meta'>Starost: " . htmlspecialchars($animal['starost']) . "</p>";
                    echo "<span class='animal-icon'>{$icon_emoji}</span>";
                    echo "</div>";
                    echo "</div>";
                    echo "</a>"; // Zaključek povezave
                }
            } else {
                echo "<p>V zavetišču žal ni zabeleženih živali.</p>";
            }
        } catch (PDOException $e) {
            // Ob napaki nastavimo število živali na 0
            $animal_count = 0;
            echo "<p style='color: red;'>Napaka pri pridobivanju podatkov.</p>"; 
        }
        ?>
    </section>

        <script>
            const toggleButton = document.getElementById('darkModeToggle');
            const body = document.body;
            const isDarkMode = localStorage.getItem('darkMode') === 'enabled';

            // 1. Nastavi stanje ob nalaganju strani
            if (isDarkMode) {
                body.classList.add('dark-mode');
                // Spremeni ikono, če jo želite
                toggleButton.textContent = 'light_mode'; 
            }

            // 2. Definira funkcijo preklopa
            function toggleDarkMode() {
                body.classList.toggle('dark-mode');

                if (body.classList.contains('dark-mode')) {
                    localStorage.setItem('darkMode', 'enabled');
                    toggleButton.textContent = 'light_mode'; // Sonce
                } else {
                    localStorage.setItem('darkMode', 'disabled');
                    toggleButton.textContent = 'dark_mode'; // Luna
                }
            }

            // 3. Dodaj poslušalca dogodkov na gumb
            if (toggleButton) {
                toggleButton.addEventListener('click', toggleDarkMode);
            }
        </script>
    </main>
            
    <?php include 'footer.php';?>
    </body>
</html>