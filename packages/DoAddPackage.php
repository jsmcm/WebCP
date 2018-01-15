<?php
session_start();

function __autoload($classname)
{
        require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.".$classname.".php");
}

$oUser = new User();
$oPackage = new Package();
$oUtils = new Utils();

$ClientID = $oUser->getClientId();
if( ($ClientID < 1) || ($oUser->Role == "client") )
{
        header("Location: /domains/index.php");
        exit();
}


$Action = $_POST["Action"];
$PackageName = $_POST["PackageName"];

$Settings = array();
$Settings["Emails"] = ((strtolower($_POST["Emails"]) == "unlimited")? -1:$_POST["Emails"]);
$Settings["Domains"] = $_POST["Domains"];
$Settings["SubDomains"] = ((strtolower($_POST["SubDomains"]) == "unlimited")? -1:$_POST["SubDomains"]);
$Settings["ParkedDomains"] = ((strtolower($_POST["ParkedDomains"]) == "unlimited")? -1:$_POST["ParkedDomains"]);
$Settings["DiskSpace"] = floatval($_POST["DiskSpace"]);
$Settings["Traffic"] = floatval($_POST["Traffic"]);
$Settings["FTP"] = ((strtolower($_POST["FTP"]) == "unlimited")? -1:$_POST["FTP"]);
$Settings["MySQL"] = ((strtolower($_POST["MySQL"]) == "unlimited")? -1:$_POST["MySQL"]);
//$Settings["PostgreSQL"] = $_POST["PostgreSQL"];

$PackageID = $_POST["PackageID"];

$Settings["DiskSpace"] = $oUtils->ConvertToBytes($Settings["DiskSpace"], "Mb");
$Settings["Traffic"] = $oUtils->ConvertToBytes($Settings["Traffic"], "Mb");

if($Action == 'add')
{
	if($oPackage->PackageExists($PackageName) > 0)
	{
		header("location: index.php?Notes=The package name already exists, please retry");
		exit();
	}
	
	if($oPackage->AddPackage($PackageName, $Settings, $ClientID) < 1)
	{ 
		header("location: index.php?Notes=Cannot add package at this time");
		exit();
	}

	header("location: index.php?Notes=Package name added");
}
else
{
	if($oPackage->EditPackage($PackageID, $PackageName, $Settings) < 1)
	{
		header("location: index.php?Notes=Cannot edit the package");
		exit();
	}
	header("location: index.php?Notes=package updated");
}

?>


