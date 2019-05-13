<?php

$LicenseKey = $_POST["LicenseKey"];

if( (strlen($LicenseKey) != 16) && (strlen($LicenseKey) != 19) ) 
{
	header("location: index.php?Notes=That license key looks wrong, please try again<br><a href=\"enter_license.php\">Click here to enter a license key</a>");
	exit();
}
else
{
	if(strlen($LicenseKey) == 19)
	{
		if( (substr($LicenseKey, 4, 1) != "-") && (substr($LicenseKey, 9, 1) != "-") && (substr($LicenseKey, 14, 1) != "-"))
		{
			header("location: index.php?Notes=That license key looks wrong, please try again<br><a href=\"enter_license.php\">Click here to enter a license key</a>");
			exit();
		}
		else
		{
			$LicenseKey = substr($LicenseKey, 0, 4).substr($LicenseKey, 5, 4).substr($LicenseKey, 10,4).substr($LicenseKey, 15, 4);
		}
	}


	// looks good, write it!
	$f = fopen($_SERVER["DOCUMENT_ROOT"]."/includes/license.conf", "w");
	fwrite($f, $LicenseKey);
	fclose($f);

	header("Location: index.php?Notes=License file added, please try logging in again");
}
				
?>


