<?php

require "../../init.php";

include_once '../../user/is-loged.php';
include_once "../../model/list/list/List.php";
include_once "../../model/list/list/ListDB.php";
include_once "../../model/list/cupboard/CupboardDB.php";
include_once "../../model/list/cupboard/Cupboard.php";
include_once '../../controlers/list/list/CList.php';
include_once '../../controlers/guard/GList.php';

// Get the posted data.
$postdata = file_get_contents("php://input"); 
$shopList = null; 
$cupboard = null;
$cupAlreadyFused = null;


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
        $shopList = new ShopList(array(//On instancie la liste 
            "id" => $request->data->list->id,
            "author" => new User(array(
                "house" => $current_user->get_house()
            ))
        ));
        guard($list, $current_user, true);
        fuse_remover($shopList);
    }
    
}