<?php

include_once(dirname(__FILE__)."/includes/classes/Images.php");
$oImages = new Images();

$imagePath = filter_var($_GET["xsource"], FILTER_SANITIZE_STRING);
$user = filter_var($_GET["xuser"], FILTER_SANITIZE_STRING);

$convertedImagePath = str_replace("/", "_", $imagePath);

file_put_contents("/var/www/html/webcp/nm/".$convertedImagePath.".webp", $user);

$filename = basename($imagePath);
$file_extension = strtolower(substr(strrchr($filename,"."),1));

$ctype = "";

switch( $file_extension ) {
    case "gif": $ctype="image/gif"; break;
    case "png": $ctype="image/png"; break;
    case "jpeg":
    case "jpg": $ctype="image/jpeg"; break;
    case "svg": $ctype="image/svg+xml"; break;
    case "webp": $ctype="image/webp"; break;
    default:
}

header('Content-type: ' . $ctype);
echo file_get_contents($imagePath);
