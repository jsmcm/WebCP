<?php

$LicenseKey = filter_var($_POST["LicenseKey"], FILTER_SANITIZE_STRING);

if( strlen($LicenseKey) != 32 ) {
	
	header("location: index.php?Notes=That license key looks wrong, please try again<br><a href=\"enter_license.php\">Click here to enter a license key</a>");
	exit();

} else {

	// looks good, write it!
	$f = fopen($_SERVER["DOCUMENT_ROOT"]."/includes/license.conf", "w");
	fwrite($f, $LicenseKey);
	fclose($f);

	header("Location: index.php?Notes=License file added, please try logging in again");

}
