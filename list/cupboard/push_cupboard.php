<?php

require "../../init.php";
include_once '../../user/is-loged.php';
include_once "../../model/list/cupboard/CupboardDB.php";
include_once "../../model/list/cupboard/Cupboard.php";
include_once "../../model/list/products/ProductDB.php";
include_once "../../model/list/products/Product.php";
include_once "../../model/user/User.php";
include_once '../../controlers/guard/rootGuard.php';

$productDB =  new ProductDB();
$cupboardDB = new CupboardDB(); 
// Get the posted data.
$postdata = file_get_contents("php://input");

if(isset($postdata) && !empty($postdata)) { 
    $request = json_decode($postdata);
    // Validate.
    if(
        !isset($request->data->product->id) ||
        (int)$request->data->product->id <= 0  
    ) {
      
      return http_response_code(400);
    }
  
    else {
        $prdt =  new CupboardLine(array( //on crée un produit avec la langue
             "id" => $request->data->product->id,
            "user" => new User(array(
                "house" => $current_user->get_house()
            ))
        ));
        if (!$productDB->prdt_is_existing_by_id($prdt)) {
            $output["data"]["error"] = "product no exist";
            print_r (json_encode($output));
            return http_response_code(200);
        }

        if ($cupboardDB->push_cupboard($prdt)) {
            $output["data"]["status"] = "ok";
            print_r (json_encode($output));
            return http_response_code(200);
        }
        else {
            $output["data"]["error"] = "product is already in cupboard";
            print_r (json_encode($output));
            return http_response_code(200);
        }
        



        
    }
  

}
