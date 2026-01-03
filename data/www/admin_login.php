<?php
session_start();
require 'db_connect.php';
require_once 'libs/GoogleAuthenticator.php'; // Zunanja knjižnica

$ga = new PHPGangsta_GoogleAuthenticator();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $geslo = $_POST['geslo'];
    $otp_koda = trim($_POST['otp_koda']); // Koda iz aplikacije

    // Poiščemo admina
    $stmt = $pdo->prepare("SELECT * FROM Uporabnik WHERE email = :email AND TK_tip_uporabnika = 2");
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch();

    if ($user && password_verify($geslo, $user['geslo'])) {
        
        // PREVERJANJE 2FA KODE (TOTP)
        // Če admin v bazi nima ključa, ga spustimo naprej (varnostna luknja), 
        // zato je nujno, da imaš 'two_fa_secret' nastavljen.
        $secret = $user['two_fa_secret'];
        
        // Preverimo kodo (dovolimo 1 interval odstopanja - 30 sekund)
        $preverjeno = $ga->verifyCode($secret, $otp_koda, 1);

        if ($preverjeno) {
            // Vse je v redu, ustvarimo sejo
            $_SESSION['admin_id'] = $user['ID_uporabnik'];
            $_SESSION['admin_ime'] = $user['ime'];
            header("Location: admin_index.php");
            exit();
        } else {
            $napaka = "Napačna 2FA koda iz aplikacije.";
        }
    } else {
        $napaka = "Napačni prijavni podatki.";
    }
}
?>
<!DOCTYPE html>
<html lang="sl">
<head>
    <meta charset="UTF-8">
    <title>Admin Prijava s 2FA</title>
    <style>
        body { font-family: sans-serif; background: #f4f4f4; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .login-card { background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); width: 320px; }
        h2 { text-align: center; color: #333; }
        input { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        .two-fa-section { background: #e8f5e9; padding: 10px; border-radius: 4px; margin: 10px 0; border-left: 4px solid #4CAF50; }
        .two-fa-section label { font-size: 0.85rem; color: #2e7d32; font-weight: bold; }
        button { width: 100%; padding: 12px; background: #4CAF50; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 1rem; margin-top: 10px; }
        button:hover { background: #45a049; }
        .error { color: #d32f2f; text-align: center; margin-bottom: 15px; font-size: 0.9rem; background: #ffebee; padding: 8px; border-radius: 4px; }
    </style>
</head>
<body>
    <div class="login-card">
        <h2>Admin Vstop</h2>
        
        <?php if(isset($napaka)) echo "<p class='error'>$napaka</p>"; ?>
        
        <form method="POST">
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="geslo" placeholder="Geslo" required>
            
            <div class="two-fa-section">
                <label for="otp_koda">6-mestna TOTP koda:</label>
                <input type="text" name="otp_koda" id="otp_koda" placeholder="000000" maxlength="6" pattern="\d{6}" required title="Vpišite 6 številk iz aplikacije">
            </div>

            <button type="submit">Varna prijava</button>
        </form>
    </div>
</body>
</html>