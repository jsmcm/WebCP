<?php


class Images
{



    function makeWebP($imagePath)
    {

        if (!file_exists($imagePath)) {
        
            return false;

        }

        $imageSize = getimagesize($imagePath);


        $img = null;

        if ( ! isset($imageSize["mime"])) {

            return false; // probably not the right thing to do here.

        }


        if ($imageSize["mime"] == "image/png") {
         
            $img = imagecreatefrompng($imagePath);
        
        } else if ($imageSize["mime"] == "image/jpeg") {
 
            $img = imagecreatefromjpeg($imagePath);
        
        }

        imagepalettetotruecolor($img);
        imagealphablending($img, true);
        imagesavealpha($img, true);
        imagewebp($img, $imagePath.".webp", 64);
        imagedestroy($img);
        

        return $imagePath.".webp";

    }


    public function smushImage($imagePath, $quality=70)
    {
        
        $userName = "webcp";
        $password = "server";

        if ($userName == "" || $password == "") {
    		return false;
        }

        $authentication = base64_encode($userName." ".$password);

        $c = curl_init();

        curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
        
        $headers = array();
        $headers[] = "Authorization: basic ".$authentication;
        curl_setopt($c, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($c, CURLOPT_URL, "https://images.softsmart.co.za/api/v1/smusher");

        $exif["MimeType"] = "";

        if ( strstr(strtolower($imagePath), "png") ) {
            $exif["MimeType"] = "image/png";
        } else if( strstr(strtolower($imagePath), "jpg") || strstr(strtolower($imagePath), "jpeg") ) {
            //$exif = exif_read_data($imagePath);
            $exif["MimeType"] = "image/jpeg";
        }

        if (function_exists('curl_file_create')) { // php 5.5+
            $cFile = curl_file_create($imagePath, $exif["MimeType"]);
        } else { // 
            $cFile = '@' . $imagePath;
        }
        

        $post_array = array(
            "file"=>$cFile,
            "quality"=> $quality
        );
        
        curl_setopt($c, CURLOPT_POSTFIELDS, $post_array);
        
        
        $resultString = curl_exec($c);
        curl_close($c);
        
        $result = json_decode($resultString, true);
        
        
        if ( $result["status"] == "error" ) {
            return false;
        }

        if ( $result["status"] === "success") {
            return $result;
        }

        return false;

    }

}

