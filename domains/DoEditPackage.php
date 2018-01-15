<?php
error_reporting(E_ALL);

session_start();

function __autoload($classname)
{
        require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.".$classname.".php");
}

$oUser = new User();
$oDomain = new Domain();
$oPackage = new Package();
$oLog = new Log();
$oFTP = new FTP();

$ClientID = $oUser->getClientId();
if($ClientID < 1)
{
        header("Location: /index.php");
        exit();
}

$DomainID = -1;
if(isset($_POST["DomainID"]))
{
	$DomainID = $_POST["DomainID"];
}

if($DomainID < 1)
{
	header("location: index.php?Notes=Error updating package");
	exit();
}


$PackageID = -1;
if(isset($_POST["PackageID"]))
{
	$PackageID = $_POST["PackageID"];
}

if($PackageID < 1)
{
	header("location: index.php?Notes=Error updating package");
	exit();
}


$DomainOwnerID = $oDomain->GetDomainOwner($DomainID);

if( ($DomainOwnerID != $ClientID) && ($oUser->Role != "admin") )
{
	header("location: index.php?Notes=Error updating package");
	exit();

}

$oDomain->UpdateDomainPackage($DomainID, $PackageID);

$DiskSpace = $oPackage->GetPackageAllowance("DiskSpace", $PackageID);
print "Disk Space = ".$DiskSpace."<p>";
$oFTP->UpdateDomainFTPDiskQuotas($DomainID, $DiskSpace);
$oPackage->CreateDiskQuotaScriptForDomain($DomainID, $DiskSpace);

header("Location: index.php?Notes=Package updated");

?>
