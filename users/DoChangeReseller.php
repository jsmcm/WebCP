<?php
error_reporting(E_ALL);

session_start();

function __autoload($classname)
{
        require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.".$classname.".php");
}

$oUser = new User();
$oReseller = new Reseller();

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


$ClientID = -1;
if(isset($_POST["ClientID"]))
{
	$ClientID = intVal($_POST["ClientID"]);
}

if($ClientID < 1)
{
	header("location: index.php?Notes=Error changing reseller (CID: ".$ClientID.")&NoteType=error");
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
	if($oReseller->AssignClientToReseller($ResellerID, $ClientID) > 0)
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
	$oReseller->RemoveClientFromResellers($ClientID);
	header("Location: index.php?NoteType=Success&Notes=Reseller Changed");
}
?>
