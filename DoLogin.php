<?php
session_start();

if (! file_exists($_SERVER["DOCUMENT_ROOT"]."/includes/license.conf")) {
    header("Location: index.php?Notes=License file not found, please contact support OR<br><a href=\"enter_license.php\">Click here to enter your license key</a>");
    exit();
}


require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.Email.php");
$oEmail = new Email();

require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.User.php");
$oUser = new User();

require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.Log.php");
$oLog = new Log();

require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.Utils.php");
$oUtils = new Utils();


$LicenseKey = file_get_contents($_SERVER["DOCUMENT_ROOT"]."/includes/license.conf");
$Hash = $oUtils->GetValidationHash($LicenseKey);

if ($oUtils->ValidateHash($Hash, $LicenseKey) == true) {
    file_put_contents($_SERVER["DOCUMENT_ROOT"]."/includes/activation.dat", md5($LicenseKey.$_SERVER["SERVER_ADDR"].date("Y-m-t 23:59:59")));
} else {
 


    // The license server has an intermittent issue, so recheck!
    if (strlen($Hash) == 34) {
       
        $Hash = $oUtils->GetValidationHash($LicenseKey);

        if ($oUtils->ValidateHash($Hash, $LicenseKey) == true) {
            file_put_contents($_SERVER["DOCUMENT_ROOT"]."/includes/activation.dat", md5($LicenseKey.$_SERVER["SERVER_ADDR"].date("Y-m-t 23:59:59")));
        } else {
            header("location: index.php?Notes=Error with license file or license expired, please contact support:<p><b>".$Hash."</b><p><a href=\"/enter_license.php\">Enter New License</a>");
            exit();
        }

    } else {
        header("location: index.php?Notes=Error with license file or license expired, please contact support:<p><b>".$Hash."</b><p><a href=\"/enter_license.php\">Enter New License</a>");
        exit();
    }

}

$emailAddress = "";
$emailAddress = filter_var($_POST["EmailAddress"], FILTER_SANITIZE_EMAIL);

$password = "";
$password = filter_var($_POST["password"], FILTER_SANITIZE_STRING);


$oLog->WriteLog("DEBUG", "/DoLogin.php -> CheckingLoginCredentials");

$ClientID = $oUser->CheckLoginCredentials($emailAddress, $password);

if($ClientID > 0)
{
	$oLog->WriteLog("DEBUG", "/DoLogin.php -> Log in suceeded, redirecting to /domains/index.php");

	if($emailAddress == "admin@admin.admin")
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
	$x = $oEmail->logInEmailAccount($emailAddress, $password);

	// Check if its a mail user trying to admin their own email address...
	if( $x !== false )
	{
		header("Location: /emails/index.php");
		exit();
	}
	else
	{
		// If we're here, the login failed. Write it to a failed log so that it can be picked up by fail2ban

		$Log = fopen("/var/log/webcp/failedlog", 'a');
			fwrite($Log, date("Y-m-d H:i:s")." - Failed Login Attempt - IP Address = ".$_SERVER["REMOTE_ADDR"]."\r\n");
		fclose($Log);

		header("Location: index.php?Notes=Login failed!");
	}
}


?>

