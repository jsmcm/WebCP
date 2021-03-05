<?php
session_start();

include_once($_SERVER["DOCUMENT_ROOT"]."/vendor/autoload.php");
$oUser = new User();

require($_SERVER["DOCUMENT_ROOT"]."/includes/License.inc.php");

$ClientID = $oUser->getClientId();
if($ClientID < 1) {
    header("Location: /index.php");
    exit();
}

$Role = $oUser->Role;
$oDomain = new Domain();

$oEmail = new Email();

$DomainID = $oEmail->GetDomainIDFromEmailID($_REQUEST["id"]);

if($DomainID > -1) {
    $DomainInfoArray = array();
    
    $random = random_int(1, 1000000);
    $oUser = new User();
    $oSimpleNonce = new SimpleNonce();
    $nonceArray = [	
            $oUser->Role,
            $oUser->ClientID,
            $DomainID,
            $random
    ];
    $nonce = $oSimpleNonce->GenerateNonce("getDomainInfo", $nonceArray);
    $oDomain->GetDomainInfo($DomainID, $random, $DomainInfoArray, $nonce);

    $ClientID = $DomainInfoArray["ClientID"];
    $Role = 'client';

}

$id = intVal($_REQUEST["id"]);
$UserName = "";
$LocalPart = "";
$DomainName = "";
$DomainID = "";
$oEmail->GetEmailInfo($id, $UserName, $LocalPart, $DomainName, $DomainID);


if($oEmail->DeleteEmail($ClientID, $oUser->Role, $id) == 1) {
    $Notes="Email Deleted";
    touch(dirname(__DIR__)."/nm/".$UserName.".mailpassword");

} else {	
    $Notes="Email cannot be deleted";
}

if(isset($_REQUEST["ClientID"])) {
	header("location: index.php?Notes=".$Notes."&ClientID=".intVal($_REQUEST["ClientID"]));	
} else {
	header("location: index.php?Notes=".$Notes);	
}
