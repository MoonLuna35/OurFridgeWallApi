<?php 
    

    function fuse_remover($shopList) {
        $listDb = new ListDB();
        
        if ($listDb->fusion_remover($shopList)) { //SI on arrive a fusioner ALORS
            $output["data"]["status"] = "ok";//On l'indique au client
            print_r(json_encode($output));
            return http_response_code(200);
        }
        else { //SINON 
            return http_response_code(500);
        }
    }
    
   
