<?php
    require 'admin_check.php';
    require 'db_connect.php';

    $napaka = "";
    $uspeh = "";
    //pridobi vrste in statuse za spustne sezname
    $vrste = $pdo->query("SELECT * FROM Vrsta")->fetchAll();
    $statusi = $pdo->query("SELECT * FROM Status")->fetchAll();

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $ime = $_POST['ime'];
        $opis = $_POST['opis'];
        $starost = $_POST['starost'];
        $datum_rojstva = $_POST['datum_rojstva'];
        $spol = $_POST['spol'];
        $barva = $_POST['barva'];
        $teza = $_POST['teza'];
        $datum_najdbe = $_POST['datum_najdbe'];
        $vrsta_id = $_POST['vrsta'];
        $status_id = $_POST['status'];
        $cepljen = isset($_POST['cepljen']) ? 1 : 0;
        $steriliziran = isset($_POST['steriliziran']) ? 1 : 0;
        //logika za nalaganje slike
        if (isset($_FILES['slika']) && $_FILES['slika']['error'] == 0) {
            //uporabi originalno ime
            $ime_datoteke = $_FILES['slika']['name']; 
            $pot_za_shranjevanje = "slike/" . $ime_datoteke;

            if (move_uploaded_file($_FILES['slika']['tmp_name'], $pot_za_shranjevanje)) {
                try {
                    $pdo->beginTransaction();
                    //vstavi žival
                    $sql_zival = "INSERT INTO Zival (ime, opis, starost, datumRojstva, spol, barvaKozuha, teza, cepljen, sterilizacija, datumNajdbe, TK_vrsta, TK_status, TK_zavetisce) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)";
                    $stmt = $pdo->prepare($sql_zival);
                    $stmt->execute([
                        $ime, $opis, $starost, $datum_rojstva, $spol, $barva, 
                        $teza, $cepljen, $steriliziran, $datum_najdbe, $vrsta_id, $status_id
                    ]);
                    
                    $zadnja_id_zival = $pdo->lastInsertId();
                    //vstavi pot do slike v tabelo Fotografija
                    $sql_foto = "INSERT INTO Fotografija (potDoDatoteke, TK_zival) VALUES (?, ?)";
                    $pdo->prepare($sql_foto)->execute([$pot_za_shranjevanje, $zadnja_id_zival]);

                    $pdo->commit();
                    $uspeh = "Žival $ime je bila uspešno dodana z originalno sliko!";
                } catch (Exception $e) {
                    $pdo->rollBack();
                    $napaka = "Napaka pri shranjevanju v bazo: " . $e->getMessage();
                }
            } else {
                $napaka = "Napaka: Datoteke ni bilo mogoče premakniti v mapo 'slike/'. Preveri pravice mape.";
            }
        } else {
            $napaka = "Prosimo, naložite sliko živali ali preverite velikost datoteke.";
        }
    }
?>
<!DOCTYPE html>
<html lang="sl">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Dodaj novo žival - ShelterCompass</title>
        <link rel="stylesheet" href="style.css">
        <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    </head>
        <body class="<?= (isset($_COOKIE['darkMode']) && $_COOKIE['darkMode'] === 'enabled') ? 'dark-mode' : '' ?>">
            <?php include 'header.php'; ?>

            <div class="form-container">
                <a href="admin_index.php" class="back-link">
                    <span class="material-icons" style="margin-right: 8px;">arrow_back</span> Nazaj v nadzorno ploščo
                </a>

                <h2>🐾 Dodaj novo žival</h2>
                
                <?php if($uspeh): ?>
                    <p style="background: rgba(76, 175, 80, 0.1); color: #4CAF50; padding: 10px; border-radius: 8px; border: 1px solid #4CAF50;"><?= $uspeh ?></p>
                <?php endif; ?>
                <?php if($napaka): ?>
                    <p style="background: rgba(244, 67, 54, 0.1); color: #f44336; padding: 10px; border-radius: 8px; border: 1px solid #f44336;"><?= $napaka ?></p>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data">
                    <div class="form-grid">
                        <div class="full-width">
                            <label>Ime živali</label>
                            <input type="text" name="ime" required placeholder="Npr. Boni">
                        </div>
                        <div>
                            <label>Vrsta</label>
                            <select name="vrsta">
                                <?php foreach($vrste as $v): ?>
                                    <option value="<?= $v['ID_vrsta'] ?>"><?= $v['imeVrste'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label>Status</label>
                            <select name="status">
                                <?php foreach($statusi as $s): ?>
                                    <option value="<?= $s['ID_status'] ?>"><?= $s['vrstaStatusa'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label>Starost</label>
                            <input type="text" name="starost" required placeholder="Npr. 2 leti">
                        </div>
                        <div>
                            <label>(Okvirni) Datum rojstva</label>
                            <input type="date" name="datum_rojstva" required>
                        </div>
                        <div>
                            <label>Spol</label>
                            <select name="spol">
                                <option value="Samec">Samec</option>
                                <option value="Samička">Samička</option>
                            </select>
                        </div>
                        <div>
                            <label>Teža (kg)</label>
                            <input type="text" name="teza" required placeholder="Npr. 12.5">
                        </div>
                        <div class="full-width">
                            <label>Slika živali</label>
                            <input type="file" name="slika" accept="image/*" required>
                        </div>
                        <div class="full-width">
                            <label>Opis</label>
                            <textarea name="opis" rows="4" placeholder="Kratek opis živali..."></textarea>
                        </div>
                        <div>
                            <label>Barva kožuha</label>
                            <input type="text" name="barva" required>
                        </div>
                        <div>
                            <label>Datum najdbe</label>
                            <input type="date" name="datum_najdbe" required>
                        </div>
                        <div class="full-width" style="display: flex; gap: 20px; align-items: center; margin-top: 10px;">
                            <label style="display: flex; align-items: center; cursor: pointer;">
                                <input type="checkbox" name="cepljen"> Cepljen
                            </label>
                            <label style="display: flex; align-items: center; cursor: pointer;">
                                <input type="checkbox" name="steriliziran"> Steriliziran
                            </label>
                        </div>
                        <div class="full-width" style="margin-top: 20px;">
                            <button class="button-add" type="submit">Shrani žival</button>
                            <a href="admin_index.php" class="exit">Prekliči</a>
                        </div>
                    </div>
                </form>
            </div>
            <script src="script.js"></script>
            <?php include 'footer.php'; ?>
        </body>
</html>