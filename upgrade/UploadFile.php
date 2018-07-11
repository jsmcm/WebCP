<?php
session_start();

require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.User.php");
$oUser = new User();

require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.Settings.php");
$oSettings = new Settings();

require($_SERVER["DOCUMENT_ROOT"]."/includes/License.inc.php");

$ClientID = $oUser->getClientId();
if($ClientID < 1)
{
        header("Location: /index.php");
        exit();
}

	if( ! file_exists($_SERVER["DOCUMENT_ROOT"]."/upgrade/db_backups") )
	{
		mkdir($_SERVER["DOCUMENT_ROOT"]."/upgrade/db_backups/", 0755);
		chmod($_SERVER["DOCUMENT_ROOT"]."/upgrade/db_backups/", 0755);
	}

	if(trim(substr($_FILES["NewFile"]["name"], strlen($_FILES["NewFile"]["name"]) - 4)) != ".zip")
	{
		header("location: index.php?Notes=Only zip files are valid, please try again!");
		exit();
	}

	move_uploaded_file($_FILES['NewFile']['tmp_name'], $ZipFile=$TempFolder.$_FILES['NewFile']['name']);
	chmod($ZipFile, 0755);

	header("location: DoUploadFile.php?ZipFile=".$ZipFile);
?>
