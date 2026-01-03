<?php
use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;
    use PHPMailer\PHPMailer\SMTP;

    require 'PHPMailer/src/Exception.php';
    require 'PHPMailer/src/PHPMailer.php';
    require 'PHPMailer/src/SMTP.php';

    if (isset($_GET['akcija']) && isset($_GET['id_povp'])) {
        $id = $_GET['id_povp'];
        $akcija = $_GET['akcija'];
        
        $termin_raw = isset($_GET['termin']) ? $_GET['termin'] : '';
        $termin = !empty($termin_raw) ? date('d. m. Y \ob H:i', strtotime($termin_raw)) : 'po dogovoru';
        
        $stmt_info = $pdo->prepare("SELECT u.email, u.ime, z.ime as zival_ime, z.ID_zival 
                                    FROM Povprasevanje p 
                                    JOIN Uporabnik u ON p.TK_uporabnik = u.ID_uporabnik 
                                    JOIN Zival z ON p.TK_zival = z.ID_zival 
                                    WHERE p.ID_povprasevanje = ?");
        $stmt_info->execute([$id]);
        $podatki = $stmt_info->fetch();

    if ($podatki) {
            $to_email = $podatki['email'];
            $ime_uporabnika = $podatki['ime'];
            $ime_zivali = $podatki['zival_ime'];
            $id_zivali = $podatki['ID_zival'];
            //nastavitve za PHPMailer
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $moj_gmail        = 'info.sheltercompass@gmail.com'; 
                $mail->Username   = $moj_gmail;
                $mail->Password   = 'abkdfepyfjqynjbr';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;
                $mail->CharSet    = 'UTF-8';

                $mail->setFrom($moj_gmail, 'Zavetišče ShelterCompass');
                $mail->addAddress($to_email, $ime_uporabnika);

                if ($akcija == 'sprejmi') {
                    $status = 'Sprejeto';
                    $subject = "Dobre novice! Vaše povpraševanje za $ime_zivali je sprejeto";
                    //stilizirano HTML sporočilo - mail
                    $mail_body = "
                    <div style='background-color: #f4f7f6; padding: 30px; font-family: sans-serif;'>
                        <div style='max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 15px; border: 1px solid #e0e0e0; overflow: hidden;'>
                            <div style='background-color: #27ae60; padding: 20px; text-align: center; color: white;'>
                                <h1 style='margin: 0;'>Čestitamo!</h1>
                            </div>
                            <div style='padding: 30px; color: #333;'>
                                <h2 style='color: #4CAF50;'>Pozdravljeni, $ime_uporabnika!</h2>
                                <p>Z veseljem vas obveščamo, da je bilo vaše povpraševanje za posvojitev našega varovanca z imenom <strong>$ime_zivali</strong> odobreno.</p>
                                <div style='background-color: #f9f9f9; border-left: 4px solid #4CAF50; padding: 15px; margin: 20px 0;'>
                                    <p style='margin: 0;'><strong>Vaš termin za obisk:</strong></p>
                                    <p style='font-size: 1.2rem; color: #4CAF50; margin: 10px 0;'>📅 $termin</p>
                                </div>
                                <p>Prosimo, da v primeru zadržanosti odgovorite na ta mail.</p>
                            </div>
                        </div>
                    </div>";

                    $pdo->prepare("UPDATE Zival SET TK_status = (SELECT ID_status FROM Status WHERE vrstaStatusa LIKE '%Rezerviran%' LIMIT 1) WHERE ID_zival = ?")
                        ->execute([$id_zivali]);
                } else {
                    $status = 'Zavrnjeno';
                    $subject = "Obvestilo o povpraševanju za žival $ime_zivali";
                    
                    $mail_body = "
                    <div style='background-color: #f4f7f6; padding: 30px; font-family: sans-serif;'>
                        <div style='max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 15px; border: 1px solid #e0e0e0;'>
                            <div style='padding: 30px; color: #333;'>
                                <h2>Pozdravljeni, $ime_uporabnika</h2>
                                <p>Žal vas moramo obvestiti, da vaše povpraševanje za <strong>$ime_zivali</strong> tokrat ni bilo izbrano.</p>
                                <p>Vabimo vas, da spremljate našo stran še naprej za ostale živali, ki iščejo dom.</p>
                                <p>Lep pozdrav, ekipa ShelterCompass</p>
                            </div>
                        </div>
                    </div>";
                }
                //pošiljanje maila
                $mail->isHTML(true);
                $mail->Subject = $subject;
                $mail->Body    = $mail_body;
                $mail->send();
                //posodobitev statusa v bazi
                $stmt_update = $pdo->prepare("UPDATE Povprasevanje SET statusPovprasevanja = ? WHERE ID_povprasevanje = ?");
                $stmt_update->execute([$status, $id]);

            } catch (Exception $e) {
                //v primeru napake lahko dodaš logiranje ali obvestilo
                header("Location: admin_index.php?napaka=" . urlencode($mail->ErrorInfo));
                exit();
            }
        }
        
        header("Location: admin_index.php?uspeh=1");
        exit();
    }
?>