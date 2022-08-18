<?php
    require "../../init.php";
    include_once "../../user/is-loged.php";
    include_once "../../model/list/products/ProductDB.php";
    include_once "../../model/list/products/Product.php";
    include_once "../../model/list/type/TypeDB.php";
    include_once "../../model/user/userDB.php";

    $postdata = file_get_contents("php://input");

    $productDB =  new ProductDB(); 

    if(isset($postdata) && !empty($postdata)) { 
        // Extract the data.
        $request = json_decode($postdata);
            

        // Validate.
        if( !isset($request->data->offset) ||
            (int)$request->data->offset < 0
        ) {

            return http_response_code(400);
        }
        else {
            $product_arr = array();
            $products = $productDB->select_by_house($current_user, (int)$request->data->offset);
            if ($products === "no product") {
                $output["data"]["status"] = "no product";
                print_r(json_encode($output));
                return http_response_code(200);
            }
            else {
                for ($i = 0; $i < sizeof($products); $i++) {
                    array_push($product_arr, $products[$i]->to_array());
                    
                }
                $output["data"]["qte_prdt"] = $product_arr;
                print_r(json_encode($output));
                return http_response_code(200);
            }
            
        }
    }