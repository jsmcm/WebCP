<?php
session_start();

require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.Package.php");
$oPackage = new Package();

require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.User.php");
$oUser = new User();

if($oUser->Role == "client")
{
        header("Location: /index.php");
        exit();
}

	if($oPackage->DeletePackage($_REQUEST["PackageID"], $oUser->Role, $oUser->ClientID) == 1)
	{
		$Notes="Package Deleted";
	}
	else
	{	
		$Notes="Package cannot be deleted";
	}

	header("location: index.php?Notes=".$Notes);	

exit();

?>

