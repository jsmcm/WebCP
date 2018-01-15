<?php
session_start();

function __autoload($classname)
{
	require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.".$classname.".php");
}

$oUser = new User();
$oEmail = new Email();
$oDomain = new Domain();
$oSettings = new Settings();



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
if(isset($_POST["EmailAddress"]))
{
	$EmailAddress = $_POST["EmailAddress"];
}
else
{
	header("location: index.php?Notes=Email address not specified&NoteType=Error");
	exit();
}

$SpamSubjectModifier = "";
if(isset($_POST["SpamSubjectModifier"]))
{
	$SpamSubjectModifier = $_POST["SpamSubjectModifier"];
}


$SpamWarnLevel = "";
if(isset($_POST["SpamWarnLevel"]))
{
	$SpamWarnLevel = $_POST["SpamWarnLevel"];
}


$SpamBlockLevel = "";
if(isset($_POST["SpamBlockLevel"]))
{
	$SpamBlockLevel = $_POST["SpamBlockLevel"];
}

	$DomainOwnerID = $oEmail->GetClientIDFromEmailAddress($EmailAddress);

	$oEmail->DeleteSpamAssassin($loggedInId, $Role, $EmailAddress);

	if($oEmail->AddSpamAssassin($DomainOwnerID, $EmailAddress, $SpamBlockLevel, $SpamWarnLevel, $SpamSubjectModifier) == true)
	{
		header("location: index.php?Notes=Spam Assassin added&NoteType=Success");
	}
	else
	{	
		header("location: index.php?Notes=Cannot add spam assassin&NoteType=Error");
	}
	
?>

