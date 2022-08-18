<?php 
    require "../../init.php";
    include_once "../../controlers/list/type/CTypes.php";
    
    include_once "../../user/is-loged.php";

    include_once "../../model/list/type/TypeDB.php";
    include_once "../../model/list/type/Type.php";
    include_once "../../model/user/User.php";
    include_once '../../controlers/guard/rootGuard.php';

    $typeDb = new TypeDB();
    $type =  null;
    
    $postdata = file_get_contents("php://input");
    
    if(isset($postdata) && !empty($postdata)) { 
        $request = json_decode($postdata);
        
      
        // Validate.
        if( 
            !isset($request->data->type->name) ||
            !isset($request->data->type->logo_color) ||
            !isset($request->data->type->logo_patern) ||
            trim($request->data->type->name) === "" || 
            preg_match('/[^a-zA-Z_0-9-_äâàèéèëêïîöôùûü]/', trim($request->data->type->name)) ||
            trim($request->data->type->logo_color) === "" || 
            preg_match('/#[0-9a-f]/', trim($request->data->type->logo_color)) === 0 ||
            (int)$request->data->type->logo_patern < 1
        ) {
            header('HTTP/1.1 418 I am a teapoad');
            exit; 
        }
        else {
            if(!file_exists("../../media/type_patern/" . $request->data->type->logo_patern . ".png")) { //SI le logo n'existe pas ALORS
                $output["data"]["error"] = "logo not found";
                print_r(json_encode($output));
                return http_response_code(200);//On renvoie une erreur
            }
               
            $type = new Type(array(
                "name" => $request->data->type->name,
                "logo_color" => hexdec(str_replace("#", "", $request->data->type->logo_color)), //On convertie la couleur en int
                "logo_patern" => (int)$request->data->type->logo_patern,
                "user" => $current_user 
            ));
             if($typeDb->add_type($type) !== false) {
                $output["data"]["status"] = "ok";
                $output["data"]["type"] = $type->to_array();
                print_r(json_encode($output));
                return http_response_code(200);//On renvoie le type ajouter avec son id
             }   
            else { //SINON
                $output["data"]["error"] = "unable to add the new type";
                print_r(json_encode($output));
                return http_response_code(200);//On renvoie une erreur
            } 
                
        }
    }
    else {
        header('HTTP/1.1 400 Bad Request');
        exit; 
    }