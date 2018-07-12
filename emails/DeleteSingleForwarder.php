<?php
session_start();

require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.User.php");
$oUser = new User();

require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.Domain.php");
$oDomain = new Domain();

require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.Email.php");
$oEmail = new Email();

require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.SimpleNonce.php");
$oSimpleNonce = new SimpleNonce();

$ClientID = $oUser->GetClientID();
$Role = $oUser->Role;

$loggedInId = $ClientID;

$email_ClientID = $oEmail->getLoggedInEmailId();

if($ClientID < 1)
{
        if( $email_ClientID < 1 )
        {
                header("Location: /index.php");
                exit();
        }
        $loggedInId = $email_ClientID;

	$Role = "email";

}



$NonceMeta = array("loggedInId"=>$loggedInId, "forwarderId"=>intVal($_REQUEST["id"]) );

$Nonce = filter_var($_REQUEST["Nonce"], FILTER_SANITIZE_STRING);
$TimeStamp = filter_var($_REQUEST["TimeStamp"], FILTER_SANITIZE_STRING);

if( ! $oSimpleNonce->VerifyNonce($Nonce, "deleteSingleForwarder", $TimeStamp, $NonceMeta) )
{
        header("Location: index.php");
        exit();
}




$DomainID = $oEmail->GetDomainIDFromSingleForwardID($_REQUEST["id"]);

if($DomainID > -1)
{
        $DomainInfoArray = array();
        $oDomain->GetDomainInfo($DomainID, $DomainInfoArray);

        $ClientID = $DomainInfoArray["ClientID"];

}












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

header("location: forward.php?Notes=".$Notes);	
exit();

?>

