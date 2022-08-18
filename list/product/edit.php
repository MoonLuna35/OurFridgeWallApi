<?php
    require "../../init.php";
    include_once "../../user/is-loged.php";
    include_once "../../model/list/products/ProductDB.php";
    include_once "../../model/list/products/Product.php";
    include_once "../../model/list/type/TypeDB.php";
    include_once "../../model/user/userDB.php";
    include_once '../../controlers/guard/rootGuard.php';

    $postdata = file_get_contents("php://input");

    $productDB =  new ProductDB(); 
    $typeDB = new TypeDB();

    if(isset($postdata) && !empty($postdata)) { 
        // Extract the data.
        $request = json_decode($postdata);
            

        // Validate.
        if( !isset($request->data->product->id) ||
            !isset($request->data->product->type->id) ||
            !isset($request->data->product->label) ||
            !isset($request->data->product->unity) ||
            (int)$request->data->product->id <= 0 || 
            (int)$request->data->product->type->id < 1 || 
            trim($request->data->product->label) === "" ||
            trim($request->data->product->unity) === "" 

        ) {

            return http_response_code(400);
        }


        $prdt =  new Product(array( //on crée un produit avec la langue
            "id" => $request->data->product->id,
            "label" => $request->data->product->label,
             "type" => new Type(array(
                "id" => (int)$request->data->product->type->id,
            )),
            "unity" => trim($request->data->product->unity),
            "user" => new User(array(
                "house" => $current_user->get_house()
            ))
        ));
        if($typeDB->type_is_existing($prdt->get_type(), $prdt->get_user())) {
            if(!$productDB->prdt_is_existing($prdt)) {
                if($productDB->edit($prdt)) {
                    $output["data"]["status"] = "ok";
                    print_r(json_encode($output)); 
                    return http_response_code(200);
                }
                else {
                    $output["data"]["error"] = "udpate fail";
                    print_r(json_encode($output)); 
                    return http_response_code(200);
                }
            }
            else {
                //On tente de modifier uniquement le type
                if($productDB->edit_type($prdt)) {
                    $output["data"]["status"] = "ok";
                    print_r(json_encode($output)); 
                    return http_response_code(200);
                }
                else {
                    $output["data"]["error"] = "article already exists";
                    print_r(json_encode($output)); 
                    return http_response_code(200);
                }
            }
           
        }
        else {
            $output["data"]["error"] = "type unvalid";
            print_r(json_encode($output)); 
            return http_response_code(200);
        }


    }
    else {
        header('HTTP/1.1 400 Bad Request');
        exit; 
    }