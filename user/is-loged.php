<?php 
    define('__ROOT__', dirname(dirname(__FILE__)));

    require __ROOT__."/model/user/userDB.php";

    use Firebase\JWT\JWT;
    
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        // The request is using the POST method
        
        return http_response_code(200);
    
    }
    $user_returned = new User(array());
    
    $current_user = null;
    $output["data"] = array();
    function isLoggedIn(&$current_user) {
        $now = new DateTimeImmutable();
        $imutableTime = new DateTimeImmutable();
        $refresh_expire_at = $now->modify('+1 year')->getTimestamp(); //L'utilisateur a cliquer sur "rester connecté"
        $headers = getallheaders();
        
        if (!isset($headers['Authorization'])) {
            return http_response_code(401);
        }
        else {
            $splited_auth = explode(" ", $headers['Authorization']);
            if (sizeof($splited_auth) !== 2) {
                
                return http_response_code(401);
            }
            else {
                
                if($splited_auth[0] !== "Bearer") {
                    
                    return http_response_code(401);
                }
            }
            
            $token = $splited_auth[1];
            
            try {
                
                $decoded = JWT::decode($token, PUBLIC_KEY, array('RS256'));
                if (
                    $decoded->iss !== "theFridgeDoor.fr" ||
                    $decoded->aud !== "theFridgeDoor.fr" ||
                    $decoded->nbf > $now->getTimestamp() ||
                    $decoded->exp < $now->getTimestamp())
                {
                    
                    return http_response_code(401);
                }
                else {
                    
                    $current_user = new User(array(
                        "id" => $decoded->id,
                        "house" => $decoded->house,
                        "is_root" => $decoded->is_root,
                        "is_prenium" => $decoded->is_premium
                    ));
                    
                }
                
            }
            catch (Exception $e) {
                
                if ($e->getMessage() === "Expired token") { //SI le token est expiré MAIS valide ALORS
                    if(isset($_COOKIE["refresh"])) { //SI il y a un token de rafraichissement
                        $refresh_token = $_COOKIE["refresh"];
                        try {//SI il est valide 
                            
                            $decoded_refresh = JWT::decode($refresh_token, PUBLIC_KEY, array('RS256'));
                            if ( //SI le token de rafraichissement ne contients pas des valeurs corrects ALORS
                                $decoded_refresh->iss !== "theFridgeDoor.fr" ||
                                $decoded_refresh->aud  !== "theFridgeDoor.fr" ||
                                $decoded_refresh->nbf > $now->getTimestamp() ||
                                $decoded_refresh->exp < $now->getTimestamp())
                            {
                                
                                return http_response_code(401);
                            }
                            else { //SINON (SI tout vas bien)
                                
                                $userDB = new UserDb(); 
                                $user = $userDB->get_user_by_refresh($refresh_token, $decoded_refresh->id);//On tente de rechercher les données de l'utilisateur
                                if ($user !=="NO USER") { //SI on y arrive (il n'a pas été supprimer ou banni) ALORS
                                    $user->generate_token();//On génère de nouveaux tokens
                                    $user->generate_refresh_token();
                                    setcookie("refresh", $user->refresh_token(), $refresh_expire_at, "/OurFridgeWall", "localhost", false, true);
                                    $userDB->add_new_refresh_token($refresh_token, $user->refresh_token(), $user);//On renvoie l'utilisateur
                                        
                            
                                    
                                    $current_user = new User(array(
                                        "id" => $user->get_id(),
                                        "house" => $user->get_house(),
                                        "is_root" => $user->get_is_root(),
                                        "is_prenium" => $user->get_is_premium()
                                    ));
                                    
                                    return $user;
                                }
                                else { //SINON Si on arrive pas à récupérer l'utilisateur
                                    
                                    header('HTTP/1.1 401 Unauthorized');
                                    exit;
                                }
                                    
                            }
                        }
                        catch (Exeption $err) { //SI le token de rafraichissement est invalide ALORS
                            
                            return http_response_code(401);
                        }
                    }
                    else { //SI il n'y a pas de cookie de rafraichissement ALORS
                        //header('HTTP/1.1 401 Unauthorized'); //On autorise pas la connexion
                        
                        return http_response_code(401);
                    }
                }
                else { //SINON Si le token a un autre problème que l'expiration ALORS
                    
                    return http_response_code(401);
                }
                    
                
            }
            
            
        }
        
        
    }

    $user_returned = isLoggedIn($current_user);
    //print_r($user_returned);
    if (isset($user_returned) && $user_returned->token() !== null && $user_returned->token()!=="") { //SI on a réaliser un rafraichissement silencieux ALORS
        $user_returned = $user_returned->to_array(true); //On prévoit d'afficher l'utilisateur
        $output["data"]["user"] = $user_returned;
    }
    
    
    
    
?>