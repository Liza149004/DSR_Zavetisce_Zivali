<?php
    require 'admin_check.php';
    require 'db_connect.php';
    require 'funkcije_admin.php';
    //LOGIKA ZA AKCIJE
    if (isset($_GET['akcija'], $_GET['id_povp'])) {
        $id = $_GET['id_povp'];
        $akcija = $_GET['akcija'];
        $termin_raw = $_GET['termin'] ?? '';
        $termin = !empty($termin_raw) ? date('d. m. Y \ob H:i', strtotime($termin_raw)) : 'po dogovoru';

        $stmt = $pdo->prepare("SELECT u.email, u.ime, z.ime as zival_ime, z.ID_zival FROM Povprasevanje p JOIN Uporabnik u ON p.TK_uporabnik = u.ID_uporabnik JOIN Zival z ON p.TK_zival = z.ID_zival WHERE p.ID_povprasevanje = ?");
        $stmt->execute([$id]);
        $podatki = $stmt->fetch();

        if ($podatki) {
            if (posljiEmailObvestilo($podatki, $akcija, $termin, $pdo)) {
                //posodobitev statusa v bazi
                $status = ($akcija == 'sprejmi') ? 'Sprejeto' : 'Zavrnjeno';
                $pdo->prepare("UPDATE Povprasevanje SET statusPovprasevanja = ? WHERE ID_povprasevanje = ?")->execute([$status, $id]);
                header("Location: admin_index.php?uspeh=1");
            } else {
                header("Location: admin_index.php?napaka=MailError");
            }
            exit();
        }
    }
    //LOGIKA ZA PRIKAZ PODATKOV
    $sql = "SELECT p.*, u.ime, u.priimek, u.email as u_email, z.ime as zival_ime, 
                (SELECT potDoDatoteke FROM Fotografija WHERE TK_zival = z.ID_zival LIMIT 1) as pot_do_slike
            FROM Povprasevanje p 
            JOIN Uporabnik u ON p.TK_uporabnik = u.ID_uporabnik 
            JOIN Zival z ON p.TK_zival = z.ID_zival 
            WHERE p.statusPovprasevanja = 'v_cakanju' OR p.statusPovprasevanja IS NULL OR p.statusPovprasevanja = 'V'
            ORDER BY p.datumOddaje DESC";

    $povp = $pdo->query($sql)->fetchAll();
?>
<!DOCTYPE html>
<html lang="sl">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>ShelterCompass - Nadzorna plošča</title>
        <link rel="stylesheet" href="style.css"> <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    </head>
        <body class="<?= (isset($_COOKIE['darkMode']) && $_COOKIE['darkMode'] === 'enabled') ? 'dark-mode' : '' ?>">

            <?php include 'header.php'; ?>

            <div id="terminModal" class="modal">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2>Potrditev termina</h2>
                        <p>Izberite datum in uro ogleda za posvojitelja.</p>
                        <span class="close-button" onclick="zapriModal()">&times;</span>
                    </div>
                    
                    <div class="form-group" style="margin-top: 20px;">
                        <label for="inputTermin">Datum in ura ogleda <span style="color:red;">*</span></label>
                        <input type="datetime-local" id="inputTermin" required>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="cancel-button" onclick="zapriModal()">Prekliči</button>
                        <button type="button" class="submit-button" onclick="potrdiSprejem()">Sprejmi in pošlji e-pošto</button>
                    </div>
                </div>
            </div>

            <main>
                <section class="hero" style="padding: 40px 20px;">
                    <h1>Pozdravljeni, <?= htmlspecialchars($_SESSION['admin_ime']) ?>! 👋</h1>
                </section>

                <section class="filters-container">
                    <nav class="admin-nav">
                        <a href="dodaj_zival.php"><span class="material-icons">add_circle</span> Dodaj žival</a>
                        <a href="uredi_zivali_seznam.php"><span class="material-icons">pets</span> Uredi živali</a>
                        <a href="logout.php?redirect=index.php"><span class="material-icons">visibility</span> Ogled strani</a> 
                        <a href="izvoz_pdf.php" class="admin-nav-item"><span class="material-icons">picture_as_pdf</span> PDF Poročilo</a>
                        <a href="izvoz_excel.php" class="admin-nav-item"><span class="material-icons">table_view</span> Excel Poročilo</a>
                        <a href="logout.php?redirect=index.php" style="margin-left: auto; color: var(--status-inactive);"><span class="material-icons">logout</span> Odjava</a>
                    </nav>
                    
                    <div class="filters-header">
                        <span class="material-icons">mail</span>
                        <h3>Nerešena povpraševanja <span class="admin-badge"><?= count($povp) ?></span></h3>
                    </div>
                </section>

                <section class="animal-gallery">
                    <?php if (count($povp) > 0): ?>
                        <?php foreach ($povp as $p): ?>
                            <div class="animal-card" style="cursor: default; transform: none;">
                                <div class="image-placeholder" style="background-image: url('<?= !empty($p['pot_do_slike']) ? htmlspecialchars($p['pot_do_slike']) : 'images/placeholder.jpg' ?>')">
                                    <span class="status-badge available"><?= date('d. m. Y', strtotime($p['datumOddaje'])) ?></span>
                                </div>
                                <div class="card-details">
                                    <h4>Žival: <?= htmlspecialchars($p['zival_ime']) ?></h4>
                                    <p><strong>Od:</strong> <?= htmlspecialchars($p['ime'] . " " . $p['priimek']) ?></p>
                                    <div class="msg-bubble">"<?= nl2br(htmlspecialchars($p['sporocilo'])) ?>"</div>

                                    <div style="margin-top: 15px; display: flex; gap: 10px;">
                                        <button onclick="odpriModal(<?= $p['ID_povprasevanje'] ?>)" class="btn-accept">
                                            <span class="material-icons">check</span> Sprejmi
                                        </button>
                                        <a href="?akcija=zavrni&id_povp=<?= $p['ID_povprasevanje'] ?>" class="btn-reject" onclick="return confirm('Ali res želite zavrniti to povpraševanje?')">
                                            <span class="material-icons">close</span> Zavrni
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div style="grid-column: 1/-1; text-align: center; padding: 50px; opacity: 0.5;">
                            <span class="material-icons" style="font-size: 50px;">done_all</span>
                            <p class="description">Vsa povpraševanja so rešena!</p>
                        </div>
                    <?php endif; ?>
                </section>
            </main>
            <script src="script.js"></script>
            <?php include 'footer.php'; ?>
        </body>
</html>