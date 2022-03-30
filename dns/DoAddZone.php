<?php
session_start();

include_once $_SERVER["DOCUMENT_ROOT"]."/vendor/autoload.php";

$oUser = new User();
$oDNS = new DNS();
$oLog = new Log();
$oDomain = new Domain();

$ClientID = $oUser->getClientId();
if($ClientID < 1){
	header("Location: /index.php");
	exit();
}

$ZoneName = filter_var($_POST["ZoneName"], FILTER_SANITIZE_URL);
if($ZoneName != $_POST["ZoneName"]) {
	header("location: index.php?Notes=Invalid Domain Name&NoteType=error");
	exit();
}

$IPv4 = filter_var($_POST["IPv4"], FILTER_VALIDATE_IP);
if($IPv4 != $_POST["IPv4"]) {
	header("location: index.php?Notes=Invalid IPv4&NoteType=error");
	exit();
}

$IPv6 = filter_var($_POST["IPv6"], FILTER_VALIDATE_IP);
if($IPv6 != $_POST["IPv6"]) {
	header("location: index.php?Notes=Invalid IPv6&NoteType=error");
	exit();
}

if( ($IPv4 == "") && ($IPv6 == "") ) {
	header("location: index.php?Notes=Need at least one IP address&NoteType=error");
	exit();
}


$Role = $oUser->Role;

if($oDNS->ValidateDomainName($ZoneName) < 1) {
	header("Location: index.php?NoteType=Error&Notes=Incorrectly formatted domain name - <b>".$ZoneName."</b>");
	exit();
}

if(substr($ZoneName, 0, 7) == "http://") {
	$ZoneName = substr($ZoneName, 7);
}

if(substr($ZoneName, 0, 4) == "www.") {
	$ZoneName = substr($ZoneName, 4);
}


for($x = 0; $x < strlen($ZoneName); $x++) {
			
	if(!ctype_alnum($ZoneName[$x])) {
		if($ZoneName[$x] != '_' && $ZoneName[$x] != '-' && $ZoneName[$x] != '.') {
			header("location: index.php?NoteType=Error&Notes=Incorrectly formatted domain name");
			exit();
		}
	}
}




if($oDNS->DomainExists($ZoneName) > 0) {
	header("location: index.php?NoteType=Error&Notes=Zone already exists");
	exit();
}

$Error = "";

if($oDNS->AddZone($ZoneName, $IPv4, $IPv6, "", $ClientID) < 1) {
	header("location: index.php?NoteType=Error&Notes=Cannot add domain");
	exit();
}

header("location: index.php?NoteType=Message&Notes=Zone added<br>".$Error);
