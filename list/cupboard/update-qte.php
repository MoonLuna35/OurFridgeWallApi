<?php

require "../../init.php";
include_once '../../user/is-loged.php';
include_once "../../model/list/cupboard/CupboardDB.php";
include_once "../../model/list/products/Product.php";
include_once "../../model/user/User.php";
include_once '../../controlers/guard/rootGuard.php';

// Get the posted data.
$postdata = file_get_contents("php://input");
$cupboardDB = new CupboardDB();

sleep(1);

if(isset($postdata) && !empty($postdata)) { 
    $request = json_decode($postdata);
   
    sleep(1);
    if( 
        !isset($request->data->product->id) ||
        !isset($request->data->product->qte) ||
        (int)$request->data->product->id <= 0  ||
        (int)$request->data->product->qte <=0 
    ){
      
      return http_response_code(400);
    }
  
    else {
        $prdt =  new CupboardLine(array( //on crée un produit avec la langue
            "id" => $request->data->product->id,
            "user" => new User(array(
                "house" => $current_user->get_house()
            )),
            "qte" => $request->data->product->qte
        ));
        if($cupboardDB->update_qte($prdt)) {
            $output["data"]["status"] = "ok";
            print_r (json_encode($output));
            return http_response_code(200);
        }
        else {
            $output["data"]["error"] = "unable to update qte";
            print_r (json_encode($output));
            return http_response_code(200);
        }
        
    }
  

}
