<html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>ShelterCompass - Poišči Svojega Popolnega Spremljevalca</title>
        <link rel="stylesheet" href="style copy.css">
        <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    </head>
        <body>
            <?php 
            require 'db_connect.php';
            // Vključi glavo (header.php) - ta že vključuje db_connect.php in $pdo
            include 'header.php'; 

            if (isset($_GET['status']) && isset($_GET['message'])) {
                $status_class = ($_GET['status'] === 'success') ? 'success' : 'error';
                $message = htmlspecialchars($_GET['message']);
                
                // Prikaz opozorilne/uspešne vrstice
                echo "<div class='alert-message $status_class'>$message</div>";
            }

            // 1. Preverimo, ali je ID živali prisoten in veljaven
            $animal_id = $_GET['id'] ?? null;
            $animal = null;
            $animal_count = 0; 

            // Funkcija za prikaz ikone in statusa glede na BOOLEAN vrednost
            function display_health_status($value, $text) {
                if ($value) {
                    // Zelena kljukica (✔ Vaccinated)
                    $class = 'checked';
                    $icon = 'check';
                } else {
                    // Rdeči križec (✖ Not Vaccinated)
                    $class = 'unchecked';
                    $icon = 'close';
                }
                return "<span class='health-badge $class'><span class='material-icons'>$icon</span> $text</span>";
            }

            if ($animal_id && is_numeric($animal_id)) {
                // 2. POSODOBLJENA SQL poizvedba za pridobitev VSEH podatkov, vključno s spolom, težo, barvo, cepljenjem in sterilizacijo
                $sql = "SELECT
                            Z.ime AS ime_zivali,
                            Z.opis,
                            Z.starost,
                            Z.spol,
                            Z.barvaKozuha,
                            Z.teza,
                            Z.cepljen,
                            Z.sterilizacija,
                            V.imeVrste AS vrsta, 
                            S.vrstaStatusa AS status,
                            MAX(F.potDoDatoteke) AS pot_do_slike
                        FROM
                            Zival Z
                        LEFT JOIN Vrsta V ON Z.TK_vrsta = V.ID_vrsta
                        LEFT JOIN Status S ON Z.TK_status = S.ID_status
                        LEFT JOIN Fotografija F ON Z.ID_zival = F.TK_zival
                        WHERE Z.ID_zival = :id
                        GROUP BY Z.ID_zival, Z.ime, Z.opis, Z.starost, Z.spol, Z.barvaKozuha, Z.teza, Z.cepljen, Z.sterilizacija, V.imeVrste, S.vrstaStatusa"; 

                try {
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindParam(':id', $animal_id, PDO::PARAM_INT);
                    $stmt->execute();
                    $animal = $stmt->fetch();
                    if ($animal) {
                        $animal_count = 1;
                    }
                } catch (PDOException $e) {
                    $animal = null;
                }
                
            }    

            $vrsta_emoji = ($animal['vrsta'] ?? '') === 'Mačka' ? '🐱' : '🐾';?>

            <div class="profile-container">
                <a href="index.php" class="back-link">
                    <span class="material-icons">arrow_back</span> Nazaj na galerijo
                </a>
                
                <?php if ($animal):
                  
                    // --- DODANA LOGIKA ZA DOLOČANJE BARVNEGA RAZREDA GLEDE NA STATUS ---
                    $status_raw = $animal['status'] ?? 'Neznano';
                    $status_lower = strtolower(trim($status_raw));
                    $status_class = 'in-care'; // Privzeta vrednost

                    if ($status_lower === 'aktiven') {
                        $status_class = 'available';
                    } elseif ($status_lower === 'posvojen') {
                        $status_class = 'adopted';
                    } elseif ($status_lower === 'rezerviran') {
                        $status_class = 'reserved';
                    } elseif ($status_lower === 'neaktiven') {
                        $status_class = 'inactive';
                    }
                ?>
                    <div class="profile-layout-grid">
                        
                        <div class="main-content-column">
                            
                            <div class="image-sidebar-wrapper">
                                <div class="image-area" style="background-image: url('<?php echo htmlspecialchars($animal['pot_do_slike'] ?? 'images/placeholder.jpg'); ?>');">
                                    <div class="status-badge-wrapper">
                                        <span class="status-badge <?php echo htmlspecialchars($status_class); ?>">
                                            <?php echo htmlspecialchars($animal['status'] ?? 'Neznano'); ?>
                                        </span>
                                    </div>
                                </div>

                                <div class="sidebar-on-image-row">
                                    <div class="sidebar-card share-profile-box">
                                        <h4>Deli Profil</h4>
                                        <img src="images/qr_placeholder.png" alt="QR koda profila" class="qr-code">
                                        <p>Skeniraj za ogled profila</p>
                                    </div>
                                    
                                    <div class="sidebar-card inquiry-box">
                                        <h4>Zanima vas <?php echo htmlspecialchars($animal['ime_zivali']); ?>?</h4>
                                        
                                        <?php 
                                        // Priprava logike za gumb in disclaimer
                                        $can_inquire = true;
                                        $disclaimer = "";
                                        $button_text = "Oddaj povpraševanje";

                                        // Preverjamo status (uporabimo že pripravljeno spremenljivko $status_lower)
                                        if ($status_lower === 'posvojen' || $status_lower === 'neaktiven') {
                                            $can_inquire = false;
                                            $button_text = "Ni več na voljo";
                                        } elseif ($status_lower === 'v oskrbi') {
                                            $disclaimer = "Žival je trenutno še v oskrbi pri testni družini, vendar že sprejemamo povpraševanja.";
                                        } elseif ($status_lower === 'rezerviran') {
                                            $disclaimer = "Za to žival je že bil izbran potencialni posvojitelj, vendar nas lahko še vedno kontaktirate.";
                                        }
                                        ?>

                                        <?php if ($disclaimer !== ""): ?>
                                            <div class="status-disclaimer" style="background: rgba(255,193,7,0.1); border-left: 4px solid #ffc107; padding: 10px; margin-bottom: 15px; font-size: 0.9rem; display: flex; align-items: flex-start; gap: 8px;">
                                                <span class="material-icons" style="color: #ffc107; font-size: 1.2rem;">info</span>
                                                <span><?php echo $disclaimer; ?></span>
                                            </div>
                                        <?php endif; ?>

                                        <p>Oddajte povpraševanje in naša ekipa vas bo kontaktirala v 24 urah.</p>

                                        <?php if ($can_inquire): ?>
                                            <button class="inquiry-button" onclick="openModal()"><?php echo $button_text; ?></button>
                                        <?php else: ?>
                                            <button class="inquiry-button" style="background-color: #ccc; cursor: not-allowed;" disabled>
                                                <?php echo $button_text; ?>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <div class="profile-data-box">

                                <div class="name-status-row">
                                    <h1><?php echo htmlspecialchars($animal['ime_zivali']); ?> <?php echo $vrsta_emoji; ?></h1>

                                </div>

                                <p class="type-info"><?php echo htmlspecialchars($animal['vrsta'] ?? 'Neznana vrsta'); ?></p>

                                <div class="meta-grid">
                                    <div class="meta-item">
                                        <span class="material-icons">calendar_today</span>
                                        <p class="meta-value"><?php echo htmlspecialchars($animal['starost'] ?? 'Neznano'); ?></p>
                                        <p class="meta-label">Starost</p>
                                    </div>
                                    <div class="meta-item">
                                        <span class="material-icons">favorite</span>
                                        <p class="meta-value"><?php echo htmlspecialchars($animal['spol'] ?? 'Neznano'); ?></p>
                                        <p class="meta-label">Spol</p>
                                    </div>
                                    <div class="meta-item">
                                        <span class="material-icons">monitor_weight</span>
                                        <p class="meta-value"><?php echo htmlspecialchars($animal['teza'] ?? 'Neznano'); ?> kg</p>
                                        <p class="meta-label">Teža</p>
                                    </div>
                                    <div class="meta-item">
                                        <span class="material-icons">palette</span>
                                        <p class="meta-value"><?php echo htmlspecialchars($animal['barvaKozuha'] ?? 'Neznano'); ?></p>
                                        <p class="meta-label">Barva</p>
                                    </div>
                                </div>

                                <h2>Zdravstveni Status</h2>
                                <div class="health-badges-wrapper">
                                    <?php echo display_health_status($animal['cepljen'] ?? 0, 'Cepljen'); ?>
                                    <?php echo display_health_status($animal['sterilizacija'] ?? 0, 'Steriliziran/Kastriran'); ?>
                                </div>

                                <h2>O <?php echo htmlspecialchars($animal['ime_zivali']); ?></h2>
                                <p class="description">
                                    <?php echo nl2br(htmlspecialchars($animal['opis'] ?? 'Žival nima vnesenega opisa.')); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                    
                <?php else: ?>
                    <p class="error-message">Žival s tem ID-jem ni bila najdena ali ID ni veljaven.</p>
                <?php endif; ?>
            </div>

            <div id="inquiryModal" class="modal">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2>Povpraševanje za posvojitev</h2>
                        <p>Za <?php echo htmlspecialchars($animal['ime_zivali'] ?? 'Unknown Animal'); ?></p>
                        <span class="close-button" onclick="closeModal()">&times;</span>
                    </div>
                    <form id="inquiryForm" action="oddaja_povprasevanja.php" method="POST">
                        <input type="hidden" name="animal_id" value="<?php echo htmlspecialchars($animal_id); ?>">
                        <input type="hidden" name="user_id" value="1"> <div class="form-group">
                            <label for="fullName">Ime in priimek <span class="required">*</span></label>
                            <input type="text" id="fullName" name="fullName" required>
                        </div>

                        <div class="form-group">
                            <label for="email">Email <span class="required">*</span></label>
                            <input type="email" id="email" name="email" required>
                        </div>

                        <div class="form-group">
                            <label for="petExperience">Izkušnje in bivalni pogoji hišnih ljubljenčkov <span class="required">*</span></label>
                            <textarea id="message" name="sporocilo" rows="4" required placeholder="Povejte nam o svojih izkušnjah s hišnimi ljubljenčki in o svojih življenjskih razmerah ..."></textarea>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="cancel-button" onclick="closeModal()">Preklic</button>
                            <button type="submit" class="submit-button">Oddaj povpraševanje</button>
                        </div>
                    </form>
                </div>
            </div>

            <script>
                // Funkcije za odpiranje in zapiranje modala
                function openModal() {
                    document.getElementById('inquiryModal').style.display = 'block';
                }

                function closeModal() {
                    document.getElementById('inquiryModal').style.display = 'none';
                }

                // Zapiranje modala s klikom zunaj okna
                window.onclick = function(event) {
                    const modal = document.getElementById('inquiryModal');
                    if (event.target == modal) {
                        closeModal();
                    }
                }

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

            <?php include 'footer.php'; ?>
        </body>
</html>