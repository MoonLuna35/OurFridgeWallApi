<?php 
 use PHPMailer\PHPMailer\PHPMailer;
 require 'vendor/autoload.php';
    include_once 'CONST.php';
    include_once "model/user/User.php";
 
    function send_mail($code, $tu, $activation_code = null) {
        $mail = new PHPMailer; 
 
        $mail->isSMTP();                      // Set mailer to use SMTP 
        $mail->Host = $_ENV['MAIL_HOST'];       // Specify main and backup SMTP servers 
        $mail->SMTPAuth = true;               // Enable SMTP authentication 
        $mail->Username = $_ENV['MAIL_USERNAME'];   // SMTP username 
        $mail->Password = $_ENV['MAIL_PASS'];   // SMTP password 
        $mail->SMTPSecure = 'tls';            // Enable TLS encryption, `ssl` also accepted 
        $mail->Port = $_ENV['MAIL_PORT'];                    // TCP port to connect to 
 
        // Sender info 
        $mail->setFrom('sing_in@thefridgewall.fr', 'The Fridge Wall');  
 
        // Add a recipient 
        $mail->addAddress('gwen.dujet@gmail.com'); 
 
        //$mail->addCC('cc@example.com'); 
        //$mail->addBCC('bcc@example.com'); 
 
        // Set email format to HTML 
        $mail->isHTML(true); 
 
        // Mail subject 
        $mail->Subject = 'Derniere etape pour {{ your }} inscription'; 
 
        // Mail body content 
        $bodyContent = '<h1>{{ Finalize }} {{ your }} inscription</h1>'; 
        $bodyContent .= '<p>Bonjour,</p>'; 
        $bodyContent .= '<p>{{ You_can }} cliquer sur ce lien pour activer {{ your }} compte</p>'; 
        $bodyContent .= '<p><a target="_blank" href="' . $_ENV['SITE_URL'] . '/activate-account/' . $activation_code .'">' . $_ENV['SITE_URL'] . getenv('SITE_URL') . '/activate-account/' . $activation_code .'</a></p>'; 
        

        if ($tu) {
            $mail->Subject = str_replace("{{ your }}", "ton", $mail->Subject);
            $bodyContent = str_replace("{{ your }}", "ton", $bodyContent);
            $bodyContent = str_replace("{{ Finalize }}", "Finalise", $bodyContent);
            $bodyContent = str_replace("{{ You_can }}", "Tu peut", $bodyContent);
        } 
        else {
            $mail->Subject = str_replace("{{ your }}", "votre", $mail->Subject);
            $bodyContent = str_replace("{{ your }}", "votre", $bodyContent);
            $bodyContent = str_replace("{{ Finalize }}", "Finalisez", $bodyContent);
            $bodyContent = str_replace("{{ You_can }}", "Vous pouvez", $bodyContent);
        } 

        $mail->Body   = $bodyContent; 
 
        // Send email 
        if(!$mail->send()) { 
            return false;
        } else { 
            return true; 
        } 
    }

    function send_new_log($user) {
        $mail = new PHPMailer; 
 
        $mail->isSMTP();                      // Set mailer to use SMTP 
        $mail->Host = $_ENV['MAIL_HOST'];       // Specify main and backup SMTP servers 
        $mail->SMTPAuth = true;               // Enable SMTP authentication 
        $mail->Username = $_ENV['MAIL_USERNAME'];   // SMTP username 
        $mail->Password = $_ENV['MAIL_PASS'];   // SMTP password 
        $mail->SMTPSecure = 'tls';            // Enable TLS encryption, `ssl` also accepted 
        $mail->Port = $_ENV['MAIL_PORT'];                    // TCP port to connect to 
 
        // Sender info 
        $mail->setFrom('log@thefridgewall.fr', 'The Fridge Wall');  
 
        // Add a recipient 
        $mail->addAddress('gwen.dujet@gmail.com'); 
 
        //$mail->addCC('cc@example.com'); 
        //$mail->addBCC('bcc@example.com'); 
 
        // Set email format to HTML 
        $mail->isHTML(true); 
 
        // Mail subject 
        $mail->Subject = 'Connexion depuis un nouvel emplacement'; 
 
        // Mail body content 
        $bodyContent = '<h1>C\'est bien vous ? </h1>'; 
        $bodyContent .= '<p>Bonjour,</p>'; 
        $bodyContent .= '<p>On a détecter une connexion depuis un nouvel emplacement</p>'; 
        $bodyContent .= '<p>Vous tentez bien de vous connecté depuis le pays : {{ country }} avec  {{ OS }} comme OS ?</p>';
        $bodyContent .= '<p><a target="_blank" href="' . $_ENV['SITE_URL'] . '/new-device/' . $user->first_log_token() . '">Oui, c\'est bien moi</a></p>'; 
        $bodyContent .= '<p>Si ce n\'est pas vous, nous vous invitons à modifier votre mot de passe.</p>';
        $bodyContent .= '<p>A très vite</p>';
        

        $bodyContent = str_replace("{{ OS }}", "Linux", $bodyContent);
        $bodyContent = str_replace("{{ country }}", "France", $bodyContent);
        
        $mail->Body   = $bodyContent; 
        // Send email 
        if(!$mail->send()) { 
            return false;
        } else { 
            return true; 
        } 
    }


 
?>