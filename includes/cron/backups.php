<?php
session_start();

$Debug = false;

if (!file_exists($_SERVER["DOCUMENT_ROOT"]."/../backups/tmp/")) {
    mkdir($_SERVER["DOCUMENT_ROOT"]."/../backups/tmp/", 0755);
}

if (!file_exists($_SERVER["DOCUMENT_ROOT"]."/includes/cron/tmp/")) {
    mkdir($_SERVER["DOCUMENT_ROOT"]."/includes/cron/tmp/", 0755);
}

include_once($_SERVER["DOCUMENT_ROOT"]."/vendor/autoload.php");

$oUser = new User();
$oDomain = new Domain();
$oSettings = new Settings();
$oDNS = new DNS();

if($oDNS->IPExists($_SERVER["REMOTE_ADDR"]) == false)
{
        if( ($_SERVER["REMOTE_ADDR"] != "127.0.0.1") && ($_SERVER["REMOTE_ADDR"] != "::1") )
        {
                file_put_contents("./tmp/log.txt", $_SERVER["REMOTE_ADDR"]." not allowed!\n");

                if($Debug == false)
                {
                        exit();
                }
                print "Remote address not allowed, BUT in debug so continuing<p>";
        }
}











if( ($_SERVER["REMOTE_ADDR"] != "127.0.0.1") && ($_SERVER["REMOTE_ADDR"] != "::1") )
{
	require($_SERVER["DOCUMENT_ROOT"]."/includes/License.inc.php");
}


if( file_exists($_SERVER["DOCUMENT_ROOT"]."/includes/cron/tmp/auto_backups_".date("Ymd")) )
{
	if($Debug == false)
	{
		exit();
	}
	print "Backup already ran today, BUT in debug so continuing<p>";
}

touch($_SERVER["DOCUMENT_ROOT"]."/includes/cron/tmp/auto_backups_".date("Ymd"));

if( ! file_exists($_SERVER["DOCUMENT_ROOT"]."/nm/"))
{
	mkdir($_SERVER["DOCUMENT_ROOT"]."/nm/", 0755);
}


$FTPSettingsArray = array();
$oSettings->GetFTPBackupSettings($FTPSettingsArray);

$FTPHost = "";
if(isset($FTPSettingsArray["FTPHost"]))
{
	$FTPHost = $FTPSettingsArray["FTPHost"];
}

$FTPRemotePath = "";
if(isset($FTPSettingsArray["FTPRemotePath"]))
{
	$FTPRemotePath = $FTPSettingsArray["FTPRemotePath"];
}

$FTPUserName = "";
if(isset($FTPSettingsArray["FTPUserName"]))
{
	$FTPUserName = $FTPSettingsArray["FTPUserName"];
}

$FTPPassword = "";
if(isset($FTPSettingsArray["FTPPassword"]))
{
	$FTPPassword = $FTPSettingsArray["FTPPassword"];
}



     
$DailyBackupSettingsArray = array();
$oSettings->GetBackupSettings('daily', $DailyBackupSettingsArray);
     
$DailyBackupStatus = true;
if(isset($DailyBackupSettingsArray["BackupStatus"]))
{
        if($DailyBackupSettingsArray["BackupStatus"] != "on")
	{
		$DailyBackupStatus = false;
	}
}

$DailyBackupWhat = "all";
if(isset($DailyBackupSettingsArray["BackupWhat"]))
{
        $DailyBackupWhat = $DailyBackupSettingsArray["BackupWhat"];
}

	
$DailyBackupUseFTP = false;
if(isset($DailyBackupSettingsArray["BackupUseFTP"]))
{
	if(trim(strtolower($DailyBackupSettingsArray["BackupUseFTP"])) == "true")
	{
		$DailyBackupUseFTP = true;
	}
}

$DailyBackupFTPCount = 0;
if(isset($DailyBackupSettingsArray["BackupFTPCount"]))
{
        $DailyBackupFTPCount = intVal($DailyBackupSettingsArray["BackupFTPCount"]);
}



     
$WeeklyBackupSettingsArray = array();
$oSettings->GetBackupSettings('weekly', $WeeklyBackupSettingsArray);
     
$WeeklyBackupStatus = false;
if(isset($WeeklyBackupSettingsArray["BackupStatus"]))
{
        if($WeeklyBackupSettingsArray["BackupStatus"] == "on")
	{
		$WeeklyBackupStatus = true;
	}
}

$WeeklyBackupWhat = "web";
if(isset($WeeklyBackupSettingsArray["BackupWhat"]))
{
        $WeeklyBackupWhat = $WeeklyBackupSettingsArray["BackupWhat"];
}

$WeeklyBackupUseFTP = false;
if(isset($WeeklyBackupSettingsArray["BackupUseFTP"]))
{
	if(trim(strtolower($WeeklyBackupSettingsArray["BackupUseFTP"])) == "true")
	{
		$WeeklyBackupUseFTP = true;
	}
}

$WeeklyBackupFTPCount = 0;
if(isset($WeeklyBackupSettingsArray["BackupFTPCount"]))
{
        $WeeklyBackupFTPCount = intVal($WeeklyBackupSettingsArray["BackupFTPCount"]);
}




     
$MonthlyBackupSettingsArray = array();
$oSettings->GetBackupSettings('monthly', $MonthlyBackupSettingsArray);

$MonthlyBackupStatus = false;
if(isset($MonthlyBackupSettingsArray["BackupStatus"]))
{
        if($MonthlyBackupSettingsArray["BackupStatus"] == "on")
	{
		$MonthlyBackupStatus = true;
	}
}

$MonthlyBackupWhat = "web";
if(isset($MonthlyBackupSettingsArray["BackupWhat"]))
{
        $MonthlyBackupWhat = $MonthlyBackupSettingsArray["BackupWhat"];
}

$MonthlyBackupUseFTP = false;
if(isset($MonthlyBackupSettingsArray["BackupUseFTP"]))
{
	if(trim(strtolower($MonthlyBackupSettingsArray["BackupUseFTP"])) == "true")
	{
		$MonthlyBackupUseFTP = true;
	}
}

$MonthlyBackupFTPCount = 0;
if(isset($MonthlyBackupSettingsArray["BackupFTPCount"]))
{
        $MonthlyBackupFTPCount = intVal($MonthlyBackupSettingsArray["BackupFTPCount"]);
}

function stringVal($v)
{
	return (($v)? "true": "false");
}

$BackupTypes = array();
$BackupTypes["daily"] = (($DailyBackupStatus==true)? "$DailyBackupWhat": "off");
$BackupTypes["weekly"] = (($WeeklyBackupStatus==true)? "$WeeklyBackupWhat": "off");
$BackupTypes["monthly"] = (($MonthlyBackupStatus==true)? "$MonthlyBackupWhat": "off");

if($Debug == true)
{
	print "FTPHost: ".$FTPHost."<br>";
	print "FTPRemotePath: ".$FTPRemotePath."<br>";
	print "FTPUserName: ".$FTPUserName."<br>";
	print "FTPPassword: ".$FTPPassword."<p>";
	
	print "DailyBackupStatus: ".stringVal($DailyBackupStatus)."<br>";
	print "DailyBackupWhat: ".$DailyBackupWhat."<br>";
	print "DailyBackupUseFTP: ".stringVal($DailyBackupUseFTP)."<br>";
	print "DailyBackupFTPCount: ".$DailyBackupFTPCount."<p>";
	
	
	print "WeeklyBackupStatus: ".stringVal($WeeklyBackupStatus)."<br>";
	print "WeeklyBackupWhat: ".$WeeklyBackupWhat."<br>";
	print "WeeklyBackupUseFTP: ".stringVal($WeeklyBackupUseFTP)."<br>";
	print "WeeklyBackupFTPCount: ".$WeeklyBackupFTPCount."<p>";
		
	print "MonthlyBackupStatus: ".stringVal($MonthlyBackupStatus)."<br>";
	print "MonthlyBackupWhat: ".$MonthlyBackupWhat."<br>";
	print "MonthlyBackupUseFTP: ".stringVal($MonthlyBackupUseFTP)."<br>";
	print "MonthlyBackupFTPCount: ".$MonthlyBackupFTPCount."<p>";
	
	
	print "<p>";
	print_r($BackupTypes);
	print "<p>";
	print "today's day: ".date("j")."<p>";
	print "today is: ".date("D")."<p>";
}

$DoBackups = false;
$DailyBackups = false;
$WeeklyBackups = false;
$MonthlyBackups = false;

if( (intVal(date("j")) == 7) && ($MonthlyBackupStatus == true) )
{
	$DoBackups = true;
	$MonthlyBackups = true;
	
	if($Debug == true)
	{
		print "DoBackups = true in monthly<p>";
	}
}

if( (strtolower(date("D")) == "sun") && ($WeeklyBackupStatus == true) )
{
	$DoBackups = true;
	$WeeklyBackups = true;

	if($Debug == true)
	{
		print "DoBackups = true in weekly<p>";
	}
}

if($DailyBackupStatus == true)
{
	$DoBackups = true;
	$DailyBackups = true;
	
	if($Debug == true)
	{
		print "DoBackups = true in daily<p>";
	}
}

if($DoBackups == false)
{
	
	if($Debug == true)
	{
		print "DoBackups == false, exiting...<p>";
	}
	exit();
}


$DomainBackupListArray = array();
$DomainBackupListArrayCount = 0;
$oDomain->GetDomainList($DomainBackupListArray, $DomainBackupListCount, 0, "admin");

if($Debug == true)
{
	print "<p>";
	print_r($DomainBackupListArray);
	print "<p>";
}



for($DomainCount = 0; $DomainCount < $DomainBackupListCount; $DomainCount++)
{

	$DomainName = "";
	$DomainUserName = "";
	$DomainPath = "";
	
	if($Debug == true)
	{
		print "<hr>";
	}

	if( ($DomainBackupListArray[$DomainCount]["type"] != "primary") || ($DomainBackupListArray[$DomainCount]["Suspended"] != 0) )
	{
		if($Debug == true)
		{
			print $DomainBackupListArray[$DomainCount]["domain_name"]." not eligable<p>";
		}
		continue;
	}

	if($Debug == true)
	{
		print "Backing up ".$DomainBackupListArray[$DomainCount]["domain_name"]."<p>";
	}
	$DomainName = $DomainBackupListArray[$DomainCount]["domain_name"];
	$DomainUserName = $DomainBackupListArray[$DomainCount]["username"];
	$DomainPath = $DomainBackupListArray[$DomainCount]["Path"];

	$DomainID = $DomainBackupListArray[$DomainCount]["id"];

	$XMLContent = new SimpleXMLElement('<?xml version="1.0" ?><BackupScript />');

	$DomainArray = array();
	$oDomain->GetDomainInfo($DomainID, $DomainArray);

	$oPackage = new Package();

	$PackageSettingValues = array();
	$ArrayCount = 0;
	$oPackage->GetPackageDetails($DomainArray["PackageID"], $PackageSettingValues, $ArrayCount, "admin", 0);

	$PackageXML = $XMLContent->addChild("Package");
	foreach($PackageSettingValues as $key=>$val)
	{
		$PackageXML->addChild($key, $val);
	}
	
	
	$ClientArray = array();
	$oUser->GetUserInfoArray($DomainArray["ClientID"], $ClientArray);
	
	$ClientXML = $XMLContent->addChild("User");
	foreach($ClientArray as $key=>$val)
	{
		$ClientXML->addChild($key, $val);
	}
	$Role = $ClientArray["Role"];
	
	
	$DomainListArray = array();
	$oDomain->GetDomainTree($DomainID, $DomainListArray, $ArrayCount);
	
	$DomainXML = $XMLContent->addChild("Domain");
	for($x = 0; $x < $ArrayCount; $x++)
	{
		$DomainInstanceXML = $DomainXML->addChild("Instance");
		foreach($DomainListArray[$x] as $key=>$val)
		{
			$DomainInstanceXML->addChild($key, $val);
		}
	}
	
	
	$MySQLArray = array();
	
	$oMySQL = new MySQL();
	$oMySQL->GetMySQLDomainList($MySQLArray, $ArrayCount, $DomainArray["UserName"]);
	
	$MySQLXML = $XMLContent->addChild("MySQL");
	
	for($x = 0; $x < $ArrayCount; $x++)
	{
		$MySQLInstanceXML = $MySQLXML->addChild("Instance");
		foreach($MySQLArray[$x] as $key=>$val)
		{
			$MySQLInstanceXML->$key = $val;
		}
	}
	
	$EmailArray = array();
	$oEmail = new Email();
	
	$oEmail->GetDomainEmailList($EmailArray, $ArrayCount, $DomainArray["UserName"]);
	
	$EmailXML = $XMLContent->addChild("Email");
	
	for($x = 0; $x < $ArrayCount; $x++)
	{
		$EmailInstanceXML = $EmailXML->addChild("Instance");
		foreach($EmailArray[$x] as $key=>$val)
		{
			$EmailInstanceXML->addChild($key, $val);
		}
	}
	
	
	
	$EmailForwardingArray = array();
	
	$oEmail->GetDomainSingleForwardList($EmailForwardingArray, $ArrayCount, $DomainArray["UserName"]);
	
	$EmailXML = $XMLContent->addChild("EmailForwarding");
	
	for($x = 0; $x < $ArrayCount; $x++)
	{
		$EmailInstanceXML = $EmailXML->addChild("Instance");
		foreach($EmailForwardingArray[$x] as $key=>$val)
		{
			$EmailInstanceXML->addChild($key, $val);
		}
	}
	
	
	
	$EmailOptionsArray = array();
	
	$oEmail->GetDomainEmailOptionsList($EmailOptionsArray, $ArrayCount, $DomainArray["DomainName"]);
	
	$EmailXML = $XMLContent->addChild("EmailOptions");
	
	for($x = 0; $x < $ArrayCount; $x++)
	{
		$EmailInstanceXML = $EmailXML->addChild("Instance");
		foreach($EmailOptionsArray[$x] as $key=>$val)
		{
			$EmailInstanceXML->addChild($key, $val);
		}
	}
	
	
	$AutoReplyArray = array();
	$oEmail->GetAutoReplyList($AutoReplyArray, $ArrayCount, $DomainArray["ClientID"], $Role);
	
	$AutoReplyXML = $XMLContent->addChild("AutoReply");
	
	for($x = 0; $x < $ArrayCount; $x++)
	{
		if($oEmail->GetDomainIDFromEmailID($AutoReplyArray[$x]["MailBoxID"]) == $DomainID)
		{
			$AutoReplyInstanceXML = $AutoReplyXML->addChild("Instance");
			foreach($AutoReplyArray[$x] as $key=>$val)
			{
				$AutoReplyInstanceXML->addChild($key, $val);
			}
		}
	}
	
	
	
	
	
	$oFTP = new FTP();
	$FTPArray = array();
	
	$oFTP->GetDomainFTPList($FTPArray, $ArrayCount, $DomainID);
	
	$FTPXML = $XMLContent->addChild("FTP");
	
	for($x = 0; $x < $ArrayCount; $x++)
	{
		$FTPInstanceXML = $FTPXML->addChild("Instance");
		foreach($FTPArray[$x] as $key=>$val)
		{
			$FTPInstanceXML->addChild($key, $val);
		}
	}
	
		$RandomString = date("Y-m-d_H-i-s")."_";
		$RandomString = $RandomString.rand(0,9);
		$RandomString = $RandomString.rand(0,9);
		$RandomString = $RandomString.rand(0,9);
		$RandomString = $RandomString.rand(0,9);
		$RandomString = $RandomString.rand(0,9);
		$RandomString = $RandomString.rand(0,9);
	
		while(is_dir($_SERVER["DOCUMENT_ROOT"]."/../backups/tmp/".$RandomString))
		{
			$RandomString = date("Y-m-d_H-i-s")."_";
			$RandomString = $RandomString.rand(0,9);
			$RandomString = $RandomString.rand(0,9);
			$RandomString = $RandomString.rand(0,9);
			$RandomString = $RandomString.rand(0,9);
			$RandomString = $RandomString.rand(0,9);
			$RandomString = $RandomString.rand(0,9);
		}
	
	//print "<p>Making: '".$_SERVER["DOCUMENT_ROOT"]."/../backups/tmp/".$RandomString."'<p>";
	mkdir($_SERVER["DOCUMENT_ROOT"]."/../backups/tmp/".$RandomString, 0755);
	chmod($_SERVER["DOCUMENT_ROOT"]."/../backups/tmp/".$RandomString, 0755);
	
	$fp = fopen($_SERVER["DOCUMENT_ROOT"]."/../backups/tmp/".$RandomString."/".$DomainArray["UserName"].".xml", "w");
	fwrite($fp, $XMLContent->asXML());
	fclose($fp);
	
	$fp = fopen($_SERVER["DOCUMENT_ROOT"]."/nm/".$DomainID.".backup", "w");
	fwrite($fp, "RandomString=".$RandomString."\n");
	fwrite($fp, "DomainName=".$DomainName."\n");
	fwrite($fp, "DomainUserName=".$DomainUserName."\n");
	fwrite($fp, "DomainPath=".$DomainPath."\n");

	if($DailyBackups == true)
	{
		fwrite($fp, "Daily=".$BackupTypes["daily"]."\n");
		if($DailyBackupUseFTP == true)
		{
			fwrite($fp, "DailyFTPUse=on\n");
			fwrite($fp, "DailyFTPCount=".$DailyBackupFTPCount."\n");
		}
	}

	if($WeeklyBackups == true)
	{
		fwrite($fp, "Weekly=".$BackupTypes["weekly"]."\n");
		if($WeeklyBackupUseFTP == true)
		{
			fwrite($fp, "WeeklyFTPUse=on\n");
			fwrite($fp, "WeeklyFTPCount=".$WeeklyBackupFTPCount."\n");
		}
	}

	if($MonthlyBackups == true)
	{
		fwrite($fp, "Monthly=".$BackupTypes["monthly"]."\n");
		if($MonthlyBackupUseFTP == true)
		{
			fwrite($fp, "MonthlyFTPUse=on\n");
			fwrite($fp, "MonthlyFTPCount=".$MonthlyBackupFTPCount."\n");
		}
	}


	if($FTPHost != "")	
	{
		fwrite($fp, "FTPHost=".$FTPHost."\n");
		fwrite($fp, "FTPRemotePath=".$FTPRemotePath."\n");
		fwrite($fp, "FTPUserName=".$FTPUserName."\n");
		fwrite($fp, "FTPPassword=".$FTPPassword."\n");
	}		
	/*
	if($EmailAddress != "")
	{
	        fwrite($fp, "EmailAddress=".$EmailAddress."\n");
	}
	*/
	
	fclose($fp);
}
