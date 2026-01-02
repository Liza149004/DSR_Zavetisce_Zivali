<?php
header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1.
header("Pragma: no-cache"); // HTTP 1.0.
header("Expires: 0"); // Proxies.

session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

require 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Pridobimo podatke iz obrazca (modala)
    $id = $_POST['id_zival'];
    $ime = $_POST['ime'];
    $vrsta_id = $_POST['vrsta'];
    $status_id = $_POST['status'];
    $opis = $_POST['opis'];
    $starost = $_POST['starost'];
    $teza = $_POST['teza'];

    try {
        // Pripravimo SQL stavek za posodobitev
        $sql = "UPDATE Zival SET 
                ime = ?, 
                TK_vrsta = ?, 
                TK_status = ?, 
                opis = ?, 
                starost = ?, 
                teza = ? 
                WHERE ID_zival = ?";
        
        $stmt = $pdo->prepare($sql);
        $uspeh = $stmt->execute([$ime, $vrsta_id, $status_id, $opis, $starost, $teza, $id]);

        if ($uspeh) {
            // Preusmerimo nazaj na seznam z uspehom
            header("Location: uredi_zivali_seznam.php?sporocilo=uspeh");
            exit();
        } else {
            header("Location: uredi_zivali_seznam.php?sporocilo=napaka");
            exit();
        }

    } catch (PDOException $e) {
        // V primeru napake v bazi
        die("Napaka pri posodabljanju baze: " . $e->getMessage());
    }
} else {
    // Če nekdo poskuša dostopati do datoteke direktno brez POST-a
    header("Location: uredi_zivali_seznam.php");
    exit();
}
?>