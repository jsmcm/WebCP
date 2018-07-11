<?php
session_start();

require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.User.php");
$oUser = new User();

require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.Domain.php");
$oDomain = new Domain();

require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.Email.php");
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
if($oEmail->DeleteBlackWhiteList($loggedInId, $Role, intVal($_REQUEST["id"]), "black") == 1)
{
	$Notes="Black List Deleted";
}
else
{	
	$Notes="Black List cannot be deleted";
}

//print "<p>".$Notes."<p>";

header("location: index.php?Notes=".$Notes);	
exit();

?>

