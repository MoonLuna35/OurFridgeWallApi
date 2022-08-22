<?php

require "../../init.php";
include_once '../../user/is-loged.php';
include_once "../../model/list/cupboard/CupboardDB.php";
include_once "../../model/list/cupboard/Cupboard.php";
include_once "../../model/list/list/List.php";
include_once "../../model/list/list/ListDB.php";
include_once '../../controlers/guard/GList.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    // The request is using the POST method
    return http_response_code(200);

}

// Get the posted data.
$postdata = file_get_contents("php://input");
$cupboardDb = new CupboardDB(); 
$listDb = new ListDB();
$shopList = null; 
$cupboard = null;


if(!isset($postdata) || empty($postdata)) {
    log400(__FILE__, __LINE__); 
}
else {
    $request = json_decode($postdata);
    if (
        !isset($request->data->list->id) ||
        (int)$request->data->list->id <= 0
        ) {
            log400(__FILE__, __LINE__); 
        }
    else {
        $shopList = new ShopList(array( //Om instancie la liste
            "id" => $request->data->list->id,
            "author" => new User(array(
                "house" => $current_user->get_house()
            ))
        ));
        guard($shopList, $current_user);
        if (!$listDb->list_is_existing($shopList)) { //SI elle n'exite pas ALORS
            return http_response_code(404);
        }

        $cupboard = $cupboardDb->select_from_list($shopList); //On recupere le placards
        if ($cupboard->is_empty()) { //SI il est vide ALORS 
            $output["data"]["status"] = "cupboard is empty";//On l'indique au client
            print_r(json_encode($output));
            return http_response_code(200);
        }
        else { //SINON (le placard n'est pas vide)
            $output["data"]["cupboard"] = $cupboard->to_array(); //On le renvoie au client
            print_r(json_encode($output));
            return http_response_code(200);
        }
    }
}