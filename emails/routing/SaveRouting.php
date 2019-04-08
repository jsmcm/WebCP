<?php
session_start();

include_once($_SERVER["DOCUMENT_ROOT"]."/vendor/autoload.php");

$oUser = new User();
$oDomain = new Domain();

$ClientID = $oUser->getClientId();
if($ClientID < 1)
{
        header("Location: /index.php");
        exit();
}

$Role = $oUser->Role;
$DomainName = "";
$DomainOwnerID = -1;
$DomainID = -1;
$Routing = "";

if(isset($_REQUEST["domain_id"]))
{
	$Routing = $_REQUEST["routing"];
        $DomainID = $_REQUEST["domain_id"];

        $InfoArray = array();
        $oDomain->GetDomainInfo($DomainID, $InfoArray);

        $DomainName = $InfoArray["DomainName"];
        $DomainOwnerID = $InfoArray["ClientID"];
}
else
{
        print "Please select correct domain&NoteType=Error";
	exit();
}

if($DomainOwnerID  != $DomainID)
{
	if($Role != 'admin')
	{
		print "You do not have permission to edit this domain!";
		exit();
	}
}

$oDomain->UpdateMailRouting($DomainID, $Routing);
print "Routing updated for ".$DomainName." to ".$Routing." routing!";

?>
