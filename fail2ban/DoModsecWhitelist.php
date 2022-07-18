<?php
session_start();

if( ! file_exists($_SERVER["DOCUMENT_ROOT"]."/nm/")) {
    mkdir($_SERVER["DOCUMENT_ROOT"]."/nm/", 0755);
}

include_once($_SERVER["DOCUMENT_ROOT"]."/vendor/autoload.php");

$oUser = new User();
$oSettings = new Settings();
$oFirewall = new Firewall();
$oDomain = new Domain();
$oReseller = new Reseller();


$ClientID = $oUser->getClientId();
if($ClientID < 1)
{
	header("Location: /index.php");
	exit();
}
if($oUser->Role != "admin")
{
        $FirewallControl = "";
        if($oUser->Role == "reseller")
        {
                $FirewallControl = $oReseller->GetResellerSetting($oUser->ClientID, "FirewallControl");
        }
        if($FirewallControl != "on")
        {
                header("Location: /index.php");
                exit();
        }
}



$IP = $_REQUEST["IP"];
$ModsecID = $_REQUEST["ModsecID"];
$HostName = $_REQUEST["HostName"];
$URI = $_REQUEST["URI"]; 

//print "IP: ".$IP."<br>";
//print "ModsecID: ".$ModsecID."<br>";
//print "HostName: ".$HostName."<br>";
//print "URI: ".$URI."<br>";


	$oFirewall->ManualUnban($IP);
	$x =  $oFirewall->ModsecWhitelist($ModsecID, $HostName, $URI);

	if($x > 0)
	{
		if($HostName == "global")
		{
			touch($_SERVER["DOCUMENT_ROOT"]."/nm/modsec_gw");

			//print "Calling RecreateAllVhostFiles()<br>";
		}
		else
		{
			
                        if( $oDomain->DomainExists($HostName) < 1)
                        {
                                if(substr($HostName, 0, 4) == "www.")
                                {
                                        $Temp = substr($HostName, 4);
                                }
                                else
                                {
                                        $Temp = "www.".$HostName;
                                }

                                if($oDomain->DomainExists($Temp) > 0)
                                {
                                        $HostName = $Temp;
                                }
                        }



			$AncestorID = $oDomain->GetDomainIDFromDomainName($HostName);
			$AncestorID = $oDomain->GetParentDomainIDRecursive($AncestorID);

	                $oDomain->DeleteDomainFile($AncestorID);
	                $oDomain->MakeDomainFile($AncestorID);
			//print "Calling MakeDomainFile(".$AncestorID.")<br>";
		}

		header("Location: index.php?Notes=The whitelist was added&NoteType=Success");
		exit();
	}
	else if($x == -1)
	{
		//print "That condition already exists<br>";
		header("Location: index.php?Notes=The whitelist not added because an exact whitelist already exists&NoteType=Error");
		exit();
	}
	else if($x == -2)
	{
		//print "That domain does not exist<br>";
		header("Location: index.php?Notes=The whitelist not added because that domain name does not exist&NoteType=Error");
		exit();
	}

	header("Location: index.php?Notes=The whitelist not added. Unknown error&NoteType=Error");

?>

