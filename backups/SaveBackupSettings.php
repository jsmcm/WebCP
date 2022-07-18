<?php
session_start();

include_once($_SERVER["DOCUMENT_ROOT"]."/vendor/autoload.php");

$oUser = new User();
$oSettings = new Settings();



$ClientID = $oUser->getClientId();
if($ClientID < 1) {
	header("Location: /index.php");
	exit();
}

if($oUser->Role != "admin") {
	header("Location: /index.php");
	exit();
}
	


$FTPHost = filter_input(INPUT_POST, "FTPHost", FILTER_SANITIZE_STRING);
$FTPRemotePath = filter_input(INPUT_POST, "FTPRemotePath", FILTER_SANITIZE_STRING);
$FTPUserName = filter_input(INPUT_POST, "FTPUserName", FILTER_SANITIZE_STRING);
$FTPPassword = filter_input(INPUT_POST, "FTPPassword", FILTER_SANITIZE_STRING);

if ((strlen($FTPRemotePath) > 0) && (substr($FTPRemotePath, strlen($FTPRemotePath) - 1, 1) != "/")) {
    $FTPRemotePath = $FTPRemotePath."/";
}

$oSettings->SaveBackupFTPSettings($FTPHost, $FTPRemotePath, $FTPUserName, $FTPPassword);





$awsBucketName = filter_input(INPUT_POST, "awsBucketName", FILTER_SANITIZE_STRING);
$awsRegion = filter_input(INPUT_POST, "awsRegion", FILTER_SANITIZE_STRING);
$awsKeyId = filter_input(INPUT_POST, "awsKeyId", FILTER_SANITIZE_STRING);
$awsSecretKey = filter_input(INPUT_POST, "awsSecretKey", FILTER_SANITIZE_STRING);

if ($awsBucketName != "" && $awsKeyId != "" && $awsSecretKey != "" && $awsRegion != "") {

    // get the current settings and compare... if the bucket name is different, create a new bucket
    // also, send a test file and read it back to check that things are working.
    $awsSettingsArray = array();
    $awsSettingsArray = $oSettings->getAwsBackupSettings();

    if ($awsSecretKey == "****************") {
        $awsSecretKey = $awsSettingsArray["AWSBackupSecretKey"];
    }


    if (
        ($awsSettingsArray["AWSBackupBucket"] != $awsBucketName) ||
        ($awsSettingsArray["AWSBackupRegion"] != $awsRegion) ||
        ($awsSettingsArray["AWSBackupKeyId"] != $awsKeyId) ||
        ($awsSettingsArray["AWSBackupSecretKey"] != $awsSecretKey)
        ) {
        $oSettings->saveBackupAwsSettings($awsBucketName, $awsRegion, $awsKeyId, $awsSecretKey);

        $configFile = "[default]\r\nregion=".$awsRegion."\r\noutput=json\r\n";
        file_put_contents(dirname(__DIR__)."/nm/config.aws", $configFile);

        $credentialsFile = "[default]\r\naws_access_key_id=".$awsKeyId."\r\naws_secret_access_key=".$awsSecretKey."\r\n";
        file_put_contents(dirname(__DIR__)."/nm/credentials.aws", $credentialsFile);
	}
	
} else {

	file_put_contents(dirname(__DIR__)."/nm/config.aws", "");

	
	file_put_contents(dirname(__DIR__)."/nm/credentials.aws", "");

	
	$oSettings->deleteAwsBackupSettings();

}




$DailyBackupStatus = filter_input(INPUT_POST, "DailyBackupStatus", FILTER_SANITIZE_STRING);
if(! isset($DailyBackupStatus)) {
	$DailyBackupStatus = "off";
}


$DailyBackupWhat = filter_input(INPUT_POST, "DailyBackupWhat", FILTER_SANITIZE_STRING);
$DailyBackupUseFTP = filter_input(INPUT_POST, "DailyBackupUseFTP", FILTER_SANITIZE_STRING);
if( $DailyBackupUseFTP == "") {
	$DailyBackupUseFTP = "false";
}
$DailyBackupUseAWS = filter_input(INPUT_POST, "DailyBackupUseAWS", FILTER_SANITIZE_STRING);
if( $DailyBackupUseAWS == "") {
	$DailyBackupUseAWS = "false";
}


$DailyBackupFTPCount = filter_input(INPUT_POST, "DailyBackupFTPCount", FILTER_SANITIZE_STRING);
$oSettings->SaveBackupSettings("daily", $DailyBackupStatus, $DailyBackupWhat, $DailyBackupUseFTP, $DailyBackupUseAWS, $DailyBackupFTPCount);


$WeeklyBackupStatus = filter_input(INPUT_POST, "WeeklyBackupStatus", FILTER_SANITIZE_STRING);
if(! isset($WeeklyBackupStatus)) {
	$WeeklyBackupStatus = "off";
}

$WeeklyBackupWhat = filter_input(INPUT_POST, "WeeklyBackupWhat", FILTER_SANITIZE_STRING);
$WeeklyBackupUseFTP = filter_input(INPUT_POST, "WeeklyBackupUseFTP", FILTER_SANITIZE_STRING);
if($WeeklyBackupUseFTP == "") {
	$WeeklyBackupUseFTP = "false";
}
$WeeklyBackupUseAWS = filter_input(INPUT_POST, "WeeklyBackupUseAWS", FILTER_SANITIZE_STRING);
if($WeeklyBackupUseAWS == "") {
	$WeeklyBackupUseAWS = "false";
}

$WeeklyBackupFTPCount = filter_input(INPUT_POST, "WeeklyBackupFTPCount", FILTER_SANITIZE_STRING);
$oSettings->SaveBackupSettings("weekly", $WeeklyBackupStatus, $WeeklyBackupWhat, $WeeklyBackupUseFTP, $WeeklyBackupUseAWS, $WeeklyBackupFTPCount);

$MonthlyBackupStatus = filter_input(INPUT_POST, "MonthlyBackupStatus", FILTER_SANITIZE_STRING);
if(! isset($MonthlyBackupStatus)) {
	$MonthlyBackupStatus = "off";
}

$MonthlyBackupWhat = filter_input(INPUT_POST, "MonthlyBackupWhat", FILTER_SANITIZE_STRING);
$MonthlyBackupUseFTP = filter_input(INPUT_POST, "MonthlyBackupUseFTP", FILTER_SANITIZE_STRING);
if($MonthlyBackupUseFTP == "") {
	$MonthlyBackupUseFTP = "false";
}
$MonthlyBackupUseAWS = filter_input(INPUT_POST, "MonthlyBackupUseAWS", FILTER_SANITIZE_STRING);
if($MonthlyBackupUseAWS=="") {
	$MonthlyBackupUseAWS = "false";
}

$MonthlyBackupFTPCount = filter_input(INPUT_POST, "MonthlyBackupFTPCount", FILTER_SANITIZE_STRING);
$oSettings->SaveBackupSettings("monthly", $MonthlyBackupStatus, $MonthlyBackupWhat, $MonthlyBackupUseFTP, $MonthlyBackupUseAWS, $MonthlyBackupFTPCount);


header("Location: settings.php?Notes=Saved!");

