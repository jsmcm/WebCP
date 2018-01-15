<?php
session_start();

function __autoload($classname)
{
        require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.".$classname.".php");
}

$oUser = new User();
$oUtils = new Utils();
$oReseller = new Reseller();
$oPackage = new Package();
$oDomain = new Domain();

$ClientID = $oUser->getClientId();
if( ($ClientID < 1) || ($oUser->Role != "admin") )
{
        header("Location: /domains/index.php");
        exit();
}


$ResellerID = $_POST["ResellerID"];

$Settings = array();
$Settings["Accounts"] = ((strtolower($_POST["Accounts"]) == "unlimited")? -1:$_POST["Accounts"]);
$Settings["DiskSpace"] = $oUtils->ConvertToBytes($_POST["DiskSpace"], "Mb");
$Settings["Traffic"] = $oUtils->ConvertToBytes($_POST["Traffic"], "Mb");
$Settings["FirewallControl"] = "off";
if( (isset($_POST["FirewallControl"])) && ($_POST["FirewallControl"] == "on") )
{
	$Settings["FirewallControl"] = "on";
}

$TotalDiskSpace = $oPackage->GetTotalDiskSpace();  
$NonResellerUsage = $oDomain->GetPackageDiskSpaceUsage(0); // non reseller usage  
$ResellerUsage = $oDomain->GetPackageDiskSpaceUsage(-1); // all reseller usage  

$DiskSpaceBytes = $oReseller->GetDiskSpaceAllocation($ResellerID);  
$ResellerAllocations = $oReseller->GetDiskSpaceAllocation(-1); // all reseller allocations  
$LeftOverBytes = $TotalDiskSpace + $DiskSpaceBytes - ($NonResellerUsage + $ResellerAllocations);  
  
$LeftOver = $LeftOverBytes;  
$Scale = "b";  
$Available = $oUtils->ConvertFromBytes($LeftOver, $Scale);  
  
$LeftOver = $LeftOverBytes;  
$Scale = "b";  
$AvailableMb = $oUtils->ConvertFromBytes($LeftOver, $Scale, "Mb"); 

if(floatVal($Settings["DiskSpace"]) > floatVal($LeftOverBytes) )
{
	header("Location: resellers.php?Notes=You've attempted to allocate more disk space than the server has available!&NoteType=error");
	exit();
}

if($oReseller->EditReseller($ResellerID, $Settings, $oUser->Role, $ClientID) < 1)
{ 
	header("location: resellers.php?Notes=Cannot edit this reseller, please contact support");
	exit();
}

header("location: resellers.php?Notes=Reseller changed");

?>


