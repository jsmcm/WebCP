<?php
session_start();

function __autoload($classname)
{
	require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.".$classname.".php");
}

//if( ! file_exists($_SERVER["DOCUMENT_ROOT"]."/includes/license.conf"))
//{
//	header("Location: index.php?Notes=License file not found, please contact support OR<br><a href=\"enter_license.php\">Click here to enter your license key</a>");
//	exit();
//}


$oUser = new User();
$oEmail = new  Email();
$oLog = new Log();
$oUtils = new Utils();

if( ! file_exists($_SERVER["DOCUMENT_ROOT"]."/includes/Variables.inc.php"))
{
	$oUtils->CreateDefaultVariables();
}

$LicenseKey = file_get_contents($_SERVER["DOCUMENT_ROOT"]."/includes/license.conf");
//print "LicenseKey: ".$LicenseKey."<p>";
//$Hash = $oUtils->GetValidationHash($LicenseKey);
//print "Hash: ".$Hash."<p>";

//if($oUtils->ValidateHash($Hash, $LicenseKey) == true)
//{
	//print "Writing activation.dat: ".md5($LicenseKey.$_SERVER["SERVER_ADDR"].date("Y-m-t 23:59:59"))."<p>";
	//file_put_contents($_SERVER["DOCUMENT_ROOT"]."/includes/activation.dat", md5($LicenseKey.$_SERVER["SERVER_ADDR"].date("Y-m-t 23:59:59")));
//}
//else
//{
	//print "License Failed<p>";
	//exit();
	//header("location: index.php?Notes=Error with license file or license expired, please contact support<p>".$Hash);
	//exit();
//}

//print "LicensePassed<p>";
//exit();

$EmailAddress = $_POST["EmailAddress"];
$Password = $_POST["password"];


$oLog->WriteLog("DEBUG", "/DoLogin.php -> CheckingLoginCredentials");

$ClientID = $oUser->CheckLoginCredentials($EmailAddress, $Password);

if($ClientID > 0)
{
	$oLog->WriteLog("DEBUG", "/DoLogin.php -> Log in suceeded, redirecting to /domains/index.php");

	if($EmailAddress == "admin@admin.admin")
	{
		header("Location: /users/AddUser.php?NoteType=Error&Notes=Please update your profile before continuing...&ClientID=".$ClientID);
	}
	else
	{
		header("Location: /domains/index.php");
	}

	exit();
}
else
{
	$x = $oEmail->logInEmailAccount($EmailAddress, $Password);

	// Check if its a mail user trying to admin their own email address...
	if( $x !== false )
	{
		header("Location: /emails/index.php");
		exit();
	}
	else
	{
		// If we're here, the login failed. Write it to a failed log so that it can be picked up by fail2ban
		$Log = fopen($_SERVER["DOCUMENT_ROOT"]."/failedlog", 'a');
			fwrite($Log, date("Y-m-d H:i:s")." - Failed Login Attempt - IP Address = ".$_SERVER["REMOTE_ADDR"]."\r\n");
		fclose($Log);

		header("Location: index.php?Notes=Login failed!");
	}
}


?>

