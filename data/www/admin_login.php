<?php
    session_start();
    require 'db_connect.php';
    require_once 'libs/GoogleAuthenticator.php'; //zunanja knjižnica za dvojno avtentikacijo (TOTP)

    $ga = new PHPGangsta_GoogleAuthenticator();

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $email = trim($_POST['email']);
        $geslo = $_POST['geslo'];
        $otp_koda = trim($_POST['otp_koda']); //koda iz aplikacije
        //poiščee admina
        $stmt = $pdo->prepare("SELECT * FROM Uporabnik WHERE email = :email AND TK_tip_uporabnika = 2");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();
        //PREVERJANJE (TOTP)
        if ($user && password_verify($geslo, $user['geslo'])) {
            $secret = $user['two_fa_secret'];
            //preveri kodo (dovoli 1 interval odstopanja - 30 sekund)
            $preverjeno = $ga->verifyCode($secret, $otp_koda, 1);

            if ($preverjeno) {
                //če je vse v redu, ustvarimo sejo
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
        <title>Admin prijava</title>
        <link rel="stylesheet" href="style.css">
    </head>
        <body class="login-body">
            <div class="login-card">
                <h2>Admin vstop</h2>
                
                <?php if(isset($napaka)) echo "<p class='error'>$napaka</p>"; ?>
                
                <form method="POST">
                    <input type="email" name="email" placeholder="Email" required>
                    <input type="password" name="geslo" placeholder="Geslo" required>
                    
                    <div class="two-fa-section">
                        <label for="otp_koda">6-mestna TOTP koda:</label>
                        <input type="text" name="otp_koda" id="otp_koda" placeholder="000000" maxlength="6" pattern="\d{6}" required title="Vpišite 6 številk iz aplikacije">
                    </div>

                    <button class="button-login" type="submit">Varna prijava</button>
                </form>
            </div>
        </body>
</html>