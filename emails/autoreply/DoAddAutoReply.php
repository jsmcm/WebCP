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



$Subject = "Autoreply [re: \$h_subject]";
if(isset($_POST["Subject"]))
{
	$Subject = $_POST["Subject"];
}
if(strstr($Subject, "\$h_subject"))
{
	if( ! strstr($Subject, "\$h_subject:"))
	{
		$Subject = str_replace("h_subject", "h_subject:", $Subject);
	}
}


$MessageBody = "";
if(isset($_POST["MessageBody"]))
{
	$MessageBody = $_POST["MessageBody"];
}

$MailBoxID = -1;
if(isset($_POST["MailBoxID"]))
{
	$MailBoxID = $_POST["MailBoxID"];
}

$Frequency = "1d";
if(isset($_POST["Frequency"]))
{
	$Frequency = $_POST["Frequency"];
}

$StartDate = "2000-01-01 00:00:00";
$EndDate = "9999-01-01 00:00:00";

$DomainOwnerID = $oEmail->GetClientIDFromMailBoxID($MailBoxID);


/*
print "DomainOwnerID: ".$DomainOwnerID."<br>";
print "ClientID: ".$ClientID."<br>";
print "Subject: ".$Subject."<br>";
print "MessageBody: ".$MessageBody."<br>";
print "MailBoxID: ".$MailBoxID."<br>";

exit();
*/

if($oEmail->AutoReplyExists($MailBoxID) == false)
{
	if($oEmail->AddAutoReply($DomainOwnerID, $MailBoxID, $Subject, $MessageBody, $Frequency, $StartDate, $EndDate) == true)
	{
		header("location: index.php?Notes=Auto reply added&NoteType=Success");
	}
	else
	{	
		header("location: index.php?Notes=Cannot add aut reply&NoteType=Error");
	}
}
else
{
	header("location: index.php?Notes=Auto reply already exists for that mailbox&NoteType=Error");
}
	
?>

