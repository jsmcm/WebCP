<?php
session_start();

function __autoload($classname)
{
        require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.".$classname.".php");
}

$oUser = new User();

$ClientID = $oUser->getClientId();
if($ClientID < 1)
{
        header("Location: /index.php");
        exit();
}




$Role = $oUser->Role;


$oDomain = new Domain();
$oEmail = new Email();

$DomainID = $oEmail->GetDomainIDFromEmailID($_REQUEST["id"]);

if($DomainID > -1)
{
        $DomainInfoArray = array();
        $oDomain->GetDomainInfo($DomainID, $DomainInfoArray);

        $ClientID = $DomainInfoArray["ClientID"];
        $Role = 'client';

}








if($oEmail->DeleteEmail($ClientID, $oUser->Role, $_REQUEST["id"]) == 1)
{
	$Notes="Email Deleted";
}
else
{	
	$Notes="Email cannot be deleted";
}

if(isset($_REQUEST["ClientID"]))
{
	header("location: index.php?Notes=".$Notes."&ClientID=".$_REQUEST["ClientID"]);	
}
else
{
	header("location: index.php?Notes=".$Notes);	
}
exit();

?>

