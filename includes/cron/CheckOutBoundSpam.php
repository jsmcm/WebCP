<?php


$Debug = true;
//$Debug = false;

if(!file_exists($_SERVER["DOCUMENT_ROOT"]."/includes/cron/tmp/")) {
        mkdir($_SERVER["DOCUMENT_ROOT"]."/includes/cron/tmp/", 0755);
}


if(file_exists($_SERVER["DOCUMENT_ROOT"]."/includes/cron/tmp/OutboundSpam.lock")) {
        // if its older than 10 minutes something's gone wrong, delete it.
        $datetime1 = new DateTime(date("Y-m-d H:i:s", filemtime($_SERVER["DOCUMENT_ROOT"]."/includes/cron/tmp/OutboundSpam.lock")));
        $datetime2 = new DateTime(date("Y-m-d H:i:s"));
        $interval = $datetime1->diff($datetime2);

        if( (int)$interval->format('%i') > 10) {
                // The previous instance stalled...
                unlink($_SERVER["DOCUMENT_ROOT"]."/includes/cron/tmp/OutboundSpam.lock");
        }

        if($Debug == false) {
                exit();
        }
        print "Lock file exists, BUT in debug so continuing<p>";
}

touch($_SERVER["DOCUMENT_ROOT"]."/includes/cron/tmp/OutboundSpam.lock");

include_once($_SERVER["DOCUMENT_ROOT"]."/vendor/autoload.php");

$oSendMail = new SendMail();
$oSettings = new Settings();
$oUser = new User();
$oEmail = new Email();
$oUtils = new Utils();
$oFirewall = new Firewall();
$oDomain = new Domain();

$TempFirewallArray = array();
$TemplBlockEmailArray = array();
$FirewallArray = array();
$BlockEmailArray = array();
$Subject = "";

$SpamAction = $oSettings->GetOutBoundMailAction();
$HighSubjectCount = $oSettings->GetOutBoundMailSubjectCount();
$High550Count = $oSettings->GetOutBoundMail550Count();

$StartDate = date('Y-m-d H:i:s', time() - 3600);
$EndDate = date("Y-m-d H:i:s");

if($Debug == true)
{
	print "SpamAction: ".$SpamAction."<br>";
	print "HighSubjectCount: ".$HighSubjectCount."<br>";
	print "High550Count: ".$High550Count."<br>";

	print "StartDate: ".$StartDate."<br>";
	print "EndDate: ".$EndDate."<br>";
}

$oEmail->GetHighSubjectCount($HighSubjectCount, $StartDate, $EndDate, $Subject, $FirewallArray, $BlockEmailArray);

if($Debug == true)
{
	print "Subject: ".$Subject."<br>";
	print "FW: <br>";
	print_r($FirewallArray);
	print "<p>";

	print "BE: <br>";	
	print_r($BlockEmailArray);
	print "<p>";
}

$oEmail->GetHigh550Count($High550Count, $StartDate, $EndDate, $TempFireWallArray, $TempBlockEmailArray);

if($Debug == true)
{
	print "550s<br>";
	print "FW: <br>";
	print_r($TempFirewallArray);
	print "<p>";

	print "BE: <br>";	
	print_r($TempBlockEmailArray);
	print "<p>";
}

$FirewallArray = array_merge($FirewallArray, $TempFireWallArray);
$FirewallArray = array_unique($FirewallArray);

$BlockEmailArray = array_merge($BlockEmailArray, $TempBlockEmailArray);
$BlockEmailArray = array_unique($BlockEmailArray);

if($Debug == true)
{
	print "Merged FW: <br>";
	print_r($FirewallArray);
	print "<p>";

	print "Merged BE: <br>";	
	print_r($BlockEmailArray);
	print "<p>";
}

$FirewallArray = $oUtils->ConvertStringArrayToIPAddresses($FirewallArray);

if($Debug == true)
{
	print "Converted FW: <br>";
	print_r($FirewallArray);
	print "<p>";
}

if($SpamAction == "block") {
	foreach($FirewallArray as $IP) {
		//touch( dirname(__FILE__)."/../../nm/".$IP, 0755 );
	}
}

$BlockEmailArray = $oUtils->FixEmailFromEximAuthDataArray($BlockEmailArray);

if($Debug == true) {
	print "Converted BE: <br>";
	print_r($BlockEmailArray);
	print "<p>";
}

print "Spamaction = ".$SpamAction."<p>";

if($SpamAction == "block")
{
	$BlockEmailArray = $oEmail->RemoveAlreadySuspendedFromArray($BlockEmailArray);
	
	print_r($BlockEmailArray);
	print "<p>";
	foreach($BlockEmailArray as $EmailAddress)
	{
		$oEmail->SuspendEmail(-1, "admin", $EmailAddress);
		$domainId = $oDomain->GetDomainIDFromDomainName(substr($EmailAddress, strpos($EmailAddress, "@") + 1));

		if ( $domainId > -1 ) {

	
			$random = random_int(1, 1000000);
			$nonceArray = [	
					$oUser->Role,
					$oUser->ClientID,
					$DomainID,
					$random
			];
			$oSimpleNonce = new SimpleNonce();
			$nonce = $oSimpleNonce->GenerateNonce("getDomainInfo", $nonceArray);
			$oDomain->GetDomainInfo($DomainID, $random, $infoArray, $nonce);

			
			touch ( dirname(__FILE__)."/../../nm/".$infoArray["UserName"].".mailpassword", 0755 );
		}
	}
}

foreach($BlockEmailArray as $SpamAddress)
{
	$FirstName = "";
	$Surname = "";
	$EmailAddress = "";
	$Username = "";
	$UserID = $oEmail->GetEmailOwnerFromEmailAddress($SpamAddress);
	
	$role = "";

	$oUser->GetUserDetails($UserID, $FirstName, $Surname, $EmailAddress, $Username, $role);

	$EmailMessage = "Hello ".$FirstName.",<p>We have detected what appears to be spam originating from your email address, ".$SpamAddress.". We will investigate this, but please look into it yourself by going to the hosting control panel and clicking on Emails->Email Trace. If you see what appears to be spam originating from your address please immediately change the password.";
	if($SpamAction == "block")
	{
		$EmailMessage = "Hello ".$FirstName.",<p>We have detected what appears to be spam originating from your email address, ".$SpamAddress.". As such we have suspended this email account until we've managed to investigate the cause of this.";
	}
	
	if ($EmailAddress != "") {
        
            if (!file_exists($_SERVER["DOCUMENT_ROOT"]."/tmp/spamguard_".$SpamAddress."_".date("YmdH"))) {
	         $oSendMail->SendEmail($EmailAddress, "Outgoing Spam from your email address", $EmailMessage);
                 touch($_SERVER["DOCUMENT_ROOT"]."/tmp/spamguard_".$SpamAddress."_".date("YmdH"));
            }
	}
}

unlink($_SERVER["DOCUMENT_ROOT"]."/includes/cron/tmp/OutboundSpam.lock");
