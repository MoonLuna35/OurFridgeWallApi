<?php

require "../../init.php";
include_once '../../user/is-loged.php';
include_once "../../model/list/list/List.php";
include_once "../../model/list/list/ListDB.php";
include_once '../../controlers/guard/GList.php';


if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    // The request is using the POST method
    return http_response_code(200);

}
// Get the posted data.
$postdata = file_get_contents("php://input"); 
$listDb = new ListDB();
$shopList = null; 


if(!isset($postdata) || empty($postdata)) { 
    
    return http_response_code(400);
}
else {
    $request = json_decode($postdata);
    if (
        !isset($request->data->list->id) ||
        (int)$request->data->list->id <= 0
    ) {
        return http_response_code(400);
    }
    else {
        $shopList = new ShopList(array( //Om instancie la liste
            "id" => $request->data->list->id, 
            "author" => new User(array(
                "house" => $current_user->get_house()
            ))
        ));
        guard($shopList, $current_user, true);
        $is_unarchived = $listDb->unarchive($shopList);
        if ($is_unarchived === true) {
            $output["data"]["status"] = "ok";//On l'indique au client
            print_r(json_encode($output));
            return http_response_code(200);
        } 
        else if ($is_unarchived === "list already unarchived") {
            $output["data"]["status"] = "list already unarchived";//On l'indique au client
            print_r(json_encode($output));
            return http_response_code(200);
        }   
        else {
            $output["data"]["status"] = "unable to archive";
            print_r(json_encode($output));
            return http_response_code(200);
        }   
    }
}