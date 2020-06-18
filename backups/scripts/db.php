<?php
session_start();

include_once($_SERVER["DOCUMENT_ROOT"]."/vendor/autoload.php");

if (!file_exists($_SERVER["DOCUMENT_ROOT"]."/../backups/tmp/")) {
    mkdir($_SERVER["DOCUMENT_ROOT"]."/../backups/tmp/", 0755);
}


if (!file_exists($_SERVER["DOCUMENT_ROOT"]."/nm/")) {
    mkdir($_SERVER["DOCUMENT_ROOT"]."/nm/", 0755);
}

$oUser = new User();
$oSettings = new Settings();


$ClientID = $oUser->getClientId();
$Role = $oUser->Role;

if ($_SERVER["REMOTE_ADDR"] != $_SERVER["SERVER_ADDR"]) {
    if ($ClientID < 1) {
        header("Location: /index.php");
        exit();
    }
}

if (isset($_REQUEST["DomainID"])) {
    $DomainID = intVal($_REQUEST["DomainID"]);
} else {
    header("Location: ../index.php?Notes=Error, please try again!");
}

$Type = "adhoc";
if (isset($_REQUEST["Type"])) {
    $Type = trim(strtolower(filter_var($_REQUEST["Type"], FILTER_SANITIZE_STRING)));
}

$EmailAddress = "";
if (isset($_REQUEST["EmailAddress"])) {
    $EmailAddress = trim(filter_var($_REQUEST["EmailAddress"], FILTER_SANITIZE_EMAIL));
}

$RandomString = "";
if (isset($_REQUEST["RandomString"])) {
    $RandomString = trim(filter_var($_REQUEST["RandomString"], FILTER_SANITIZE_STRING));
}

$ReturnURL = "";
if (isset($_REQUEST["ReturnURL"])) {
    $ReturnURL = trim(filter_var($_REQUEST["ReturnURL"], FILTER_SANITIZE_URL));
}

$Notes = "";
if (isset($_REQUEST["Notes"])) {
    $Notes = "Notes=".trim(filter_var($_REQUEST["Notes"], FILTER_SANITIZE_STRING));
}

$XMLContent = new SimpleXMLElement('<?xml version="1.0" ?><BackupScript />');


$oDomain = new Domain();


$random = random_int(1, 1000000);
$nonceArray = [	
	$oUser->Role,
	$oUser->ClientID,
	$DomainID,
	$random
];
$oSimpleNonce = new SimpleNonce();
$nonce = $oSimpleNonce->GenerateNonce("getDomainInfo", $nonceArray);

$DomainArray = array();
$oDomain->GetDomainInfo($DomainID, $random, $DomainArray, $nonce);


$oPackage = new Package();

$PackageSettingValues = array();
$ArrayCount = 0;

$oPackage->GetPackageDetails($DomainArray["PackageID"], $PackageSettingValues, $ArrayCount, $Role, $ClientID);

$PackageXML = $XMLContent->addChild("Package");
foreach ($PackageSettingValues as $key=>$val) {
    $PackageXML->addChild($key, $val);
}


$ClientArray = array();
$oUser->GetUserInfoArray($DomainArray["ClientID"], $ClientArray);

$ClientXML = $XMLContent->addChild("User");
foreach ($ClientArray as $key=>$val) {
    $ClientXML->addChild($key, $val);
}

$Role = $ClientArray["Role"];


$DomainListArray = array();
$oDomain->GetDomainTree($DomainID, $DomainListArray, $ArrayCount);

$DomainXML = $XMLContent->addChild("Domain");
for ($x = 0; $x < $ArrayCount; $x++) {
    $DomainInstanceXML = $DomainXML->addChild("Instance");
    foreach ($DomainListArray[$x] as $key=>$val) {
        $DomainInstanceXML->addChild($key, $val);
    }
}


$MySQLArray = array();

$oMySQL = new MySQL();
$oMySQL->GetMySQLDomainList($MySQLArray, $ArrayCount, $DomainArray["UserName"]);

$MySQLXML = $XMLContent->addChild("MySQL");

for($x = 0; $x < $ArrayCount; $x++)
{
	$MySQLInstanceXML = $MySQLXML->addChild("Instance");
	foreach($MySQLArray[$x] as $key=>$val)
	{
		$MySQLInstanceXML->$key = $val;
	}
}

$EmailArray = array();
$oEmail = new Email();

$oEmail->GetDomainEmailList($EmailArray, $ArrayCount, $DomainArray["UserName"]);

$EmailXML = $XMLContent->addChild("Email");

for ($x = 0; $x < $ArrayCount; $x++) {
    $EmailInstanceXML = $EmailXML->addChild("Instance");
    foreach ($EmailArray[$x] as $key=>$val) {
        $EmailInstanceXML->addChild($key, $val);
    }
}



$EmailForwardingArray = array();

$oEmail->GetDomainSingleForwardList($EmailForwardingArray, $ArrayCount, $DomainArray["UserName"]);

$EmailXML = $XMLContent->addChild("EmailForwarding");

for ($x = 0; $x < $ArrayCount; $x++) {
    $EmailInstanceXML = $EmailXML->addChild("Instance");
    foreach ($EmailForwardingArray[$x] as $key=>$val) {
        $EmailInstanceXML->addChild($key, $val);
    }
}



$EmailOptionsArray = array();

$oEmail->GetDomainEmailOptionsList($EmailOptionsArray, $ArrayCount, $DomainArray["DomainName"]);

$EmailXML = $XMLContent->addChild("EmailOptions");

for ($x = 0; $x < $ArrayCount; $x++) {
    $EmailInstanceXML = $EmailXML->addChild("Instance");
    foreach ($EmailOptionsArray[$x] as $key=>$val) {
	$EmailInstanceXML->addChild($key, $val);
    }
}


$AutoReplyArray = array();
$oEmail->GetAutoReplyList($AutoReplyArray, $ArrayCount, $DomainArray["ClientID"], $Role);

$AutoReplyXML = $XMLContent->addChild("AutoReply");

for ($x = 0; $x < $ArrayCount; $x++) {
    if( $oEmail->GetDomainIDFromEmailID($AutoReplyArray[$x]["MailBoxID"]) == $DomainID) {
	$AutoReplyInstanceXML = $AutoReplyXML->addChild("Instance");
	foreach($AutoReplyArray[$x] as $key=>$val) {
	    $AutoReplyInstanceXML->addChild($key, $val);
	}
    }
}





$oFTP = new FTP();
$FTPArray = array();

$oFTP->GetDomainFTPList($FTPArray, $ArrayCount, $DomainID);

$FTPXML = $XMLContent->addChild("FTP");

for ($x = 0; $x < $ArrayCount; $x++) {
    $FTPInstanceXML = $FTPXML->addChild("Instance");
    foreach ($FTPArray[$x] as $key=>$val) {
        $FTPInstanceXML->addChild($key, $val);
    }
}

if ($RandomString == "") {
    $RandomString = date("Y-m-d_H-i-s")."_";
    $RandomString = $RandomString.rand(0,9);
    $RandomString = $RandomString.rand(0,9);
    $RandomString = $RandomString.rand(0,9);
    $RandomString = $RandomString.rand(0,9);
    $RandomString = $RandomString.rand(0,9);
    $RandomString = $RandomString.rand(0,9);

    while (is_dir($_SERVER["DOCUMENT_ROOT"]."/../backups/tmp/".$RandomString)) {
        $RandomString = date("Y-m-d_H-i-s")."_";
        $RandomString = $RandomString.rand(0,9);
	$RandomString = $RandomString.rand(0,9);
	$RandomString = $RandomString.rand(0,9);
	$RandomString = $RandomString.rand(0,9);
	$RandomString = $RandomString.rand(0,9);
	$RandomString = $RandomString.rand(0,9);
    }
}

//print "<p>Making: '".$_SERVER["DOCUMENT_ROOT"]."/backups/tmp/".$RandomString."'<p>";
mkdir($_SERVER["DOCUMENT_ROOT"]."/../backups/tmp/".$RandomString, 0755);
chmod($_SERVER["DOCUMENT_ROOT"]."/../backups/tmp/".$RandomString, 0755);

$fp = fopen($_SERVER["DOCUMENT_ROOT"]."/../backups/tmp/".$RandomString."/".$DomainArray["UserName"].".xml", "w");
fwrite($fp, $XMLContent->asXML());
fclose($fp);


$FTPSettingsArray = array();
$oSettings->GetFTPBackupSettings($FTPSettingsArray);

$FTPHost = "";
if (isset($FTPSettingsArray["FTPHost"])) {
    $FTPHost = $FTPSettingsArray["FTPHost"];
}

$FTPRemotePath = "";
if (isset($FTPSettingsArray["FTPRemotePath"])) {
    $FTPRemotePath = $FTPSettingsArray["FTPRemotePath"];
}

$FTPUserName = "";
if (isset($FTPSettingsArray["FTPUserName"])) {
    $FTPUserName = $FTPSettingsArray["FTPUserName"];
}

$FTPPassword = "";
if (isset($FTPSettingsArray["FTPPassword"])) {
    $FTPPassword = $FTPSettingsArray["FTPPassword"];
}




$fp = fopen($_SERVER["DOCUMENT_ROOT"]."/nm/".$DomainID.".backup", "w");
fwrite($fp, "RandomString=".$RandomString."\n");
fwrite($fp, "DomainName=".$DomainArray["DomainName"]."\n");
fwrite($fp, "DomainUserName=".$DomainArray["UserName"]."\n");
fwrite($fp, "DomainPath=".$DomainArray["Path"]."\n");
fwrite($fp, "Adhoc=all\n");


if ($FTPHost != "") {
    fwrite($fp, "FTPHost=".$FTPHost."\n");
    fwrite($fp, "FTPRemotePath=".$FTPRemotePath."\n");
    fwrite($fp, "FTPUserName=".$FTPUserName."\n");
    fwrite($fp, "FTPPassword=".$FTPPassword."\n");
}


if ($EmailAddress != "") {
    fwrite($fp, "EmailAddress=".$EmailAddress."\n");
}

fclose($fp);

if ($ReturnURL != "") {
    header("Location: ".$ReturnURL."?".$Notes);
}
