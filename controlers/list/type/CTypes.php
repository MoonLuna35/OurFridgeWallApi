<?php 
/*
* 09/10/21 : Teddy : algo de vérification d'image
*/

/*
*   Partant du principe que chaque icone du type de produit est une image crée par nous même (les dev)
*   on peut les tester par correspondance. évitant ainsi les risques qu'un utilisateur stock n'importe quoi
*/
function check_type_img(String $img_name) {
    $img_in = imagecreatefrompng($img_name);//On prends l'image
    imageAlphaBlending($img_in, true);
    imageSaveAlpha($img_in, true);
    $patern_name = array();
    $is_match =  false;
    $dir_content = scandir("../../media/type_patern");//On récupère la liste de fichiers
    for ($i = 2; $i < sizeof($dir_content); $i++) {//On récupère uniquement les png
        if(strpos(".png", $dir_content[$i]) !== 0) {
            array_push($patern_name, $dir_content[$i]);
        }
    }
    //On la réduit à 50px * 50px
    imagefilter($img_in, IMG_FILTER_GRAYSCALE);//On baisse la saturation au minimum 
    imagefilter($img_in, IMG_FILTER_BRIGHTNESS, -127);//On baisse la saturation au minimum 
    imagefilter($img_in, IMG_FILTER_CONTRAST, +255);//On augemente le contraste au maximum
    
    $i = 0;
    while (!$is_match && $i < sizeof($patern_name)) { //TANT QUE aucune image ne corresponds FAIRE
        $unmatch_rat = 0;
        $patern = imagecreatefrompng("../../media/type_patern/" . $patern_name[$i]);
        imageAlphaBlending($patern, true);
        imageSaveAlpha($patern, true);
        imagefilter($patern, IMG_FILTER_GRAYSCALE);//On baisse la saturation au minimum 
        imagefilter($patern, IMG_FILTER_BRIGHTNESS, -127);//On baisse la saturation au minimum 
        imagefilter($patern, IMG_FILTER_CONTRAST, +255);//On baisse la saturation au minimum
        //header('Content-Type: image/png');
        //imagepng($patern);
        $is_same = true;
        for($x = 0; $x< 50; $x++) { //POUR toute la longueur de l'image FAIRE
            for($y = 0;  $y < 50; $y++) {//POUR toute hauteur  FAIRE
                $colors_in = imagecolorsforindex($img_in, imagecolorat($img_in, $x, $y));
                $colors_patern = imagecolorsforindex($patern, imagecolorat($patern, $x, $y));
                
                if(
                    imagecolorat($img_in, $x, $y) !== imagecolorat($patern, $x, $y) && 
                    ($colors_patern["alpha"] === 0 || $colors_in["alpha"] === 0)
                ) {//SI les pixels ne correspondent pas ET que l'un OU l'autre des pixel n'est pas transparent ALORS
                    $unmatch_rat ++; //on incrémente le taux d'erreur
                }
                $y++;
            } 
            $x++;  
        }
        if ($unmatch_rat < 2500-(2500*0.98)) { //SI la probabilité que l'image corresponde est suppérieur à 98% ALORS 
            $is_match = true;
            return true; //On renvoie true
        }
        $i++;
    }
    return false; //On a parcouru tout les paterns sans trouver de correspondance, l'image ne corresponds à aucuns patterns    
            
}