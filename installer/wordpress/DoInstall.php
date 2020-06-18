<?php

if( ! file_exists($_SERVER["DOCUMENT_ROOT"]."/nm")) {
	mkdir($_SERVER["DOCUMENT_ROOT"]."/nm", 0755);
}


function generatePassword($length=15, $Strength=1) 
{
	$vowels_lower = 'aeiou';
	$consonants_lower = 'bcdfghjklmnpqrstvwxyz';
 
	$vowels_upper = strtoupper($vowels_lower);
	$consonants_upper = strtoupper($consonants_lower);
	
	$number = "8374261590";
	
	$special = "!^*~-+";
	
	if($Strength == 1)
	{
		$LongString = $vowels_lower.$number.$vowels_upper.$consonants_lower.$special.$consonants_upper;
	} else {
		$LongString = $number;
	}

	$password = '';
	
	for ($i = 0; $i < $length; $i++)  {
		$password .= $LongString[rand(0, strlen($LongString) - 1)];
	}
	return $password;
}


include_once($_SERVER["DOCUMENT_ROOT"]."/vendor/autoload.php");

$oDomain = new Domain();
$oUser = new User();
$oPackage = new Package();
$oMySQL = new MySQL();

$DomainID = -1;
$DomainID = $_POST["DomainID"];

$DomainInfoArray = array();
$random = random_int(1, 1000000);
$nonceArray = [	
		$oUser->Role,
		$oUser->ClientID,
		$DomainID,
		$random
];

$oSimpleNonce = new SimpleNonce();
$nonce = $oSimpleNonce->GenerateNonce("getDomainInfo", $nonceArray);
$oDomain->GetDomainInfo($DomainID, $random, $DomainInfoArray, $nonce);


$WP_DomainUserName = $DomainInfoArray["UserName"];
$WP_Path = $DomainInfoArray["Path"];

if($WP_DomainUserName == "")
{
	print "<font color=\"red\">ERROR, please contact the site admin!</font><br>";
	exit();
}

$WP_ClientID = $DomainInfoArray["ClientID"];
$WP_UserName = $DomainInfoArray["DomainOwner"];
$WP_PackageID = $DomainInfoArray["PackageID"];

$WP_MySQLUsage = $oPackage->GetMySQLUsage($WP_DomainUserName);
$WP_MySQLAllowance = $oPackage->GetPackageAllowance("MySQL", $WP_PackageID);

$WP_DomainName = $DomainInfoArray["DomainName"];


/*
print "WP_DomainUserName: ".$WP_DomainUserName."<br>";
print "WP_Path: ".$WP_Path."<br>";
print "WP_ClientID: ".$WP_ClientID."<br>";
print "WP_UserName: ".$WP_UserName."<br>";
print "WP_PackageID: ".$WP_PackageID."<br>";
print "WP_MySQLUsage: ".$WP_MySQLUsage."<br>";
print "WP_MySQLAllowance: ".$WP_MySQLAllowance."<br>";
print "WP_DomainName = ".$WP_DomainName."<br>";
print "DomainID: ".$DomainID."<p>";
*/

if( ($WP_MySQLAllowance - $WP_MySQLUsage) > 0)
{
	$WP_Password = generatePassword(15,1);
	$WP_DatabaseUserName = "un".generatePassword(3,0);
	$WP_DatabaseName = $WP_DomainUserName."_db".generatePassword(3,0);

	$TmpFile = $_SERVER["DOCUMENT_ROOT"]."/nm/".date("YmdHis")."_".generatePassword(5,0).".wp";

	/*
	print "ClientID: ".$WP_ClientID."<br>";	
	print "DomainID: ".$DomainID."<br>";	
	print "password = ".$WP_Password."<br>";
	print "UserName = ".$WP_DomainUserName."_".$WP_DatabaseUserName."<br>";
	print "DB = ".$WP_DatabaseName."<br>";
	print "Domain name = ".$WP_DomainName."<br>";
	print "UserName: ".$WP_UserName."<br>";
	print "Domain UserName: ".$WP_DomainUserName."<br>";
	print "Path: ".$WP_Path."<br>";
	print "PackageID: ".$WP_PackageID."<p>";
	print "TmpFile: ".$TmpFile."<p>";
	*/
	
	$oMySQL->AddMySQL($DomainID, $WP_DatabaseName, $WP_DomainUserName."_".$WP_DatabaseUserName, $WP_Password, $WP_ClientID, $WP_PackageID);
	
	$f = fopen($TmpFile, "w");
	fwrite($f, "DomainUsername:".$WP_DomainUserName."\n");
	fwrite($f, "DatabaseName:".$WP_DatabaseName."\n");
	fwrite($f, "DatabaseUsername:".$WP_DomainUserName."_".$WP_DatabaseUserName."\n");
	fwrite($f, "DatabasePassword:".$WP_Password."\n");
	fwrite($f, "Path:".$WP_Path."\n");
	fclose($f);


	$FileContents = trim(file_get_contents($TmpFile));
	//print $FileContents."<br>";

	$x = 0;
	while($FileContents != "done")
	{
		if($x++ > 10)
		{
			print "Timeout: ".$TmpFile."<p>";
			exit();
			break;
		}

		flush();
		sleep(1);
		$FileContents = trim(file_get_contents($TmpFile));
		//print "'".$FileContents."'<br>";
	}

	unlink($TmpFile);	


	header("Location: http://".$WP_DomainName);

}
else
{
	print "<font color=\"red\">ERROR, you have no more MySQL databases left. Install cannot continue</font>";
}
