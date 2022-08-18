<?php

require '../init.php';
require '../model/user/userDB.php';
require '../mail.php';
use Firebase\JWT\JWT;
// Get the posted data.
$postdata = file_get_contents("php://input");
$user_db = new UserDb();
$user_logged;
$imutableTime = new DateTimeImmutable();
$refresh_expire_at = $imutableTime->modify('+1 year')->getTimestamp(); //L'utilisateur a cliquer sur "rester connecté"
if(isset($postdata) && !empty($postdata))
{ 
  // Extract the data.
  $user = json_decode($postdata);
	
  

  // Validate.
    if(
      (!isset($user->data->mail) || trim($user->data->mail) === '') ||
      (!isset($user->data->pass) || trim($user->data->pass) === '')  
    ){
        return http_response_code(400);
    }

    if (
        strlen(trim($user->data->pass)) < 8 || 
        preg_match('/[0-9]/', trim($user->data->pass)) === 0 ||
        preg_match('/[a-z]/', trim($user->data->pass)) === 0 ||
        preg_match('/[A-Z]/', trim($user->data->pass)) === 0 ||
        preg_match('/[\W_]/', trim($user->data->pass)) === 0    
    ) {//SI le pass ne convient pas aux normes de sécurité ALORS
        return http_response_code(400); //On renvoie une erreur
    }

    if (!filter_var(trim($user->data->mail), FILTER_VALIDATE_EMAIL)) {  //SI le mail n'est pas du bon format ALORS
        return http_response_code(400); //On renvoie une erreur//On renvoie une erreur
    }

    $user_logged = $user_db->log_user($user->data->mail, $user->data->pass);
    if ( //SI l'utilisateur est connecté ALORS
        $user_logged != "NO USER" && 
        $user_logged->pass_is_matched($user->data->pass)
    ) {
        
        if (!isset($_COOKIE["user" . $user_logged->get_id()])) {//SI l'utilsateur ne c'est jamais connecté avec cette machine (il n'a pas de cookie longue durée de vie) ALORS
            
            $user_logged->generate_first_log_token();//On génère un JWT de 30 minutes pour qu'il active sa connexion
            $user_db->add_new_first_log_token($user_logged);//On l'ajoute dans la base de données 
            //SI c'est bon (l'utilisateur n'a pas tenté de se connecter + de 3 fois sans valider sa machine dans les 30 minutes) ALORS
                send_new_log($user_logged);//On lui envoie par mail avec son OS et le pays où est situé la machine
                echo json_encode(array("data" => array("status" => "NEW PLACE")));
        }
        else { //SINON
            $user_logged->generate_token();  //On génére le token d'accès
            if (isset($_COOKIE["refresh"])) {//SI l'utilisateur a déjà un token de rafraichissement
                $old_refresh_token = $_COOKIE["refresh"];  //On tente de le metre à jour
            }
            else { //SINON
                $old_refresh_token = "";//On en crée un nouveau
            }
            $user_db->add_new_refresh_token($old_refresh_token, $user_logged->generate_refresh_token(), $user_logged); //On ajoute le token de rafreaichissment dans la base de donnée
            setcookie("refresh", $user_logged->refresh_token(), $refresh_expire_at, "/OurFridgeWall", "localhost", false, true); //On envoie le cookie de rafraichissement
            echo json_encode(array("data" =>  $user_logged->to_array(true)));
        } 
       
        
    }
    else if ($user_logged === "NO USER") {
        echo json_encode(array("data" => array("status" => "no mail")));
    }
    else if (
        $user_logged != "NO USER" && 
        !$user_logged->pass_is_matched($user->data->pass)
    ) {
        echo json_encode(array("data" => array("status" => "pass faillure")));
    }
    else {
        header('HTTP/1.1 401 Unauthorized');
        exit;//On renvoie l'utilisateur vers la page de connexion
    }
    
}