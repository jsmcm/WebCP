<?php
session_start();

require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.Reseller.php");
$oReseller = new Reseller();

require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.User.php");
$oUser = new User();

require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.MySQL.php");
$oMySQL = new MySQL();

$ClientID = $oUser->getClientId();
if($ClientID < 1)
{
        header("Location: /index.php");
        exit();
}

$ID = $_POST["id"];
$UserName = $_POST["UserName"];
$HostList = $_POST["HostList"];

$MySQLOwner = $oMySQL->GetMySQLOwner($ID);


$MySQLUserName = "";
$MySQLDatabaseName = "";
$oMySQL->GetMySQLInfo($ID, $MySQLUserName, $MySQLDatabaseName);

$Role = $oUser->Role;
$DomainUserName = substr($UserName, 0, strpos($UserName, "_"));

$Password = $oMySQL->GetDatabasePassword($ID, $DomainUserName, $UserName, $MySQLDatabaseName, $MySQLOwner);

$ResellerPermission = false;
if($oUser->Role == "reseller")
{
        if($oReseller->GetClientResellerID($MySQLOwner) == $ClientID)
        {
                $ResellerPermission = true;
        }
}

if( ($MySQLOwner != $ClientID) && ($Role != "admin") )
{
	if($ResellerPermission == false)
	{
		header("location: index.php?NoteType=oops, something went wrong&NoteType=error");
		exit();
	}
}

if($UserName != $MySQLUserName)
{
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
?>


