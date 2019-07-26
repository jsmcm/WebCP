<?php
session_start();
include_once($_SERVER["DOCUMENT_ROOT"]."/vendor/autoload.php");

$oUser = new User();
$oDomain = new Domain();
$oPackage = new Package();
$oSimpleNonce = new SimpleNonce();

require($_SERVER["DOCUMENT_ROOT"]."/includes/License.inc.php");

$ClientID = $oUser->getClientId();
if($ClientID < 1) {
	header("Location: /ListParkedDomains.php");
	exit();
}

$DomainID = $_REQUEST["DomainID"];
$Role = $oUser->Role;
$DomainOwner = $oDomain->GetDomainOwner($DomainID);

$nonceArray = [
	$oUser->Role,
	$oUser->getClientId(),
	$DomainID
];

$nonce = $oSimpleNonce->GenerateNonce("getDomainNameFromDomainID", $nonceArray);
$PrimaryDomain = $oDomain->GetDomainNameFromDomainID($DomainID, $nonce);
$AncestorDomainID = $oDomain->GetAncestorDomainID($DomainID);

$DomainInfoArray = array();
$oDomain->GetDomainInfo($DomainID, $DomainInfoArray);

$DomainUserName = $DomainInfoArray["UserName"];
$PackageID = $DomainInfoArray["PackageID"];

$ParkedDomainAllowance = $oPackage->GetPackageAllowance("ParkedDomains", $PackageID);

if( ($ClientID != $DomainOwner) && ($Role != 'admin')) {
	print "<font color=\"red\"><b>FATAL ERROR:</b> You do not have the correct permissions to do this!<br></font>";
	exit();
}

$Error = array();

/// loop starts here
$DomainArray = array();
$DomainArray = explode("\n", file_get_contents("./bulk.txt"));

for($DomainArrayCount = 0; $DomainArrayCount < count($DomainArray); $DomainArrayCount++) {
	$ParkedDomain = $DomainArray[$DomainArrayCount];

	$ParkedDomainUsage = $oPackage->GetParkedDomainUsage($DomainUserName);
	
	if( ($ParkedDomainUsage >= $ParkedDomainAllowance) && ($oUser->Role != "admin") ) {
		array_push($Error, "<font color=\"red\"><b>FATAL ERROR:</b> You have used all of your parked domains available, no more can be added</font><br>");
		break;
	}
	
	if( substr($ParkedDomain, strlen($ParkedDomain) - 1, 1) == ".") {
		array_push($Error, "<font color=\"red\">This domain looks incorrectly formatted (trailing period - ".$ParkedDomain."), can't add</font><br>");
		continue;
	}
	
	if(!strstr($ParkedDomain, ".")) {
		array_push($Error, "<font color=\"red\">This domain looks incorrectly formatted (not a FQDN - ".$ParkedDomain."), can't add</font><br>");
		continue;
	}
	
	if(substr($ParkedDomain, 0, 7) == "http://") {
		$ParkedDomain = substr($ParkedDomain, 7);
	}
	
	if(substr($ParkedDomain, 0, 4) == "www.") {
		$ParkedDomain = substr($ParkedDomain, 4);
	}
	
	
	for($x = 0; $x < strlen($ParkedDomain); $x++) {
		if(!ctype_alnum($ParkedDomain[$x])) {
			if($ParkedDomain[$x] != '_' && $ParkedDomain[$x] != '-' && $ParkedDomain[$x] != '.') {
				array_push($Error, "<font color=\"red\">This domain looks incorrectly formatted (invalid characters - ".$ParkedDomain."), can't add</font><br>");
				continue;
			}
			
		}
	}
	
	if($oDomain->DomainExists($ParkedDomain) > 0) {
		array_push($Error, "<font color=\"red\">This domain already exists on the system, can't add</font><br>");
		continue;
	}

	if($oDomain->AddParkedDomain($ParkedDomain, $PrimaryDomain, $PackageID, $DomainOwner, $DomainID, $ErrorString) < 1) {
		array_push($Error, "<font color=\"red\">Unspecified error adding domain ".$ParkedDomain.", can't add (".$ErrorString.")</font><br>");
	} else {
		array_push($Error, "<font color=\"green\">Added domain ".$ParkedDomain."</font><br>");
	}

}

for($x = 0; $x < count($Error); $x++) {
	print $Error[$x];
}

unlink("./bulk.txt");

