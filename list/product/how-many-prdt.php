<?php
    require "../../init.php";
    include_once "../../user/is-loged.php";
    include_once "../../model/list/products/ProductDB.php";
    include_once "../../model/list/products/Product.php";
    include_once "../../model/list/type/TypeDB.php";
    include_once "../../model/user/userDB.php";


    $productDB =  new ProductDB(); 
            
            $output["data"]["qte_prdt"] = $productDB->count_by_house($current_user);
            print_r(json_encode($output));
            return http_response_code(200);
    
