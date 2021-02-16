<?php

session_start();
include_once($_SERVER["DOCUMENT_ROOT"]."/vendor/autoload.php");
require($_SERVER["DOCUMENT_ROOT"]."/includes/License.inc.php");

$oUser = new User();
$oUtils = new Utils();
$oSimpleNonce = new SimpleNonce();
$oSettings = new Settings();
$oDomain = new Domain();


$ClientID = $oUser->getClientId();
if($ClientID < 1) {
	header("Location: /index.php?1");
	exit();
}

if($oUser->Role != "admin") {
	header("Location: /index.php");
	exit();
}

$domainName = "";
$domainId = 0;

if (isset($_POST["domainName"])) {
	$domainName = filter_var($_POST["domainName"], FILTER_SANITIZE_STRING);
}

if (isset($_POST["domainId"])) {
	$domainId = intVal($_POST["domainId"]);
}


$deleteSettingsOnly = false;
if (isset($_POST["submitButton"]) && $_POST["submitButton"] == "Restore Default Configuration File") {
	
	$deleteSettingsOnly = true;

}

$nonce = filter_var($_POST["nonce"], FILTER_SANITIZE_STRING);
$timeStamp = filter_var($_POST["timeStamp"], FILTER_SANITIZE_STRING);
$version = filter_var($_POST["version"], FILTER_SANITIZE_STRING);
	
$nonceArray = [	
    $oUser->Role,
    $oUser->ClientID,
    $version
];

$nonceResult = $oSimpleNonce->VerifyNonce(
    $nonce, 
    "savePHPConfig", 
    $timeStamp, 
    $nonceArray
);


if ($nonceResult === false) {
	
	header("location: index.php?Notes=Something went wrong, please try again.&noteType=error");
    exit();
    
}

$file = "[PHP]\n";

$opCacheTagged = false;

if ($domainId > 0) {
	$oDomain->deleteDomainSettingsByPrefix($domainId, "php_".$version."_");
}

foreach ($_POST as $key => $value) {

	if ($key != "version" && $key != "nonce" && $key != "timeStamp" && $key != "domainId" && $key != "domainName") {

		$key = str_replace("-", ".", $key);

		if ( (substr($key, 0, 8) == "opcache." || substr($key, 0, 8) == "opcache_") && ($opCacheTagged == false) ) {

			$file .= "\n";
			$file .= "[opcache]\n";
			$opCacheTagged = true;

		}

		if ( ($domainId > 0) && ($deleteSettingsOnly === false) ) {

			$nonceArray = [
				$oUser->Role,
				$ClientID,
				$domainId,
				"php_".$version."_".$key,
				$value
			];
			
			$nonce = $oSimpleNonce->GenerateNonce("saveDomainSetting", $nonceArray);
			$oDomain->saveDomainSetting($domainId, "php_".$version."_".$key, $value, "", "", $nonce);
			

		}
		

		$file .= $key." = ".$value."\n";
		
	}

}

if ($domainId == 0) {
	file_put_contents($_SERVER["DOCUMENT_ROOT"]."/nm/".$version.".phpconfig", $file);
	header("Location: index.php?Notes=PHP Config Saved - it may take a few minutes to load");
}

file_put_contents($_SERVER["DOCUMENT_ROOT"]."/nm/".$domainId.".subdomain", "");	
header("Location: /domains/index.php?Notes=PHP Config Saved - it may take a few minutes to load");


