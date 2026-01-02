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

// Brisanje živali
if (isset($_GET['izbrisi'])) {
    $id = $_GET['izbrisi'];
    $pdo->prepare("DELETE FROM Zival WHERE ID_zival = ?")->execute([$id]);
    header("Location: uredi_zivali_seznam.php");
    exit();
}

$zivali = $pdo->query("SELECT Z.*, V.imeVrste as vrsta, S.vrstaStatusa as status 
                       FROM Zival Z 
                       JOIN Vrsta V ON Z.TK_vrsta = V.ID_vrsta 
                       JOIN Status S ON Z.TK_status = S.ID_status 
                       ORDER BY Z.ID_zival DESC")->fetchAll();

$vrste = $pdo->query("SELECT * FROM Vrsta")->fetchAll();
$statusi = $pdo->query("SELECT * FROM Status")->fetchAll();
?>

<!DOCTYPE html>
<html lang="sl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upravljanje - ShelterCompass</title>
    <link rel="stylesheet" href="style.css"> <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <style>
        /* Dodatni stili za tabelo, ki jih v tvojem CSS še ni */
        .admin-table-container {
            max-width: 1000px;
            margin: 30px auto;
            padding: 20px;
            background: var(--surface-color);
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
        }
        body.dark-mode .admin-table-container {
            background: var(--dark-surface);
            border: 1px solid var(--dark-card-border);
        }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid var(--card-border); }
        body.dark-mode th, body.dark-mode td { border-bottom: 1px solid var(--dark-card-border); }
        
        .btn-icon { background: none; border: none; cursor: pointer; padding: 5px; }
        .edit-color { color: var(--green-dark); }
        .delete-color { color: var(--status-inactive); }

        /* Stil za modal (povzeto po tvoji logiki) */
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); overflow-y: auto; }
        .modal-content { background: var(--surface-color); margin: 5% auto; padding: 25px; border-radius: 15px; width: 90%; max-width: 600px; }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
        .full-width { grid-column: span 2; }
        
        input, select, textarea { 
            width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--card-border); 
            background: white; color: var(--text-color);
        }
        body.dark-mode input, body.dark-mode select, body.dark-mode textarea {
            background: var(--dark-bg); border-color: var(--dark-card-border); color: var(--dark-text);
        }
        .button { background: #4CAF50; color: white; padding: 15px; border: none; border-radius: 8px; cursor: pointer; width: 100%; font-size: 1.1em; }
        .status-badge {
        padding: 5px 12px;
        border-radius: 20px;
        font-weight: bold;
        color: white;
        display: inline-block;
        }

        /* Barve glede na tvoje spremenljivke v root */
        .aktiven, .available { background-color: var(--status-available); }
        .posvojen, .adopted { background-color: var(--status-adopted); }
        .v-oskrbi, .in-care { background-color: var(--status-incare); }
        .rezerviran, .reserved { background-color: var(--status-reserved); }
        .neaktiven, .inactive { background-color: var(--status-inactive); }
    </style>
</head>
<body class="<?= (isset($_COOKIE['darkMode']) && $_COOKIE['darkMode'] === 'enabled') ? 'dark-mode' : '' ?>">
    <?php include 'header.php'; ?>

    <div class="admin-table-container">
        <a href="admin_index.php" class="back-link" style="margin-bottom: 25px; display: inline-flex; align-items: center; text-decoration: none; color: var(--green-dark); font-weight: 600;">
            <span class="material-icons">arrow_back</span> Nazaj v nadzorno ploščo
        </a>

        <h2 style="margin-top:20px;">🐾 Upravljanje živali</h2>

        <table>
            <thead>
                <tr>
                    <th>Ime</th>
                    <th>Vrsta</th>
                    <th>Status</th>
                    <th style="text-align:right;">Akcije</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($zivali as $z): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($z['ime']) ?></strong></td>
                    <td><?= htmlspecialchars($z['vrsta']) ?></td>
                    <td>
                        <span class="status-badge <?= strtolower(str_replace(' ', '-', $z['status'])) ?>" style="position:static; font-size: 0.8em;">
                            <?= htmlspecialchars($z['status']) ?>
                        </span>
                    </td>
                    <td style="text-align:right;">
                        <button class="btn-icon edit-color" onclick='openEditModal(<?= json_encode($z) ?>)'>
                            <span class="material-icons">edit</span>
                        </button>
                        <a href="?izbrisi=<?= $z['ID_zival'] ?>" class="btn-icon delete-color" onclick="return confirm('Izbrišem?')">
                            <span class="material-icons">delete</span>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div id="editModal" class="modal">
        <div class="modal-content">
            <h2 id="modalTitle" style="color: var(--green-dark);">Uredi podatke</h2>
            <form action="posodobi_zival.php" method="POST">
                <input type="hidden" name="id_zival" id="edit_id">
                <div class="form-grid">
                    <div class="full-width">
                        <label>Ime</label>
                        <input type="text" name="ime" id="edit_ime" required>
                    </div>
                    <div>
                        <label>Vrsta</label>
                        <select name="vrsta" id="edit_vrsta">
                            <?php foreach($vrste as $v): ?>
                                <option value="<?= $v['ID_vrsta'] ?>"><?= $v['imeVrste'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label>Status</label>
                        <select name="status" id="edit_status">
                            <?php foreach($statusi as $s): ?>
                                <option value="<?= $s['ID_status'] ?>"><?= $s['vrstaStatusa'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="full-width">
                        <label>Opis</label>
                        <textarea name="opis" id="edit_opis" rows="4"></textarea>
                    </div>
                    <div>
                        <label>Starost</label>
                        <input type="text" name="starost" id="edit_starost">
                    </div>
                    <div>
                        <label>Teža (kg)</label>
                        <input type="text" name="teza" id="edit_teza">
                    </div>
                </div>
                
                <div style="margin-top: 25px; display: flex; gap: 10px;">
                    <button type="submit" class="button">Shrani spremembe</button>
                    <button type="button" class="cancel-button" onclick="closeModal()" style="flex:1; padding:12px; border-radius:5px; cursor:pointer;">Prekliči</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openEditModal(zival) {
            document.getElementById('edit_id').value = zival.ID_zival;
            document.getElementById('edit_ime').value = zival.ime;
            document.getElementById('modalTitle').innerText = "Urejanje: " + zival.ime;
            document.getElementById('edit_opis').value = zival.opis;
            document.getElementById('edit_starost').value = zival.starost;
            document.getElementById('edit_teza').value = zival.teza;
            document.getElementById('edit_vrsta').value = zival.TK_vrsta;
            document.getElementById('edit_status').value = zival.TK_status;
            document.getElementById('editModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('editModal').style.display = 'none';
        }

        window.onclick = function(event) {
            if (event.target == document.getElementById('editModal')) closeModal();
        }
        // Ponoviva dark mode logiko, da bo delala tudi tukaj
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

    <?php include 'footer.php'; ?>
</body>
</html>