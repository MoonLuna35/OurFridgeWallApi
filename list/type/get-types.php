<?php 
    require "../../init.php";
    
    include_once "../../user/is-loged.php";

    include_once "../../model/list/type/TypeDB.php";
    include_once "../../model/list/type/Type.php";
    include_once "../../model/user/User.php";

    $user = $current_user;
    $typeDb = new TypeDB(); 

    $postdata = file_get_contents("php://input");
    if(!isset($postdata) || empty($postdata)) { 
        header('HTTP 400 Bad Request');
        exit; 
    }
    $request = json_decode($postdata);
    if(
        !isset($request->data->type_printed) || //SI une variable dont on a besoin n'existe pas OU
        (int) $request->data->type_printed < 0
    ) {
        header('HTTP/1.1 400 Bad Request'); //On renvoie une erreur
        exit; 
    }

    $types = $typeDb->select_all_custom_types($user, $request->data->type_printed);
    if (!$types) {
        $output["data"]["status"] = "no more custom types";  
        print_r(json_encode($output));//On renvoie ok
        return http_response_code(200);
    }
    else {
        for ($i = 0; $i < sizeof($types); $i++) { //On prépare les types
            $types[$i] = $types[$i]->to_array();
        }
        $output["data"]["status"] = "ok";
        $output["data"]["types"] = $types; 
        print_r(json_encode($output));//On renvoie ok
        return http_response_code(200);
    }