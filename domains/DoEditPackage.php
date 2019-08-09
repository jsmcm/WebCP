<?php

session_start();

include_once($_SERVER["DOCUMENT_ROOT"]."/vendor/autoload.php");
$oUser = new User();
$oDomain = new Domain();
$oPackage = new Package();
$oLog = new Log();
$oFTP = new FTP();

require($_SERVER["DOCUMENT_ROOT"]."/includes/License.inc.php");

$ClientID = $oUser->getClientId();
if($ClientID < 1) {
	header("Location: /index.php");
	exit();
}

$DomainID = -1;
if(isset($_POST["DomainID"])) {
	$DomainID = $_POST["DomainID"];
}

if($DomainID < 1) {
	header("location: index.php?Notes=Error updating package");
	exit();
}


$PackageID = -1;
if(isset($_POST["PackageID"])) {
	$PackageID = intVal($_POST["PackageID"]);
}

if($PackageID < 1) {
	header("location: index.php?Notes=Error updating package");
	exit();
}

$random = random_int(1, 100000);
$nonceArray = [
	$oUser->Role,
	$oUser->ClientID,
	$DomainID,
	$random
];

$nonce = $oSimpleNonce->GenerateNonce("getDomainOwner", $nonceArray);
$DomainOwnerID = $oDomain->GetDomainOwner($DomainID, $random, $nonce);

if( ($DomainOwnerID != $ClientID) && ($oUser->Role != "admin") ) {
	header("location: index.php?Notes=Error updating package");
	exit();
}

$oDomain->UpdateDomainPackage($DomainID, $PackageID);

$DiskSpace = $oPackage->GetPackageAllowance("DiskSpace", $PackageID);
$oFTP->UpdateDomainFTPDiskQuotas($DomainID, $DiskSpace);
$oPackage->CreateDiskQuotaScriptForDomain($DomainID, $DiskSpace);

header("Location: index.php?Notes=Package updated");
