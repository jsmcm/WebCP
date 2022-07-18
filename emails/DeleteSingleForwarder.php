<?php
session_start();

include_once($_SERVER["DOCUMENT_ROOT"]."/vendor/autoload.php");
$oUser = new User();
$oDomain = new Domain();
$oEmail = new Email();
$oSimpleNonce = new SimpleNonce();

$ClientID = $oUser->GetClientID();
$Role = $oUser->Role;


$loggedInId = $ClientID;

$email_ClientID = $oEmail->getLoggedInEmailId();

if($ClientID < 1) {
        if( $email_ClientID < 1 )
        {
                header("Location: /index.php");
                exit();
        }
        $loggedInId = $email_ClientID;

	$Role = "email";

}


$localPart = filter_var($_GET["localPart"], FILTER_SANITIZE_STRING);


$NonceMeta = array("loggedInId"=>$loggedInId, "forwarderId"=>intVal($_REQUEST["id"]), "localPart"=>$localPart );

$Nonce = filter_var($_REQUEST["Nonce"], FILTER_SANITIZE_STRING);
$TimeStamp = filter_var($_REQUEST["TimeStamp"], FILTER_SANITIZE_STRING);

if( ! $oSimpleNonce->VerifyNonce($Nonce, "deleteSingleForwarder", $TimeStamp, $NonceMeta) )
{
        header("Location: index.php");
        exit();
}




$DomainID = $oEmail->GetDomainIDFromSingleForwardID($_REQUEST["id"]);
$DomainInfoArray = array();

if($DomainID > -1) {

	$random = random_int(1, 1000000);
	$nonceArray = [	
			$oUser->Role,
			$oUser->ClientID,
			$DomainID,
			$random
	];
	$nonce = $oSimpleNonce->GenerateNonce("getDomainInfo", $nonceArray);
	$oDomain->GetDomainInfo($DomainID, $random, $DomainInfoArray, $nonce);

        $ClientID = $DomainInfoArray["ClientID"];

}

$DomainUserName = $DomainInfoArray["UserName"];
$DomainName = $DomainInfoArray["DomainName"];



$oEmail = new Email();
if($oEmail->DeleteSingleForwarder($loggedInId, $Role, intVal($_REQUEST["id"])) == 1)
{
	$Notes="Forwarder Deleted";
}
else
{	
	$Notes="Forwarder cannot be deleted";
}

//print "<p>".$Notes."<p>";

file_put_contents(dirname(__DIR__)."/nm/".$DomainUserName.".delete_forward_address", "LOCAL_PART=".$localPart."\r\nDOMAIN_NAME=".$DomainName."\r\n");

header("location: forward.php?Notes=".$Notes);

