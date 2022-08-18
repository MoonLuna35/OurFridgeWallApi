<?php
require "../../init.php";
include_once "../../user/is-loged.php";
include_once "../../model/list/products/ProductDB.php";
include_once "../../model/list/products/Product.php";
include_once "../../model/list/type/Type.php";
include_once '../../controlers/guard/rootGuard.php';


// Get the posted data.
$postdata = file_get_contents("php://input");

$productDB =  new ProductDB(); 
$prdt;

if(isset($postdata) && !empty($postdata))
{ 
  // Extract the data.
  $request = json_decode($postdata);
	

  // Validate.
  if(
    !isset($request->data->product->label) ||
    trim($request->data->product->label) === '' ||
    preg_match('/[^a-zA-Z0-9-_ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØŒŠþÙÚÛÜÝŸàáâãäåæçèéêëìíîïðñòóôõöøœšÞùúûüýÿ ]/',trim($request->data->product->label))
    ) {

        header('HTTP/1.1 400 Bad Request');
        exit; 
  }
  else {
    $prdt = new Product(array(
        "label" =>  $request->data->product->label,
        "user" => $current_user
    ));
    $products = $productDB->search($prdt);
    $product_arr = array();
    if($products) {
        for ($i = 0; $i < sizeof($products); $i++) {
            array_push($product_arr, $products[$i]->to_array());
        }
    }
    
    $output["data"]["products"] = $product_arr;
    print_r(json_encode($output));
    return http_response_code(200);
  }
}
else {
    header('HTTP/1.1 400 Bad Request');
    exit; 
}
