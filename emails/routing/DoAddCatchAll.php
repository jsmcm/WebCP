<?php
session_start();

function __autoload($classname)
{
	require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.".$classname.".php");
}

$oUser = new User();
$oEmail = new Email();

$ClientID = $oUser->getClientId();
if($ClientID < 1)
{
	header("Location: /index.php");
	exit();
}
$Role = $oUser->Role;

$DomainName = "";
if(isset($_POST["DomainName"]))
{
	$DomainName = $_POST["DomainName"];
}
else
{
	header("index.php?Notes=Please select the domain&NoteType=Error");
	exit();
}


$EmailAddress = "";
if(isset($_POST["EmailAddress"]))
{
	$EmailAddress = $_POST["EmailAddress"];
}
else
{
	header("index.php?Notes=Please select the email address&NoteType=Error");
	exit();
}

	if($oEmail->AddCatchAll($ClientID, $Role, $DomainName, $EmailAddress) == true)
	{
		header("location: index.php?Notes=Catch all added&NoteType=Success");
	}
	else
	{	
		header("location: index.php?Notes=Cannot add catch all&NoteType=Error");
	}
	
?>

