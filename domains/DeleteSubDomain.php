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

$subDomainId = intVal( $_REQUEST["SubDomainID"] );

$oDomain = new Domain();

$random = random_int(1, 100000);
$nonceArray = [
	$oUser->Role,
	$oUser->ClientID,
    $subDomainId,
    $random
];

$oSimpleNonce = new SimpleNonce();

$nonce = $oSimpleNonce->GenerateNonce("getDomainOwner", $nonceArray);
$SubDomainOwnerClientID = $oDomain->GetDomainOwner($subDomainId, $random, $nonce);

$DomainID = $oDomain->GetDomainIDFromSubDomainID($subDomainId);


$ResellerPermission = false;
if($oUser->Role == "reseller") {

        $nonceArray = [
            $oUser->Role,
            $oUser->ClientID,
            $SubDomainOwnerClientID
        ];
        
        $oReseller = new Reseller();
        $nonce = $oSimpleNonce->GenerateNonce("getClientResellerID", $nonceArray);

        if($oReseller->GetClientResellerID($SubDomainOwnerClientID, $nonce) == $ClientID) {
            $ResellerPermission = true;
        }
}

if( ($ClientID != $SubDomainOwnerClientID) && ($Role != "admin")) {
    if($ResellerPermission == false) {
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
