<?php
session_start();

include_once($_SERVER["DOCUMENT_ROOT"]."/vendor/autoload.php");
$oUser = new User();
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



$EmailAddress = "";
if(isset($_REQUEST["EmailAddress"]))
{
	$EmailAddress = $_REQUEST["EmailAddress"];
}
else
{
	header("location: index.php?Notes=Spam Assassin Not Deleted&NoteType=error");
	exit();
}

$oEmail->DeleteSpamAssassin($loggedInId, $Role, $EmailAddress);

header("location: index.php?Notes=Spam Assassin Deleted&NoteType=success");

?>
