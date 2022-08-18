<?php
    require "../../init.php";
    include_once "../../user/is-loged.php";
    include_once "../../model/list/products/ProductDB.php";
    include_once "../../model/list/products/Product.php";
    include_once '../../controlers/guard/rootGuard.php';

    $postdata = file_get_contents("php://input");

    $productDB =  new ProductDB(); 
    $product = null;

    if(isset($postdata) && !empty($postdata)) { 
        // Extract the data.
        $request = json_decode($postdata);
            

        // Validate.
        if( !isset($request->data->product->id) ||
            (int)$request->data->product->id < 1
        ) {
            return http_response_code(400);
        }
        else {
            $product = new Product(array(
                "id" => (int)$request->data->product->id,
                "user" => new User(array(
                    "house" => $current_user->get_house()
                ))
            ));
            $product_selected = $productDB->select_by_id($product);
            if($product_selected === "no product") {
                $output["data"]["error"] = "no product";
                print_r(json_encode($output)); 
                return http_response_code(200);
            }
            else if($product_selected === "udpate fail") {
                $output["data"]["error"] = "udpate fail";
                print_r(json_encode($output)); 
                return http_response_code(200);
            } 
            else {
                $output["data"]["status"] = "ok";
                $output["data"]["product"] = $product_selected->to_array();
                print_r(json_encode($output)); 
                return http_response_code(200);
            }
        }
    }