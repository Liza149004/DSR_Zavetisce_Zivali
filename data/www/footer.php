<html>
    <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ShelterCompass - Poišči Svojega Popolnega Spremljevalca</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    </head>
    <body>
<footer>
    <div class="footer-content">
        <div class="contact-info">
            <h2>Obiščite Nas</h2>
            <p>123 Shelter Lane</p>
            <p>Springfield, ST 12345</p>
            <p>Telefon: (555) 123-4567</p>
            <p>E-mail: info@sheltercompass.org</p>
        </div>
        
        <div class="location-map">
            <h2>Naša Lokacija</h2>
            <div class="map-placeholder">
                <div class="map-controls">
                    <button>+</button>
                    <button>−</button>
                </div>
                <div class="map-footer-text">
                    Prijavi težavo | &copy; OpenStreetMap contributors
                </div>
            </div>
        </div>
    </div>

    <div class="copyright">
        <p>&copy; <?php echo date("Y"); ?> ShelterCompass. Vse pravice pridržane.</p>
    </div>
</footer>

<script>
    // Uporaba PHP spremenljivke, ki je nastavljena v index.php
    document.getElementById('showing-info').textContent = "Prikazanih <?php echo $animal_count; ?> živali";
</script>

</body>
</html>