<?php
    require 'admin_check.php';
    require 'db_connect.php';

    //brisanje živali
    if (isset($_GET['izbrisi'])) {
        $id = $_GET['izbrisi'];
        
        try {
            $pdo->beginTransaction();
            //najprej izbriše zapise v tabeli Fotografija, ki so povezani s to živaljo
            $stmtFoto = $pdo->prepare("DELETE FROM Fotografija WHERE TK_zival = ?");
            $stmtFoto->execute([$id]);
            //potem komaj lahko varno izbriše žival
            $stmtZival = $pdo->prepare("DELETE FROM Zival WHERE ID_zival = ?");
            $stmtZival->execute([$id]);

            $pdo->commit();
            header("Location: uredi_zivali_seznam.php?uspeh=1");
            exit();
        } catch (Exception $e) {
            $pdo->rollBack();
            die("Napaka pri brisanju: " . $e->getMessage());
        }
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
        <title>ShelterCompass - Seznam živali</title>
        <link rel="stylesheet" href="style.css"> <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    </head>
        <body class="<?= (isset($_COOKIE['darkMode']) && $_COOKIE['darkMode'] === 'enabled') ? 'dark-mode' : '' ?>">
            <?php include 'header.php'; ?>

            <div class="admin-table-container">
                <a href="admin_index.php" class="back-link" style="margin-bottom: 25px; display: inline-flex; align-items: center; text-decoration: none; color: var(--green-dark); font-weight: 600;">
                    <span class="material-icons">arrow_back</span> Nazaj v nadzorno ploščo
                </a>

                <h2 style="margin-top:20px;">🐾 Seznam živali</h2>

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
            <script src="script.js"></script>
            <?php include 'footer.php'; ?>
        </body>
</html>