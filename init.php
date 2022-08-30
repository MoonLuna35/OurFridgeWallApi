<?php
    require 'vendor/autoload.php';
    require 'CONST.php';
    
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();

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

    function validateDate($date, $format = 'Y-m-d H:i') {
        $d = DateTime::createFromFormat($format, $date);
        // The Y ( 4 digits year ) returns TRUE for any integer with any number of digits so changing the comparison from == to === fixes the issue.
        return $d && $d->format($format) === $date;
    }

   
    
