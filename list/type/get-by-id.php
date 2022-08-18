<?php 
    require "../../init.php";
    
    include_once "../../user/is-loged.php";

    include_once "../../model/list/type/TypeDB.php";
    include_once "../../model/list/type/Type.php";
    include_once "../../model/list/products/ProductDB.php";
    include_once "../../model/list/products/Product.php";
    include_once "../../model/list/type/Type.php";
    include_once "../../model/user/User.php";

    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        // The request is using the POST method
        header("HTTP/1.1 200 OK");
        return;
    
    }

    $typeDb = new TypeDB();
    $type =  null;

    $postdata = file_get_contents("php://input");
    if(!isset($postdata) || empty($postdata)) { 
        return http_response_code(400);
    }
    $request = json_decode($postdata);
    // Validate.
    if(
        !isset($request->data->type->id) || //SI une variable dont on a besoin n'existe pas OU
        (int)$request->data->type->id <= 0 //que l'id est négatif OU
    ) {
        return http_response_code(400); 
    }
    else { //SINON (les données sont valides)
        $type =  new Type(array(
            "id" => $request->data->type->id, 
            "user" => $current_user
        
        ));
        $type_selected = $typeDb->select_custom_type_by_id($type);
        if (!$type_selected) {
            $output["data"]["error"] = "no custom type";  
            print_r(json_encode($output));//On renvoie ok
            return http_response_code(418);
        }
        else {
            $type_selected->set_is_deletable($typeDb->type_is_deletable($type_selected));
            $type_selected = $type_selected->to_array();
            $output["data"]["status"] = "ok";
            $output["data"]["type"] = $type_selected;  
            print_r(json_encode($output));//On renvoie ok
            return http_response_code(200);
        }
    }
    return http_response_code(500);