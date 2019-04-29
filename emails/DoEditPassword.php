<?php
session_start();

include_once($_SERVER["DOCUMENT_ROOT"]."/vendor/autoload.php");
$oSimpleNonce = new SimpleNonce();
$oUser = new User();
$oEmail = new Email();

require($_SERVER["DOCUMENT_ROOT"]."/includes/License.inc.php");

$ClientID = $oUser->GetClientID();
$loggedInId = $ClientID;

$email_ClientID = $oEmail->getLoggedInEmailId();

if($ClientID < 1)
{
        if( $email_ClientID < 1 )
        {
                header("Location: /index.php");
                exit();
        }
        $loggedInId = $email_ClientID;
}



$ID = intVal($_POST["id"]);
$Password = filter_var($_POST["Password"], FILTER_SANITIZE_STRING);

$emailAddress = filter_Var($_POST["emailAddress"], FILTER_SANITIZE_EMAIL);

$Nonce = filter_var($_POST["Nonce"], FILTER_SANITIZE_STRING);
$TimeStamp = filter_var($_POST["TimeStamp"], FILTER_SANITIZE_STRING);

$MetaData = array("id"=>$ID, "emailAddress"=>$emailAddress, "loggedInID"=>$loggedInId);
if( ! $oSimpleNonce->VerifyNonce($Nonce, "doEditEmailAddress", $TimeStamp, $MetaData) )
{
	header("Location: index.php");
	exit();
}


$Role = $oUser->Role;

$UserName = "";
$LocalPart = "";
$DomainName = "";
$DomainID = 0;

$oEmail->GetEmailInfo($ID, $UserName, $LocalPart, $DomainName, $DomainID);

$Environmental = array('webcp', $LocalPart, $DomainName);
$Options = array('disable' => array('special', "upper"));
$CustomLanguage = array(
'length' => 'must be between %s and %s characters long',
'upper'  => 'must contain at least one uppercase character',
'lower'  => 'must contain at least one lowercase character',
'numeric'=> 'must contain at least one numeric character',
'special'=> 'must contain at least one special character',
'common' => 'too common and / or easily guessed',
'environ'=> "Cannot use the email address or part thereof");

require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.StupidPass.php");
$sp = new StupidPass(40, $Environmental, null, $CustomLanguage, $Options);

$bool = $sp->validate($Password);

if( ! $bool)
{
        $PasswordFailedMessage = "Password verification failed!<p>";

        foreach($sp->GetErrors() as $e)
        {
                $PasswordFailedMessage = $PasswordFailedMessage."<font color=\"red\">";
                $PasswordFailedMessage = $PasswordFailedMessage.$e."<br />";
                $PasswordFailedMessage = $PasswordFailedMessage."</font>";
        }

        header("location: index.php?NoteType=error&Notes=".$PasswordFailedMessage);
        exit();
}


if($oEmail->EditEmailPassword($ID, $Password) < 1)
{
	header("location: index.php?NoteType=Error&Notes=Cannot change password");
	exit();
}

touch(dirname(__DIR__)."/nm/".$UserName.".mailpassword");

header("location: index.php?NoteType=Success&Notes=Password changed");

?>


