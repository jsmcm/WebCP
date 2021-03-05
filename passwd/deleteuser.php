<?php
session_start();
	
include_once($_SERVER["DOCUMENT_ROOT"]."/vendor/autoload.php");
$oUser = new User();
$oDomain = new Domain();


$UserArray = array();

$UserName = $_POST["UserName"];
$PasswordPath = $_POST["PasswordPath"];	
$URL = $_POST["URL"];
$Path = $_POST["Path"];

$nonceArray = [
	$oUser->Role,
	$oUser->ClientID,
	$URL
];

$oSimpleNonce = new SimpleNonce();
$nonce = $oSimpleNonce->GenerateNonce("getDomainOwnerFromDomainName", $nonceArray);
if( ($oDomain->GetDomainOwnerFromDomainName($URL, $nonce) != $oUser->ClientID) && ($oUser->Role != "admin") ) {
	header("location: index.php?Notes=You do not have permission to access this sites detail");
	exit();
}



if(file_exists($PasswordPath)) {

	if(filesize($PasswordPath) > 0) {
		$UserArray = explode("\n", file_get_contents($PasswordPath));
	}
}


for($x = 0; $x < count($UserArray); $x++) {
	if(substr($UserArray[$x], 0, strlen($UserName) + 1) == $UserName.":") {
		// Delete this user
		$UserArray[$x] = "";
	}
}


$f = fopen($PasswordPath, "w");

for($x = 0; $x < count($UserArray); $x++) {
	while( substr($UserArray[$x], strlen($UserArray[$x]) - 1, 1) == '\n') {
		$UserArray[$x] = substr($UserArray, 0, strlen($UserArray) - 1);
	}

	if(strlen($UserArray[$x]) > 0) {
		fwrite($f, $UserArray[$x]."\n");
	}
}

fclose($f);


header("location: manage.php?URL=".$URL."&Path=".$Path."&Notes=User Deleted");
