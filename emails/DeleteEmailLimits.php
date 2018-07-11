<?php
session_start();

require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.User.php");
$oUser = new User();

require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.Email.php");
$oEmail = new Email();

$ClientID = $oUser->getClientId();
if($ClientID < 1)
{
        header("Location: /index.php");
        exit();
}

$Role = $oUser->Role;

if($Role != "admin")
{
	header("location: /index.php");
	exit();
}

$oEmail->DeleteEmailOptions('max_per_hour', $_POST["DomainName"]);
$oEmail->DeleteEmailOptions('max_recipients', $_POST["DomainName"]);

$ReturnURL = $_SERVER["HTTP_REFERER"];
//print "ReturnURL: ".$ReturnURL."<br>";

$ReturnURL = substr($_SERVER["HTTP_REFERER"], strrpos($_SERVER["HTTP_REFERER"], "/") + 1);
//print "ReturnURL: ".$ReturnURL."<br>";

if(strpos($ReturnURL, "?") > -1)
{
	$ReturnURL = substr($ReturnURL, 0, strpos($ReturnURL, "?"));
}

//print "ReturnURL: ".$ReturnURL."<br>";


header("location: ./".$ReturnURL."?Notes=Email limits set to defaults&DomainName=".$_POST["DomainName"]);

?>


