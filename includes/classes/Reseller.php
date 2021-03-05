<?php
/*********************************************************************
*********************************************************************/
if(!isset($_SESSION)) 
{
     session_start();
}

include_once($_SERVER["DOCUMENT_ROOT"]."/vendor/autoload.php");

class Reseller
{
     	var $oDatabase = null;
     	var $DatabaseConnection = null;

     	function __construct()
     	{
          	$this->oDatabase = new Database();
          	$this->DatabaseConnection = $this->oDatabase->GetConnection();
     	}

        function deleteOldResellers()
        {
                try
                {
                        $query = $this->DatabaseConnection->prepare("UPDATE reseller_settings SET deleted = 1 WHERE reseller_id NOT IN (SELECT id FROM admin WHERE role = 'reseller' AND deleted = 0)");
                        $query->execute();
                }
                catch(PDOException $e)
                {
                        $oLog = new Log();
                        $oLog->WriteLog("error", "/class.Reseller.php -> deleteOldResellers(); Error = ".$e);
                }
        }

	
	function GetResellerSettings($ResellerID, &$Array, &$ArrayCount, $UserID, $Role)
	{
          	$Array = array();
		$ArrayCount = 0;

		if($Role != "admin")
		{
			return;
		}

          	try
          	{
               		$query = $this->DatabaseConnection->prepare("SELECT * FROM reseller_settings WHERE deleted = 0 AND reseller_id = :reseller_id;");
			$query->bindParam(":reseller_id", $ResellerID);
			$query->execute();

               		while($result = $query->fetch(PDO::FETCH_ASSOC))
               		{
                    		$Array[$result["setting"]] = $result["value"];
               		}

          	}
          	catch(PDOException $e)
          	{
               		$oLog = new Log();
               		$oLog->WriteLog("error", "/class.Reseller.php -> GetResellerSettings(); Error = ".$e);
          	}
     	}
	
	function GetResellerSetting($ResellerID, $SettingName)
	{
          	try
          	{
               		$query = $this->DatabaseConnection->prepare("SELECT * FROM reseller_settings WHERE deleted = 0 AND setting = :setting_name AND reseller_id = :reseller_id;");
			$query->bindParam(":reseller_id", $ResellerID);
			$query->bindParam(":setting_name", $SettingName);
			$query->execute();

               		if($result = $query->fetch(PDO::FETCH_ASSOC))
               		{
                    		return $result["value"];
               		}

          	}
          	catch(PDOException $e)
          	{
               		$oLog = new Log();
               		$oLog->WriteLog("error", "/class.Reseller.php -> GetResellerSetting(); Error = ".$e);
          	}

		return false;
     	}

	function GetAccountsCreatedCount($ResellerID)
	{
          	try
          	{
               		$query = $this->DatabaseConnection->prepare("SELECT COUNT(id) AS count FROM domains WHERE deleted = 0 AND domain_type = 'primary' AND client_id IN (SELECT client_id FROM reseller_relationships WHERE reseller_id = ".$ResellerID." AND deleted = 0 UNION SELECT ".$ResellerID." AS client_id);");

               		$query->execute();

               		if($result = $query->fetch(PDO::FETCH_ASSOC))
               		{
				return $result["count"];
               		}
          	}
          	catch(PDOException $e)
          	{
               		$oLog = new Log();
               		$oLog->WriteLog("error", "/class.Reseller.php -> GetAccountsCreatedCount(); Error = ".$e);
          	}

		return 0;
	}

	function GetAccountsLimit($ResellerID)
	{
          	try
          	{
               		$query = $this->DatabaseConnection->prepare("SELECT value FROM reseller_settings WHERE deleted = 0 AND setting = 'Accounts' AND reseller_id = ".$ResellerID.";");
               		$query->execute();

               		if($result = $query->fetch(PDO::FETCH_ASSOC))
               		{
				return $result["value"];
               		}
          	}
          	catch(PDOException $e)
          	{
               		$oLog = new Log();
               		$oLog->WriteLog("error", "/class.Reseller.php -> GetAccountsLimit(); Error = ".$e);
          	}

		return 0;
	}
	
	function GetTrafficAllocation($ResellerID)
	{
          	try
          	{
			if($ResellerID < 0) // Get all resellers
			{
               			$query = $this->DatabaseConnection->prepare("SELECT SUM(value) AS value FROM reseller_settings WHERE deleted = 0 AND setting = 'Traffic';");
			}
			else if($ResellerID > 0) // Get specific reseller
			{
               			$query = $this->DatabaseConnection->prepare("SELECT value FROM reseller_settings WHERE deleted = 0 AND setting = 'Traffic' AND reseller_id = ".$ResellerID.";");
			}

               		$query->execute();

               		if($result = $query->fetch(PDO::FETCH_ASSOC))
               		{
				return $result["value"];
               		}
          	}
          	catch(PDOException $e)
          	{
               		$oLog = new Log();
               		$oLog->WriteLog("error", "/class.Reseller.php -> GetTrafficAllocation(); Error = ".$e);
          	}

		return 0;
	}


	function GetDiskSpaceAllocation($ResellerID)
	{
          	try
          	{
			if($ResellerID < 0) // Get all resellers
			{
               			$query = $this->DatabaseConnection->prepare("SELECT SUM(value) AS value FROM reseller_settings WHERE deleted = 0 AND setting = 'DiskSpace';");
			}
			else if($ResellerID > 0) // Get specific reseller
			{
               			$query = $this->DatabaseConnection->prepare("SELECT value FROM reseller_settings WHERE deleted = 0 AND setting = 'DiskSpace' AND reseller_id = ".$ResellerID.";");
			}

               		$query->execute();

               		if($result = $query->fetch(PDO::FETCH_ASSOC))
               		{
				return $result["value"];
               		}
          	}
          	catch(PDOException $e)
          	{
               		$oLog = new Log();
               		$oLog->WriteLog("error", "/class.Reseller.php -> GetDiskSpaceAllocation(); Error = ".$e);
          	}

		return 0;
	}

        function GetResellerDetails($ResellerID, &$Array, &$ArrayCount, $Role, $UserID)
	{
          	$Array = array();
		$ArrayCount = 0;

		if($Role != "admin")
		{
			return;
		}

          	try
          	{
               		$query = $this->DatabaseConnection->prepare("SELECT id, username, first_name, surname FROM admin WHERE deleted = 0 AND id = ".$ResellerID.";");
               		$query->execute();

               		if($result = $query->fetch(PDO::FETCH_ASSOC))
               		{
                    		$Array["UserID"] = $result["id"];
				$Array["UserName"] = $result["username"];
				$Array["FirstName"] = $result["first_name"];
				$Array["Surname"] = $result["surname"];
				
				$ArrayCount = 4;

				$SettingsArray = array();
				$SettingsArrayCount = 0;
				$this->GetResellerSettings($Array["UserID"], $SettingsArray, $SettingsArrayCount, $UserID, $Role);
					
				foreach($SettingsArray as $key => $val)
				{
					$Array[$key] = $val;
					$ArrayCount++;
				}

               		}
          	}
          	catch(PDOException $e)
          	{
               		$oLog = new Log();
               		$oLog->WriteLog("error", "/class.Reseller.php -> GetReselleDetails(); Error = ".$e);
          	}
     	}

	function GetClientResellerID($ClientID, $random, $nonceArray)
	{

		$oSimpleNonce = new SimpleNonce();
		$oUser = new User();

		if ( intVal($ClientID) < 1 ) {
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Domain.php -> GetClientResellerID(); ClientID invalid");
			throw new Exception("<p><b>ClientID invalid in Reseller::GetClientResellerID</b></p>");
		}


		if ( $random == "" ) {
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Domain.php -> GetDomainOwner(); random cannot be blank in Domain::GetDomainOwner");
			throw new Exception("<p><b>random cannot be blank in Domain::GetDomainOwner</b><p>");
		}


		if ( ! ( is_array($nonceArray) && !empty($nonceArray)) ) {
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Reseller.php -> GetClientResellerID(); No nonce given");
			throw new Exception("<p><b>No nonce given in Reseller::getGetClientResellerID</b></p>");
		}

		$nonceMeta = [
			$oUser->Role,
			$oUser->getClientId(),
			$ClientID,
			$random
		];
		
		$nonceResult = $oSimpleNonce->VerifyNonce($nonceArray["Nonce"], "getClientResellerID", $nonceArray["TimeStamp"], $nonceMeta);

		if ( ! $nonceResult ) {
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Reseller.php -> GetClientResellerID(); Nonce failed");
			throw new Exception("<p><b>Nonce failed in Reseller::GetClientResellerID()</b></p>");
		}

		try {
			$query = $this->DatabaseConnection->prepare("SELECT reseller_id FROM reseller_relationships WHERE deleted = 0 AND client_id = :client_id;");
			$query->bindParam(":client_id", $ClientID);
			$query->execute();

			if($result = $query->fetch(PDO::FETCH_ASSOC)) {
				return $result["reseller_id"];
			}
		} catch(PDOException $e) {
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Reseller.php -> GetClientResellerID(); Error = ".$e);
		}

		return 0;
	}


	function GetDomainResellerID($DomainName)
	{
          	try
          	{
               		$query = $this->DatabaseConnection->prepare("SELECT reseller_id FROM reseller_relationships WHERE deleted = 0 AND client_id = (SELECT client_id FROM domains where fqdn = :domain_name AND deleted = 0);");
			$query->bindParam(":domain_name", $DomainName);
               		$query->execute();

               		if($result = $query->fetch(PDO::FETCH_ASSOC))
               		{
				return $result["reseller_id"];
               		}
          	}
          	catch(PDOException $e)
          	{
               		$oLog = new Log();
               		$oLog->WriteLog("error", "/class.Reseller.php -> GetDomainResellerID(); Error = ".$e);
          	}

		return 0;
	}



	function GetResellerList(&$Array, &$ArrayCount, $UserID, $Role)
	{
          	$Array = array();
		$ArrayCount = 0;

		if($Role != "admin")
		{
			return;
		}

          	try
          	{
               		$query = $this->DatabaseConnection->prepare("SELECT id, username, first_name, surname FROM admin WHERE deleted = 0 AND role = 'reseller';");
               		$query->execute();

               		while($result = $query->fetch(PDO::FETCH_ASSOC))
               		{
                    		$Array[$ArrayCount]["UserID"] = $result["id"];
				$Array[$ArrayCount]["UserName"] = $result["username"];
				$Array[$ArrayCount]["FirstName"] = $result["first_name"];
				$Array[$ArrayCount]["Surname"] = $result["surname"];
				
				$SettingsArray = array();
				$SettingsArrayCount = 0;
				$this->GetResellerSettings($Array[$ArrayCount]["UserID"], $SettingsArray, $SettingsArrayCount, $UserID, $Role);
					
				foreach($SettingsArray as $key => $val)
				{
					$Array[$ArrayCount][$key] = $val;
				}

				$ArrayCount++;
               		}
          	}
          	catch(PDOException $e)
          	{
               		$oLog = new Log();
               		$oLog->WriteLog("error", "/class.Reseller.php -> GetResellerList(); Error = ".$e);
          	}
     	}




	function RemoveClientFromResellers($ClientID)
     	{
          	try
          	{
               		$query = $this->DatabaseConnection->prepare("DELETE FROM reseller_relationships WHERE client_id = :client_id;");
			$query->bindParam(":client_id", $ClientID);
               		$query->execute();
          	}
          	catch(PDOException $e)
          	{
               		$oLog = new Log();
               		$oLog->WriteLog("error", "/class.Reseller.php -> RemoveClientFromResellers(); Error = ".$e);
        	}
	}
     
 
	
     	function AssignClientToReseller($ResellerID, $ClientID)
     	{
          	try
          	{
			$this->RemoveClientFromResellers($ClientID);
               		$query = $this->DatabaseConnection->prepare("INSERT INTO reseller_relationships values (0, :reseller_id, :client_id, 0);");
			$query->bindParam(":reseller_id", $ResellerID);
			$query->bindParam(":client_id", $ClientID);
               		$query->execute();
			return 1;
          	}
          	catch(PDOException $e)
          	{
               		$oLog = new Log();
               		$oLog->WriteLog("error", "/class.Reseller.php -> AssignClientToReseller(); Error = ".$e);
        	}
		return 0;
	}
    
	function __DeleteResellerSettings($ResellerID, $Role, $UserID)
     	{

		if($Role != "admin")
		{
			return;
		}

          	try
          	{
               		$query = $this->DatabaseConnection->prepare("DELETE FROM reseller_settings WHERE reseller_id = :reseller_id;");
			$query->bindParam(":reseller_id", $ResellerID);
               		$query->execute();
          	}
          	catch(PDOException $e)
          	{
               		$oLog = new Log();
               		$oLog->WriteLog("error", "/class.Reseller.php -> __DeleteResellerSettings(); Error = ".$e);
        	}
	}


	function __InsertSetting($ResellerID, $SettingName, $SettingValue, $Extra1, $Extra2, $Role, $UserID)  
     	{
		if($Role != "admin")
		{
			return;
		}

          	try
          	{
				$query = $this->DatabaseConnection->prepare("INSERT INTO reseller_settings values (0, :reseller_id, :setting_name, :setting_value, :extra1, :extra2, 0);");
				$query->bindParam(":reseller_id", $ResellerID);
				$query->bindParam(":setting_name", $SettingName);
				$query->bindParam(":setting_value", $SettingValue);
				$query->bindParam(":extra1", $Extra1);
				$query->bindParam(":extra2", $Extra2);
				$query->execute();
          	}
          	catch(PDOException $e)
          	{
               		$oLog = new Log();
               		$oLog->WriteLog("error", "/class.Reseller.php -> InsertSetting(); Error = ".$e);
        	}
	}

	function EditReseller($ResellerID, $SettingsArray, $Role, $UserID)
     	{
		if($Role != "admin")
		{
			return 0;
		}

		$this->__DeleteResellerSettings($ResellerID, $Role, $UserID);

		foreach($SettingsArray as $key => $val)
		{
			$this->__InsertSetting($ResellerID, $key, $val, '', '', $Role, $UserID);
		}
	
		return 1;
	}
}
