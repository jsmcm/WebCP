<?php
session_start();

require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.User.php");
$oUser = new User();

$ClientID = $oUser->getClientId();
if($ClientID < 1)
{
        header("Location: /index.php");
        exit();
}

require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.Domain.php");
$oDomain = new Domain();
$DomainOwnerClientID = $oDomain->GetDomainOwner($_REQUEST["DomainID"]);

if( ($ClientID != $DomainOwnerClientID) && ($oUser->Role != 'admin') )
{	
	header("location: index?Notes=No%20Permission!!!");
	exit();
}

//print "DomainOwnerClientID: ".$DomainOwnerClientID."<br>";
//print "DomainID: ".$_REQUEST["DomainID"]."<br>";

if($oDomain->DeleteDomain($DomainOwnerClientID, $_REQUEST["DomainID"], $Error) == 1)
{
	$Notes="Domain Deleted";
}
else
{	
	$Notes="Domain cannot be deleted";
}


header("location: index.php?Notes=".$Notes.$Error);	

exit();

?>

