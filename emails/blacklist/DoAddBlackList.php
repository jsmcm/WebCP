<?php
session_start();

function __autoload($classname)
{
	require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.".$classname.".php");
}

$oUser = new User();
$oEmail = new Email();
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
if(isset($_POST["DomainNameOrEmailAddress"]))
{
	$EmailAddress = $_POST["DomainNameOrEmailAddress"];
}
else
{
	header("Location: index.php?Notes=No client email address / domain selected&NoteType=Error");
	exit();
}

$BlackListAddress = "";
if(isset($_POST["BlackListAddress"]))
{
	$BlackListAddress = $_POST["BlackListAddress"];
}
else
{
	header("Location: index.php?Notes=No black list email address / domain selected&NoteType=Error");
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

if($oEmail->AddBlackWhiteList($EmailAddressOwner, $EmailAddress, $BlackListAddress, "black") == 1)
{
	header("Location: index.php?Notes=Black List Added&NoteType=Success&EmailAddress=".$EmailAddress);
}
else
{
	header("Location: index.php?Notes=Black List Failed&NoteType=Error");
}


?>

