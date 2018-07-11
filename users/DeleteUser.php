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

if($ClientID == $_REQUEST["id"])
{
	$Notes = "User cannot delete themselves!";
}
else
{

	$oUser = new User();
	if($oUser->DeleteUser($ClientID, $oUser->Role, $_REQUEST["id"]) == 1)
	{
		$Notes="User user Deleted";
	}
	else
	{	
		$Notes="User user cannot be deleted";
	}
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

