<?php
require '../connect.php';
require '../mail.php';
// Get the posted data.
$postdata = file_get_contents("php://input");



/*$postdata = 
  '{ "data":  {
        "id": -1,
        "pseudo": "",
        "is_using_name": null,
        "civility": "",
        "name": "",
        "surname": "",
        "birthday": "", 
        "is_double_auth": null,
        "is_call_by_name": null,
        "is_tu": null,
        "pronum": "",
        "talk_about_me": "",
        "is_plural": null,
        "activate": "wbggQ6Aki97EjFO2Z3cdR334COqfkj1XzGKrTF3f@lA1jNRlTk"
        }
    }' ;*/


function send_request($user, $db) {
    $req = $db->prepare( //On récupère l'utilisateur qui a la clé d'activation passée dans les paramètres
        "SELECT
            id,
            pseudo,
            is_using_name,
            civility,
            name,
            is_call_by_name,
            is_tu,
            pronum,
            talk_about_me,
            is_plural,
            is_activated 
        FROM 
            users
        WHERE
            activate = :activate"
    );
    $req->execute(array(
            ":activate" => $user->data->activate
        )
    );
    
    if ($req->rowCount() === 0 ) { //SI il n'y a aucun utilisateur ALORS
        $json = array ("error" => "NO ACCOUNT HAS THIS ACTIVATION TOKEN"); //on renvoie une erreur
        echo json_encode(array("data"=> $json));
        return http_response_code(200);
    }
    else if ($req->rowCount() === 1 ) { //SINON SI il y a un et un seul utilisateur qui a le token ALORS
        $rep = $req->fetchAll(PDO::FETCH_ASSOC); //On récupère la ligne de l'utilisateur
        if (isset($rep[0]["is_activated"]) && !$rep[0]["is_activated"]) { //SI il n'est pas encore activer ALORS
            
            $up = $db->prepare(//On l'active en stockant qu'il est actif dans la base de donnée
                "UPDATE
                    users 
                SET 
                    is_activated = 1
                WHERE
                    activate = :activate"
            );
            $up->execute(array(
                ":activate" => $user->data->activate
            ));
            if ($up) { //SI on a bien put l'activer ALORS
                $json = array( //On le revoie vers le front-end
                    "id" => $rep[0]["id"],
                    "pseudo" => $rep[0]["pseudo"],
                    "is_using_name" => $rep[0]["is_using_name"],
                    "civility" => $rep[0]["civility"],
                    "name" => $rep[0]["name"],
                    "is_call_by_name" => $rep[0]["is_call_by_name"],
                    "is_tu" => $rep[0]["is_tu"],
                    "pronum" => $rep[0]["pronum"],
                    "talk_about_me" => $rep[0]["talk_about_me"],
                    "is_plural" => $rep[0]["is_plural"],
                );
                echo json_encode(array("data"=> $json));
                return http_response_code(200);
            }
            else { //SINON (il y a un problème dans l'update)
                return http_response_code(500); //le serveur est en erreur
                //On écrit dans le log
            }
        }
        else if (isset($rep[0]["is_activated"]) && $rep[0]["is_activated"]) { //SINON SI le compte est déjà actif ALORS
            $json = array ("error" => "ACCOUNT IS ALREADY ACTIVATE"); //On renvoie une erreur
            echo json_encode(array("data"=> $json));
            return http_response_code(200);
        }
    }
    else { //SINON (plusieurs utilisateurs on la même clé s'activation)
        return http_response_code(500); //le serveur est en erreur
        //On écrit dans le log
    }


}

if(isset($postdata) && !empty($postdata)) { 
  $user = json_decode($postdata);
  
    if (!isset($user->data->activate)) {
        return http_response_code(400); //On renvoie une erreur
    } 
    
    send_request($user, $db);

}