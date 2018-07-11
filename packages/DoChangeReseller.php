<?php

session_start();

require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.User.php");
$oUser = new User();

require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.Package.php");
$oPackage = new Package();

$ClientID = $oUser->getClientId();
if($ClientID < 1)
{
        header("Location: /index.php");
        exit();
}

if($oUser->Role != "admin")
{
	header("location: index.php?Notes=You don't have permission to be here&NoteType=error");
	exit();
}


$PackageID = -1;
if(isset($_POST["PackageID"]))
{
	$PackageID = intVal($_POST["PackageID"]);
}

if($PackageID < 1)
{
	header("location: index.php?Notes=Error changing reseller (PID: ".$PackageID.")&NoteType=error");
	exit();
}

$ResellerID = -1;
if(isset($_POST["ResellerID"]))
{
	$ResellerID = intVal($_POST["ResellerID"]);
}


if( ($ResellerID < 1) && ($ResellerID != -2) )
{
	header("location: index.php?Notes=Error changing reseller (RID: ".$ResellerID.")&NoteType=error");
	exit();
}

if($ResellerID > 0)
{
	if($oPackage->ChangePackageOwner($ResellerID, $PackageID) > 0)
	{
		//print "Success";
		header("Location: index.php?NoteType=Success&Notes=Reseller Changed");
	}
	else
	{
		//print "Error";
		header("Location: index.php?NoteType=Error changing reseller&Notes=error");
	}
}
else if($ResellerID == -2)
{
	$oPackage->RemovePackageOwnere($PackageID);
	header("Location: index.php?NoteType=Success&Notes=Reseller Changed");
}
?>
