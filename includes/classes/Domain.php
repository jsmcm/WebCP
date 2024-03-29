<?php
/*********************************************************************
*********************************************************************/
if(!isset($_SESSION))  {
    session_start();
}

include_once($_SERVER["DOCUMENT_ROOT"]."/vendor/autoload.php");
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class Domain
{
	var $oDatabase = null; 
	var $DatabaseConnection = null;
	var $LastErrorDescription = "";

	function __construct()
	{
		$this->oDatabase = new Database();
		$this->DatabaseConnection = $this->oDatabase->GetConnection();
			
		if( ! file_exists($_SERVER["DOCUMENT_ROOT"]."/nm/")) {
			mkdir($_SERVER["DOCUMENT_ROOT"]."/nm/", 0755);
		}
	}


	function deleteDomainSettingsByPrefix($domainId, $prefix)
	{

		try {
			$query = $this->DatabaseConnection->prepare("UPDATE domain_settings SET deleted = 1 WHERE domain_id = :domain_id AND setting_name like '".$prefix."%'");

			$query->bindParam(":domain_id", $domainId);

			$query->execute();

		} catch(PDOException $e) {
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Domain.php -> deleteDomainSettingsByPrefix(); Error = ".$e);
		}

	}



	function deleteDomainSettings($domainId)
    {

 		try {
			$query = $this->DatabaseConnection->prepare("UPDATE domain_settings SET deleted = 1 WHERE domain_id = :domain_id");

			$query->bindParam(":domain_id", $domainId);

			$query->execute();

		} catch(PDOException $e) {
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Domain.php -> deleteDomainSettings(); Error = ".$e);
		}

    }


	function deleteDomainSetting($domainId, $setting, $nonceArray)
    {


		if ( intVal($domainId) < 1 ) {
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Domain.php -> deleteDomainSetting(); domainId cannot be blank in Domain::deleteDomainSetting");
			throw new Exception("<p><b>domainId cannot be blank in Domain::deleteDomainSetting</b><p>");
		}

		if ( $setting == "" ) {
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Domain.php -> deleteDomainSetting(); setting cannot be blank in Domain::deleteDomainSetting");
			throw new Exception("<p><b>setting cannot be blank in Domain::deleteDomainSetting</b><p>");
		}

		if ( ! (is_array($nonceArray) && !empty($nonceArray) ) ) {
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Domain.php -> deleteDomainSetting(); Nonce not set");
			throw new Exception("<p><b>Nonce not set in Domain::deleteDomainSetting</b><p>");
		}
		
		$oUser = new User();
		$ClientID = $oUser->getClientId();

		$nonceMeta = [
			$oUser->Role,
			$ClientID,
			$domainId,
			$setting
		];

		$oSimpleNonce = new SimpleNonce();
		$nonceResult = $oSimpleNonce->VerifyNonce($nonceArray["Nonce"], "deleteDomainSetting", $nonceArray["TimeStamp"], $nonceMeta);

		if ( ! $nonceResult ) {
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Domain.php -> deleteDomainSetting(); Nonce failed");
			throw new Exception("<p><b>Nonce failed in Domain::deleteDomainSetting</b></p>");
		}

 		try {
			$query = $this->DatabaseConnection->prepare("UPDATE domain_settings SET deleted = 1 WHERE setting_name = :setting AND domain_id = :domain_id");

			$query->bindParam(":setting", $setting);
			$query->bindParam(":domain_id", $domainId);

			$query->execute();

		} catch(PDOException $e) {
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Domain.php -> deleteDomainSetting(); Error = ".$e);
		}

    }

	function saveDomainSetting($domainId, $setting, $value, $extra1="", $extra2="", $nonceArray)
	{

		if ( intVal($domainId) < 1 ) {
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Domain.php -> saveDomainSetting(); domainId cannot be blank in Domain::saveDomainSetting");
			throw new Exception("<p><b>domainId cannot be blank in Domain::saveDomainSetting</b><p>");
		}

		if ( $setting == "" ) {
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Domain.php -> saveDomainSetting(); setting cannot be blank in Domain::saveDomainSetting");
			throw new Exception("<p><b>setting cannot be blank in Domain::saveDomainSetting</b><p>");
		}

		if ( $value == "" ) {
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Domain.php -> saveDomainSetting(); value cannot be blank in Domain::saveDomainSetting");
			throw new Exception("<p><b>value cannot be blank in Domain::saveDomainSetting</b><p>");
		}


		if ( ! (is_array($nonceArray) && !empty($nonceArray) ) ) {
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Domain.php -> saveDomainSetting(); Nonce not set");
			throw new Exception("<p><b>Nonce not set in Domain::saveDomainSetting</b><p>");
		}
		
		$oUser = new User();
		$ClientID = $oUser->getClientId();

		$nonceMeta = [
			$oUser->Role,
			$ClientID,
			$domainId,
			$setting,
			$value
		];

		$oSimpleNonce = new SimpleNonce();
		$nonceResult = $oSimpleNonce->VerifyNonce($nonceArray["Nonce"], "saveDomainSetting", $nonceArray["TimeStamp"], $nonceMeta);

		if ( ! $nonceResult ) {
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Domain.php -> saveDomainSetting(); Nonce failed");
			throw new Exception("<p><b>Nonce failed in Domain::saveDomainSetting</b></p>");
		}


		$nonceArray = [
			$oUser->Role,
			$ClientID,
			$domainId,
			$setting
		];
		
		$nonce = $oSimpleNonce->GenerateNonce("deleteDomainSetting", $nonceArray);
 		$this->deleteDomainSetting($domainId, $setting, $nonce);
		

		try {
			$query = $this->DatabaseConnection->prepare("INSERT INTO domain_settings VALUES (0, :domain_id, :setting, :value, :extra1, :extra2, 0)");

			$query->bindParam(":domain_id", $domainId);
			$query->bindParam(":setting", $setting);
			$query->bindParam(":value", $value);
			$query->bindParam(":extra1", $extra1);
			$query->bindParam(":extra2", $extra2);

			$query->execute();
			return true;
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Domain.php -> saveDomainSetting(); Error = ".$e);
		}

		return false;
	}

	function GetAccountsCreatedCount()
	{
		try {
			$query = $this->DatabaseConnection->prepare("SELECT COUNT(id) AS count FROM domains WHERE deleted = 0 AND domain_type = 'primary'");

			$query->execute();

			if($result = $query->fetch(PDO::FETCH_ASSOC)) {
				return $result["count"];
			}
		} catch(PDOException $e) {
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Domain.php -> GetAccountsCreatedCount(); Error = ".$e);
		}

		return 0;
	}

	function MakeUnsuspendFile($UserName)
	{
		if(file_exists($_SERVER["DOCUMENT_ROOT"]."/nm/".$UserName.".suspend")) {
			unlink($_SERVER["DOCUMENT_ROOT"]."/nm/".$UserName.".suspend");
		}

		$myfile = $_SERVER["DOCUMENT_ROOT"]."/nm/".$UserName.".unsuspend";
		$fh = fopen($myfile, 'a');
		fwrite($fh, $UserName);
		fclose($fh);
	}


	function MakeSuspendFile($UserName)
	{
		if(file_exists($_SERVER["DOCUMENT_ROOT"]."/nm/".$UserName.".unsuspend")) {
			unlink($_SERVER["DOCUMENT_ROOT"]."/nm/".$UserName.".unsuspend");
		}

		$myfile = $_SERVER["DOCUMENT_ROOT"]."/nm/".$UserName.".suspend";
		$fh = fopen($myfile, 'a');
		fwrite($fh, $UserName);
		fclose($fh);
	}


	function UpdateMailRouting($DomainID, $Routing)
	{

		try {
			$query = $this->DatabaseConnection->prepare("UPDATE domains SET mail_type = :routing WHERE id = :id");

			$Routing = strtolower($Routing);
			$query->bindParam(":routing", $Routing);
			$query->bindParam(":id", $DomainID);

			$query->execute();

		} catch(PDOException $e) {
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Domain.php -> UpdateMailRouting(); Error = ".$e);
		}


		return 1;
	}

   
   
	function Unsuspend($DomainID)
	{
		$oFTP = new FTP();
		$oMySQL = new MySQL();
		
		$oFTP->ManageSuspension($DomainID, 1);

		try
		{
			$query = $this->DatabaseConnection->prepare("UPDATE domains SET suspended = 0 WHERE id = :id");

			$query->bindParam(":id", $DomainID);

			$query->execute();

		} catch(PDOException $e) {
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Domain.php -> Unsuspend(); Error = ".$e);
		}


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
		
		$InfoArray = array();
		$this->GetDomainInfo($DomainID, $random, $InfoArray, $nonce);
		
		$oMySQL->unSuspendUserAccounts($InfoArray["UserName"]);
		
		$this->MakeUnsuspendFile($InfoArray["UserName"]);

		return 1;
	}

   
	function Suspend($DomainID)
	{
		$oFTP = new FTP();
		$oMySQL = new MySQL();
		$oUser = new User();

		$oFTP->ManageSuspension($DomainID, 0);


		try {
			$query = $this->DatabaseConnection->prepare("UPDATE domains SET suspended = 1 WHERE id = :id");

			$query->bindParam(":id", $DomainID);

			$query->execute();


		} catch(PDOException $e) {
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Domain.php -> Suspend(); Error = ".$e);
		}

		$random = random_int(1, 1000000);
		$oSimpleNonce = new SimpleNonce();
		$nonceArray = [	
			$oUser->Role,
			$oUser->ClientID,
			$DomainID,
			$random
		];
		$nonce = $oSimpleNonce->GenerateNonce("getDomainInfo", $nonceArray);
		

		$InfoArray = array();
		$this->GetDomainInfo($DomainID, $random, $InfoArray, $nonce);

		$oMySQL->suspendUserAccounts($InfoArray["UserName"]);

		$this->MakeSuspendFile($InfoArray["UserName"]);

		return 1;
	}


	function GetPackageDiskSpaceUsage($ResellerID=0) 
	{

		$oPackage = new Package();

		$PackageStatsArray = array();
		$ArrayCount = 0;

		$oPackage->GetPackageUsageStats($PackageStatsArray, $ArrayCount, $ResellerID);

		$TotalDiskUsage = 0;

		for($x = 0; $x < $ArrayCount; $x++) {
			$TotalDiskUsage = $TotalDiskUsage + $PackageStatsArray[$x]["PackageCount"] * $PackageStatsArray[$x]["DiskSpace"];
		}

		return $TotalDiskUsage;
	}


	function GetPackageTrafficUsage($ResellerID=0) 
	{

		$oPackage = new Package();

		$PackageStatsArray = array();
		$ArrayCount = 0;

		$oPackage->GetPackageUsageStats($PackageStatsArray, $ArrayCount, $ResellerID);

		$TotalTrafficUsage = 0;

		for($x = 0; $x < $ArrayCount; $x++) {
			$TotalTrafficUsage = $TotalTrafficUsage + $PackageStatsArray[$x]["PackageCount"] * $PackageStatsArray[$x]["Traffic"];
		}

		return $TotalTrafficUsage;

	}



	function ValidateDomainName($DomainName)
	{
		$DomainName = strtolower($DomainName);

		// check for double period
		if(strstr($DomainName, "..")) {
			return -1;
		}

		// First char must be alphanum
		if( (substr($DomainName, 0, 1) < 'a') || (substr($DomainName, 0, 1) > 'z') ) {
			if( (substr($DomainName, 0, 1) < '0') || (substr($DomainName, 0, 1) > '9') ) {
				return -2;
			}
		}



		for($x = 0; $x < strlen($DomainName); $x++) {
			if( (substr($DomainName, $x, 1) < 'a') || (substr($DomainName, $x, 1) > 'z') ) {
				if( (substr($DomainName, $x, 1) < '0') || (substr($DomainName, $x, 1) > '9') ) {
					if( (substr($DomainName, $x, 1) != '-') && (substr($DomainName, $x, 1) != '.') ) {
						return -3;
					}
				}
			}
		}

		if(strlen($DomainName) > 100) {
			return -4;
		}


		if(strlen($DomainName) < 4) {
			return -5;
		}

		// must contain at least 1 .
		if(!strstr($DomainName, ".")) {
			return -6;
		}

		return 1;
	}

	
	function GetParentDomainIDRecursive($DomainID, $StopAtParked=false)
	{
		$Count = 0;

		try
		{
			$query = $this->DatabaseConnection->prepare("SELECT parent_domain_id, domain_type FROM domains WHERE deleted = 0 AND id = :id");

			$query->bindParam(":id", $DomainID);

			$query->execute();

			if($result = $query->fetch(PDO::FETCH_ASSOC))
			{
				$DomainType = $result["domain_type"];
				$ParentID = $result["parent_domain_id"];

				if( ($DomainType == "primary") || ( ($StopAtParked == true) && ($DomainType == 'parked') ) )
				{
					return $DomainID;
				}
				else
				{
					return $this->GetParentDomainIDRecursive($ParentID);
				}
			}
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Domain.php -> GetParentDomainIDRecursive(); Error = ".$e);
		}



		return -1;
	}

   
	function GetDomainIDChain($DomainID) 
	{

		$DomainIDChainString = "";
		
		try
		{
			$query = $this->DatabaseConnection->prepare("SELECT id FROM domains WHERE (id = :id OR ancestor_domain_id = :id2) AND deleted = 0");

			$query->bindParam(":id", $DomainID);
			$query->bindParam(":id2", $DomainID);

			$query->execute();

			while($result = $query->fetch(PDO::FETCH_ASSOC))
			{

				if($DomainIDChainString != "")
				{
					$DomainIDChainString = $DomainIDChainString.", ";
				}
	
				$DomainIDChainString = $DomainIDChainString.$result["id"];
		
			}
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Domain.php -> GetDomainIDChain(); Error = ".$e);
		}

		
		return $DomainIDChainString;

	}

	function GetClientIDFromDomainUserName($DomainUserName) 
	{
			
		try
		{
			$query = $this->DatabaseConnection->prepare("SELECT client_id FROM domains WHERE UserName = :domain_user_name AND deleted = 0");

			$query->bindParam(":domain_user_name", $DomainUserName);

			$query->execute();

			while($result = $query->fetch(PDO::FETCH_ASSOC))
			{
				return $result["client_id"];
			}
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Domain.php -> GetClientIDFromDomainUserName(); Error = ".$e);
		}

		return -1;

	}



	function GetClientIDFromDomainName($DomainName) 
	{
		
		try
		{
			$query = $this->DatabaseConnection->prepare("SELECT client_id FROM domains WHERE fqdn = :domain_name AND deleted = 0");

			$query->bindParam(":domain_name", $DomainName);

			$query->execute();

			while($result = $query->fetch(PDO::FETCH_ASSOC))
			{
				return $result["client_id"];
			}
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Domain.php -> GetClientIDFromDomainName(); Error = ".$e);
		}

		return -1;
		
	}



	function GetSubDomainName($SubDomainID) 
	{
		
		try
		{
			$query = $this->DatabaseConnection->prepare("SELECT sub_domain FROM sub_domains WHERE deleted = 0 AND id = :id");

			$query->bindParam(":id", $SubDomainID);

			$query->execute();

			while($result = $query->fetch(PDO::FETCH_ASSOC))
			{
				return $result["sub_domain"];
			}
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Domain.php -> GetSubDomainName(); Error = ".$e);
		}


		return "";

	}
	
	
	function GetParkedDomainCountRecursive($DomainID)
	{
		$Count = 0;

		try
		{
			$query = $this->DatabaseConnection->prepare("SELECT COUNT(id) AS count FROM domains WHERE deleted = 0 AND domain_type = 'parked' AND parent_domain_id = :domain_id");

			$query->bindParam(":domain_id", $DomainID);

			$query->execute();

			while($result = $query->fetch(PDO::FETCH_ASSOC))
			{
				$Count = $Count + $result["count"];
			}
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Domain.php -> GetParkedDomainCountRecursive(); Error = ".$e);
		}




		try
		{
			$query = $this->DatabaseConnection->prepare("SELECT id FROM domains WHERE domain_type != 'primary' AND parent_domain_id = :domain_id");

			$query->bindParam(":domain_id", $DomainID);

			$query->execute();

			while($result = $query->fetch(PDO::FETCH_ASSOC))
			{
				$Count = $Count + $this->GetParkedDomainCountRecursive($result["id"]);
			}
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Domain.php -> GetParkedDomainCountRecursive(); Error = ".$e);
		}
		
		return $Count;
	}

	function GetParkedDomainCount($DomainID) 
	{
		return $this->GetParkedDomainCountRecursive($DomainID);
	}
	

	function GetParkedDomainCount_bck($DomainID) 
	{
		

		try
		{
			$query = $this->DatabaseConnection->prepare("SELECT COUNT(id) AS count FROM domains WHERE deleted = 0 AND domain_type = 'parked' AND parent_domain_id = :domain_id");

			$query->bindParam(":domain_id", $DomainID);

			$query->execute();

			while($result = $query->fetch(PDO::FETCH_ASSOC))
			{
				return $result["count"];
			}
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Domain.php -> GetParkedDomainCount_bck(); Error = ".$e);
		}


	}
	


	function GetSubDomainCountRecursive($DomainID)
	{
		$Count = 0;


		try
		{
			$query = $this->DatabaseConnection->prepare("SELECT COUNT(id) AS count FROM domains WHERE deleted = 0 AND domain_type = 'subdomain' AND parent_domain_id = :domain_id");

			$query->bindParam(":domain_id", $DomainID);

			$query->execute();

			if($result = $query->fetch(PDO::FETCH_ASSOC))
			{
				$Count = $Count + $result["count"];
			}
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Domain.php -> GetSubDomainCountRecursive(); Error = ".$e);
		}






		try
		{
			$query = $this->DatabaseConnection->prepare("SELECT id FROM domains WHERE deleted = 0 AND domain_type != 'primary' AND parent_domain_id = :domain_id");

			$query->bindParam(":domain_id", $DomainID);

			$query->execute();

			while($result = $query->fetch(PDO::FETCH_ASSOC))
			{
				$Count = $Count + $this->GetSubDomainCountRecursive($result["id"]);
			}
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Domain.php -> GetSubDomainCountRecursive(); Error = ".$e);
		}
		
		return $Count;
	}

	function GetSubDomainCount($DomainID) 
	{
		return $this->GetSubDomainCountRecursive($DomainID);
	}
	
	


	
	function SubDomainExists($SubDomainName, $DomainID) 
	{
		
		$oUser = new User();
		$oSimpleNonce = new SimpleNonce();

		$random = random_int(1,100000);
		$nonceArray = [
			$oUser->Role,
			$oUser->getClientId(),
			$DomainID,
			$random
		];
		
		$nonce = $oSimpleNonce->GenerateNonce("getDomainNameFromDomainID", $nonceArray);
		$DomainName = $this->GetDomainNameFromDomainID($DomainID, $random, $nonce);

		try
		{
			$query = $this->DatabaseConnection->prepare("SELECT id FROM domains WHERE fqdn = :sub_domain_name.:domain_name' AND deleted = 0 AND parent_domain_id = :domain_id");

			$query->bindParam(":sub_domain_name", $SubDomainName);
			$query->bindParam(":domain_name", $DomainName);
			$query->bindParam(":domain_id", $DomainID);

			$query->execute();

			if($result = $query->fetch(PDO::FETCH_ASSOC))
			{
				return $result["id"];
			}
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Domain.php -> SubDomainExists(); Error = ".$e);
		}
		

		return -1;
		
	}
	
	
		
	function DomainExists($DomainName) 
	{

		try {
			$query = $this->DatabaseConnection->prepare("SELECT id FROM domains WHERE fqdn = :domain_name AND deleted = 0");

			$query->bindParam(":domain_name", $DomainName);

			$query->execute();

			if($result = $query->fetch(PDO::FETCH_ASSOC)) {
				return $result["id"];
			}

		} catch(PDOException $e) {
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Domain.php -> DomainExists(); Error = ".$e);
		}
		
		return -1;
		
	}
	

	function GetParkedDomainListRecursive($DomainID, $ClientID, &$ParkedDomainArray, &$ArrayCount)
	{
		$oUser = new User();
		$oSimpleNonce = new SimpleNonce();

		$clientId = $oUser->getClientId();
		$role = $oUser->Role;

		try
		{
			$query = $this->DatabaseConnection->prepare("SELECT * FROM domains WHERE deleted = 0 AND domain_type = 'parked' AND client_id = :client_id AND parent_domain_id = :domain_id");

			$query->bindParam(":domain_id", $DomainID);
			$query->bindParam(":client_id", $ClientID);

			$query->execute();

			while($result = $query->fetch(PDO::FETCH_ASSOC)) {
				$ParkedDomainArray[$ArrayCount]["ID"] = $result["id"];

				$random = random_int(1,100000);
				$nonceArray = [
					$role,
					$clientId,
					$result["parent_domain_id"],
					$random
				];
				
				$nonce = $oSimpleNonce->GenerateNonce("getDomainNameFromDomainID", $nonceArray);
				$ParkedDomainArray[$ArrayCount]["ParkedOn"] = $this->GetDomainNameFromDomainID($result["parent_domain_id"], $random, $nonce);
	
				// if this is parked on a subdomain which has been deleted but the files still remain, show path
				if($ParkedDomainArray[$ArrayCount]["ParkedOn"] == "")
				{
					$ParkedDomainArray[$ArrayCount]["ParkedOn"] = $result["path"];
				}
	
				$ParkedDomainArray[$ArrayCount++]["ParkedDomain"] = $result["fqdn"];
			}
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Domain.php -> GetParkedDomainListRecursive(); Error = ".$e);
		}



		try
		{
			$query = $this->DatabaseConnection->prepare("SELECT id FROM domains WHERE domain_type != 'primary' AND client_id = :client_id AND parent_domain_id = :domain_id");

			$query->bindParam(":domain_id", $DomainID);
			$query->bindParam(":client_id", $ClientID);

			$query->execute();

			while($result = $query->fetch(PDO::FETCH_ASSOC))
			{
				$this->GetParkedDomainListRecursive($result["id"], $ClientID, $ParkedDomainArray, $ArrayCount);
			}
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Domain.php -> GetParkedDomainListRecursive(); Error = ".$e);
		}

		
	}

	function GetParkedDomainList(&$DomainArray, &$ArrayCount, $DomainID, $ClientID, $Role)
	{
		$DomainArray = array();
		$ArrayCount = 0;
		$this->GetParkedDomainListRecursive($DomainID, $ClientID, $DomainArray, $ArrayCount);
	}


	function GetParkedDomainList_bck(&$DomainArray, &$ArrayCount, $DomainID, $ClientID, $Role)
	{
		$DomainArray = array();
		$ArrayCount = 0;

		try
		{
			$query = $this->DatabaseConnection->prepare("SELECT * FROM domains WHERE deleted = 0 AND client_id = :client_id AND domain_type = 'parked' AND parent_domain_id = :domain_id ORDER BY fqdn ASC");

			$query->bindParam(":domain_id", $DomainID);
			$query->bindParam(":client_id", $ClientID);

			$query->execute();

			while($result = $query->fetch(PDO::FETCH_ASSOC))
			{	
				$DomainArray[$ArrayCount]["ID"] = $line["id"];
				$DomainArray[$ArrayCount++]["ParkedDomain"] = $line["fqdn"];;
			}
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Domain.php -> GetParkedDomainList_bck(); Error = ".$e);
		}

	}


	
	function GetAncestorDomainID($DomainID)
	{
		return $this->GetParentDomainIDRecursive($DomainID);
	}

	function RecreateVHostFiles()
	{
		$oSSL = new SSL();

		$DomainArray = array();
		$ArrayCount = 0;
		$this->GetDomainList($DomainArray, $ArrayCount, 0, 'admin');
		
		for($x = 0; $x < $ArrayCount; $x++)
		{
			if($DomainArray[$x]["type"] == "primary")
			{
				$oSSL->GetCertificatesChainName($DomainArray[$x]["domain_name"]);
				touch("/var/www/html/webcp/nm/".$DomainArray[$x]["id"].".subdomain");
			}
		}
		
	}	


	function MakeDMLFile($UserUserName)
	{

		$myfile = "/var/www/html/webcp/nm/".$UserUserName.".dml";
		$fh = fopen($myfile, 'a') or die("can't open file");
		fclose($fh);

	}

	function MakeDVHFile($DomainName, $UserUserName)
	{

		$myfile = "/var/www/html/webcp/nm/".$DomainName.".dvh";
		$fh = fopen($myfile, 'a') or die("can't open file");
		fwrite($fh, $UserUserName);
		fclose($fh);

	}



	function MakeNMLFile($UserName, $UID)
	{

		$myfile = "/var/www/html/webcp/nm/".$UserName.".nml";
		$fh = fopen($myfile, 'a') or die("can't open file");
		fwrite($fh, $UID);
		fclose($fh);

	}

	function MakeNVHFile($DomainName, $UserName)
	{

		$myfile = "/var/www/html/webcp/nm/".$DomainName.".nvh";
		$fh = fopen($myfile, 'a') or die("can't open file");
		fwrite($fh, $UserName);
		fclose($fh);

	}

	function MakeAccountFile($DomainID)
	{
		$myfile = "/var/www/html/webcp/nm/".$DomainID.".delete_domain";
		$fh = fopen($myfile, "a");
		fwrite($fh, "");
		fclose($fh);
	}
	
	function DeleteAccountFile($DomainID)
	{
		$myfile = "/var/www/html/webcp/nm/".$DomainID.".delete_domain";
		
		if(file_exists($myfile))
		{
			unlink($myfile);
		}
	}

	function MakeDomainFile($DomainID)
	{
		$oLog = new Log();

                $oSSL = new SSL();
	
		$oLog->WriteLog("DEBUG", "Top of MakeDominFile");
		
		$myfile = "/var/www/html/webcp/nm/".$DomainID.".subdomain";
		
		$oLog->WriteLog("DEBUG", "myfile: '".$myfile."'");
	
		//$DomainName = $this->GetDomainNameFromDomainID($DomainID);	
		//$oSSL->GetCertificatesChainName($DomainName);

		$fh = fopen($myfile, "a");
		fwrite($fh, "");
		fclose($fh);
		
	}
	
	function DeleteDomainFile($DomainID)
	{
		$oLog = new Log();
		$oUser = new User();
		$oSimpleNonce = new SimpleNonce();

		$clientId = $oUser->getClientId();
		$role = $oUser->Role;
		
		$myfile = "/var/www/html/webcp/nm/".$DomainID.".subdomain";
		
		$oLog->WriteLog("DEBUG", "myfile: '".$myfile."'");
		
		if(file_exists($myfile)) {

			$random = random_int(1,100000);
			$nonceArray = [
				$role,
				$clientId,
				$DomainID,
				$random
			];
			
			$nonce = $oSimpleNonce->GenerateNonce("getDomainNameFromDomainID", $nonceArray);
			$DomainName = $this->GetDomainNameFromDomainID($DomainID, $random, $nonce);	
			if(file_exists("/var/www/html/webcp/nm/".$DomainName.".crtchain")) {
				unlink("/var/www/html/webcp/nm/".$DomainName.".crtchain");
			}

			$oLog->WriteLog("DEBUG","file exists, unlinking");
			unlink($myfile);
		}
	}

	function DeleteNMLFile($UserUserName)
	{
		$myfile = "/var/www/html/webcp/nm/".$UserUserName.".nml";
		unlink($myfile);
	}

	function DeleteNVHFile($DomainName)
	{
		$myfile = "/var/www/html/webcp/nm/".$DomainName.".nvh";
		unlink($myfile);
	}

	function CreateUserName($DomainName)
	{
		$UserName = "";

		for($x = 0; $x < strlen($DomainName); $x++)
		{
			if(ctype_alnum(substr($DomainName, $x, 1)))
			{
				$UserName = $UserName.substr($DomainName, $x, 1);
			}
		}

		if(strlen($UserName) > 8)
		{
			$UserName = substr($UserName, 0, 8);
		}

		$x = 0;		
		while($this->UserNameExists($UserName) == 1)
		{
			$x = $x + 1;
			$UserName = substr($UserName, 0, (8 - strlen($x))).$x;
		}

		return $UserName;


	}

	function DeleteSubDomain($ClientID, $SubDomainID, &$Error)
	{
		$oUser = new User();

		$random = random_int(1, 100000);
		$nonceArray = [
			$oUser->Role,
			$oUser->ClientID,
			$SubDomainID,
			$random
		];
		
		$oSimpleNonce = new SimpleNonce();
		
		$nonce = $oSimpleNonce->GenerateNonce("getDomainOwner", $nonceArray);
		if($ClientID != $this->GetDomainOwner($SubDomainID, $random, $nonce)) {
			return 0;
		}

		$x = $this->DeleteSubDomainRecursive($SubDomainID, $ClientID, $Error);

		return $x;
	}

	
	function SendNewDomainEmail($DomainName, $FirstName, $Surname, $EmailAddress)
	{

		$oSettings = new Settings();

		$SendSystemEmails = $oSettings->GetSendSystemEmails();
		if($SendSystemEmails == "off")
		{
			return -2;
		}
		
		$BCC = $oSettings->GetForwardSystemEmailsTo();

		// Send Client Email
		$somecontent = "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">";
		$somecontent = $somecontent."<html xmlns=\"http://www.w3.org/1999/xhtml\">";
		$somecontent = $somecontent."<head profile=\"http://gmpg.org/xfn/11\">";
		$somecontent = $somecontent."";
		$somecontent = $somecontent."<meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />";
		$somecontent = $somecontent."";
		$somecontent = $somecontent."";
		$somecontent = $somecontent."<style>";
		$somecontent = $somecontent."input[type=\"text\"], input[type=\"password\"], textarea, select { ";
		$somecontent = $somecontent."outline: none;";
		$somecontent = $somecontent."}";
		$somecontent = $somecontent."";
		$somecontent = $somecontent."* {";
		$somecontent = $somecontent."   border:none; ";
		$somecontent = $somecontent."   margin:0; ";
		$somecontent = $somecontent."   padding:0;";

		$somecontent = $somecontent."}";
		$somecontent = $somecontent."";
		$somecontent = $somecontent."body {";
		$somecontent = $somecontent."   color: #000; ";
		$somecontent = $somecontent."   font:12.35px Verdana, sans-serif;";
		$somecontent = $somecontent."}";
		$somecontent = $somecontent."";
		$somecontent = $somecontent."a:link. a:visited {";
		$somecontent = $somecontent."   color:#0054a6;";
		$somecontent = $somecontent."   text-decoration:none; ";
		$somecontent = $somecontent."}";
		$somecontent = $somecontent."";
		$somecontent = $somecontent."a:hover {";
		$somecontent = $somecontent."   text-decoration:underline";
		$somecontent = $somecontent."}";
		$somecontent = $somecontent."";
		$somecontent = $somecontent."h1 {";
		$somecontent = $somecontent."   font-size:20px;";
		$somecontent = $somecontent."   margin-bottom:20px; ";
		$somecontent = $somecontent."}";
		$somecontent = $somecontent."";
		$somecontent = $somecontent."h3 {";
		$somecontent = $somecontent."   text-decoration: underline;";
		$somecontent = $somecontent."   margin-bottom:10px;";
		$somecontent = $somecontent."}";
		$somecontent = $somecontent."";
		$somecontent = $somecontent."#wrap {";
		$somecontent = $somecontent."   margin:20px 100px;";
		$somecontent = $somecontent."   width:900px; ";
		$somecontent = $somecontent."}";
		$somecontent = $somecontent."";
		$somecontent = $somecontent."p {";
		$somecontent = $somecontent."   margin:15px 0;";
		$somecontent = $somecontent."}";
		$somecontent = $somecontent."";
		$somecontent = $somecontent."#header {";
		$somecontent = $somecontent."   margin-bottom:20px;";
		$somecontent = $somecontent."}";
		$somecontent = $somecontent."";


		$somecontent = $somecontent."label {";
		$somecontent = $somecontent."   display:block; ";
		$somecontent = $somecontent."   padding-bottom:5px; ";
		$somecontent = $somecontent."   margin-top:10px;";
		$somecontent = $somecontent."   font-size:13px;";
		$somecontent = $somecontent."}";
		$somecontent = $somecontent."";
		$somecontent = $somecontent."#contactform {";
		$somecontent = $somecontent."   width:900px; ";
		$somecontent = $somecontent."   overflow:hidden;";
		$somecontent = $somecontent."}";
		$somecontent = $somecontent."";
		$somecontent = $somecontent."#contactform li {";
		$somecontent = $somecontent."   list-style:none; ";
		$somecontent = $somecontent."   padding-bottom:20px;";
		$somecontent = $somecontent."}";
		$somecontent = $somecontent."";
		$somecontent = $somecontent."#contactform li .fieldbox {";
		$somecontent = $somecontent."   background:transparent url(/img/subfield.jpg) no-repeat top left; ";
		$somecontent = $somecontent."   float:left; ";
		$somecontent = $somecontent."   height:27px; ";
		$somecontent = $somecontent."   padding-left:5px;";
		$somecontent = $somecontent."}";
		$somecontent = $somecontent."";
		$somecontent = $somecontent."#contactform li .fieldbox select {";
		$somecontent = $somecontent."   background:transparent url(/img/subfield.jpg) no-repeat top right; ";
		$somecontent = $somecontent."   height:27px; ";
		$somecontent = $somecontent."   padding-top:1px;";
		$somecontent = $somecontent."   width:400px;";
		$somecontent = $somecontent."}";
		$somecontent = $somecontent."#contactform li .fieldbox input {";
		$somecontent = $somecontent."   background:transparent url(/img/subfield.jpg) no-repeat top right; ";
		$somecontent = $somecontent."   height:27px; ";
		$somecontent = $somecontent."   padding-top:1px;";
		$somecontent = $somecontent."   width:400px;";
		$somecontent = $somecontent."}";
		$somecontent = $somecontent."";
		$somecontent = $somecontent."#contactform li .fieldbox #contact {";
		$somecontent = $somecontent."   width:200px;";



		$somecontent = $somecontent."}";
		$somecontent = $somecontent."";
		$somecontent = $somecontent."#contactform li .msgbox {";
		$somecontent = $somecontent."   background:transparent url(/img/msgfield.jpg) no-repeat top left; ";
		$somecontent = $somecontent."   float:left; ";
		$somecontent = $somecontent."   height:110px; ";
		$somecontent = $somecontent."   padding-left:5px;";
		$somecontent = $somecontent."}";
		$somecontent = $somecontent."";
		$somecontent = $somecontent."#contactform li .msgbox textarea {";
		$somecontent = $somecontent."   background:transparent url(/img/msgfield.jpg) no-repeat top right; ";
		$somecontent = $somecontent."   height:110px;";
		$somecontent = $somecontent."   padding-top:5px;";
		$somecontent = $somecontent."   width:500px;     ";
		$somecontent = $somecontent."}";
		$somecontent = $somecontent."";
		$somecontent = $somecontent."#button {";
		$somecontent = $somecontent."   background:#acb4cb; color:#fff; ";
		$somecontent = $somecontent."   cursor:pointer;";
		$somecontent = $somecontent."   padding:5px 20px; ";
		$somecontent = $somecontent."   -moz-border-radius:4px;";
		$somecontent = $somecontent."   -webkit-border-radius:4px";
		$somecontent = $somecontent."}";
		$somecontent = $somecontent."</style>";
		$somecontent = $somecontent."</head>";
		$somecontent = $somecontent."";
		$somecontent = $somecontent."<body style=\"margin:0; background: #ededed\">";
		$somecontent = $somecontent."";
		$somecontent = $somecontent."";
		$somecontent = $somecontent."<div style=\"height:95px; background: #000000; \">";
		$somecontent = $somecontent."<div style=\"float: left; width:100%; margin-top:20px;\"><font style=\"margin-left:50px; color:white; font-family: 'Droid Sans', Verdana; font-size:50px;\">Web Control Panel Lite</font> </div>";
		$somecontent = $somecontent."";
		$somecontent = $somecontent."</div>";
		$somecontent = $somecontent."<div style=\"font-weight: bold; height:35px; background-color:blue; font-size:18px; padding-top:8px; padding-left:85px; color:white; font-family: 'Droid Sans', Verdana;\">New Domain Details</div>";
		$somecontent = $somecontent."";
		$somecontent = $somecontent."";

		$somecontent = $somecontent."";
		$somecontent = $somecontent."        <div id=\"wrap\">";
		$somecontent = $somecontent."";
		$somecontent = $somecontent."<b>Good day ".$FirstName." ".$Surname.",</b>";
		$somecontent = $somecontent."<p>";
		$somecontent = $somecontent."The domain name ".$DomainName." has been set up. Once the domain fee has been settled, the domain name will be registered and should be live within 24 hours.";
		$somecontent = $somecontent."";
		$somecontent = $somecontent."<p>";
		$somecontent = $somecontent."";
		$somecontent = $somecontent."<h3>Important Links</h3>";
		$somecontent = $somecontent."Web Control Panel: <a href=\"http://".$DomainName."/webcp\">http://".$DomainName."/webcp</a><br>";
		$somecontent = $somecontent."Web Mail: <a href=\"http://".$DomainName."/mail\">http://".$DomainName."/mail</a><br>";
		$somecontent = $somecontent."phpMyAdmin: <a href=\"http://".$DomainName."/mysql\">http://".$DomainName."/mysql</a>";
		$somecontent = $somecontent."";
		$somecontent = $somecontent."<p>";
		$somecontent = $somecontent."";
		$somecontent = $somecontent."<h3>Temporary Links</h3>";
		$somecontent = $somecontent."<b>NOTE: </b> The above links will only work once your domain name has been registered. Once registered, it can take up ";
		$somecontent = $somecontent."to 24 hours to be live. This is known as propogation.";
		$somecontent = $somecontent."<p>";
		$somecontent = $somecontent."You should be able to access the same links as above using the following URLs:";
		$somecontent = $somecontent."";
		$somecontent = $somecontent."<p>";
		$somecontent = $somecontent."";
		$somecontent = $somecontent."Web Control Panel: <a href=\"http://".$_SERVER["SERVER_NAME"]."/webcp\">http://".$_SERVER["SERVER_NAME"]."/webcp</a><br>";
		$somecontent = $somecontent."Web Mail: <a href=\"http://".$_SERVER["SERVER_NAME"]."/mail\">http://".$_SERVER["SERVER_NAME"]."/mail</a><br>";
		$somecontent = $somecontent."phpMyAdmin: <a href=\"http://".$_SERVER["SERVER_NAME"]."/mysql\">http://".$_SERVER["SERVER_NAME"]."/mysql</a>";
		$somecontent = $somecontent."";
		$somecontent = $somecontent."<p>";



		$somecontent = $somecontent."";
		$somecontent = $somecontent."Once your domain is live, you can use your own domain name to access these features.";
		$somecontent = $somecontent."";
		$somecontent = $somecontent."<p>";
		$somecontent = $somecontent."";
		$somecontent = $somecontent."Regards....";
		$somecontent = $somecontent."<br>";
		$somecontent = $somecontent."<a href=\"http://".$_SERVER["SERVER_NAME"]."\">".$_SERVER["SERVER_NAME"]."</a>";
		$somecontent = $somecontent."";
		$somecontent = $somecontent."</div>";
		$somecontent = $somecontent."";
		$somecontent = $somecontent."";
		$somecontent = $somecontent."</body>";
		$somecontent = $somecontent."</html>";


		$message = $somecontent;









		$AltMessage = "Good day ".$FirstName." ".$Surname."\r\n\r\n";
		$AltMessage = $AltMessage."The domain name ".$DomainName." has been set up. Once the domain fee has been settled, the domain name will be registered and should be live within 24 hours.\r\n\r\n";
		$AltMessage = $AltMessage."Important Links\r\n\r\n";
		$AltMessage = $AltMessage."Web Control Panel: http://".$DomainName."/webcp\r\n";
		$AltMessage = $AltMessage."Web Mail: http://".$DomainName."/mail\r\n";
		$AltMessage = $AltMessage."phpMyAdmin: http://".$DomainName."/mysql\r\n\r\n";


		$AltMessage = $AltMessage."Temporary Links\r\n\r\n";
		$AltMessage = $AltMessage."NOTE: The above links will only work once your domain name has been registered. Once registered, it can take up to 24 hours to be live. This is known as propogation.\r\n\r\n";

		$AltMessage = $AltMessage."You should be able to access the same links as above using the following URLs:\r\n\r\n";

		$AltMessage = $AltMessage."Web Control Panel: http://".$_SERVER["SERVER_NAME"]."/webcp\r\n";
		$AltMessage = $AltMessage."Web Mail: http://".$_SERVER["SERVER_NAME"]."/mail\r\n";
		$AltMessage = $AltMessage."phpMyAdmin: http://".$_SERVER["SERVER_NAME"]."/mysql\r\n\r\n";

		$AltMessage = $AltMessage."Once your domain is live, you can use your own domain name to access these features.\r\n\r\n";
		$AltMessage = $AltMessage."Regards....\r\n";
		$AltMessage = $AltMessage.$_SERVER["SERVER_NAME"];




		$MailFrom = $_SERVER["SERVER_NAME"];

		if(strstr($MailFrom, "http://"))
		{
			$MailFrom = substr($MailFrom, 7);
		}

		if(strstr($MailFrom, "www."))
		{
			$MailFrom = substr($MailFrom, 4);
		}


		$mail = new PHPMailer(true);

		$mail->IsSMTP();
		
		$mail->SMTPOptions = [
    'ssl' => [
        'verify_peer' => false,
        'verify_peer_name' => false,
        'allow_self_signed' => true,
    ]
];
		$mail->ClearAddresses();
		$mail->ClearAttachments();
		$mail->IsHTML(true);
		$mail->AddReplyTo("noreply@".$MailFrom, $_SERVER["SERVER_NAME"]);
		$mail->From = "noreply@".$MailFrom;
		$mail->FromName = $_SERVER["SERVER_NAME"];

		
		$mail->AddAddress($EmailAddress);


		if(strlen(trim($BCC)) > 0)
		{
			$mail->AddBCC($BCC);
		}

		$mail->Subject = "Web Hosting New Domain";
		$mail->Body = $message;
		$mail->AltBody = $AltMessage;
		$mail->WordWrap = 80;

		$mail->Send();


	}




	/**
	 * Gets the domain user name, given its id
	 * @param int 	$id			The domain's DB ID
	 * @param mixed $nonceArray The nonce for this call
	 */
	function getDomainUserName($id, $nonceArray) {

		//print debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS,2)[1]['function'];
		//exit();

		$oSimpleNonce = new SimpleNonce();
		$oUser = new User();

		if ( intVal($id) < 1 ) {
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Domain.php -> getDomainPath(); Domain ID invalid");
			throw new Exception("<p><b>Domain ID invalid in Domain::getDomainPath</b></p>");
		}

		if ( ! ( is_array($nonceArray) && !empty($nonceArray)) ) {
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Domain.php -> getDomainPath(); No nonce given");
			throw new Exception("<p><b>No nonce given in Domain::getDomainPath</b></p>");
		}

		$nonceMeta = [
			$oUser->Role,
			$oUser->getClientId(),
			$id
		];
		$nonceResult = $oSimpleNonce->VerifyNonce($nonceArray["Nonce"], "getDomainUserName", $nonceArray["TimeStamp"], $nonceMeta);

		if ( ! $nonceResult ) {
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Domain.php -> getDomainUserName(); Nonce failed");
			throw new Exception("<p><b>Nonce failed in Domain::getDomainUserName()</b></p>");
		}

		$random = random_int(1, 1000000);
		$oSimpleNonce = new SimpleNonce();
		$nonceArray = [	
			$oUser->Role,
			$oUser->ClientID,
			$id,
			$random
		];
		$nonce = $oSimpleNonce->GenerateNonce("getDomainInfo", $nonceArray);
		

		$infoArray = array();
		$this->GetDomainInfo($id, $random, $infoArray, $nonce);

		if(isset($infoArray["UserName"])) {
			return $infoArray["UserName"];
		}

		return "";
	}



	/**
	 * Gets the path to the public_html directory of a domain, given its id
	 * @param int 	$id			The domain's DB ID
	 * @param mixed $nonceArray The nonce for this call
	 */
	function getDomainPath($id, $nonceArray) {
		
		
		$oSimpleNonce = new SimpleNonce();
		$oUser = new User();

		if ( intVal($id) < 1 ) {
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Domain.php -> getDomainPath(); Domain ID invalid");
			throw new Exception("<p><b>Domain ID invalid in Domain::getDomainPath</b></p>");
		}

		if ( ! ( is_array($nonceArray) && !empty($nonceArray)) ) {
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Domain.php -> getDomainPath(); No nonce given");
			throw new Exception("<p><b>No nonce given in Domain::getDomainPath</b></p>");
		}

		$nonceMeta = [
			$oUser->Role,
			$oUser->getClientId(),
			$id
		];
		$nonceResult = $oSimpleNonce->VerifyNonce($nonceArray["Nonce"], "getDomainPath", $nonceArray["TimeStamp"], $nonceMeta);

		if ( ! $nonceResult ) {
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Domain.php -> getDomainPath(); Nonce failed");
			throw new Exception("<p><b>Nonce failed in Domain::getDomainPath()</b></p>");
		}


		$random = random_int(1, 1000000);
		$oSimpleNonce = new SimpleNonce();
		$nonceArray = [	
			$oUser->Role,
			$oUser->ClientID,
			$id,
			$random
		];
		$nonce = $oSimpleNonce->GenerateNonce("getDomainInfo", $nonceArray);
		

		$infoArray = array();
		$this->GetDomainInfo($id, $random, $infoArray, $nonce);

		if(isset($infoArray["Path"])) {
			return $infoArray["Path"];
		}

		return "";
	}

	function GetDomainType($id) {
		
		$random = random_int(1, 1000000);
		$oUser = new User();
		$oSimpleNonce = new SimpleNonce();
		$nonceArray = [	
			$oUser->Role,
			$oUser->ClientID,
			$id,
			$random
		];
		$nonce = $oSimpleNonce->GenerateNonce("getDomainInfo", $nonceArray);
		
		$InfoArray = array();
		$this->GetDomainInfo($id, $random, $InfoArray, $nonce);

		if(isset($InfoArray["DomainType"]))
		{
			return $InfoArray["DomainType"];
		}

		return "";
	}
	
	function DeleteDomain($ClientID, $DomainID, &$Error, $nonceArray)
	{

		if ( intVal($ClientID) < 1 ) {
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Domain.php -> DeleteDomain(); ClientID cannot be blank in Domain::DeleteDomain");
			throw new Exception("<p><b>ClientID cannot be blank in Domain::DeleteDomain</b><p>");
		}


		if ( intVal($DomainID) < 1 ) {
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Domain.php -> DeleteDomain(); DomainID cannot be blank in Domain::DeleteDomain");
			throw new Exception("<p><b>DomainID cannot be blank in Domain::DeleteDomain</b><p>");
		}

		if ( ! (is_array($nonceArray) && !empty($nonceArray) ) ) {
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Domain.php -> DeleteDomain(); Nonce not set");
			throw new Exception("<p><b>Nonce not set in Domain::DeleteDomain</b><p>");
		}
		
		$oUser = new User();
		$nonceMeta = [
			$oUser->Role,
			$oUser->ClientID,
			$ClientID,
			$DomainID
		];

		$oSimpleNonce = new SimpleNonce();
		$nonceResult = $oSimpleNonce->VerifyNonce($nonceArray["Nonce"], "deleteDomain", $nonceArray["TimeStamp"], $nonceMeta);

		if ( ! $nonceResult ) {
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Domain.php -> DeleteDomain(); Nonce failed");
			throw new Exception("<p><b>Nonce failed in Domain::DeleteDomain</b></p>");
		}




		$oLog = new Log();
		$oEmail = new Email();
		$oFTP = new FTP();
		$oFirewall = new Firewall();
		$oMySQL = new MySQL();

		$oLog->WriteLog("Debug", "At top of class.Domains.php -> DeleteDomain; ClientID: ".$ClientID."; DomainID: ".$DomainID);

		$random = random_int(1, 1000000);
		$nonceArray = [	
			$oUser->Role,
			$oUser->ClientID,
			$DomainID,
			$random
		];
		$nonce = $oSimpleNonce->GenerateNonce("getDomainInfo", $nonceArray);
		
		$InfoArray = array();
		$this->GetDomainInfo($DomainID, $random, $InfoArray, $nonce);

		if($ClientID != $InfoArray["ClientID"]) {
			$oLog->WriteLog("Debug", "Not same client id (request from: ".$ClientId."; belongs to: ".$InfoArray["ClientID"]."!");
			return 0;
		}


		$oEmail->DeleteDomainEmails($DomainID, $ClientID);
		$oEmail->DeleteDomainEmailForwarders($DomainID, $ClientID);
			
		$oEmail->DeleteEmailOptions('max_per_hour', $InfoArray["DomainName"]);
		$oEmail->DeleteEmailOptions('max_recipients', $InfoArray["DomainName"]);

		$oEmail->DeleteCatchAll($ClientID, 'admin', $InfoArray["DomainName"]);

		$oFTP->DeleteDomainFTP($DomainID, $ClientID);		
		$oMySQL->DeleteDomainMySQL($DomainID, $ClientID, $InfoArray["UserName"]);


		$oFirewall->DeleteModsecWhitelistHostName($InfoArray["DomainName"]);
		
		$x = $this->DeleteDomainRecursive($DomainID, $ClientID, $Error);

		// We delete recursively because we also need to send info to the nameservers 1 by 1, eg, parked domains.
		// now we want to make sure its all really gone...

		$this->DeleteDomainDescendants($DomainID, $ClientID);
		$this->deleteDomainSettings($DomainID);
	
		$this->DeleteAccountFile($DomainID);
		$this->MakeAccountFile($DomainID);
		return $x;
	}
	

	function GetSubDomainListRecursive($DomainID, $ClientID, &$SubDomainArray, &$ArrayCount)
	{
		
		try
		{
			$query = $this->DatabaseConnection->prepare("SELECT * FROM domains WHERE deleted = 0 AND domain_type = 'subdomain' AND client_id = :client_id AND parent_domain_id = :domain_id");

			$query->bindParam(":client_id", $ClientID);
			$query->bindParam(":domain_id", $DomainID);

			$query->execute();

			while($result = $query->fetch(PDO::FETCH_ASSOC))
			{	
				$SubDomainArray[$ArrayCount]["ID"] = $result["id"];
				$SubDomainArray[$ArrayCount++]["SubDomain"] = $result["fqdn"];
			}
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Domain.php -> GetSubDomainListRecursive(); Error = ".$e);
		}





		try
		{
			$query = $this->DatabaseConnection->prepare("SELECT id FROM domains WHERE deleted = 0 AND domain_type != 'primary' AND client_id = :client_id AND parent_domain_id = :domain_id");

			$query->bindParam(":client_id", $ClientID);
			$query->bindParam(":domain_id", $DomainID);

			$query->execute();

			while($result = $query->fetch(PDO::FETCH_ASSOC))
			{	
				$this->GetSubDomainListRecursive($result["id"], $ClientID, $SubDomainArray, $ArrayCount);
			}
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Domain.php -> GetSubDomainListRecursive(); Error = ".$e);
		}

	}

	function GetSubDomainList(&$DomainArray, &$ArrayCount, $DomainID, $ClientID, $Role)
	{
		$DomainArray = array();
		$ArrayCount = 0;
		$this->GetSubDomainListRecursive($DomainID, $ClientID, $DomainArray, $ArrayCount);
	}



	function DomainDeleted($DomainID)
	{
		
		if($DomainID == 0)
		{
			return 0;
		}

		try
		{
			$query = $this->DatabaseConnection->prepare("SELECT deleted FROM domains WHERE id = :domain_id");

			$query->bindParam(":domain_id", $DomainID);

			$query->execute();

			if($result = $query->fetch(PDO::FETCH_ASSOC))
			{	
				return $result["deleted"];
			}
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Domain.php -> DomainDeleted(); Error = ".$e);
		}

		return 0;

	}


        function GetDomainTree($DomainID, &$DomainArray, &$ArrayCount)
        {
			$DomainArray = array();
			$ArrayCount = 0;
		
			$oUser = new User();
			$oSimpleNonce = new SimpleNonce();
	
			$clientId = $oUser->getClientId();
			$role = $oUser->Role;
			
			try {
				$query = $this->DatabaseConnection->prepare("SELECT * FROM domains WHERE deleted = 0 AND (id = :domain_id OR ancestor_domain_id = :domain_id1) ORDER BY id ASC");
				
				$query->bindParam(":domain_id", $DomainID);
				$query->bindParam(":domain_id1", $DomainID);
				
				$query->execute();

				while($result = $query->fetch(PDO::FETCH_ASSOC)) {
					if( ($this->DomainDeleted($result["parent_domain_id"]) == 0) && ($this->DomainDeleted($result["ancestor_domain_id"]) == 0) ) {
						$DomainArray[$ArrayCount]["ID"] = $result["id"];
						$DomainArray[$ArrayCount]["DomainName"] = $result["fqdn"];
						$DomainArray[$ArrayCount]["Path"] = $result["path"];
						$DomainArray[$ArrayCount]["UserName"] = $result["UserName"];
						$DomainArray[$ArrayCount]["AdminUserName"] = $result["admin_username"];
						$DomainArray[$ArrayCount]["ClientID"] = $result["client_id"];
						$DomainArray[$ArrayCount]["PackageID"] = $result["package_id"];
						$DomainArray[$ArrayCount]["Suspended"] = $result["suspended"];
						$DomainArray[$ArrayCount]["ParentDomainID"] = $result["parent_domain_id"];

						$DomainArray[$ArrayCount]["ParentDomain"] = "";

						if ( intVal($result["parent_domain_id"]) > 0 ) {
							$random = random_int(1,100000);
							$nonceArray = [
								$role,
								$clientId,
								$result["parent_domain_id"],
								$random
							];
						
							$nonce = $oSimpleNonce->GenerateNonce("getDomainNameFromDomainID", $nonceArray);
							$DomainArray[$ArrayCount]["ParentDomain"] = $this->GetDomainNameFromDomainID($result["parent_domain_id"], $random, $nonce);
						}

					
						
					
						$DomainArray[$ArrayCount]["AncestorDomainID"] = $result["ancestor_domain_id"];
						
						$DomainArray[$ArrayCount]["AncestorDomain"] = "";

						if ( intVal($result["ancestor_domain_id"]) > 0 ) {
							$random = random_int(1,100000);
							$nonceArray = [
								$role,
								$clientId,
								$result["ancestor_domain_id"],
								$random
							];
						
							$nonce = $oSimpleNonce->GenerateNonce("getDomainNameFromDomainID", $nonceArray);
							$DomainArray[$ArrayCount]["AncestorDomain"] = $this->GetDomainNameFromDomainID($result["ancestor_domain_id"], $random, $nonce);
						}

						$DomainArray[$ArrayCount++]["type"] = $result["domain_type"];
					}
					
				}


			} catch(PDOException $e) {
				$oLog = new Log();
				$oLog->WriteLog("error", "/class.Domain.php -> GetDomainTree(); Error = ".$e);
			}


        }


	
        function GetDomainList(&$DomainArray, &$ArrayCount, $ClientID, $Role)
        {
                $DomainArray = array();
		$ArrayCount = 0;
	
		try
		{

			if(($Role == 'admin'))
			{
				$query = $this->DatabaseConnection->prepare("SELECT * FROM domains WHERE deleted = 0 ORDER BY fqdn ASC;");
			}
			else if($Role == "reseller")
			{
				$query = ";";
				$query = $this->DatabaseConnection->prepare("SELECT * FROM domains WHERE deleted = 0 AND client_id IN (SELECT client_id FROM reseller_relationships WHERE deleted = 0 AND reseller_id = :client_id UNION SELECT :client_id2 AS client_id)");
				$query->bindParam(":client_id", $ClientID);	
				$query->bindParam(":client_id2", $ClientID);				
			}
			else
			{
				$query = $this->DatabaseConnection->prepare("SELECT * FROM domains WHERE deleted = 0 AND client_id = :client_id ORDER BY fqdn ASC");
				$query->bindParam(":client_id", $ClientID);
			}


			$query->execute();
	
			while($result = $query->fetch(PDO::FETCH_ASSOC))
			{
				$DomainArray[$ArrayCount]["id"] = $result["id"];
				$DomainArray[$ArrayCount]["domain_name"] = $result["fqdn"];
				$DomainArray[$ArrayCount]["Path"] = $result["path"];
				$DomainArray[$ArrayCount]["AncestorDomainID"] = $result["ancestor_domain_id"];
				$DomainArray[$ArrayCount]["username"] = $result["UserName"];
				$DomainArray[$ArrayCount]["admin_username"] = $result["admin_username"];
				$DomainArray[$ArrayCount]["client_id"] = $result["client_id"];
				$DomainArray[$ArrayCount]["PackageID"] = $result["package_id"];
				$DomainArray[$ArrayCount]["Suspended"] = $result["suspended"];
				$DomainArray[$ArrayCount]["EmailRouting"] = $result["mail_type"];
				$DomainArray[$ArrayCount++]["type"] = $result["domain_type"];				
			}
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Domain.php -> GetDomainList(); Error = ".$e);
		}

        }



	function GetPackageID($DomainUserName)
	{

		try
		{
			$query = $this->DatabaseConnection->prepare("SELECT package_id FROM domains WHERE UserName = :domain_user_name");
			
			$query->bindParam(":domain_user_name", $DomainUserName);
			
			$query->execute();
	
			if($result = $query->fetch(PDO::FETCH_ASSOC))
			{
				return $result["package_id"];
			}
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Domain.php -> GetPackageID(); Error = ".$e);
		}
		
		return 0;
	}
	
	
	
	function UpdateDomainUser($DomainID, $ClientID)
	{

		$oUser = new User();

		$random = random_int(1, 1000000);
		$oSimpleNonce = new SimpleNonce();
		$nonceArray = [	
			$oUser->Role,
			$oUser->ClientID,
			$DomainID,
			$random
		];
		$nonce = $oSimpleNonce->GenerateNonce("getDomainInfo", $nonceArray);
		
		$InfoArray = array();
		$this->GetDomainInfo($DomainID, $random, $InfoArray, $nonce);

		

		$ClientUserName = $oUser->GetUserName($ClientID);

		$DomainUserName = $InfoArray["UserName"];

		$this->UpdateQuery("UPDATE domains SET client_id = ".$ClientID.", admin_username = '".$ClientUserName."' WHERE id = ".$DomainID);
		$this->UpdateQuery("UPDATE domains SET client_id = ".$ClientID.", admin_username = '".$ClientUserName."' WHERE ancestor_domain_id = ".$DomainID);
		$this->UpdateQuery("UPDATE email_forwarding set client_id = ".$ClientID." WHERE domain_id = ".$DomainID);
		$this->UpdateQuery("UPDATE ftpd SET client_id = ".$ClientID." WHERE domain_id = ".$DomainID);
		$this->UpdateQuery("UPDATE mysql SET client_id = ".$ClientID." WHERE domain_username = '".$DomainUserName."'");

		return 1;	
	}

	function UpdateQuery($Query)
	{
		try
		{
			$query = $this->DatabaseConnection->prepare($Query);
			$query->execute();
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Domain.php -> UpdateQuery(); Error = ".$e);
		}
		
		return 1;
	}

	function UpdateDomainPackage($DomainID, $PackageID)
	{

		try
		{
			$query = $this->DatabaseConnection->prepare("UPDATE domains SET package_id = :package_id WHERE id = :domain_id OR ancestor_domain_id = :domain_id2");
			
			$query->bindParam(":package_id", $PackageID);
			$query->bindParam(":domain_id", $DomainID);
			$query->bindParam(":domain_id2", $DomainID);
			
			$query->execute();
	
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Domain.php -> UpdateDomainPackage(); Error = ".$e);
		}
		
		return 1;
	}



	function GetDomainIDFromSubDomainID($SubDomainID)
	{
		
		try
		{
			$query = $this->DatabaseConnection->prepare("SELECT ancestor_domain_id FROM domains WHERE id = :sub_domain_id;");
			
			$query->bindParam(":sub_domain_id", $SubDomainID);
			
			$query->execute();
	
			if($result = $query->fetch(PDO::FETCH_ASSOC))
			{
				return $result["ancestor_domain_id"];
			}
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Domain.php -> GetDomainIDFromSubDomainID(); Error = ".$e);
		}


		return 0;
	}
	

	function GetDomainOwnerFromDomainName($DomainName, $nonceArray)
	{


		if ( $DomainName == "" ) {
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Domain.php -> GetDomainOwnerFromDomainName(); DomainName cannot be blank in Domain::GetDomainOwnerFromDomainName");
			throw new Exception("<p><b>DomainName cannot be blank in Domain::GetDomainOwnerFromDomainName</b><p>");
		}

		if ( ! (is_array($nonceArray) && !empty($nonceArray) ) ) {
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Domain.php -> GetDomainOwnerFromDomainName(); Nonce not set");
			throw new Exception("<p><b>Nonce not set in Domain::GetDomainOwnerFromDomainName</b><p>");
		}
		
		$oUser = new User();
		$ClientID = $oUser->getClientId();

		$nonceMeta = [
			$oUser->Role,
			$ClientID,
			$DomainName
		];

		$oSimpleNonce = new SimpleNonce();
		$nonceResult = $oSimpleNonce->VerifyNonce($nonceArray["Nonce"], "getDomainOwnerFromDomainName", $nonceArray["TimeStamp"], $nonceMeta);

		if ( ! $nonceResult ) {
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Domain.php -> GetDomainOwnerFromDomainName(); Nonce failed");
			throw new Exception("<p><b>Nonce failed in Domain::GetDomainOwnerFromDomainName</b></p>");
		}

		try {
			$query = $this->DatabaseConnection->prepare("SELECT client_id FROM domains WHERE fqdn = :domain_name AND deleted = 0");
			
			$query->bindParam(":domain_name", $DomainName);
			$query->execute();
	
			if($result = $query->fetch(PDO::FETCH_ASSOC)) {
				return $result["client_id"];
			}
	
		} catch(PDOException $e) {
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Domain.php -> GetDomainOwnerFromDomainName(); Error = ".$e);
		}

		return 0;
	}
	
	function GetDomainOwner($domain_id, $random, $nonceArray)
	{


		if ( intVal($domain_id) < 1 ) {
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Domain.php -> GetDomainOwner(); domain_id cannot be blank in Domain::GetDomainOwner");
			throw new Exception("<p><b>domain_id cannot be blank in Domain::GetDomainOwner</b><p>");
		}

		if ( $random == "" ) {
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Domain.php -> GetDomainOwner(); random cannot be blank in Domain::GetDomainOwner");
			throw new Exception("<p><b>random cannot be blank in Domain::GetDomainOwner</b><p>");
		}

		if ( ! (is_array($nonceArray) && !empty($nonceArray) ) ) {
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Domain.php -> GetDomainOwner(); Nonce not set");
			throw new Exception("<p><b>Nonce not set in Domain::GetDomainOwner</b><p>");
		}
		
		$oUser = new User();
		$ClientID = $oUser->getClientId();

		$nonceMeta = [
			$oUser->Role,
			$ClientID,
			$domain_id,
			$random
		];

		$oSimpleNonce = new SimpleNonce();
		$nonceResult = $oSimpleNonce->VerifyNonce($nonceArray["Nonce"], "getDomainOwner", $nonceArray["TimeStamp"], $nonceMeta);

		if ( ! $nonceResult ) {
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Domain.php -> GetDomainOwner(); Nonce failed");
			throw new Exception("<p><b>Nonce failed in Domain::GetDomainOwner</b></p>");
		}

		try {
			$query = $this->DatabaseConnection->prepare("SELECT client_id FROM domains WHERE id = :domain_id");
			
			$query->bindParam(":domain_id", $domain_id);
			$query->execute();
	
			if($result = $query->fetch(PDO::FETCH_ASSOC)) {
				return $result["client_id"];
			}
	
		} catch(PDOException $e) {
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Domain.php -> GetDomainOwner(); Error = ".$e);
		}

		return 0;
		
	}
	
	function UserNameExists($UserName)
	{
	
		try
		{
			$query = $this->DatabaseConnection->prepare("SELECT UserName FROM domains WHERE UserName = '".$UserName."' AND deleted = 0 AND domain_type = 'primary';");
			
			
			$query->execute();
	
			if($result = $query->fetch(PDO::FETCH_ASSOC))
			{
				return 1;
			}
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Domain.php -> UserNameExists(); Error = ".$e);
		}


		return 0;
	}

	function GetNextUID()
	{
		
		try
		{
			$query = $this->DatabaseConnection->prepare("SELECT Uid FROM domains ORDER BY Uid DESC LIMIT 1");
			$query->execute();
	
			if($result = $query->fetch(PDO::FETCH_ASSOC))
			{
				return $result["Uid"] + 1;
			}
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Domain.php -> GetNextUID(); Error = ".$e);
		}

		return 3000;
		
	}

	function AddSubDomain($SubDomain, $DomainID, $ClientID, &$Error = "")
	{

		$oSettings = new Settings();

		$oDNS = new DNS();
		$oUser = new User();
		
		$TempUser = new User();

		$AncestorID = $this->GetParentDomainIDRecursive($DomainID);

		$random = random_int(1, 1000000);
		$oSimpleNonce = new SimpleNonce();
		$nonceArray = [	
			$oUser->Role,
			$oUser->ClientID,
			$DomainID,
			$random
		];
		$nonce = $oSimpleNonce->GenerateNonce("getDomainInfo", $nonceArray);
		
		$DomainInfoArray = array();
		$this->GetDomainInfo($DomainID, $random, $DomainInfoArray, $nonce);

		$oLog = new Log();

		$lastInsertId = 0;
		
		try {
			$query = $this->DatabaseConnection->prepare("INSERT INTO domains VALUES (0, ".$ClientID.", '".$DomainInfoArray["UserName"]."', ".$DomainInfoArray["GroupID"].", ".$DomainInfoArray["UserID"].", '".$SubDomain.".".$DomainInfoArray["DomainName"]."', '".$DomainInfoArray["Path"]."/".strtolower($SubDomain)."', '".$DomainInfoArray["DomainOwner"]."', 1, 0, '".date('Y-m-d H:i:s')."', '".date('Y-m-d H:i:s')."', 'local', 'subdomain',  ".$DomainID.", ".$AncestorID.", ".$DomainInfoArray["PackageID"].", 0)");				
			$query->execute();
			
			$lastInsertId = $this->DatabaseConnection->lastInsertId();
	
		} catch(PDOException $e) {
	
			$oLog->WriteLog("error", "/class.Domain.php -> AddSubDomain(); Error = ".$e);
		}



		$this->DeleteDomainFile($AncestorID);
		$this->MakeDomainFile($AncestorID);
	
		$random = random_int(1,100000);
		$nonceArray = [
			$oUser->Role,
			$oUser->ClientID,
			$DomainID,
			$random
		];

		$nonce = $oSimpleNonce->GenerateNonce("getDomainNameFromDomainID", $nonceArray);
		$DomainName = $this->GetDomainNameFromDomainID($DomainID, $random, $nonce);
		
		$ParentDomainName = $DomainName;
		$ParentID = $this->GetParentDomainIDRecursive($DomainID, true);

		$random = random_int(1,100000);
		$nonceArray = [
			$oUser->Role,
			$oUser->ClientID,
			$ParentID,
			$random
		];

		$nonce = $oSimpleNonce->GenerateNonce("getDomainNameFromDomainID", $nonceArray);
		$ParentDomainName = $this->GetDomainNameFromDomainID($ParentID, $random, $nonce);


		$FQDN = $SubDomain.".".$DomainName;			
		$SubDomain = substr($FQDN, 0, strlen($FQDN) - (strlen($ParentDomainName) + 1));
		
		try	 {
			$ServerType = $oDNS->GetSetting("server_type");

			if($ServerType == "master") {
				$oDNS->AddSubDomain($SubDomain, $ParentDomainName, $oDNS->GetDomainIP($ParentDomainName), "");
				$Error = "DNS Added";
			} else if($ServerType == "slave") {

				$HostName = $oDNS->GetSetting("master_host_name");
				$IPAddress = $oDNS->GetSetting("master_ip_address");
				$Password = $oDNS->GetSetting("master_password");
				$PublicKey = $oDNS->GetSetting("master_public_key");

				$port = 8443;
			
				$result = $oDNS->createSubDomainInZone($SubDomain, $ParentDomainName, $IPAddress, $HostName, $port, $Password, $PublicKey);

				if ( $result == false ) {
					// try non-ssl
					$port = 8880;

					$result = $oDNS->createSubDomainInZone($SubDomain, $ParentDomainName, $IPAddress, $HostName, $port, $Password, $PublicKey);
				}

			} else {
				$Error = "<p><b>Domain created. Please ensure you update your DNS server</b>";
			}
		} catch(Exception $e) {
			$Error = "<p><b>The DNS could not be registered due to an error:<p>".$e->getMessage()."</b>";
		}

		return $lastInsertId;
		
	}



	function AddParkedDomain($ParkedDomainReference, $PrimaryDomain, $PackageID, $ClientID, $PrimaryDomainID, &$Error = "")
	{

		// The 3 lines below are an unelegant work around.
		// ParkedDomainReference, is as its name suggests, a pointer when used from the restore script	
		// For reasons I don't yet understand, they work fine locally, but when trying to pass is to 
		// the RPC below to register the DNS, it sends nothing. This work around creates a local copy.
		// The point of the blank characters is so that the variable is dereferenced as opposed to copying
		// the actual pointer
		$ParkedDomain = "";
		$ParkedDomain = $ParkedDomainReference." ";
		$ParkedDomain = trim($ParkedDomain);
		
		$oLog = new Log();

		$oSettings = new Settings();

		$oDNS = new DNS();

		$oLog->WriteLog("DEBUG", "ParkedDomain: '".$ParkedDomain."', PrimaryDomain: '".$PrimaryDomain."',PackageID: '".$PackageID."', ClientID: '".$ClientID."', PrimaryDomainID : '".$PrimaryDomainID."'");
		
	
		$IPV6Address = "";
		$DNSSEC = 0;
		
		if(file_exists($_SERVER["DOCUMENT_ROOT"]."/includes/ipv6.txt")) {
		    $IPV6Address = trim(file_get_contents($_SERVER["DOCUMENT_ROOT"]."/includes/ipv6.txt"));
		}


		$TempUser = new User();

		$UserRole = "";

		$TempUser->GetUserDetails($ClientID, $FirstName, $Surname, $EmailAddress, $AccountUsername, $UserRole);
		
		$AncestorID = $this->GetParentDomainIDRecursive($PrimaryDomainID);

		$random = random_int(1, 1000000);
		$oUser = new User();
		$oSimpleNonce = new SimpleNonce();
		$nonceArray = [	
			$oUser->Role,
			$oUser->ClientID,
			$PrimaryDomainID,
			$random
		];
		$nonce = $oSimpleNonce->GenerateNonce("getDomainInfo", $nonceArray);
		
		$DomainInfoArray = array();
		$this->GetDomainInfo($PrimaryDomainID, $random, $DomainInfoArray, $nonce);

		

		try {
			$query = $this->DatabaseConnection->prepare("INSERT INTO domains VALUES (0, :client_id, :user_name, :group_id, :user_id, :parked_domain, :path, :domain_owner, 1, 0, '".date('Y-m-d H:i:s')."', '".date('Y-m-d H:i:s')."', 'local', 'parked',  :primary_domain_id, :ancestor_id, :package_id, 0)");
			
			$query->bindParam(":client_id", $ClientID);
			$query->bindParam(":user_name", $DomainInfoArray["UserName"]);
			$query->bindParam(":group_id", $DomainInfoArray["GroupID"]);
			$query->bindParam(":user_id", $DomainInfoArray["UserID"]);
			$query->bindParam(":parked_domain", $ParkedDomain);
			$query->bindParam(":path", $DomainInfoArray["Path"]);
			$query->bindParam(":domain_owner", $DomainInfoArray["DomainOwner"]);
			$query->bindParam(":primary_domain_id", $PrimaryDomainID);
			$query->bindParam(":ancestor_id", $AncestorID);
			$query->bindParam(":package_id", $PackageID);
			
			$query->execute();
	
		} catch(PDOException $e) {
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Domain.php -> AddParkedDomain(); Error = ".$e);
		}
		
		$lastInsertId = $this->DatabaseConnection->lastInsertId();
		
		try {
			$ServerType = $oDNS->GetSetting("server_type");

			if($ServerType == "master") {
				$x = $oDNS->AddZone($ParkedDomain, $oDNS->GetDomainIP($DomainInfoArray["DomainName"]), "", "", $ClientID);	
				if($x < 1) {
					$oLog->WriteLog("DEBUG", "Error registering DNS, return code: ".$x);
					$Error = "<p><b>Domain DNS could not be registered, return code: ".$x.". Please contact support</b>";
				}
			} else if($ServerType == "slave") {

				$HostName = $oDNS->GetSetting("master_host_name");
				$IPAddress = $oDNS->GetSetting("master_ip_address");
				$Password = $oDNS->GetSetting("master_password");
				$PublicKey = $oDNS->GetSetting("master_public_key");

				$port = 8443;
			
				$result = $oDNS->createMasterZone($ParkedDomain, $IPAddress, $HostName, $port, $Password, $PublicKey);
				if ( $result == false ) {
					// try non-ssl
					$port = 8880;

					$result = $oDNS->createMasterZone($ParkedDomain, $IPAddress, $HostName, $port, $Password, $PublicKey);
				}


			} else {
				$Error = "<p><b>Domain created. Please ensure you update your DNS server</b>";
			}
		} catch(Exception $e) {
			$Error = "The DNS could not be registered due to an error:<p>".$e->getMessage();
			$oLog->WriteLog("DEBUG", $Error);
			$Error = "<p><b>".$Error."</b>";
		}

		
		$this->DeleteDomainFile($this->GetParentDomainIDRecursive($PrimaryDomainID));
		$this->MakeDomainFile($this->GetParentDomainIDRecursive($PrimaryDomainID));
	
		return $lastInsertId;
		
	}





	function AddDomain($DomainName, $DomainType, $PackageID, $ClientID, &$Error = "")
	{
		$IPV6Address = "";
		$DNSSEC = 0;

		$oLog = new Log();

		$oSettings = new Settings();
		$oDNS = new DNS();

		$oLog->WriteLog("DEBUG", "DomainName: '".$DomainName."', DomainType: '".$DomainType."', PackageID: '".$PackageID."', ClientID: '".$ClientID."'");


		if(file_exists($_SERVER["DOCUMENT_ROOT"]."/includes/ipv6.txt")) {
		    $IPV6Address = trim(file_get_contents($_SERVER["DOCUMENT_ROOT"]."/includes/ipv6.txt"));
		}
		
		$TempUser = new User();

		$Role = "";
		$TempUser->GetUserDetails($ClientID, $FirstName, $Surname, $EmailAddress, $AccountUsername, $Role);
		
		$NextUID = $this->GetNextUID();
		$UserName = $this->CreateUserName($DomainName);


		try
		{
			$query = $this->DatabaseConnection->prepare("INSERT INTO domains VALUES (0, :client_id, :user_name, :next_uid, :next_uid1, :domain_name, '/home/".$UserName."/home/".$UserName."/public_html', :account_user_name, 1, 0, '".date('Y-m-d H:i:s')."', '".date('Y-m-d H:i:s')."', 'local', 'primary', 0, 0, :package_id, 0)");
			
			$query->bindParam(":client_id", $ClientID);
			$query->bindParam(":user_name", $UserName);
			$query->bindParam(":next_uid", $NextUID);
			$query->bindParam(":next_uid1", $NextUID);
			$query->bindParam(":domain_name", $DomainName);
			$query->bindParam(":account_user_name", $AccountUsername);
			$query->bindParam(":package_id", $PackageID);
			
			$query->execute();
	
		} catch(PDOException $e) {
			$oLog->WriteLog("error", "/class.Domain.php -> AddDomain(); Error = ".$e);
		}
		
		$AddDomainID = $this->DatabaseConnection->lastInsertId();
		
		
		$this->SendNewDomainEmail($DomainName, $FirstName, $Surname, $EmailAddress);
	
		try {
			$ServerType = $oDNS->GetSetting("server_type");

			$oLog->WriteLog("Domains", "Server Type: ".$ServerType);

			if($ServerType == "master") {

				$dkimKey = "";
				if( file_exists("/etc/exim4/dkim.public.key") ) {
					$dkimKey = file_get_contents("/etc/exim4/dkim.public.key");

					$x = strpos($dkimKey, "-----BEGIN PUBLIC KEY-----");

					if( $x !== false) {
						$dkimKey = trim(substr($dkimKey, $x + strlen("-----BEGIN PUBLIC KEY-----")));
					}

					$x = strpos($dkimKey, "--");
						
					if( $x !== false) {
						$dkimKey = trim(substr($dkimKey, 0, $x));
					}

					$dkimKey = str_replace("\r\n", "", $dkimKey);
					$dkimKey = str_replace("\n", "", $dkimKey);
				
				}

				$x = $oDNS->AddZone($DomainName, $oDNS->GetDomainIP($DomainName), "", $dkimKey, $ClientID);	
				if($x < 1) {
					$oLog->WriteLog("DEBUG", "Error registering DNS, return code: ".$x);
					$Error = "<p><b>Domain DNS could not be registered, return code: ".$x.". Please contact support</b>";
				}
			} else if($ServerType == "slave") {
				$HostName = $oDNS->GetSetting("master_host_name");
				$IPAddress = $oDNS->GetSetting("master_ip_address");
				$Password = $oDNS->GetSetting("master_password");
				$PublicKey = $oDNS->GetSetting("master_public_key");

				$port = 8443;
			
				$result = $oDNS->createMasterZone($DomainName, $IPAddress, $HostName, $port, $Password, $PublicKey);
				if ( $result == false ) {
					// try non-ssl
					$port = 8880;

					$result = $oDNS->createMasterZone($DomainName, $IPAddress, $HostName, $port, $Password, $PublicKey);
				}


			}
			else
			{
				$Error = "<p><b>Domain created. Please ensure you update your DNS server</b>";
			}
		}
		catch(Exception $e)
		{
			$Error = "<p><b>The DNS could not be registered due to an error:<p>".$e->getMessage()."</b>";
			$oLog->WriteLog("DEBUG", $Error);
		}

		//exit();

		

		$oLog->WriteLog("DEBUG", "mysql_insert_id: ".$AddDomainID);

		$this->DeleteDomainFile($AddDomainID);
		$this->MakeDomainFile($AddDomainID);

		$oUser = new User();
		$oSimpleNonce = new SimpleNonce();

		$nonceArray = [
			$oUser->Role,
			$oUser->ClientID,
			$AddDomainID,
			"ssl_redirect",
			"enforce"
		];

		$nonce = $oSimpleNonce->GenerateNonce("saveDomainSetting", $nonceArray);
		$this->saveDomainSetting($AddDomainID, "ssl_redirect", "enforce", "", "", $nonce);

		$oEmail = new Email();
		$transactionalEmailSettings = $oEmail->getTransactionalEmailSettings();

		if ( isset($transactionalEmailSettings["hostname"]) && isset($transactionalEmailSettings["username"]) && $transactionalEmailSettings["username"] != "" && isset($transactionalEmailSettings["password"]) && $transactionalEmailSettings["password"]
!= "" && isset($transactionalEmailSettings["default"]) && $transactionalEmailSettings["default"]
== "checked") {
			$oEmail->saveTransactionalDomain($transactionalEmailSettings["servicename"], $AddDomainID);
		}

		return $AddDomainID;
		
	}


	function getDomainSettings($domainId)
 	{

		$oSimpleNonce = new SimpleNonce();
		$oUser = new User();

		$settingsArray = array();

		try {
			$query = $this->DatabaseConnection->prepare("SELECT * FROM domain_settings WHERE deleted = 0 AND domain_id = :domain_id");
			$query->bindParam(":domain_id", $domainId);
			$query->execute();
	
			while($result = $query->fetch(PDO::FETCH_ASSOC)) {
				$settingsArray[$result["setting_name"]]["value"] = $result["setting_value"];
				$settingsArray[$result["setting_name"]]["extra1"] = $result["extra1"];
				$settingsArray[$result["setting_name"]]["extra2"] = $result["extra2"];
			} 
		} catch(PDOException $e) {
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Domain.php -> getDomainSettings(); code: ".$e->GetCode()."; Error = ".$e);

			if ($e->GetCode() == "42S02") {

				$nonceArray = [
					$oUser->Role,
					$ClientID,
					"domain_settings"
				];
				
				$nonce = $oSimpleNonce->GenerateNonce("tableExists", $nonceArray);
				
 				if ($this->oDatabase->TableExists("domain_settings", $nonce) === false) {
                        
					$TableInfoArray[0]["name"] = "id";
					$TableInfoArray[0]["type"] = "int";
					$TableInfoArray[0]["key"] = "primary key auto_increment";
					$TableInfoArray[0]["default"] = "";

					$TableInfoArray[1]["name"] = "domain_id";
					$TableInfoArray[1]["type"] = "int";
					$TableInfoArray[1]["key"] = "";
					$TableInfoArray[1]["default"] = "";

					$TableInfoArray[2]["name"] = "setting_name";
					$TableInfoArray[2]["type"] = "tinytext";
					$TableInfoArray[2]["key"] = "";
					$TableInfoArray[2]["default"] = "";

					$TableInfoArray[3]["name"] = "setting_value";
					$TableInfoArray[3]["type"] = "text";
					$TableInfoArray[3]["key"] = "";
					$TableInfoArray[3]["default"] = "";

					$TableInfoArray[4]["name"] = "extra1";
					$TableInfoArray[4]["type"] = "text";
					$TableInfoArray[4]["key"] = "";
					$TableInfoArray[4]["default"] = "";

					$TableInfoArray[5]["name"] = "extra2";
					$TableInfoArray[5]["type"] = "text";
					$TableInfoArray[5]["key"] = "";
					$TableInfoArray[5]["default"] = "";

					$TableInfoArray[6]["name"] = "deleted";
					$TableInfoArray[6]["type"] = "int";
					$TableInfoArray[6]["key"] = "";
					$TableInfoArray[6]["default"] = "";


					$nonceArray = [
						$oUser->Role,
						$ClientID,
						$TableName
					];
					
					$nonce = $oSimpleNonce->GenerateNonce("createTableFromArray", $nonceArray);
					
					$this->oDatabase->CreateTableFromArray("domain_settings", $TableInfoArray);     
      			}
			}
		}			

		return $settingsArray;
	}


	function GetDomainInfo($id, $random, &$InfoArray, $nonceArray)
	{
		//print debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS,2)[1]['function'];
		
		if ( intVal($id) < 1 ) {
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Domain.php -> GetDomainInfo(); id cannot be blank in Domain::GetDomainInfo");
			throw new Exception("<p><b>id cannot be blank in Domain::GetDomainInfo</b><p>");
		}

		// This function requires a random string to be passed in for the simple nonce.
		// This is because this function may be called multiple times per thing we're doing (like adding ssh keys).
		// Calling the nonce function multiple times with teh same parameters is not allowed.. Random takes care of that!		
		if ( $random == "" ) {
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Domain.php -> GetDomainInfo(); random cannot be blank in Domain::GetDomainInfo");
			throw new Exception("<p><b>random cannot be blank in Domain::GetDomainInfo</b><p>");
		}


		if ( ! (is_array($nonceArray) && !empty($nonceArray) ) ) {
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Domain.php -> GetDomainInfo(); Nonce not set");
			throw new Exception("<p><b>Nonce not set in Domain::GetDomainInfo</b><p>");
		}
		
		$oUser = new User();
		$ClientID = $oUser->getClientId();

		$nonceMeta = [
			$oUser->Role,
			$ClientID,
			$id,
			$random
		];

		$oSimpleNonce = new SimpleNonce();
		$nonceResult = $oSimpleNonce->VerifyNonce($nonceArray["Nonce"], "getDomainInfo", $nonceArray["TimeStamp"], $nonceMeta);

		if ( ! $nonceResult ) {
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Domain.php -> GetDomainInfo(); Nonce failed");
			throw new Exception("<p><b>Nonce failed in Domain::GetDomainInfo</b></p>");
		}


		$InfoArray = array();

		try
		{
			$query = $this->DatabaseConnection->prepare("SELECT * FROM domains WHERE id = :id");
			$query->bindParam(":id", $id);
			$query->execute();
	
			if($result = $query->fetch(PDO::FETCH_ASSOC))
			{
				$InfoArray["UserName"] = $result["UserName"];
				$InfoArray["DomainName"] = $result["fqdn"];
				$InfoArray["Path"] = $result["path"];
				$InfoArray["ClientID"] = $result["client_id"];
				$InfoArray["GroupID"] = $result["Gid"];
				$InfoArray["UserID"] = $result["Uid"];
				$InfoArray["DomainOwner"] = $result["admin_username"];
				$InfoArray["DomainType"] = $result["domain_type"];
				$InfoArray["PrimaryDomainID"] = $result["parent_domain_id"];
				$InfoArray["AncestorDomainID"] = $result["ancestor_domain_id"];
				$InfoArray["Suspended"] = $result["suspended"];
				$InfoArray["Active"] = $result["active"];
				$InfoArray["PackageID"] = $result["package_id"];
			}

		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Domain.php -> GetDomainInfo(); Error = ".$e);
		}			

	
	}


	function GetDomainName($DomainUserName)
	{
		$UserUserName = "";
		$DomainName = "";

		try
		{
			$query = $this->DatabaseConnection->prepare("SELECT fqdn FROM domains WHERE active = 1 AND deleted = 0 AND domain_type = 'primary' AND  UserName = :domain_user_name");
			$query->bindParam(":domain_user_name", $DomainUserName);
			$query->execute();
	
			if($result = $query->fetch(PDO::FETCH_ASSOC))
			{
				return $result["fqdn"];
			}

		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Domain.php -> GetDomainName(); Error = ".$e);
		}			

		return "";

	}


	function GetDomainIDFromDomainName($DomainName)
	{
		try
		{
			$query = $this->DatabaseConnection->prepare("SELECT id FROM domains WHERE active = 1 AND deleted = 0 AND fqdn = :domain_name");
			$query->bindParam(":domain_name", $DomainName);
			$query->execute();
	
			if($result = $query->fetch(PDO::FETCH_ASSOC))
			{
				return $result["id"];
			}

		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Domain.php -> GetDomainIDFromDomainName(); Error = ".$e);
		}	

		return -1;

	}
	
	function GetDomainNameFromDomainID($DomainID, $random, $nonceArray)
	{
		if ( intVal($DomainID) < 1 ) {
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Domain.php -> GetDomainNameFromDomainID(); DomainID cannot be blank in Domain::GetDomainNameFromDomainID");
			throw new Exception("<p><b>domainId cannot be blank in Domain::GetDomainNameFromDomainID</b><p>");
		}


		if ( $random == "" ) {
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Domain.php -> GetDomainNameFromDomainID(); random cannot be blank in Domain::GetDomainNameFromDomainID");
			throw new Exception("<p><b>random cannot be blank in Domain::GetDomainNameFromDomainID</b><p>");
		}


		if ( ! (is_array($nonceArray) && !empty($nonceArray) ) ) {
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Domain.php -> GetDomainNameFromDomainID(); Nonce not set");
			throw new Exception("<p><b>Nonce not set in Domain::GetDomainNameFromDomainID</b><p>");
		}
		
		$oUser = new User();
		$ClientID = $oUser->getClientId();

		$nonceMeta = [
			$oUser->Role,
			$oUser->ClientID,
			$DomainID,
			$random
		];

		$oSimpleNonce = new SimpleNonce();
		$nonceResult = $oSimpleNonce->VerifyNonce($nonceArray["Nonce"], "getDomainNameFromDomainID", $nonceArray["TimeStamp"], $nonceMeta);

		if ( ! $nonceResult ) {
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Domain.php -> GetDomainNameFromDomainID(); Nonce failed");
			throw new Exception("<p><b>Nonce failed in Domain::GetDomainNameFromDomainID</b></p>");
		}

		//$UserUserName = "";
		//$DomainName = "";
		
		try {
			$query = $this->DatabaseConnection->prepare("SELECT fqdn FROM domains WHERE active = 1 AND deleted = 0 AND id = :domain_id");
			$query->bindParam(":domain_id", $DomainID);
			$query->execute();
	
			if($result = $query->fetch(PDO::FETCH_ASSOC))
			{
				return $result["fqdn"];
			}

		} catch(PDOException $e) {
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Domain.php -> GetDomainNameFromDomainID(); Error = ".$e);
		}	
		

		return "";

	}





	function DeleteFTPAccounts($FTPUserName)
	{
		

		try
		{
			$query = $this->DatabaseConnection->prepare("DELETE FROM ftpd WHERE User LIKE '".$FTPUserName."%'");
			$query->execute();


		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Domain.php -> DeleteFTPAccounts(); Error = ".$e);
		}	
		
		return 1;
	}





	function DeleteDomainRecursive($DomainID, $ClientID, &$Error)
	{
		$oLog = new Log();
	
		$oSettings = new Settings();
	
		$oFirewall = new Firewall();
		
		$oDNS = new DNS();

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
		
		$DomainInfoArray = array();
		$this->GetDomainInfo($DomainID, $random, $DomainInfoArray, $nonce);

		$PrimaryDomainID = $DomainInfoArray["PrimaryDomainID"];
		$DomainName = $DomainInfoArray["DomainName"];

		$oFirewall->DeleteModsecWhitelistHostName($DomainName);
		
		try
		{
			$query = $this->DatabaseConnection->prepare("UPDATE domains SET deleted = 1 WHERE id = :domain_id AND client_id = :client_id");
			$query->bindParam(":domain_id", $DomainID);
			$query->bindParam(":client_id", $ClientID);
			
			$query->execute();


		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Domain.php -> DeleteDomainRecursive(); Error = ".$e);
		}
		
		try
		{
                        $ServerType = $oDNS->GetSetting("server_type");

                        if($ServerType == "master")
                        {
                                $oDNS->DeleteZone($DomainName);
                        }
                        else if($ServerType == "slave")
                        {
                                $HostName = $oDNS->GetSetting("master_host_name");
                                $IPAddress = $oDNS->GetSetting("master_ip_address");
                                $Password = $oDNS->GetSetting("master_password");
                                $PublicKey = $oDNS->GetSetting("master_public_key");

                                $options = array(
					'uri' => $IPAddress,
					'location' => 'http://'.$HostName.':8880/api/dns/DNS.php',
					'trace' => 1
				);

                                $Message = json_encode(array("Password" => $Password, "DomainName" => $DomainName));

                                $EncryptedMessage = "";
                                openssl_public_encrypt($Message, $EncryptedMessage, $PublicKey);

                                $Message = base64_encode($EncryptedMessage);
                                try
                                {
                                        $client = new SoapClient(NULL, $options);
                                        $Result = $client->DeleteZoneForSlave($Message);

                                        if($Result < 1)
                                        {
                                                $oLog->WriteLog("DEBUG", "Error registering DNS, return code: ".$Result);
                                        }
                                }
                                catch (Exception $e)
                                {
                                }

                        }

		}
		catch(Exception $e)
		{
			$Error = $Error."<p>".$SubDomainName.".".$DomainName." - Could not delete DNS zone: <p>Error: ".$e->getMessage();
			$oLog->WriteLog("Error", "/includes/classes/class.Domain.php -> ".$Error);
		}
	
	
		try
		{
			$query = $this->DatabaseConnection->prepare("SELECT * FROM domains WHERE deleted = 0 AND parent_domain_id = :domain_id");
			$query->bindParam(":domain_id", $DomainID);
			$query->execute();
	
			while($result = $query->fetch(PDO::FETCH_ASSOC))
			{
				$this->DeleteDomainRecursive($result["id"], $ClientID, $Error);
			}

		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Domain.php -> GetDomainName(); Error = ".$e);
		}	


		return 1;
	}






	function DeleteDomainDescendants($DomainID, $ClientID)
	{
	
		try
		{
			$query = $this->DatabaseConnection->prepare("UPDATE domains SET deleted = 1 WHERE ancestor_domain_id = :domain_id AND client_id = :client_id");
			
			$query->bindParam(":domain_id", $DomainID);
			$query->bindParam(":client_id", $ClientID);
			
			$query->execute();
	

	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Domain.php -> DeleteDomainDescendants 1(); Error = ".$e);
		}
		
		
		try
		{
		
			$query = $this->DatabaseConnection->prepare("UPDATE domains SET deleted = 1 WHERE parent_domain_id = :domain_id AND client_id = :client_id");
			
			
			$query->bindParam(":domain_id", $DomainID);
			$query->bindParam(":client_id", $ClientID);
			
			
			$query->execute();
	
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Domain.php -> DeleteDomainDescendants 2(); Error = ".$e);
		}		
		
		
	}















	function DeleteSubDomainRecursive($SubDomainID, $ClientID, &$Error)
	{
		
		$oLog = new Log();
		$oUser = new User();
		$oFirewall = new Firewall();
		
		$oSettings = new Settings();
		
		$oDNS = new DNS();

		$random = random_int(1, 1000000);
		$oUser = new User();
		$oSimpleNonce = new SimpleNonce();
		$nonceArray = [	
			$oUser->Role,
			$oUser->ClientID,
			$SubDomainID,
			$random
		];
		$nonce = $oSimpleNonce->GenerateNonce("getDomainInfo", $nonceArray);
		
		$DomainInfoArray = array();
		$this->GetDomainInfo($SubDomainID, $random, $DomainInfoArray, $nonce);

				
		$random = random_int(1,100000);
		$nonceArray = [
			$oUser->Role,
			$oUser->getClientId(),
			$DomainInfoArray["PrimaryDomainID"],
			$random
		];

		$nonce = $oSimpleNonce->GenerateNonce("getDomainNameFromDomainID", $nonceArray);
		$ParentDomainName = $this->GetDomainNameFromDomainID($DomainInfoArray["PrimaryDomainID"], $random, $nonce);
		$FQDN = $DomainInfoArray["DomainName"];
		$SubDomain = substr($FQDN, 0, strlen($FQDN) - (strlen($ParentDomainName) + 1));

		$oLog->WriteLog("DEBUG", "SubDomain: ".$SubDomain);
		$oLog->WriteLog("DEBUG", "ParentDomainName: ".$ParentDomainName);
		$oLog->WriteLog("DEBUG", "FQDN: ".$FQDN);
		$oLog->WriteLog("DEBUG", "strlen(FQDN): ".strlen($FQDN));
		$oLog->WriteLog("DEBUG", "strlen(ParentDomainName) + 1: ".(strlen($ParentDomainName) + 1));
			
		
		$this->DeleteDomainFile($this->GetParentDomainIDRecursive($SubDomainID));
		$this->MakeDomainFile($this->GetParentDomainIDRecursive($SubDomainID));


	
		$oFirewall->DeleteModsecWhitelistHostName($FQDN);

		try
		{
			$query = $this->DatabaseConnection->prepare("UPDATE domains SET deleted = 1 WHERE domain_type = 'subdomain' AND id = :sub_domain_id AND client_id = :client_id");
			
			$query->bindParam(":sub_domain_id", $SubDomainID);
			$query->bindParam(":client_id", $ClientID);
			
			$query->execute();
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Domain.php -> DeleteSubDomainRecursive(); Error = ".$e);
		}
		
		
		try
		{


                        $ServerType = $oDNS->GetSetting("server_type");

                        if($ServerType == "master")
                        {
                                $oDNS->DeleteSubDomain($SubDomain, $ParentDomainName);

                        }
                        else if($ServerType == "slave")
                        {
                                $HostName = $oDNS->GetSetting("master_host_name");
                                $IPAddress = $oDNS->GetSetting("master_ip_address");
                                $Password = $oDNS->GetSetting("master_password");
                                $PublicKey = $oDNS->GetSetting("master_public_key");


                                $options = array(
                                'uri' => $IPAddress,
                                'location' => 'http://'.$HostName.':8880/api/dns/DNS.php',
                                'trace' => 1);

                                $Message = json_encode(array("Password" => $Password, "SubDomain" => $SubDomain, "ParentDomainName" => $ParentDomainName));

                                $EncryptedMessage = "";
                                openssl_public_encrypt($Message, $EncryptedMessage, $PublicKey);

                                $Message = base64_encode($EncryptedMessage);
                                try
                                {
                                        $client = new SoapClient(NULL, $options);
                                        $Result = $client->DeleteSubDomainForSlave($Message);

                                        if($Result < 1)
                                        {
                                                $oLog->WriteLog("DEBUG", "Error registering DNS, return code: ".$Result);
                                                $Error = "<p><b>Domain DNS could not be registered, return code: ".$Result.". Please contact support</b>";
                                        }
                                }
                                catch (Exception $e)
                                {
                                }

                        }
                        else
                        {
                                $Error = "<p><b>Domain created. Please ensure you update your DNS server</b>";
                        }

		}
		catch(Exception $e)
		{
			$Error = $Error."<p>".$SubDomainName.".".$DomainName." - Could not delete DNS zone: <p>Error: ".$e->getMessage();
			$oLog->WriteLog("ERROR", $Error);
		}
	
	
	
		try
		{
			$query = $this->DatabaseConnection->prepare("SELECT * FROM domains WHERE deleted = 0 AND parent_domain_id = :sub_domain_id");
			
			$query->bindParam(":sub_domain_id", $SubDomainID);
			
			$query->execute();
	
			while($result = $query->fetch(PDO::FETCH_ASSOC))
			{
				$this->DeleteSubDomainRecursive($result["id"], $ClientID, $Error);
			}
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Domain.php -> DeleteSubDomainRecursive(); Error = ".$e);
		}

		return 1;
	}



	function DeleteParkedDomain($ClientID, $ParkedDomainID, &$Error)
	{
		$oSettings = new Settings();
		$oDNS = new DNS();
		$oUser = new User();

		$random = random_int(1, 100000);
		$nonceArray = [
			$oUser->Role,
			$oUser->ClientID,
			$ParkedDomainID,
			$random
		];
		
		$oSimpleNonce = new SimpleNonce();
		
		$nonce = $oSimpleNonce->GenerateNonce("getDomainOwner", $nonceArray);
		
		if($ClientID != $this->GetDomainOwner($ParkedDomainID, $random, $nonce)) {
			return 0;
		}

		$random = random_int(1, 1000000);
		$oUser = new User();
		$oSimpleNonce = new SimpleNonce();
		$nonceArray = [	
			$oUser->Role,
			$oUser->ClientID,
			$ParkedDomainID,
			$random
		];
		$nonce = $oSimpleNonce->GenerateNonce("getDomainInfo", $nonceArray);
		
		$DomainInfoArray = array();
		$this->GetDomainInfo($ParkedDomainID, $random, $DomainInfoArray, $nonce);

		$DomainName = $DomainInfoArray["DomainName"];
		$parentDomainId = $this->GetParentDomainIDRecursive($ParkedDomainID);


		try
		{
			$query = $this->DatabaseConnection->prepare("UPDATE domains SET deleted = 1 WHERE id = :parked_domain_id AND client_id = :client_id");
			
			$query->bindParam(":parked_domain_id", $ParkedDomainID);
			$query->bindParam(":client_id", $ClientID);
			
			$query->execute();
	
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Domain.php -> DeleteParkedDomain(); Error = ".$e);
		}
		
		
		try
		{
                        $ServerType = $oDNS->GetSetting("server_type");

                        if($ServerType == "master")
                        {
                                $oDNS->DeleteZone($DomainName);
                        }
                        else if($ServerType == "slave")
                        {
                                $HostName = $oDNS->GetSetting("master_host_name");
                                $IPAddress = $oDNS->GetSetting("master_ip_address");
                                $Password = $oDNS->GetSetting("master_password");
                                $PublicKey = $oDNS->GetSetting("master_public_key");

                                $options = array(
                                'uri' => $IPAddress,
                                'location' => 'http://'.$HostName.':8880/api/dns/DNS.php',
                                'trace' => 1);

                                $Message = json_encode(array("Password" => $Password, "DomainName" => $DomainName));

                                $EncryptedMessage = "";
                                openssl_public_encrypt($Message, $EncryptedMessage, $PublicKey);

                                $Message = base64_encode($EncryptedMessage);
                                try
                                {
                                        $client = new SoapClient(NULL, $options);
                                        $Result = $client->DeleteZoneForSlave($Message);

                                        if($Result < 1)
                                        {
                                                $oLog->WriteLog("DEBUG", "Error registering DNS, return code: ".$Result);
                                        }
                                }
                                catch (Exception $e)
                                {
                                }

                        }
                        else
                        {
                                $Error = "<p><b>Domain deleted. Please ensure you update your DNS server</b>";
                        }

		}
		catch(Exception $e)
		{
			$Error = "<p>Could not delete DNS zone: <p>Error: ".$e->getMessage();
		}
		
		$this->DeleteDomainFile($parentDomainId);
		$this->MakeDomainFile($parentDomainId);
	
		return 1;
	}

	function RecreateAllVhostFiles()
	{
		
		try
		{
			$query = $this->DatabaseConnection->prepare("SELECT id FROM domains WHERE domain_type = 'primary' AND deleted = 0;");
			
			
			$query->execute();
	
			if($result = $query->fetch(PDO::FETCH_ASSOC))
			{
				$this->DeleteDomainFile($result["id"]);
				$this->MakeDomainFile($result["id"]);
			}
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Domain.php -> RecreateAllVhostFiles(); Error = ".$e);
		}
		
	}

    
}

