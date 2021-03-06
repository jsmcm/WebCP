<?php
session_start();

include_once($_SERVER["DOCUMENT_ROOT"]."/vendor/autoload.php");
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
	$EmailAddress = filter_var($_POST["EmailAddress"], FILTER_SANITIZE_EMAIL);
}
else
{
	header("location: index.php?Notes=Email address not specified&NoteType=Error");
	exit();
}

$SpamSubjectModifier = "";
if(isset($_POST["SpamSubjectModifier"]))
{
	$SpamSubjectModifier = filter_var($_POST["SpamSubjectModifier"], FILTER_SANITIZE_STRING);
}


$SpamWarnLevel = "";
if(isset($_POST["SpamWarnLevel"]))
{
	$SpamWarnLevel = intVal($_POST["SpamWarnLevel"]);
}


$SpamBlockLevel = "";
if(isset($_POST["SpamBlockLevel"]))
{
	$SpamBlockLevel = intVal($_POST["SpamBlockLevel"]);
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

