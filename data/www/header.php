
<html lang="sl">
    <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ShelterCompass - Poišči Svojega Popolnega Spremljevalca</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    </head>
<body>
<header>
    <script>
    if (localStorage.getItem('darkMode') === 'enabled') {
        document.body.classList.add('dark-mode');
    }
    </script>
    <div class="logo">
        <span class="material-icons" style="color: #27ae60;">favorite</span>
        <span class="logo-text">ShelterCompass</span>
    </div>
   <div class="header-actions">
        <!-- KLJUČNI ELEMENT: Ikona za Dark Mode -->
        <!-- Začetna ikona je 'dark_mode' (Luna), ki jo JavaScript spremeni v 'light_mode' (Sonce) v temnem načinu -->
        <span id="darkModeToggle" class="material-icons mode-toggle">
            dark_mode
        </span>
    </div>
</header>
</body>

</html>