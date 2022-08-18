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
$users_array = array();
$shopList = null; 

    
$shopList = new ShopList(array( //Om instancie la liste
    "id" => -1
));//On creer la liste

$users = $userDb->select_house_to_auth_lsit($shopList, $current_user);//On recupere la lsite des utilisateurs de la maison 
        
if ($users === false) { //Si il n'y a pas d'utilisateur ALORS
    $output["data"]["status"] = "no user";//On l'indique au client
    print_r(json_encode($output));
    return http_response_code(200);
}
else { //SINON
    $output["data"]["status"] = "ok";//On l'indique au client
    $output["data"]["users"] = array();

    for ($i = 0; $i < sizeof($users); $i++) {
        array_push($output["data"]["users"], $users[$i]->to_array());    
    }
            
    print_r(json_encode($output));
    return http_response_code(200);
}