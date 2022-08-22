<?php

require "../../init.php";

include_once '../../user/is-loged.php';
include_once "../../model/list/list/List.php";
include_once "../../model/list/list/ListDB.php";
include_once "../../model/list/cupboard/CupboardDB.php";
include_once "../../model/list/products/ProductDB.php";
include_once "../../model/list/cupboard/Cupboard.php";
include_once '../../controlers/list/list/CList.php';
include_once '../../controlers/guard/GList.php';

// Get the posted data.
$postdata = file_get_contents("php://input"); 

$list; 
$cupboard_to_fuse = array();
$cupboardDb = new CupboardDB();
$productDB = new ProductDB();
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    // The request is using the POST method
    return http_response_code(200);

}

if(!isset($postdata) || empty($postdata)) { 
    
    log400(__FILE__, __LINE__); 
}

else {
    $request = json_decode($postdata);
    if (
        !isset($request->data->list->id) ||
        !isset($request->data->cupboard) ||
        !is_array($request->data->cupboard) ||
        (int)$request->data->list->id <= 0
    ) {
        log400(__FILE__, __LINE__); 
    }
    else {
        //On instancie les objets dont on a besoin
        //La liste
        $list = new ShopList(array( 
            "id" => $request->data->list->id,
            "author" => new User(array(
                "house" => $current_user->get_house()
            )) 
        ));
        //Les lignes du placards
        for($i = 0; $i < sizeof($request->data->cupboard); $i++) { //POUR TOUTE ligne du placard FAIRE
            if( //SI 
                isset($request->data->cupboard[$i]->id) //Ce dont on a besoin existe
                &&
                isset($request->data->cupboard[$i]->qte)
                &&
                (int)$request->data->cupboard[$i]->id > 0 //ET que leurs valeurs sont dans les normes
                &&
                (int)$request->data->cupboard[$i]->qte >= 0
            ) { //ALORS
                array_push($cupboard_to_fuse, new CupboardLine(array( //On l'instancie
                    "id" => $request->data->cupboard[$i]->id,
                    "qte" => $request->data->cupboard[$i]->qte,
                    "user" => new User(array(
                        "house" => $current_user->get_house()
                    ))
                )));
                if(
                    !$cupboardDb->prdt_is_in_cupboard($cupboard_to_fuse[$i]) 
                    ||
                    $productDB->select_by_id($cupboard_to_fuse[$i]) === "no product"
                ) { //SI le produit n'est pas dans le placard ALORS
                    $output["data"]["error"] = "product not in cupboard";
                    print_r(json_encode($output));
                    return http_response_code(200); 
                }
            }   
            else { //SINON
                log400(__FILE__, __LINE__); 
            }
            
        }
        guard($list, $current_user);//On regarde qu'on ai les droits
        if($cupboardDb->fusion_remover($list)) { //On supprime les precedentes fusions dans la lsite l
            if(sizeof($cupboard_to_fuse) > 0 && !$cupboardDb->fuse($list, $cupboard_to_fuse)) {//SI il y a des lignes a ajoutees ALORS
                header('HTTP/1.1 500 Internal Server Error'); //On renvoie 500
                exit;  
            }
            $output["data"]["status"] = "ok";
            print_r(json_encode($output));
            return http_response_code(200);
        }
        else {
            $output["data"]["error"] = "unable to unfuse";
            print_r(json_encode($output));
            return http_response_code(200);
        }       
        print_r(json_encode($output));
        return http_response_code(200);   
    }
}