<?php
session_start();

include_once($_SERVER["DOCUMENT_ROOT"]."/vendor/autoload.php");
$oUser = new User();
$oDomain = new Domain();
$oEmail = new Email();



$ClientID = $oUser->GetClientID();
$loggedInId = $ClientID;

$email_ClientID = $oEmail->getLoggedInEmailId();

$Role = $oUser->Role;

if($ClientID < 1)
{
        if( $email_ClientID < 1 )
        {
                header("Location: /index.php");
                exit();
        }
        $loggedInId = $email_ClientID;

        $Role = "email";
}




$oEmail = new Email();
if($oEmail->DeleteBlackWhiteList($loggedInId, $Role, intVal($_REQUEST["id"]), "white") == 1)
{
	$Notes="White List Deleted";
}
else
{	
	$Notes="White List cannot be deleted";
}

//print "<p>".$Notes."<p>";

header("location: index.php?Notes=".$Notes);	
exit();

?>

