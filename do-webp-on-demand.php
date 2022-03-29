<?php

include_once(dirname(__FILE__)."/includes/classes/Images.php");
$oImages = new Images();


$imagePath = filter_var($_GET["xsource"], FILTER_SANITIZE_STRING);

$smushed = $oImages->smushImage($imagePath, 100);

$imagePath = $oImages->makeWebP($imagePath);
