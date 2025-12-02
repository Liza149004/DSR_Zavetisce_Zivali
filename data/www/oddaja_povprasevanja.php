<?php
require 'db_connect.php'; // Povezava na bazo

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. Pridobivanje podatkov iz skritih polj
    $animal_id = filter_input(INPUT_POST, 'animal_id', FILTER_VALIDATE_INT);
    $user_id = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT); 
    
    // Zaenkrat ne shranjujemo polj, kot so Ime, Email, Opis, saj tabela Povprasevanje zanje nima polj,
    // a jih lahko uporabite za pošiljanje e-maila zavetišču.
    // $fullName = filter_input(INPUT_POST, 'fullName', FILTER_SANITIZE_STRING); 
    // $petExperience = filter_input(INPUT_POST, 'petExperience', FILTER_SANITIZE_STRING);

    // 2. Preverjanje veljavnosti ID-jev
    if (!$animal_id || !$user_id) {
        header("Location: profile_user.php?id=" . $animal_id . "&status=error&message=Neveljaven+ID+živali+ali+uporabnika.");
        exit();
    }

    // 3. Vstavljanje povpraševanja v bazo
    // Status 'V' lahko pomeni 'V obravnavi'. Tabela Povprasevanje ima statusPovprasevanja VARCHAR(4).
    $sql = "INSERT INTO Povprasevanje (datumOddaje, statusPovprasevanja, TK_uporabnik, TK_zival) 
            VALUES (CURDATE(), 'V', :user_id, :animal_id)";

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':animal_id', $animal_id, PDO::PARAM_INT);
        $stmt->execute();

        // Uspešna oddaja
        header("Location: profil_zivali.php?id=" . $animal_id . "&status=success&message=Povpraševanje+je+uspešno+oddano!");
        exit();

    } catch (PDOException $e) {
        // Napaka pri vstavljanju
        header("Location: profile_zivali.php?id=" . $animal_id . "&status=error&message=Napaka+pri+oddaji+povpraševanja+v+bazo.");
        exit();
    }
} else {
    // Neveljavna metoda
    header("Location: index.php");
    exit();
}
?>