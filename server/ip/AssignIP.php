<?php

include_once($_SERVER["DOCUMENT_ROOT"]."/vendor/autoload.php");
require($_SERVER["DOCUMENT_ROOT"]."/includes/License.inc.php");

if( ! file_exists($_SERVER["DOCUMENT_ROOT"]."/nm/")) {
	mkdir($_SERVER["DOCUMENT_ROOT"]."/nm/", 0755);
}

$oSSL = new SSL();
$oDNS = new DNS();
$oDomain = new Domain();
$oUser = new User();
$oSimpleNonce = new SimpleNonce();

if ( $oUser->Role != "admin" ) {
	throw new Exception("Only Admins can assign IPs");
}

$clientId = $oUser->getClientId();

$domainId = intVal($_POST["DomainID"]);
$nonceArray = [
	$oUser->Role,
	$clientId,
	$domainId
];

$nonce = $oSimpleNonce->GenerateNonce("getDomainNameFromDomainID", $nonceArray);
$DomainName = $oDomain->GetDomainNameFromDomainID($domainId, $nonce);

$ip = filter_var($_POST["IPAddress"], FILTER_SANITIZE_IP);

if($oDNS->AssignIP($ip, $DomainName) == true) {
	$oSSL->GetCertificatesChainName($DomainName);
	touch($_SERVER["DOCUMENT_ROOT"]."/nm/".$domainId.".subdomain");
}

header("Location: index.php?NoteType=success&Notes=IP Address Assigned");
