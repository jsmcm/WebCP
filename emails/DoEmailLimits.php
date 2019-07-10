<?php
session_start();

include_once($_SERVER["DOCUMENT_ROOT"]."/vendor/autoload.php");
$oUser = new User();
$oEmail = new Email();

require($_SERVER["DOCUMENT_ROOT"]."/includes/License.inc.php");

$ClientID = $oUser->getClientId();
if($ClientID < 1) {
        header("Location: /index.php");
        exit();
}

$Role = $oUser->Role;

if($Role != "admin") {
	header("location: /index.php");
	exit();
}

//print "Max per hour: ".$_POST["MaxPerHour"]."<br>";
//print "Max recipients: ".$_POST["MaxRecipients"]."<br>";
//print "Domain name: ".$_POST["DomainName"]."<br>";
$domainName = filter_var($_POST["DomainName"], FILTER_SANITIZE_STRING);
$maxPerHour = intVal($_POST["MaxPerHour"]);
$maxRecipients = intVal($_POST["MaxRecipients"]);

$oEmail->SaveEmailOptions("max_per_hour", $maxPerHour, $domainName);
$oEmail->SaveEmailOptions("max_recipients", $maxRecipients, $domainName);

if ( $domainName == "" ) {
	file_put_contents("/var/www/html/mail/ratelimit", $maxPerHour);
	file_put_contents("/var/www/html/mail/maxrecipients", $maxRecipients);
} else {
	file_put_contents("/var/www/html/mail/domains/".$domainName."/ratelimit", $maxPerHour);
	file_put_contents("/var/www/html/mail/domains/".$domainName."/maxrecipients", $maxRecipients);
}

$ReturnURL = $_SERVER["HTTP_REFERER"];
//print "ReturnURL: ".$ReturnURL."<br>";

$ReturnURL = substr($_SERVER["HTTP_REFERER"], strrpos($_SERVER["HTTP_REFERER"], "/") + 1);
//print "ReturnURL: ".$ReturnURL."<br>";

if(strpos($ReturnURL, "?") > -1) {
	$ReturnURL = substr($ReturnURL, 0, strpos($ReturnURL, "?"));
}

//print "ReturnURL: ".$ReturnURL."<br>";


header("location: ./".$ReturnURL."?Notes=Email limits saved&DomainName=".$_POST["DomainName"]);

