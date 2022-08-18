<?php

require "../../init.php";
include_once '../../user/is-loged.php';
include_once "../../model/list/cupboard/CupboardDB.php"; 
include_once "../../model/list/cupboard/Cupboard.php";
include_once '../../controlers/guard/rootGuard.php';

// Get the posted data.
$postdata = file_get_contents("php://input");

$cupboardDB = new CupboardDB();

$cupboard = $cupboardDB->select_cupboar_of_user($current_user); //On prends le placard de l'utilisateur
$cupboard_lines = $cupboard->get_products(); //On obtiens les ligne
if (sizeof($cupboard_lines) === 0) { //Si il n'y en a pas 
    $output["data"]["status"]= "cupboard is empty"; //On vas renvoyer un message pour indiquer que l'utilisateur n'a rien à controler dans son placard
}
else { //SINON
    $output["data"]["cupboard"] = $cupboard->to_array(); //On vas renvoyer les lignes
}


print_r(json_encode($output)); //On renvoie le status ou le placard
return http_response_code(200);





