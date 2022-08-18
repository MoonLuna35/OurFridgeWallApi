<?php
    require 'vendor/autoload.php';
    require 'CONST.php';
    
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();

    function log_error500(int $error_code, int $user, String $file) {
        switch ($error_code) {
            //list : 2
            //type : 3
            //add : 1
            case 231 : 
                $error_str = "insert new type fail.";
            break;
            //remove 2 
            case 2321 : 
                $error_str = "delete type fail.";
            break;
            case 2322 : 
                $error_str = "delete type img fail.";
            break;
            //fuse 3
            case 233 : 
                $error_str = "cuppboard in list has too many lines.";
            break; 
        }
        $now = new DateTime();
        $out = $now->format('d-m-Y H:i:s') . ": user : $user : $error_str from $file" ;
        file_put_contents(
            $_ENV["LOG_PATH"] . "500.log",
            $out,
            FILE_APPEND
        );
        print_r($out);
    }

    
