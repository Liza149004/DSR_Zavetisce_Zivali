<?php
    require 'db_connect.php'; 

    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;
    use PHPMailer\PHPMailer\SMTP;

    require 'PHPMailer/src/Exception.php';
    require 'PHPMailer/src/PHPMailer.php';
    require 'PHPMailer/src/SMTP.php';

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        //pridobivanje podatkov iz obrazca
        $animal_id = filter_input(INPUT_POST, 'animal_id', FILTER_VALIDATE_INT);
        $user_email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
        $full_name = filter_input(INPUT_POST, 'fullName', FILTER_SANITIZE_SPECIAL_CHARS);
        $user_message = filter_input(INPUT_POST, 'sporocilo', FILTER_SANITIZE_SPECIAL_CHARS);

        if (!$animal_id || !$user_email || !$full_name) {
            header("Location: profil_zivali.php?id=$animal_id&status=error&message=Manjkajoči+podatki.");
            exit();
        }
        try {
            $pdo->beginTransaction();

        $stmt_check = $pdo->prepare("SELECT ID_uporabnik FROM Uporabnik WHERE email = :email");
            $stmt_check->execute([':email' => $user_email]);
            $existing_user_id = $stmt_check->fetchColumn();

            if ($existing_user_id) {
                $nov_user_id = $existing_user_id; //uporaba obstoječega id če obstaja
            } else {
                //drugače ustvarimo novega
                $ime_priimek = explode(' ', $full_name, 2);
                $ime = $ime_priimek[0];
                $priimek = isset($ime_priimek[1]) ? $ime_priimek[1] : '';

                $sql_user = "INSERT INTO Uporabnik (ime, priimek, email, TK_tip_uporabnika) 
                            VALUES (:ime, :priimek, :email, 1)";
                $stmt_user = $pdo->prepare($sql_user);
                $stmt_user->execute([':ime' => $ime, ':priimek' => $priimek, ':email' => $user_email]);
                $nov_user_id = $pdo->lastInsertId();
            }
            //pridobitev imena živali za mail
            $stmt_name = $pdo->prepare("SELECT ime FROM Zival WHERE ID_zival = :id");
            $stmt_name->execute([':id' => $animal_id]);
            $animal_name = $stmt_name->fetchColumn() ?: 'izbrano žival';

            $sql_povp = "INSERT INTO Povprasevanje (datumOddaje, sporocilo, TK_uporabnik, TK_zival) 
                        VALUES (NOW(), :sporocilo, :user_id, :animal_id)";
            $stmt_povp = $pdo->prepare($sql_povp);
            //tu se podatek dejansko shrani v bazo
            $stmt_povp->execute([
                ':sporocilo' => $user_message,
                ':user_id'   => $nov_user_id, 
                ':animal_id' => $animal_id
            ]);

            $pdo->commit(); //potrdi vnos v bazo
            //pošiljanje mail-a
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            
            $moj_gmail = 'info.sheltercompass@gmail.com'; 
            $mail->Username   = $moj_gmail;
            $mail->Password   = 'abkdfepyfjqynjbr';
            
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;
            $mail->CharSet    = 'UTF-8';

            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );

            $mail->setFrom($moj_gmail, 'Zavetišče ShelterCompass');
            $mail->addAddress($user_email, $full_name); 
            $mail->addReplyTo($user_email, $full_name);

            $mail->isHTML(true);
            $mail->Subject = 'Potrditev povpraševanja za žival: ' . $animal_name;
            $mail->Body    = "
                <div style='background-color: #f4f7f6; padding: 30px; font-family: sans-serif;'>
                    <div style='max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 15px; overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.1); border: 1px solid #e0e0e0;'>
                        <div style='background-color: #4CAF50; padding: 20px; text-align: center; color: white;'>
                            <h1 style='margin: 0; font-size: 24px;'>ShelterCompass</h1>
                            <p style='margin: 5px 0 0 0; opacity: 0.9;'>Hvala za vaše zanimanje!</p>
                        </div>
                        <div style='padding: 30px; color: #333333; line-height: 1.6;'>
                            <h2 style='color: #4CAF50; margin-top: 0;'>Pozdravljeni, $full_name!</h2>
                            <p>Z veseljem vas obveščamo, da smo prejeli vaše povpraševanje za našega varovanca z imenom <strong>$animal_name</strong>.</p>
                            <div style='background-color: #f9f9f9; border-left: 4px solid #4CAF50; padding: 15px; margin: 20px 0;'>
                                <p style='margin: 0;'><strong>Povzetek povpraševanja:</strong></p>
                                <ul style='margin: 10px 0 0 0; padding-left: 20px;'>
                                    <li><strong>Ime živali:</strong> $animal_name</li>
                                    <li><strong>Kontaktni email:</strong> $user_email</li>
                                    <li><strong>Datum:</strong> ".date('d. m. Y')."</li>
                                </ul>
                            </div>
                            <p>Naša ekipa bo pregledala vaše podatke in vas kontaktirala v najkrajšem možnem času.</p>
                            <div style='text-align: center; margin-top: 30px;'>
                                <a href='http://localhost:8000/index.php' style='background-color: #4CAF50; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; font-weight: bold;'>Nazaj na spletno stran</a>
                            </div>
                        </div>
                        <div style='background-color: #eeeeee; padding: 15px; text-align: center; font-size: 12px; color: #777777;'>
                            <p style='margin: 0;'>To sporočilo je bilo poslano samodejno s strani ShelterCompass.</p>
                        </div>
                    </div>
                </div>";

            $mail->send();

            //mail 2 - obvestilo zavetišču (meni)
            $mail->clearAddresses(); //pobriše prejšnjega prejemnika
            $mail->addAddress($moj_gmail, 'Admin ShelterCompass'); //prejemnik postanem jaz, basically admin
            $mail->addReplyTo($user_email, $full_name); //če klikneš 'Odgovori', pa pišeš uporabniku

            $mail->Subject = 'NOVO POVPRAŠEVANJE: ' . $animal_name;
            $mail->Body    = "
                <div style='font-family: sans-serif; padding: 20px; border: 2px solid #4CAF50;'>
                    <h2 style='color: #4CAF50;'>Prejeto je novo povpraševanje!</h2>
                    <p><strong>Uporabnik:</strong> $full_name ($user_email)</p>
                    <p><strong>Žival:</strong> $animal_name (ID: $animal_id)</p>
                    <hr>
                    <p><strong>Sporočilo uporabnika:</strong></p>
                    <div style='background: #f0f0f0; padding: 10px; font-style: italic;'>
                        " . nl2br($user_message) . "
                    </div>
                    <hr>
                    <p>Na to sporočilo lahko odgovoriš neposredno uporabniku s klikom na gumb 'Odgovori'.</p>
                </div>";

            $mail->send();

            header("Location: profil_zivali.php?id=$animal_id&status=success&message=Uspešno+oddano!");
            exit();

        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack(); //če gre karkoli narobe, prekliče vnose v bazo
            }
            $napaka = urlencode($mail->ErrorInfo);
            header("Location: profil_zivali.php?id=$animal_id&status=warning&message=Napaka:+ $napaka");
            exit();
        }
    }
?>