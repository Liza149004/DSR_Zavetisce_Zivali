<?php
    require 'db_connect.php';

    header('Content-Type: application/json');

    try {
        $sql = "SELECT 
                    Z.ID_zival, 
                    Z.ime AS ime_zivali, 
                    Z.starost, 
                    Z.spol,
                    Z.barvaKozuha,
                    V.imeVrste AS vrsta, 
                    S.vrstaStatusa AS status, 
                    MIN(F.potDoDatoteke) AS pot_do_slike
                FROM Zival Z
                LEFT JOIN Vrsta V ON Z.TK_vrsta = V.ID_vrsta
                LEFT JOIN Status S ON Z.TK_status = S.ID_status
                LEFT JOIN Fotografija F ON Z.ID_zival = F.TK_zival
                GROUP BY Z.ID_zival";

        $stmt = $pdo->query($sql);
        $animals = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode($animals);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["error" => $e->getMessage()]);
    }
?>