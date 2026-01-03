<?php
// 1. Preprečevanje predpomnilnika
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

session_start();
// Če v seji ni admin_id-ja, ga vrzi ven na prijavo
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

require 'db_connect.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

// --- LOGIKA ZA SPREJETJE / ZAVRNITEV ---
if (isset($_GET['akcija']) && isset($_GET['id_povp'])) {
    $id = $_GET['id_povp'];
    $akcija = $_GET['akcija'];
    
    $termin_raw = isset($_GET['termin']) ? $_GET['termin'] : '';
    $termin = !empty($termin_raw) ? date('d. m. Y \ob H:i', strtotime($termin_raw)) : 'po dogovoru';
    
    $stmt_info = $pdo->prepare("SELECT u.email, u.ime, z.ime as zival_ime, z.ID_zival 
                                FROM Povprasevanje p 
                                JOIN Uporabnik u ON p.TK_uporabnik = u.ID_uporabnik 
                                JOIN Zival z ON p.TK_zival = z.ID_zival 
                                WHERE p.ID_povprasevanje = ?");
    $stmt_info->execute([$id]);
    $podatki = $stmt_info->fetch();

 if ($podatki) {
        $to_email = $podatki['email'];
        $ime_uporabnika = $podatki['ime'];
        $ime_zivali = $podatki['zival_ime'];
        $id_zivali = $podatki['ID_zival'];
        
        // Nastavitve za PHPMailer
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $moj_gmail        = 'info.sheltercompass@gmail.com'; 
            $mail->Username   = $moj_gmail;
            $mail->Password   = 'abkdfepyfjqynjbr'; // Tvoj App Password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;
            $mail->CharSet    = 'UTF-8';

            $mail->setFrom($moj_gmail, 'Zavetišče ShelterCompass');
            $mail->addAddress($to_email, $ime_uporabnika);

            if ($akcija == 'sprejmi') {
                $status = 'Sprejeto';
                $subject = "Dobre novice! Vaše povpraševanje za $ime_zivali je sprejeto";
                
                // Stilizirano HTML sporočilo
                $mail_body = "
                <div style='background-color: #f4f7f6; padding: 30px; font-family: sans-serif;'>
                    <div style='max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 15px; border: 1px solid #e0e0e0; overflow: hidden;'>
                        <div style='background-color: #4CAF50; padding: 20px; text-align: center; color: white;'>
                            <h1 style='margin: 0;'>Čestitamo!</h1>
                        </div>
                        <div style='padding: 30px; color: #333;'>
                            <h2 style='color: #4CAF50;'>Pozdravljeni, $ime_uporabnika!</h2>
                            <p>Z veseljem vas obveščamo, da je bilo vaše povpraševanje za posvojitev našega varovanca z imenom <strong>$ime_zivali</strong> odobreno.</p>
                            <div style='background-color: #f9f9f9; border-left: 4px solid #4CAF50; padding: 15px; margin: 20px 0;'>
                                <p style='margin: 0;'><strong>Vaš termin za obisk:</strong></p>
                                <p style='font-size: 1.2rem; color: #4CAF50; margin: 10px 0;'>📅 $termin</p>
                            </div>
                            <p>Prosimo, da v primeru zadržanosti odgovorite na ta mail.</p>
                        </div>
                    </div>
                </div>";

                $pdo->prepare("UPDATE Zival SET TK_status = (SELECT ID_status FROM Status WHERE vrstaStatusa LIKE '%Rezerviran%' LIMIT 1) WHERE ID_zival = ?")
                    ->execute([$id_zivali]);
            } else {
                $status = 'Zavrnjeno';
                $subject = "Obvestilo o povpraševanju za žival $ime_zivali";
                
                $mail_body = "
                <div style='background-color: #f4f7f6; padding: 30px; font-family: sans-serif;'>
                    <div style='max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 15px; border: 1px solid #e0e0e0;'>
                        <div style='padding: 30px; color: #333;'>
                            <h2>Pozdravljeni, $ime_uporabnika</h2>
                            <p>Žal vas moramo obvestiti, da vaše povpraševanje za <strong>$ime_zivali</strong> tokrat ni bilo izbrano.</p>
                            <p>Vabimo vas, da spremljate našo stran še naprej za ostale živali, ki iščejo dom.</p>
                            <p>Lep pozdrav, ekipa ShelterCompass</p>
                        </div>
                    </div>
                </div>";
            }

            // Pošiljanje maila
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $mail_body;
            $mail->send();

            // Posodobitev statusa v bazi
            $stmt_update = $pdo->prepare("UPDATE Povprasevanje SET statusPovprasevanja = ? WHERE ID_povprasevanje = ?");
            $stmt_update->execute([$status, $id]);

        } catch (Exception $e) {
            // V primeru napake lahko dodaš logiranje ali obvestilo
            header("Location: admin_index.php?napaka=" . urlencode($mail->ErrorInfo));
            exit();
        }
    }
    
    header("Location: admin_index.php?uspeh=1");
    exit();
}

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
    <title>ShelterCompass - Nadzorna Plošča</title>
    <link rel="stylesheet" href="style copy.css"> <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <style>
        /* Specifični popravki za Admin panel, da se modal sklada z vašim stilom */
        .admin-nav { background: var(--card-bg); padding: 15px; border-radius: 12px; margin-bottom: 30px; display: flex; gap: 20px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1); }
        .admin-nav a { display: flex; align-items: center; gap: 8px; text-decoration: none; color: var(--text-color); font-weight: 600; }
        .msg-bubble { background: #f0f2f5; padding: 15px; border-radius: 10px; font-style: italic; margin-top: 10px; border-left: 4px solid var(--green-dark); }
        .dark-mode .msg-bubble { background: var(--dark-bg); color: #e4e6eb; }
        .admin-badge { background: #ff6b6b; color: white; padding: 2px 10px; border-radius: 20px; font-size: 0.8rem; }
        .btn-accept { background: var(--green-dark); color: white; border: none; padding: 10px; border-radius: 8px; cursor: pointer; flex: 1; font-weight: bold; display: flex; align-items: center; justify-content: center; gap: 5px; }
        .btn-reject { background: #ff6b6b; color: white; border: none; padding: 10px; border-radius: 8px; cursor: pointer; flex: 1; font-weight: bold; text-decoration: none; display: flex; align-items: center; justify-content: center; gap: 5px; }
    </style>
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
            <h1>Pozdravljena, <?= htmlspecialchars($_SESSION['admin_ime']) ?>! 👋</h1>
        </section>

        <section class="filters-container">
            <nav class="admin-nav">
                <a href="dodaj_zival.php"><span class="material-icons">add_circle</span> Dodaj žival</a>
                <a href="uredi_zivali_seznam.php"><span class="material-icons">pets</span> Uredi živali</a>
                <a href="logout.php?redirect=index.php"><span class="material-icons">visibility</span> Ogled strani</a> 
                <a href="izvoz_pdf.php" class="admin-nav-item"><span class="material-icons">picture_as_pdf</span> PDF Poročilo</a>
                <a href="izvoz_excel.php" class="admin-nav-item"><span class="material-icons">table_view</span> Excel Poročilo</a>
                <a href="logout.php?redirect=index.php" style="margin-left: auto; color: #ff6b6b;"><span class="material-icons">logout</span> Odjava</a>
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