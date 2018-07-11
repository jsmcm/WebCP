<?php
session_start();

require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.User.php");
$oUser = new User();

$ClientID = $oUser->getClientId();
if($ClientID < 1)
{
        header("Location: /index.php");
        exit();
}

require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.FTP.php");
$oFTP = new FTP();
if($oFTP->DeleteFTP($ClientID, $oUser->Role, $_REQUEST["id"]) == 1)
{
	$Notes="FTP user Deleted";
}
else
{	
	$Notes="FTP user cannot be deleted";
}

if(isset($_REQUEST["ClientID"]))
{
	header("location: index.php?Notes=".$Notes."&ClientID=".$_REQUEST["ClientID"]);	
}
else
{
	header("location: index.php?Notes=".$Notes);	
}
exit();

?>

