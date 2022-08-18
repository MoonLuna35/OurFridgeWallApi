<?php 

    require '../init.php';
    require '../model/user/userDB.php';
    require '../mail.php';
    use Firebase\JWT\JWT;
    // Get the posted data. 
    $postdata = file_get_contents("php://input");
    $data_decoded = null;
    $fst_log_token =  null;
    $user_db = new UserDb();
    $user_logged;

    $now = new DateTimeImmutable();
    $refresh_expire_at = $now->modify('+3 months')->getTimestamp();
    $device_expire_at = $now->modify('+10 years')->getTimestamp();
    
    if(isset($postdata) && !empty($postdata)) { 
        
        // Extract the data.
        $data_decoded = json_decode($postdata);
        if (isset($data_decoded->data->first_log_token) && $data_decoded->data->first_log_token !=="") { //SI le token existe et qu'il n'est pas vide ALORS
            $fst_log_token = $data_decoded->data->first_log_token;
            
            try { //ESSAYER de décoder 
                $decoded = JWT::decode($fst_log_token, PUBLIC_KEY, array('RS256'));
                if (
                    $decoded->iss !== "theFridgeDoor.fr" ||
                    $decoded->aud !== "theFridgeDoor.fr" ||
                    $decoded->nbf > $now->getTimestamp() ||
                    $decoded->exp < $now->getTimestamp())
                {
                    header('HTTP/1.1 401 Unauthorized');
                    exit;
                }
                else { //SI il est valide ALORS
                    
                    $user_logged = $user_db->get_user_by_first_log ($decoded->id, $fst_log_token); //On connecte l'utilisateur
                    if ($user_logged !=="NO USER") {//SI on y arrive (il n'a pas été ban, ni kick) ALORS
                        $user_logged->generate_token();  //On génére le token d'accès
                        $user_logged->generate_refresh_token();
                        $user_logged->generate_device_token();
                        $user_db->add_new_refresh_token("", $user_logged->refresh_token(), $user_logged); //On génère un token de rafraichissement
                        
                        setcookie("refresh", $user_logged->refresh_token(), $refresh_expire_at, "/OurFridgeWall", "localhost", false, true); //On envoie le cookie de rafraichissement
                        setcookie("user" . $user_logged->get_id(), $user_logged->get_device_token(), $device_expire_at, "/OurFridgeWall", "localhost", false, true);//On envoie un cookie avec une très longue durée de vie contenant un JWT indiquant que l'utilisateur a déjà utilisé la machine 
                        $user_db->delete_first_log_token($user_logged, $fst_log_token);//On supprime le token de 1ère connexion de la base de donnée.
                        echo json_encode(array("data" =>  $user_logged->to_array(true)));
                    }
                    
                    else { //SINON
                        
                        header('HTTP/1.1 401 Unauthorized'); //On autorise pas la connexion
                        exit;//On redirige l'utilsisateur vers la page de connexion
                    }
                }
            }
            catch (Exception $e) { //SI le token est invalide ou expirer
                print_r($e);
                
                header('HTTP/1.1 401 Unauthorized'); //On autorise pas la connexion
                exit;//On redirige l'utilsisateur vers la page de connexion
            }
        }
        else {
            header('HTTP/1.1 400 Bad Request'); //On autorise pas la connexion
            exit;//On redirige l'utilsisateur vers la page de connexion
        }
        
    }


   