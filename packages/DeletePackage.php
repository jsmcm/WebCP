<?php
session_start();

function __autoload($classname)
{
        require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.".$classname.".php");
}

$oPackage = new Package();
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

