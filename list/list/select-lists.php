<?php
    require "../../init.php";
    include_once '../../user/is-loged.php';
    include_once "../../model/list/list/ListDB.php";
    include_once "../../model/list/list/List.php";
    include_once "../../model/user/User.php";
    $postdata = file_get_contents("php://input");
    
    $list_db = new ListDB();
    $list = null;

    if(!isset($postdata) || empty($postdata)) { 
        header('HTTP 400 Bad Request');
        exit;   
    }
    else {
        $request = json_decode($postdata);
        if (
            
            !isset($request->data->already_printed) ||
            (int)$request->data->already_printed  < 0
             
        ) {
            header('HTTP 400 Bad Request');
            exit;   
        }
        
        $already_printed = (int) $request->data->already_printed;
        
        if (
            isset($request->data->is_archived) 
            && 
            is_bool($request->data->is_archived)
        ) {
            $is_archived = (bool) $request->data->is_archived;
            $lists = $list_db->select_lists($current_user, $already_printed, $is_archived); 
        }
        else {
            $lists = $list_db->select_lists($current_user, $already_printed); 
        }
        

        if(sizeof($lists) === 0) {
            $output["data"]["status"] = "no one list selected"; //On renvoie une erreur
            print_r(json_encode($output));
            return http_response_code(200);
        }
        else {
            $list_arr =  array();
            for($i = 0; $i < sizeof($lists); $i++) {
                array_push($list_arr, $lists[$i]->to_array());
            }
            $output["data"]["status"] = "ok"; //On renvoie une erreur
            $output["data"]["lists"] = $list_arr; 
            print_r(json_encode($output));
            return http_response_code(200);
        }
    }