<?php

require "../../init.php";
include_once '../../user/is-loged.php';
include_once '../../controlers/guard/GList.php';
include_once "../../model/list/list/List.php";
include_once "../../model/list/list/ListDB.php";
include_once "../../model/user/userDB.php";
include_once "../../model/user/UserForAuth.php";

// Get the posted data.
$postdata = file_get_contents("php://input"); 

$listDb = new ListDB();
$userDb = new UserDB();
$users = array();
$shopList = null; 


if(!isset($postdata) || empty($postdata)) { 
    
    header('HTTP/1.1 400 Bad Request');
    exit;   
}
else {
    $request = json_decode($postdata);
    
    if (
        !isset($request->data->list->id) ||
        !isset($request->data->list->users_auth) ||
        !is_array($request->data->list->users_auth) ||
        (int)$request->data->list->id <= 0
    ) {
        header('HTTP/1.1 400 Bad Request');
        exit;   
    }
    else {
        $shopList = new ShopList(array( //Om instancie la liste
            "id" => $request->data->list->id
        ));
        guard($shopList, $current_user);
        $shopList = $listDb->select_author($shopList); //On recupere l'auteur
        if (!$shopList) {
            header('HTTP/1.1 400 Bad Request');
            exit;   
        }
        for ($i = 0; $i < sizeof($request->data->list->users_auth); $i++) { //POUR TOUT utilisateur authoirise
            if(  
                !isset($request->data->list->users_auth[$i]->id) || //SI il n'a pas d'id OU 
                (int)$request->data->list->users_auth[$i]->id <= 0 ||//que l'id est vide ALORS
                $request->data->list->users_auth[$i]->id === $shopList->get_author()->get_id()||
                $request->data->list->users_auth[$i]->id === $current_user->get_id()
            ){
                
                header('HTTP/1.1 400 Bad Request'); //On renvoie une erreur 400 
                exit;
            }
            else { //SINON
                $user_to_try = new User(array(
                    "id" => (int)$request->data->list->users_auth[$i]->id,
                    "house" => $current_user->get_house()
                ));
                if(!$userDb->is_membership_of_house($user_to_try)) { //SI l'utilisateur qu'on veux authoriser ne fait pas parti de la maison de l'utilisateur courant ALORS
                    
                    header('HTTP/1.1 400 Bad Request'); //On renvoie une erreur 400 
                    exit;
                }
                else { //SINON 
                    array_push($users, $user_to_try); //On l'ajoute a la liste des utilisateurs
                }
            }
        }
        //On ajoute les utilisateur authorises a la liste.
        $shopList->set_users_auth($users);
        if(
            $shopList->get_author()->get_house() !== $current_user->get_house() || //SI la liste n'appartient pas a la maison de l'utilisateur courant
            $current_user->get_is_root() === false && $current_user->get_id() !==  $shopList->get_author()->get_id() ||//OU que l'utilisateur courant n'est ni root, ni l'auteur
            !$listDb->list_is_existing($shopList)  //OU que la liste n'existe pas
             
        ) { 
                
                header('HTTP/1.1 400 Bad Request');
                exit;   
        }
        else { //SINON 
                if ($listDb->to_private($shopList, $current_user)) {//SI on a reussi a rendre la liste privee ALORS
                    $output["data"]["status"] = "ok";//On l'indique au client
                    print_r(json_encode($output));
                    return http_response_code(200);//On renvoie au client
                }
                else {//SINON
                    $output["data"]["error"] = "unable to set to private";//On l'indique au client
                    print_r(json_encode($output));
                    return http_response_code(200);//On renvoie au client
                }
            
        }   
     
    }
}