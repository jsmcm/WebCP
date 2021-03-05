<?php

session_start();

include_once($_SERVER["DOCUMENT_ROOT"]."/vendor/autoload.php");
$oUser = new User();
$oDomain = new Domain();

require($_SERVER["DOCUMENT_ROOT"]."/includes/License.inc.php");

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


$UserID = -1;
if(isset($_POST["UserID"]))
{
	$UserID = $_POST["UserID"];
}

//print "DomainID: ".$DomainID."<br>";
//print "UserID: ".$UserID."<br>";
//exit();

if($oDomain->UpdateDomainUser($DomainID, $UserID) > 0)
{
	//print "Success";
	header("Location: index.php?NoteType=Success&Notes=User Changed");
}
else
{
	//print "Error";
	header("Location: index.php?NoteType=Error&Notes=Error");
}

?>
