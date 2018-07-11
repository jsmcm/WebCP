<?php
session_start();

require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.User.php");
$oUser = new User();

require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.Email.php");
$oEmail = new Email();

require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.Domain.php");
$oDomain = new Domain();


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
	header("Location: index.php?Notes=No client email address selected&NoteType=Error");
	exit();
}

$WhiteListAddress = "";
if(isset($_POST["WhiteListAddress"]))
{
	$WhiteListAddress = $_POST["WhiteListAddress"];
}
else
{
	header("Location: index.php?Notes=No black list email address selected&NoteType=Error");
	exit();
}

$EmailAddressOwner = $ClientID;
if($Role == "admin")
{

	if(strstr($EmailAddress, "@"))
	{
		$EmailAddressOwner = $oEmail->GetEmailOwnerFromEmailAddress($EmailAddress);
	}
	else
	{
		$EmailAddressOwner = $oDomain->GetDomainOwnerFromDomainName($EmailAddress);
	}
}
else if( $Role == "email" )
{
	$EmailAddressOwner = $oEmail->GetEmailOwner($loggedInId);
}


//print "EmailAddressOwner: ".$EmailAddressOwner."<br>";
//exit();

if($oEmail->AddBlackWhiteList($EmailAddressOwner, $EmailAddress, $WhiteListAddress, "white") == 1)
{
	header("Location: index.php?Notes=White List Added&NoteType=Success&EmailAddress=".$EmailAddress);
}
else
{
	header("Location: index.php?Notes=White List Failed&NoteType=Error");
}


?>

