<html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>ShelterCompass - Profil živali</title>
        <link rel="stylesheet" href="style.css">
        <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    </head>
        <body>
            <?php 
            require 'db_connect.php';
            require 'funkcije_profil.php'; //pokliče ločeno datoteko s funkcijami
            include 'header.php'; 

            //prikaz alert sporočil
            if (isset($_GET['status']) && isset($_GET['message'])) {
                $alert_class = ($_GET['status'] === 'success') ? 'success' : 'error';
                echo "<div class='alert-message $alert_class'>" . htmlspecialchars($_GET['message']) . "</div>";
            }

            $animal_id = $_GET['id'] ?? null;
            $animal = null;

            if ($animal_id && is_numeric($animal_id)) {
            $sql = "SELECT Z.ime AS ime_zivali, Z.opis, Z.starost, Z.datumRojstva, Z.spol, Z.barvaKozuha, Z.teza, 
                        Z.cepljen, Z.sterilizacija, V.imeVrste AS vrsta, S.vrstaStatusa AS status,
                        MAX(F.potDoDatoteke) AS pot_do_slike
                    FROM Zival Z
                    LEFT JOIN Vrsta V ON Z.TK_vrsta = V.ID_vrsta
                    LEFT JOIN Status S ON Z.TK_status = S.ID_status
                    LEFT JOIN Fotografija F ON Z.ID_zival = F.TK_zival
                    WHERE Z.ID_zival = :id
                    GROUP BY Z.ID_zival, Z.ime, Z.opis, Z.starost, Z.datumRojstva, Z.spol, Z.barvaKozuha, Z.teza, Z.cepljen, Z.sterilizacija, V.imeVrste, S.vrstaStatusa"; 

                $stmt = $pdo->prepare($sql);
                $stmt->execute([':id' => $animal_id]);
                $animal = $stmt->fetch();

                $sql_images = "SELECT potDoDatoteke FROM Fotografija WHERE TK_zival = :id";
                $stmt_img = $pdo->prepare($sql_images);
                $stmt_img->execute([':id' => $animal_id]);
                $images = $stmt_img->fetchAll(PDO::FETCH_COLUMN);
            }

            if ($animal):
                $status_class = get_status_class($animal['status']);
                $inquiry = get_inquiry_logic($animal['status']);
                $vrsta_emoji = ($animal['vrsta'] === 'Mačka') ? '🐱' : '🐾';
            ?>

            <div class="profile-container">
                <a href="index.php" class="back-link">
                    <span class="material-icons">arrow_back</span> Nazaj na galerijo
                </a>

                <div class="profile-layout-grid">
                    <div class="main-content-column">
                        <div class="image-sidebar-wrapper">
                            <div class="slider-viewport">
                                <div id="imageTrack" class="image-track">
                                    <?php foreach ($images as $index => $img_path): ?>
                                        <div class="slide-item">
                                            <img src="<?php echo htmlspecialchars($img_path); ?>" 
                                                alt="Slika <?php echo $index + 1; ?>" 
                                                loading="lazy" 
                                                class="slider-img">
                                        </div>
                                    <?php endforeach; ?>
                                    
                                    <?php if (empty($images)): ?>
                                        <div class="slide-item">
                                            <img src="images/placeholder.jpg" alt="Ni slike" loading="lazy" class="slider-img">
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="status-badge-wrapper">
                                    <span class="status-badge <?php echo $status_class; ?>">
                                        <?php echo htmlspecialchars($animal['status'] ?? 'Neznano'); ?>
                                    </span>
                                </div>

                                <?php if (count($images) > 1): ?>
                                    <button class="slider-nav prev" onclick="moveSlide(-1)">
                                        <span class="material-icons">chevron_left</span>
                                    </button>
                                    <button class="slider-nav next" onclick="moveSlide(1)">
                                        <span class="material-icons">chevron_right</span>
                                    </button>
                                    <div class="slide-counter"><span id="currentIdx">1</span> / <?php echo count($images); ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="sidebar-on-image-row">
                                <div class="sidebar-card share-profile-box">
                                    <h4>Deli Profil</h4>
                                    <img src="<?php echo get_qr_code_url(); ?>" alt="QR koda profila" class="qr-code">
                                </div>

                                <div class="sidebar-card inquiry-box">
                                    <h4>Zanima vas <?php echo htmlspecialchars($animal['ime_zivali']); ?>?</h4>
                                    
                                    <?php if ($inquiry['disclaimer']): ?>
                                        <div class="status-disclaimer" style="background: rgba(255,193,7,0.1); border-left: 4px solid #ffc107; padding: 10px; margin-bottom: 15px; font-size: 0.85rem; display: flex; gap: 8px;">
                                            <span class="material-icons" style="color: #ffc107; font-size: 1.1rem;">info</span>
                                            <span><?php echo $inquiry['disclaimer']; ?></span>
                                        </div>
                                    <?php endif; ?>

                                    <p>Oddajte povpraševanje in naša ekipa vas bo kontaktirala v 24 urah.</p>

                                    <button class="inquiry-button <?php echo !$inquiry['can_inquire'] ? 'disabled-btn' : ''; ?>" 
                                            onclick="<?php echo $inquiry['can_inquire'] ? 'openModal()' : ''; ?>"
                                            <?php echo !$inquiry['can_inquire'] ? 'disabled' : ''; ?>>
                                        <?php echo $inquiry['button_text']; ?>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="profile-data-box">
                            <h1><?php echo htmlspecialchars($animal['ime_zivali']); ?> <?php echo $vrsta_emoji; ?></h1>
                            <p class="type-info"><?php echo htmlspecialchars($animal['vrsta'] ?? 'Neznano'); ?></p>

                            <div class="meta-grid">
                                <div class="meta-item"><span class="material-icons">calendar_today</span><p class="meta-value"><?php echo htmlspecialchars($animal['starost']); ?></p><p class="meta-label">Starost</p></div>
                                <div class="meta-item"><span class="material-icons">cake</span><p class="meta-value"><?php echo date("d. m. Y", strtotime($animal['datumRojstva'])); ?></p><p class="meta-label">Rojstni dan</p></div>
                                <div class="meta-item"><span class="material-icons">favorite</span><p class="meta-value"><?php echo htmlspecialchars($animal['spol']); ?></p><p class="meta-label">Spol</p></div>
                                <div class="meta-item"><span class="material-icons">monitor_weight</span><p class="meta-value"><?php echo htmlspecialchars($animal['teza']); ?> kg</p><p class="meta-label">Teža</p></div>
                                <div class="meta-item"><span class="material-icons">palette</span><p class="meta-value"><?php echo htmlspecialchars($animal['barvaKozuha']); ?></p><p class="meta-label">Barva</p></div>
                            </div>

                            <h2>Zdravstveni Status</h2>
                            <div class="health-badges-wrapper">
                                <?php echo display_health_status($animal['cepljen'], 'Cepljen'); ?>
                                <?php echo display_health_status($animal['sterilizacija'], 'Steriliziran/Kastriran'); ?>
                            </div>

                            <h2>O živali</h2>
                            <p class="description"><?php echo nl2br(htmlspecialchars($animal['opis'] ?? 'Ni opisa.')); ?></p>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <p class="error-message">Žival ni bila najdena.</p>
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
        
            <script src="script.js"></script>

            <?php include 'footer.php'; ?>
        </body>
</html>