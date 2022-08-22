<?php
    require "../../init.php";
    include_once '../../user/is-loged.php';
    include_once "../../model/list/list/List.php";
    include_once "../../model/list/list/ListDB.php";
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
            !isset($request->data->list->name) ||
            !isset($request->data->list->id) ||
            (int)$request->data->list->id  <= 0 || 
            trim($request->data->list->name) === "" || 
            trim($request->data->list->name) === "first_list"||
            preg_match('/[^a-zA-Z_0-9-_äâàèéèëêïîöôùûü ]/',trim($request->data->list->name))
        ) {
            return http_response_code(400);
        }
        else {
            $list = new ShopList (array(
                "id" => $request->data->list->id,
                "name" => $request->data->list->name,
                "author" => new User(array(
                    "house" => $current_user->get_house()
                )),
            ));
            guard($list, $current_user, true);
        }

        if($list_db->rename($list)) {
            $output["data"]["status"] = "ok";
            print_r(json_encode($output));
            return http_response_code(200);
        }
        else {
            $output["data"]["error"] = "unable to rename";
            print_r(json_encode($output));
            return http_response_code(200);  
        }
    }