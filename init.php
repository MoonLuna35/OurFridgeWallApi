<?php
    require 'vendor/autoload.php';
    require 'CONST.php';
    
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();

    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        // The request is using the POST method
        return http_response_code(200);
    
    }

    function log500($file, $line) {
        $date = new DateTime();
        $date = $date->format('d-m-Y at H:i');
        print_r("500 call from <b>$file</b> on line : <b>$line</b>" );
        header('HTTP/1.1 500 Internal Server Error');
        exit;
    }

    
    function log400($file, $line) {
        $date = new DateTime();
        $date = $date->format('d-m-Y at H:i');
        print_r("400 call from <b>$file</b> on line : <b>$line</b>" );
        header('HTTP/1.1 400 Bad Request');
        exit;
    }

    function log503($file, $line) {
        $date = new DateTime();
        $date = $date->format('d-m-Y at H:i');
        print_r("$date : <b>503 Service Unavailable</b> call from <b>$file</b> on line : <b>$line</b>" );
        header('HTTP/1.1 500 Service Unavailable');
        exit;
    }

    function validateDateTime($date, $format = 'Y-m-d H:i') {
        $d = DateTime::createFromFormat($format, $date);
        // The Y ( 4 digits year ) returns TRUE for any integer with any number of digits so changing the comparison from == to === fixes the issue.
        return $d && $d->format($format) === $date;
    }
    function validateDate($date, $format = 'Y-m-d') {
        $d = DateTime::createFromFormat($format, $date);
        // The Y ( 4 digits year ) returns TRUE for any integer with any number of digits so changing the comparison from == to === fixes the issue.
        return $d && $d->format($format) === $date;
    }

    class RegExp {
        const REG_ALPHANUM_PONCT = "/^[\pL|_|-|\d| |?|.|,|:|;]{3,}$/"; 
    }

   
    
