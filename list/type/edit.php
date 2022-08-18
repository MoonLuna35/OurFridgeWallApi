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
            !isset($request->data->type->id) ||
            !isset($request->data->type->name) ||
            !isset($request->data->type->logo_color) ||
            !isset($request->data->type->logo_patern) ||
            (int)$request->data->type->id < 1 ||
            trim($request->data->type->name) === "" || 
            preg_match('/[^a-zA-Z_0-9-_äâàèéèëêïîöôùûü]/', trim($request->data->type->name)) ||
            trim($request->data->type->logo_color) === "" || 
            preg_match('/#[0-9a-f]{6}/', trim($request->data->type->logo_color)) === 0 ||
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
                "id" => (int)$request->data->type->id,
                "name" => $request->data->type->name,
                "logo_color" => hexdec(str_replace("#", "", $request->data->type->logo_color)), //On convertie la couleur en int
                "logo_patern" => (int)$request->data->type->logo_patern,
                "user" => $current_user 
            ));
             if($typeDb->type_is_existing($type, $current_user) === true) { //SI le type existe ALORS
                if ($typeDb->update_type($type) === true) { //SI on arrive a l'editer ALORS
                    $output["data"]["status"] = "ok"; //On renvoie ok
                    print_r(json_encode($output));
                    return http_response_code(200);
                }   
                else { //SINON (si on arrive pas a l'editer)
                    if($typeDb->type_is_the_same($type)) {//SI il c'est a cause que rien n'a change ALORS
                        $output["data"]["status"] = "ok"; //on renvoie OK
                        print_r(json_encode($output));
                        return http_response_code(200);
                    }
                    else {  
                        $output["data"]["error"] = "unable to edit the type";
                        print_r(json_encode($output));
                        return http_response_code(200);//On renvoie une erreur
                    }
                    
                }
            }
            else { //SINON (si il n'existe pas)
                $output["data"]["error"] = "type not exist";
                print_r(json_encode($output));
                return http_response_code(200);//On renvoie une erreur
            }
                
        }
    }
    else {
        header('HTTP/1.1 400 Bad Request');
        exit; 
    }