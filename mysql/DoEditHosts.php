<?php
session_start();

include_once($_SERVER["DOCUMENT_ROOT"]."/vendor/autoload.php");
$oReseller = new Reseller();
$oUser = new User();
$oMySQL = new MySQL();

$ClientID = $oUser->getClientId();
if($ClientID < 1) {
	header("Location: /index.php");
	exit();
}

$ID = intVal($_POST["id"]);
$UserName = filter_var($_POST["UserName"], FILTER_SANITIZE_STRING);
$HostList = filter_var($_POST["HostList"], FILTER_SANITIZE_STRING);

$MySQLOwner = $oMySQL->GetMySQLOwner($ID);


$MySQLUserName = "";
$MySQLDatabaseName = "";
$oMySQL->GetMySQLInfo($ID, $MySQLUserName, $MySQLDatabaseName);

$Role = $oUser->Role;
$DomainUserName = substr($UserName, 0, strpos($UserName, "_"));

$Password = $oMySQL->GetDatabasePassword($ID, $DomainUserName, $UserName, $MySQLDatabaseName, $MySQLOwner);


$ResellerPermission = false;
if($oUser->Role == "reseller") {

	$random = random_int(1, 100000);
	$nonceArray = [
		$oUser->Role,
		$oUser->getClientId(),
		$MySQLOwner,
		$random
	];
	
	$oSimpleNonce = new SimpleNonce();
	
	$nonce = $oSimpleNonce->GenerateNonce("getClientResellerID", $nonceArray);
	$ResellerID = $oReseller->GetClientResellerID($MySQLOwner, $random, $nonce);
	
	if($ResellerID == $ClientID) {
		$ResellerPermission = true;
	}
	
}

if( ($MySQLOwner != $ClientID) && ($Role != "admin") ) {
	if($ResellerPermission == false) {
		header("location: index.php?NoteType=oops, something went wrong&NoteType=error");
		exit();
	}
}

if($UserName != $MySQLUserName) {
	header("location: index.php?NoteType=oops, something went wrong&NoteType=error");
	exit();
}

$HostArray = array();
$HostArray = explode("\n", $HostList);
$oMySQL->DeleteUserNameHosts($UserName);

foreach($HostArray as $NextHost)
{
	if(trim($NextHost) != "")
	{
		$oMySQL->CreateUserUserName(trim($DomainUserName), trim($UserName), trim($Password), trim($NextHost));
	}
}

$oMySQL->FlushPrivileges();

header("location: index.php?NoteType=Success&Notes=Hosts updated");


