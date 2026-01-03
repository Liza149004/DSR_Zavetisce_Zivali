<?php
    require 'admin_check.php';
    require 'db_connect.php';

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        //pridobi podatke iz obrazca (modala)
        $id = $_POST['id_zival'];
        $ime = $_POST['ime'];
        $vrsta_id = $_POST['vrsta'];
        $status_id = $_POST['status'];
        $opis = $_POST['opis'];
        $starost = $_POST['starost'];
        $teza = $_POST['teza'];

        try {
            //pripravi SQL stavek za posodobitev
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
                //preusmeri nazaj na seznam če uspešno vse posodobi
                header("Location: uredi_zivali_seznam.php?sporocilo=uspeh");
                exit();
            } else {
                header("Location: uredi_zivali_seznam.php?sporocilo=napaka");
                exit();
            }

        } catch (PDOException $e) {
            //v primeru napake
            die("Napaka pri posodabljanju baze: " . $e->getMessage());
        }
    } else {
        header("Location: uredi_zivali_seznam.php");
        exit();
    }
?>