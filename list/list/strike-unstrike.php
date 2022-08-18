<?php
    require "../../init.php";
    include_once '../../user/is-loged.php';
    include_once "../../model/list/list/List.php";
    include_once "../../model/list/list/ListDB.php";
    include_once "../../model/user/User.php";
    include_once '../../controlers/guard/GList.php';

    $postdata = file_get_contents("php://input");
    
    $list_db = new ListDB();
    $list = null;

    if(!isset($postdata) || empty($postdata)) { 
        header('HTTP/1.1 400 Bad Request');
        exit;   
    }
    else {
        $request = json_decode($postdata);
        if (
            !isset($request->data->list->product->id) ||
            !isset($request->data->list->product->is_striked) ||
            !isset($request->data->list->id) ||
            (int)$request->data->list->id  <= 0 ||
            (int)$request->data->list->product->id  <= 0 ||
            !is_bool($request->data->list->product->is_striked)   
        ) {
            header('HTTP/1.1 400 Bad Request');
            exit;   
        }
        else {
            $list = new ShopList (array(
                "id" => $request->data->list->id,
                "lines" => array(
                    new ListLine(array(
                        "id" => $request->data->list->product->id, 
                        "is_striked" => $request->data->list->product->is_striked
                    ))),
                "user" => new User(array(
                    "house" => $current_user->get_house()
                ))
            ));
            guard($list, $current_user);
            if($list_db->striker_unstriker($list)) {
                $output["data"]["status"] = "ok";
                print_r(json_encode($output));
                return http_response_code(200);
            }
            else {
                $output["data"]["error"] = "unable to strike or unsrtike";
                print_r(json_encode($output));
                return http_response_code(200);
            }
        }
    }