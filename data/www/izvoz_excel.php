<?php
    error_reporting(0);
    ini_set('display_errors', 0);

    require 'db_connect.php';
    session_start();

    if (!isset($_SESSION['admin_id'])) exit('Ni dostopa');
    //pridobivanje podatkov za živali
    $sql_zivali = "SELECT z.ime, v.imeVrste, s.vrstaStatusa, z.starost 
                FROM Zival z
                LEFT JOIN Vrsta v ON z.TK_vrsta = v.ID_vrsta
                LEFT JOIN Status s ON z.TK_status = s.ID_status
                ORDER BY z.ime ASC";
    $zivali = $pdo->query($sql_zivali)->fetchAll();
    //pridobivanje podatkov za povpraševanja
    $sql_povp = "SELECT u.ime, u.priimek, z.ime as zival_ime, p.datumOddaje, p.statusPovprasevanja 
                FROM Povprasevanje p
                JOIN Uporabnik u ON p.TK_uporabnik = u.ID_uporabnik
                JOIN Zival z ON p.TK_zival = z.ID_zival
                ORDER BY p.datumOddaje DESC";
    $povprasevanja = $pdo->query($sql_povp)->fetchAll();

    $filename = "Celotno_porocilo_" . date('dmY') . ".xls"; //.xls za HTML format

    header("Content-Type: application/vnd.ms-excel; charset=utf-8");
    header("Content-Disposition: attachment; filename=\"$filename\"");
    header("Pragma: no-cache");
    header("Expires: 0");
    //začetek HTML tabele, ki jo Excel razume
    echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
    echo '<head><meta http-equiv="content-type" content="application/vnd.ms-excel; charset=UTF-8"></head>';
    echo '<body>';
    //ŽIVALI
    echo '<h3>1. Pregled živali v zavetišču</h3>';
    echo '<table border="1">';
    echo '<tr style="background-color: #4caf50ff; color: #e8f5e9; font-weight: bold;">';
    echo '<th>Ime živali</th><th>Vrsta</th><th>Status</th><th>Starost</th>';
    echo '</tr>';

    foreach ($zivali as $row) {
        echo '<tr>';
        echo '<td>' . htmlspecialchars($row['ime']) . '</td>';
        echo '<td>' . htmlspecialchars($row['imeVrste']) . '</td>';
        echo '<td>' . htmlspecialchars($row['vrstaStatusa']) . '</td>';
        echo '<td align="center">' . htmlspecialchars($row['starost']) . '</td>';
        echo '</tr>';
    }
    echo '</table>';

    echo '<br><br>';
    //POVPRAŠEVANJA
    echo '<h3>2. Zgodovina povpraševanj</h3>';
    echo '<table border="1">';
    echo '<tr style="background-color: #4caf50ff; color: #e8f5e9; font-weight: bold;">';
    echo '<th>Uporabnik</th><th>Žival</th><th>Datum</th><th>Status povpraševanja</th>';
    echo '</tr>';

    foreach ($povprasevanja as $p) {
        $st = $p['statusPovprasevanja'];
        if($st == 'v_cakanju' || empty($st)) $st = 'V čakanju';
        $uporabnik = $p['ime'] . ' ' . $p['priimek'];
        $datum = date('d.m.Y', strtotime($p['datumOddaje']));

        echo '<tr>';
        echo '<td>' . htmlspecialchars($uporabnik) . '</td>';
        echo '<td>' . htmlspecialchars($p['zival_ime']) . '</td>';
        echo '<td align="center">' . $datum . '</td>';
        echo '<td>' . htmlspecialchars($st) . '</td>';
        echo '</tr>';
    }
    echo '</table>';

    echo '</body></html>';
    exit();
?>