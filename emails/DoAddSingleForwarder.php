<?php
session_start();

function __autoload($classname)
{
        require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.".$classname.".php");
}

$oUser = new User();
$oEmail = new Email();
$oDomain = new Domain();

$oSimpleNonce = new SimpleNonce();

$ClientID = $oUser->GetClientID();
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
}



$NonceMeta = array("id"=>$loggedInId);

$Nonce = filter_var($_POST["Nonce"], FILTER_SANITIZE_STRING);
$TimeStamp = filter_var($_POST["TimeStamp"], FILTER_SANITIZE_STRING);

if( ! $oSimpleNonce->VerifyNonce($Nonce, "addSingleForwarder", $TimeStamp, $NonceMeta) )
{
        header("Location: index.php");
        exit();
}




$LocalPart = filter_var($_POST["localPart"], FILTER_SANITIZE_STRING);
$DomainID = intVal($_POST["domainId"]);
$ForwardTo = filter_var($_POST["ForwardTo"], FILTER_SANITIZE_EMAIL);

$Role = $oUser->Role;





if($DomainID > -1)
{
        $DomainInfoArray = array();
        $oDomain->GetDomainInfo($DomainID, $DomainInfoArray);

        $DomainUserName = $DomainInfoArray["UserName"];
        $DomainOwnerClientID = $DomainInfoArray["ClientID"];
        $Role = 'client';
        $ClientID = $DomainOwnerClientID;
	$DomainName = $DomainInfoArray["DomainName"];

}
else
{
	header("Location: forward.php?Notes=Something went wrong, please retry or contact support");
	exit;
}

 	/*
	print "DomainID: ".$DomainID."<br>";
        print "DomainName: ".$DomainName."<br>";
        print "Path: ".$Path."<br>";
        print "ClientID: ".$ClientID."<br>";
        print "DomainUserName: ".$DomainUserName."<br>";
        print "DomainOwnerClientID: ".$DomainOwnerClientID."<br>";

	*/


for($x = 0; $x < strlen($LocalPart); $x++)
{
				
		
	if(!ctype_alnum($LocalPart[$x]))
	{
		if($LocalPart[$x] != '_' && $LocalPart[$x] != '-' && $LocalPart[$x] != '.')
		{
			header("location: forward.php?Notes=Incorrectly formatted email address");
			exit();
		}
		
	}
}




if($oEmail->SingleForwardExists($LocalPart, $DomainID, $ForwardTo) > 0)
{
	header("location: forward.php?Notes=That forward already exists");
	exit();
}

$Reply = $oEmail->AddSingleForward($LocalPart, $DomainID, $ForwardTo, $ClientID);

if($Reply < 1)
{
	$Message = "Cannot add email forwaring";

	header("location: forward.php?Notes=".$Message);
	exit();
}

header("location: forward.php?Notes=Forward added");

?>


