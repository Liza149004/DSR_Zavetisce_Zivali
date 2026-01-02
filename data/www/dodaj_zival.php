<?php
header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1.
header("Pragma: no-cache"); // HTTP 1.0.
header("Expires: 0"); // Proxy strežniki

// 2. Preveri sejo
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}
require 'db_connect.php';

$napaka = "";
$uspeh = "";

// Pridobimo vrste in statuse za spustne sezname
$vrste = $pdo->query("SELECT * FROM Vrsta")->fetchAll();
$statusi = $pdo->query("SELECT * FROM Status")->fetchAll();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $ime = $_POST['ime'];
    $opis = $_POST['opis'];
    $starost = $_POST['starost'];
    $spol = $_POST['spol'];
    $barva = $_POST['barva'];
    $teza = $_POST['teza'];
    $datum_najdbe = $_POST['datum_najdbe'];
    $vrsta_id = $_POST['vrsta'];
    $status_id = $_POST['status'];
    $cepljen = isset($_POST['cepljen']) ? 1 : 0;
    $steriliziran = isset($_POST['steriliziran']) ? 1 : 0;

    // Logika za nalaganje slike
    if (isset($_FILES['slika']) && $_FILES['slika']['error'] == 0) {
        $ime_datoteke = time() . "_" . $_FILES['slika']['name']; // Unikatno ime
        $pot_za_shranjevanje = "slike/" . $ime_datoteke;

        if (move_uploaded_file($_FILES['slika']['tmp_name'], $pot_za_shranjevanje)) {
            try {
                $pdo->beginTransaction();

                // 1. Vstavimo žival
                $sql_zival = "INSERT INTO Zival (ime, opis, starost, spol, barvaKozuha, teza, cepljen, sterilizacija, datumNajdbe, TK_vrsta, TK_status, TK_zavetisce) 
                              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)";
                $stmt = $pdo->prepare($sql_zival);
                $stmt->execute([$ime, $opis, $starost, $spol, $barva, $teza, $cepljen, $steriliziran, $datum_najdbe, $vrsta_id, $status_id]);
                
                $zadnja_id_zival = $pdo->lastInsertId();

                // 2. Vstavimo pot do slike v tabelo Fotografija
                $sql_foto = "INSERT INTO Fotografija (potDoDatoteke, TK_zival) VALUES (?, ?)";
                $pdo->prepare($sql_foto)->execute([$pot_za_shranjevanje, $zadnja_id_zival]);

                $pdo->commit();
                $uspeh = "Žival $ime je bila uspešno dodana!";
            } catch (Exception $e) {
                $pdo->rollBack();
                $napaka = "Napaka pri shranjevanju: " . $e->getMessage();
            }
        } else {
            $napaka = "Napaka pri prenosu slike na strežnik.";
        }
    } else {
        $napaka = "Prosimo, naložite sliko živali.";
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
    <style>
        .form-container { 
            max-width: 600px; 
            margin: 40px auto; 
            background: var(--surface-color); 
            padding: 30px; 
            border-radius: 15px; 
            box-shadow: 0 5px 20px rgba(0,0,0,0.1); 
            border: 1px solid var(--card-border);
        }
        
        body.dark-mode .form-container {
            background: var(--dark-surface);
            border-color: var(--dark-card-border);
        }

        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
        .full-width { grid-column: span 2; }
        .exit { 
            color: var(--text-color);
            display:block; 
            text-align:center; 
            margin-top:15px; 
            opacity: 0.6; 
            text-decoration: none; 
        }  
        .dark-mode .exit { 
            color: white;
            display:block; 
            text-align:center; 
            margin-top:15px; 
            opacity: 0.6; 
            text-decoration: none; 
        }        
        label { display: block; margin-bottom: 5px; font-weight: bold; color: var(--text-color); }
        body.dark-mode label { color: var(--dark-text); }

        input, select, textarea { 
            width: 100%; 
            padding: 10px; 
            border: 1px solid var(--card-border); 
            border-radius: 8px; 
            box-sizing: border-box; 
            background: white;
            color: var(--text-color);
        }

        body.dark-mode input, 
        body.dark-mode select, 
        body.dark-mode textarea {
            background: var(--dark-bg);
            border-color: var(--dark-card-border);
            color: var(--dark-text);
        }

        input[type="checkbox"] { width: auto; margin-right: 8px; }

        button { 
            background: var(--green-dark); 
            color: white; 
            padding: 15px; 
            border: none; 
            border-radius: 8px; 
            cursor: pointer; 
            width: 100%; 
            font-size: 1.1em; 
            font-weight: 600;
            transition: 0.3s;
        }
        
        button:hover { opacity: 0.9; transform: translateY(-2px); }

        .back-link:hover { opacity: 0.8; }
    </style>
</head>
<body class="<?= (isset($_COOKIE['darkMode']) && $_COOKIE['darkMode'] === 'enabled') ? 'dark-mode' : '' ?>">
    <?php include 'header.php'; ?>

    <div class="form-container">
        <a href="admin_index.php" class="back-link" style="margin-bottom: 25px; display: inline-flex; align-items: center; text-decoration: none; color: var(--green-dark); font-weight: 600;">
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
                    <label>Spol</label>
                    <select name="spol">
                        <option value="Samec">Samec</option>
                        <option value="Samička">Samička</option>
                    </select>
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
                    <label>Teža (kg)</label>
                    <input type="text" name="teza" required>
                </div>
                <div>
                    <label>Datum najdbe</label>
                    <input type="date" name="datum_najdbe" required>
                </div>
                <div style="display: flex; gap: 20px; align-items: center; margin-top: 15px;">
                    <label style="display: flex; align-items: center; cursor: pointer;">
                        <input type="checkbox" name="cepljen"> Cepljen
                    </label>
                    <label style="display: flex; align-items: center; cursor: pointer;">
                        <input type="checkbox" name="steriliziran"> Steriliziran
                    </label>
                </div>
                <div class="full-width" style="margin-top: 20px;">
                    <button type="submit">Shrani žival</button>
                    <a href="admin_index.php" class="exit">Prekliči</a>
                </div>
            </div>
        </form>
    </div>

    <?php include 'footer.php'; ?>

    <script>
        const toggleButton = document.getElementById('darkModeToggle');
        const body = document.body;
        
        function updateDarkMode() {
            const isDark = body.classList.contains('dark-mode');
            localStorage.setItem('darkMode', isDark ? 'enabled' : 'disabled');
            document.cookie = "darkMode=" + (isDark ? 'enabled' : 'disabled') + ";path=/";
        }

        if (toggleButton) {
            toggleButton.addEventListener('click', () => {
                body.classList.toggle('dark-mode');
                updateDarkMode();
            });
        }
    </script>
</body>
</html>