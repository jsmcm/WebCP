<?php

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



$MailBoxID = -1;
if(isset($_POST["MailBoxID"]))
{
        $MailBoxID = $_POST["MailBoxID"];
}



$oEmail = new Email();

$DomainOwnerID = $oEmail->GetClientIDFromMailBoxID($MailBoxID);

$oEmail->EditAutoReply($_POST["AutoReplyID"], $_POST["Subject"], $_POST["MessageBody"], $_POST["Frequency"], "2000-01-01 00:00:00", "9999-01-01 00:00:00", $DomainOwnerID);

header("location: index.php");

?>
