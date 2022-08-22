<?php
    require "../../init.php";
    include_once '../../user/is-loged.php';
    include_once "../../model/list/list/List.php";
    include_once "../../model/list/list/ListDB.php";
    include_once "../../model/list/products/ProductDB.php";
    include_once "../../model/list/products/Product.php";
    include_once "../../model/user/User.php";
    include_once '../../controlers/guard/GList.php';
    
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        // The request is using the POST method
        return http_response_code(200);
    
    }

    $postdata = file_get_contents("php://input");
    
    $list_db = new ListDB();
    $list = null;

    if(!isset($postdata) || empty($postdata)) { 
        log400(__FILE__, __LINE__); 
    }
    else {
        $request = json_decode($postdata);
        if (
            !isset($request->data->list->product->id) ||
            !isset($request->data->list->id) ||
            (int)$request->data->list->id  <= 0 ||
            (int)$request->data->list->product->id  <= 0 
        ) {
            log400(__FILE__, __LINE__); 
        }
        else {
            $list = new ShopList (array(
                "id" => $request->data->list->id,
                "lines" => array(
                    new ListLine(array(
                        "id" => $request->data->list->product->id,
                    ))),
                "author" => new User(array(
                    "house" => $current_user->get_house()
                ))
            ));
            guard($list, $current_user, true);
            if($list_db->remove_line($list)) { //SI on arrive a la supprimer ALORS
                $output["data"]["status"] = "ok"; //On renvoie la ligne au client
                print_r(json_encode($output));
                return http_response_code(200);
            }
            else { //SINON
                $output["data"]["error"] = "unable to remove line"; //On renvoie une erreur
                print_r(json_encode($output));
                return http_response_code(200);
            }
        }
    }
