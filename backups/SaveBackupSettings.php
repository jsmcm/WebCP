<?php
session_start();

function __autoload($classname)
{
        require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.".$classname.".php");
}

$oUser = new User();
$oSettings = new Settings();

require($_SERVER["DOCUMENT_ROOT"]."/includes/License.inc.php");



$ClientID = $oUser->getClientId();
if($ClientID < 1)
{
        header("Location: /index.php");
        exit();
}

if($oUser->Role != "admin")
{
	 header("Location: /index.php");
        exit();
}
	


$FTPHost = filter_input(INPUT_POST, "FTPHost", FILTER_SANITIZE_STRING);
$FTPRemotePath = filter_input(INPUT_POST, "FTPRemotePath", FILTER_SANITIZE_STRING);
$FTPUserName = filter_input(INPUT_POST, "FTPUserName", FILTER_SANITIZE_STRING);
$FTPPassword = filter_input(INPUT_POST, "FTPPassword", FILTER_SANITIZE_STRING);
$oSettings->SaveBackupFTPSettings($FTPHost, $FTPRemotePath, $FTPUserName, $FTPPassword);

$DailyBackupStatus = filter_input(INPUT_POST, "DailyBackupStatus", FILTER_SANITIZE_STRING);
if(! isset($DailyBackupStatus))
{
	$DailyBackupStatus = "off";
}

$DailyBackupWhat = filter_input(INPUT_POST, "DailyBackupWhat", FILTER_SANITIZE_STRING);
$DailyBackupUseFTP = filter_input(INPUT_POST, "DailyBackupUseFTP", FILTER_SANITIZE_STRING);
if(! isset($DailyBackupsUseFTP))
{
	$DailyBackupsUseFTP = "false";
}

$DailyBackupFTPCount = filter_input(INPUT_POST, "DailyBackupFTPCount", FILTER_SANITIZE_STRING);
$oSettings->SaveBackupSettings("daily", $DailyBackupStatus, $DailyBackupWhat, $DailyBackupUseFTP, $DailyBackupFTPCount);


$WeeklyBackupStatus = filter_input(INPUT_POST, "WeeklyBackupStatus", FILTER_SANITIZE_STRING);
if(! isset($WeeklyBackupStatus))
{
	$WeeklyBackupStatus = "off";
}

$WeeklyBackupWhat = filter_input(INPUT_POST, "WeeklyBackupWhat", FILTER_SANITIZE_STRING);
$WeeklyBackupUseFTP = filter_input(INPUT_POST, "WeeklyBackupUseFTP", FILTER_SANITIZE_STRING);
if(! isset($WeeklyBackupsUseFTP))
{
	$WeeklyBackupsUseFTP = "false";
}

$WeeklyBackupFTPCount = filter_input(INPUT_POST, "WeeklyBackupFTPCount", FILTER_SANITIZE_STRING);
$oSettings->SaveBackupSettings("weekly", $WeeklyBackupStatus, $WeeklyBackupWhat, $WeeklyBackupUseFTP, $WeeklyBackupFTPCount);

$MonthlyBackupStatus = filter_input(INPUT_POST, "MonthlyBackupStatus", FILTER_SANITIZE_STRING);
if(! isset($MonthlyBackupStatus))
{
	$MonthlyBackupStatus = "off";
}

$MonthlyBackupWhat = filter_input(INPUT_POST, "MonthlyBackupWhat", FILTER_SANITIZE_STRING);
$MonthlyBackupUseFTP = filter_input(INPUT_POST, "MonthlyBackupUseFTP", FILTER_SANITIZE_STRING);
if(! isset($MonthlyBackupsUseFTP))
{
	$MonthlyBackupsUseFTP = "false";
}

$MonthlyBackupFTPCount = filter_input(INPUT_POST, "MonthlyBackupFTPCount", FILTER_SANITIZE_STRING);
$oSettings->SaveBackupSettings("monthly", $MonthlyBackupStatus, $MonthlyBackupWhat, $MonthlyBackupUseFTP, $MonthlyBackupFTPCount);

header("Location: settings.php?Notes=Saved!");
?>

