<?php
session_start();

include_once($_SERVER["DOCUMENT_ROOT"]."/vendor/autoload.php");
$oUser = new User();
$oReseller = new Reseller();

require($_SERVER["DOCUMENT_ROOT"]."/includes/License.inc.php");

$Role = $oUser->Role;
$ClientID = $oUser->getClientId();
if($ClientID < 1)
{
        header("Location: /index.php");
        exit();
}

$oDomain = new Domain();
$SubDomainOwnerClientID = $oDomain->GetDomainOwner($_REQUEST["SubDomainID"]);
$DomainID = $oDomain->GetDomainIDFromSubDomainID($_REQUEST["SubDomainID"]);


$ResellerPermission = false;
if($oUser->Role == "reseller")
{
        if($oReseller->GetClientResellerID($SubDomainOwnerClientID) == $ClientID)
        {
                $ResellerPermission = true;
        }
}

if( ($ClientID != $SubDomainOwnerClientID) && ($Role != "admin"))
{
        if($ResellerPermission == false)
        {
                exit();
        }
}


//print "SubDomainOwnerClientID: ".$SubDomainOwnerClientID."<br>";
//print "SubDomainID: ".$_REQUEST["SubDomainID"]."<br>";

if($oDomain->DeleteSubDomain($SubDomainOwnerClientID, $_REQUEST["SubDomainID"], $Error) == 1)
{
	$Notes="Sub Domain Deleted";
	$NoteType = "Success";
}
else
{	
	$Notes="Sub Domain cannot be deleted";
	$NoteType = "Error";
}


header("location: ListSubDomains.php?DomainID=".$DomainID."&NoteType=".$NoteType."&Notes=".$Notes.$Error);	
