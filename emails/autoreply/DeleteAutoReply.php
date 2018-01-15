<?php
session_start();

function __autoload($classname)
{
	require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.".$classname.".php");
}

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



if(isset($_REQUEST["ID"]))
{
	$ID = $_REQUEST["ID"];
}
else
{
	header("location: index.php?Notes=Auto Reply Not Deleted&NoteType=error");
	exit();
}

if($Role == 'admin')
{
	$AutoReplyArray = array();
	$oEmail->GetAutoReplyDetail($AutoReplyArray, $ID, $ClientID, $Role);	
	$ClientID = $AutoReplyArray["ClientID"];
	$loggedInId = $ClientID;
}


$oEmail->DeleteAutoReply($loggedInId, $Role, $ID);

header("location: index.php?Notes=Auto Reply Deleted&NoteType=success");

?>
