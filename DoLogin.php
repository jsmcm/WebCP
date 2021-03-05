<?php
session_start();

//if (! file_exists($_SERVER["DOCUMENT_ROOT"]."/includes/license.conf")) {
//    header("Location: index.php?Notes=License file not found, please contact support OR<br><a href=\"enter_license.php\">Click here to enter your license key</a>");
//    exit();
//}

include_once($_SERVER["DOCUMENT_ROOT"]."/vendor/autoload.php");

$oEmail = new Email();
$oUser = new User();
$oLog = new Log();
$oUtils = new Utils();

/*
$LicenseKey = "free";

if (file_exists($_SERVER["DOCUMENT_ROOT"]."/includes/license.conf")) {
	$LicenseKey = file_get_contents($_SERVER["DOCUMENT_ROOT"]."/includes/license.conf");
}
$key = $oUtils->getValidationKey($LicenseKey);


if( $key == "expired" ) {
	header("location: index.php?Notes=License is expired. Please renew or contact support: <a href=\"https://webcp.io\">webcp.io</a>");
	exit();
} else if ($key == "not-found" ) {
	header("location: index.php?Notes=License not found. Please register for one at: <a href=\"https://webcp.io\">webcp.io</a><p><a href=\"/enter_license.php\">Enter License Key</a>");
	exit();
}


$validationData = $oUtils->getValidationData($key);

if ( $validationData === false ) {
	header("location: index.php?Notes=Session expired, please retry logging in");
	exit();
}

$validationArray = json_decode($validationData, true);

if ( ($oUtils->ValidateHash($validationArray["hash"], $LicenseKey) !== true) || $validationArray["status"] != "valid" ) {
 	if ( file_exists("/tmp/webcp/getValidationKey_".$LicenseKey) ) {
        	unlink("/tmp/webcp/getValidationKey_".$LicenseKey);
        }

	header("location: index.php?Notes=License failed, please try logging in again or contact support.<p>Note: We have a known bug which reports this error from time to time. Usually logging in again resolves it.</p>");
	exit();
}
*/

$emailAddress = "";

if ( isset($_POST["EmailAddress"]) ) {
    $emailAddress = filter_var($_POST["EmailAddress"], FILTER_SANITIZE_EMAIL);
} else {
	header("Location: /");
}

$password = "";

if( isset($_POST["password"]) ) {
	$password = filter_var($_POST["password"], FILTER_SANITIZE_STRING);
} else {
	header("Location: /");
}

$oLog->WriteLog("DEBUG", "/DoLogin.php -> CheckingLoginCredentials");

$ClientID = $oUser->CheckLoginCredentials($emailAddress, $password);

if($ClientID > 0) {
	$oLog->WriteLog("DEBUG", "/DoLogin.php -> Log in suceeded, redirecting to /domains/index.php");

	if($emailAddress == "admin@admin.admin") {
		header("Location: /users/AddUser.php?NoteType=Error&Notes=Please update your profile before continuing...&ClientID=".$ClientID);
	} else {
		header("Location: /domains/index.php");
	}

	exit();
} else {
	$x = $oEmail->logInEmailAccount($emailAddress, $password);

	// Check if its a mail user trying to admin their own email address...
	if( $x !== false ) {
		header("Location: /emails/index.php");
		exit();
	} else {
		// If we're here, the login failed. Write it to a failed log so that it can be picked up by fail2ban


		file_put_contents("/var/log/webcp/failedlog", date("Y-m-d H:i:s")." - Failed Login Attempt - IP Address = ".$_SERVER["REMOTE_ADDR"]."\r\n", FILE_APPEND);
		header("Location: index.php?Notes=Login failed!");
	}
}

