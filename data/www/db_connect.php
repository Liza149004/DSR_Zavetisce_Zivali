<?php
// Nastavitve za povezavo na podatkovno bazo
// SPREMENJENO: Namesto 'localhost' uporabimo ime Docker storitve za MySQL
$host = "mysql"; // <--- TO JE POPRAVEK: Uporabite ime storitve iz docker-compose.yml
$db = "Zavetisce_za_zivali"; // Ime baze, ki ste jo ustvarili z SQL kodo
$user = "root"; // Uporabnik
$pass = "superVarnoGeslo"; // Geslo, nastavljeno v docker-compose.yml (MYSQL_ROOT_PASSWORD)
$charset = "utf8mb4"; 

// String za podatke o povezavi (DSN)
// Port 3306 znotraj Docker omrežja ni potreben, ker se bo povezal avtomatično, 
// a ga lahko vključite, če želite biti eksplicitni.
$dsn = "mysql:host=$host;dbname=$db;charset=$charset"; 

// Nastavitve možnosti za PDO
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
     $pdo = new PDO($dsn, $user, $pass, $options);
     
} catch (\PDOException $e) {
     // Če še vedno javlja napako, preverite, ali se ime '$host' ujema z imenom MySQL storitve
     die("Napaka pri povezovanju z bazo: " . $e->getMessage()); 
}
?>