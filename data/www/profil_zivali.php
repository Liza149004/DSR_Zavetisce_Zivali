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

            $vrsta_emoji = ($animal['vrsta'] ?? '') === 'Mačka' ? '🐱' : '🐾';
            ?>

            <div class="profile-container">
                <a href="index.php" class="back-link">
                    <span class="material-icons">arrow_back</span> Nazaj na galerijo
                </a>

                <?php if ($animal): ?>
                    
                    <div class="profile-layout-grid">
                        
                        <div class="main-content-column">
                            
                            <div class="image-sidebar-wrapper">
                                <div class="image-area" style="background-image: url('<?php echo htmlspecialchars($animal['pot_do_slike'] ?? 'images/placeholder.jpg'); ?>');">
                                    <div class="status-badge-wrapper">
                                        <span class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $animal['status'] ?? '')); ?>">
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
                                        <p>Oddajte povpraševanje in naša ekipa vas bo kontaktirala v 24 urah.</p>
                                        <button class="inquiry-button">Oddaj povpraševanje</button>
                                    </div>
                                </div>
                            </div>

                            <div class="profile-data-box">

                                <div class="name-status-row">
                                    <h1><?php echo htmlspecialchars($animal['ime_zivali']); ?> <?php echo $vrsta_emoji; ?></h1>
                                    <span class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $animal['status'] ?? '')); ?>">
                                        <?php echo ($animal['status'] ?? '') === 'Aktiven' ? 'Na voljo za posvojitev' : htmlspecialchars($animal['status'] ?? 'Neznano'); ?>
                                    </span>
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
                        
                        <div class="sidebar-column">
                            </div>
                    </div>
                    
                <?php else: ?>
                    <p class="error-message">Žival s tem ID-jem ni bila najdena ali ID ni veljaven.</p>
                <?php endif; ?>
            </div>

            <?php include 'footer.php'; ?>
        </body>
</html>