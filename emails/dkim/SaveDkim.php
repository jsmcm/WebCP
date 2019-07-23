<?php
session_start();

include_once($_SERVER["DOCUMENT_ROOT"]."/vendor/autoload.php");

$oUser = new User();
$oDomain = new Domain();

$ClientID = $oUser->getClientId();
if($ClientID < 1) {
	header("Location: /index.php");
	exit();
}

$Role = $oUser->Role;
$DomainName = "";
$DomainOwnerID = -1;
$DomainID = -1;
$Dkim = "";

if(isset($_REQUEST["domain_id"])) {
	$Dkim = $_REQUEST["dkim"];
	$DomainID = $_REQUEST["domain_id"];

	$InfoArray = array();
	$oDomain->GetDomainInfo($DomainID, $InfoArray);

	$DomainName = $InfoArray["DomainName"];
	$DomainOwnerID = $InfoArray["ClientID"];
} else {
    print "Please select correct domain&NoteType=Error";
	exit();
}

if($DomainOwnerID  != $DomainID) {
	if($Role != 'admin') {
		print "You do not have permission to edit this domain!";
		exit();
	}
}

if( $Dkim == "enabled" ) {
	touch("/etc/exim4/dkim/".$DomainName);
} else {
	unlink("/etc/exim4/dkim/".$DomainName);
}

print "Dkim updated for ".$DomainName." to ".$Dkim." dkim!";

