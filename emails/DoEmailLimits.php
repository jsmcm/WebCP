<?php
session_start();

function __autoload($classname)
{
        require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.".$classname.".php");
}

$oUser = new User();
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

//print "Max per hour: ".$_POST["MaxPerHour"]."<br>";
//print "Max recipients: ".$_POST["MaxRecipients"]."<br>";
//print "Domain name: ".$_POST["DomainName"]."<br>";

$oEmail->SaveEmailOptions("max_per_hour", $_POST["MaxPerHour"], $_POST["DomainName"]);
$oEmail->SaveEmailOptions("max_recipients", $_POST["MaxRecipients"], $_POST["DomainName"]);

$ReturnURL = $_SERVER["HTTP_REFERER"];
//print "ReturnURL: ".$ReturnURL."<br>";

$ReturnURL = substr($_SERVER["HTTP_REFERER"], strrpos($_SERVER["HTTP_REFERER"], "/") + 1);
//print "ReturnURL: ".$ReturnURL."<br>";

if(strpos($ReturnURL, "?") > -1)
{
	$ReturnURL = substr($ReturnURL, 0, strpos($ReturnURL, "?"));
}

//print "ReturnURL: ".$ReturnURL."<br>";


header("location: ./".$ReturnURL."?Notes=Email limits saved&DomainName=".$_POST["DomainName"]);

?>


