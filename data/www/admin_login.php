<?php
session_start();
require 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $geslo = $_POST['geslo'];

    // Poiščemo admina
    $stmt = $pdo->prepare("SELECT * FROM Uporabnik WHERE email = :email AND TK_tip_uporabnika = 2");
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch();

    if ($user && password_verify($geslo, $user['geslo'])) {
        // Vse je v redu, ustvarimo sejo
        $_SESSION['admin_id'] = $user['ID_uporabnik'];
        $_SESSION['admin_ime'] = $user['ime'];
        header("Location: admin_index.php");
        exit();
    } else {
        $napaka = "Napačni prijavni podatki.";
    }
}
?>
<!DOCTYPE html>
<html lang="sl">
<head>
    <meta charset="UTF-8">
    <title>Admin Prijava</title>
    <style>
        body { font-family: sans-serif; background: #f4f4f4; display: flex; justify-content: center; align-items: center; height: 100vh; }
        .login-card { background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); width: 300px; }
        input { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        button { width: 100%; padding: 10px; background: #4CAF50; color: white; border: none; border-radius: 4px; cursor: pointer; }
        .error { color: red; text-align: center; margin-bottom: 10px; }
    </style>
</head>
<body>
    <div class="login-card">
        <h2>Admin Vstop</h2>
        <?php if(isset($napaka)) echo "<p class='error'>$napaka</p>"; ?>
        <form method="POST">
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="geslo" placeholder="Geslo" required>
            <button type="submit">Prijava</button>
        </form>
    </div>
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