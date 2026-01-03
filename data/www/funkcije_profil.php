<?php
    //funkcija za prikaz zdravstvenega statusa (cepljenje, sterilizacija)
    function display_health_status($value, $text) {
        if ($value) {
            $class = 'checked';
            $icon = 'check';
        } else {
            $class = 'unchecked';
            $icon = 'close';
        }
        return "<span class='health-badge $class'><span class='material-icons'>$icon</span> $text</span>";
    }
    //funkcija za določanje CSS razreda glede na status živali
    function get_status_class($status_raw) {
        $status_lower = strtolower(trim($status_raw));
        switch ($status_lower) {
            case 'aktiven':
                return 'available';
            case 'posvojen':
                return 'adopted';
            case 'rezerviran':
                return 'reserved';
            case 'neaktiven':
                return 'inactive';
            default:
                return 'in-care';
        }
    }
    //funkcija za generiranje Google QR kode URL-ja
    function get_qr_code_url() {
        //sestavi celoten URL trenutne strani
        $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
        $host = $_SERVER['HTTP_HOST'];
        $uri = $_SERVER['REQUEST_URI'];
        
        $current_url = $protocol . "://" . $host . $uri;
        //uporabi goqr.me API
        return "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=" . urlencode($current_url);
    }
    //funkcija za logiko gumba in opozoril (disclaimerjev)
    function get_inquiry_logic($status_raw) {
        $status_lower = strtolower(trim($status_raw));
        $logic = [
            'can_inquire' => true,
            'disclaimer' => "",
            'button_text' => "Oddaj povpraševanje"
        ];

        if ($status_lower === 'posvojen' || $status_lower === 'neaktiven') {
            $logic['can_inquire'] = false;
            $logic['button_text'] = "Ni več na voljo";
        } elseif ($status_lower === 'v oskrbi') {
            $logic['disclaimer'] = "Žival je trenutno še v oskrbi pri testni družini, vendar že sprejemamo povpraševanja.";
        } elseif ($status_lower === 'rezerviran') {
            $logic['disclaimer'] = "Za to žival je že bil izbran potencialni posvojitelj, vendar nas lahko še vedno kontaktirate.";
        }

        return $logic;
    }
?>