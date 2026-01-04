<?php
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    require('tfpdf/tfpdf.php');
    require('db_connect.php');

    session_start();
    if (!isset($_SESSION['admin_id'])) exit('Ni dostopa');

    class PDF extends tFPDF {
        public $fontsReady = false;

        function Header() {
            if ($this->fontsReady) {
                $this->AddFont('DejaVu', 'B', 'DejaVuSans-Bold.ttf', true);
                $this->SetFont('DejaVu', 'B', 16);
                $this->SetTextColor(39, 174, 96); 
                $this->Cell(0, 10, 'SHELTER COMPASS - CELOTNO POROČILO', 0, 1, 'C');
                
                $this->SetDrawColor(39, 174, 96);
                $this->Line(10, 22, 200, 22);
                
                $this->SetFont('DejaVu', '', 9);
                $this->SetTextColor(100, 100, 100);
                $this->Cell(0, 10, 'Generirano: ' . date('d. m. Y H:i'), 0, 1, 'R');
                $this->Ln(2);
            }
        }

        function Footer() {
            $this->SetY(-15);
            $this->SetFont('DejaVu', '', 8);
            $this->SetTextColor(150, 150, 150);
            $this->Cell(0, 10, 'Stran ' . $this->PageNo() . '/{nb} - ShelterCompass Admin', 0, 0, 'C');
        }
    }

    $pdf = new PDF();
    $pdf->AliasNbPages();
    $pdf->AddFont('DejaVu', '', 'DejaVuSansCondensed.ttf', true);
    $pdf->AddFont('DejaVu', 'B', 'DejaVuSans-Bold.ttf', true);
    $pdf->fontsReady = true;

    $zelena_bg = [76, 175, 80];
    //1. STRAN: STANJE ŽIVALI
    $pdf->AddPage();
    $pdf->SetFont('DejaVu', 'B', 13);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Cell(0, 10, '1. Pregled živali v zavetišču', 0, 1, 'L');
    $pdf->Ln(2);

    $pdf->SetFont('DejaVu', 'B', 10);
    $pdf->SetFillColor($zelena_bg[0], $zelena_bg[1], $zelena_bg[2]);
    $pdf->SetTextColor(255, 255, 255);

    $pdf->Cell(45, 8, 'Ime živali', 1, 0, 'C', true);
    $pdf->Cell(40, 8, 'Vrsta', 1, 0, 'C', true);
    $pdf->Cell(65, 8, 'Status', 1, 0, 'C', true);
    $pdf->Cell(40, 8, 'Starost', 1, 1, 'C', true);

    $pdf->SetFont('DejaVu', '', 9);
    $pdf->SetTextColor(50, 50, 50);
    $fill = false;

    $sql_zivali = "SELECT z.ime, v.imeVrste, s.vrstaStatusa, z.starost 
                FROM Zival z
                LEFT JOIN Vrsta v ON z.TK_vrsta = v.ID_vrsta
                LEFT JOIN Status s ON z.TK_status = s.ID_status
                ORDER BY z.ime ASC";
    $zivali = $pdo->query($sql_zivali)->fetchAll();

    foreach ($zivali as $row) {
        $pdf->SetFillColor(245, 245, 245);
        $pdf->Cell(45, 7, $row['ime'], 1, 0, 'L', $fill);
        $pdf->Cell(40, 7, $row['imeVrste'], 1, 0, 'L', $fill);
        $pdf->Cell(65, 7, $row['vrstaStatusa'], 1, 0, 'L', $fill);
        $pdf->Cell(40, 7, $row['starost'], 1, 1, 'C', $fill);
        $fill = !$fill;
    }

    $pdf->Ln(10);
    //2. STRAN: POVPRAŠEVANJA
    $pdf->SetFont('DejaVu', 'B', 13);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Cell(0, 10, '2. Zgodovina povpraševanj', 0, 1, 'L');
    $pdf->Ln(2);

    $pdf->SetFont('DejaVu', 'B', 10);
    $pdf->SetFillColor($zelena_bg[0], $zelena_bg[1], $zelena_bg[2]);
    $pdf->SetTextColor(255, 255, 255);

    $pdf->Cell(40, 8, 'Uporabnik', 1, 0, 'C', true);
    $pdf->Cell(40, 8, 'Žival', 1, 0, 'C', true);
    $pdf->Cell(35, 8, 'Datum', 1, 0, 'C', true);
    $pdf->Cell(75, 8, 'Status povpraševanja', 1, 1, 'C', true);

    $pdf->SetFont('DejaVu', '', 9);
    $pdf->SetTextColor(50, 50, 50);

    $sql_povp = "SELECT u.ime, u.priimek, z.ime as zival_ime, p.datumOddaje, p.statusPovprasevanja 
                FROM Povprasevanje p
                JOIN Uporabnik u ON p.TK_uporabnik = u.ID_uporabnik
                JOIN Zival z ON p.TK_zival = z.ID_zival
                ORDER BY p.datumOddaje DESC";
    $povprasevanja = $pdo->query($sql_povp)->fetchAll();

    $fill = false;
    foreach ($povprasevanja as $p) {
        $pdf->SetFillColor(245, 245, 245);
        $uporabnik = $p['ime'] . ' ' . $p['priimek'];
        $datum = date('d.m.Y', strtotime($p['datumOddaje']));
        
        $st = $p['statusPovprasevanja'];
        if($st == 'v_cakanju' || empty($st)) $st = 'V čakanju';
        
        $pdf->Cell(40, 7, $uporabnik, 1, 0, 'L', $fill);
        $pdf->Cell(40, 7, $p['zival_ime'], 1, 0, 'L', $fill);
        $pdf->Cell(35, 7, $datum, 1, 0, 'C', $fill);
        $pdf->Cell(75, 7, $st, 1, 1, 'L', $fill);
        $fill = !$fill;
    }

    if (ob_get_contents()) ob_end_clean();
    $pdf->Output('D', 'Porocilo_stanja.pdf');
   exit();
?>