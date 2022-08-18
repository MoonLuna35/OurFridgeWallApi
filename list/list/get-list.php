<?php

require "../../init.php";
include_once '../../user/is-loged.php';
include_once '../../controlers/guard/GList.php';
include_once "../../model/list/list/List.php";
include_once "../../model/list/list/ListDB.php";
include_once "../../model/user/userDB.php";
include_once "../../model/user/UserForAuth.php";

// Get the posted data.
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        // The request is using the POST method
        header("HTTP/1.1 200 OK");
        return;
    
}
$postdata = file_get_contents("php://input"); 

$listDb = new ListDB();
$userDb = new UserDB();
$users = array();
$users_array = array();
$shopList = null; 

if(!isset($postdata) || empty($postdata)) { 
    
    header('HTTP/1.1 400 Bad Request');
    exit;   
}
else {
    $request = json_decode($postdata);
    if (
        !isset($request->data->list->id) ||
        (int)$request->data->list->id <= 0 
        ) {
        header('HTTP/1.1 400 Bad Request');
        exit;   
    }
    else {
        $shopList = new ShopList(array( //Om instancie la liste
            "id" => $request->data->list->id
        ));//On creer la liste
        guard($shopList, $current_user);
        $l = $listDb->select_list($shopList);
        if ($l === false) {
            return http_response_code(500);
        } 
        else {
            $output["data"]["status"] = "ok";//On l'indique au client
            $output["data"]["list"] = $l->to_array();
            print_r(json_encode($output));
            return http_response_code(200);
        }
    }
}