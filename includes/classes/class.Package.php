<?php
/*********************************************************************
*********************************************************************/
if(!isset($_SESSION)) 
{
     session_start();
}

include_once(dirname(__FILE__)."/class.Log.php");
include_once(dirname(__FILE__)."/class.Database.php");

class Package 
{

        var $oDatabase = null;
        var $DatabaseConnection = null;

        function __construct()
        {
                $this->oDatabase = new Database();
                $this->DatabaseConnection = $this->oDatabase->GetConnection();

	
		if(!file_exists($_SERVER["DOCUMENT_ROOT"]."/server/tmp"))
		{
			mkdir($_SERVER["DOCUMENT_ROOT"]."/server/tmp", 0755);
		}

                if( ! file_exists($_SERVER["DOCUMENT_ROOT"]."/nm/"))
                {
                        mkdir($_SERVER["DOCUMENT_ROOT"]."/nm/", 0755);
                }
   	}

	
        function RemovePackageOwnere($PackageID)
        {
                try
                {
                        $query = $this->DatabaseConnection->prepare("UPDATE packages SET user_id = 0 WHERE package_id = :package_id;");
                        $query->bindParam(":package_id", $PackageID);
                        $query->execute();
                }
                catch(PDOException $e)
                {
                        $oLog = new Log();
                        $oLog->WriteLog("error", "/class.Package.php -> RemovePackageOwnere(); Error = ".$e);
                }
        }



        function  ChangePackageOwner($ResellerID, $PackageID)
        {
                try
                {
                        $query = $this->DatabaseConnection->prepare("UPDATE packages SET user_id = :reseller_id WHERE package_id = :package_id;");
                        $query->bindParam(":reseller_id", $ResellerID);
                        $query->bindParam(":package_id", $PackageID);
                        $query->execute();
                        return 1;
                }
                catch(PDOException $e)
                {
                        $oLog = new Log();
                        $oLog->WriteLog("error", "/class.Package.php -> ChangePackageOwner(); Error = ".$e);
                }
                return 0;
        }


	function GetTotalDiskSpace()
	{
		if(!file_exists($_SERVER["DOCUMENT_ROOT"]."/server/tmp/diskspace.txt"))
		{
			// no file...
			return -1;
		}

		$LineIn = "";
		$fp = fopen($_SERVER["DOCUMENT_ROOT"]."/server/tmp/diskspace.txt", "r");
		
		$ReturnValue = -1;

		while(! feof($fp))
		{
			$LineIn = fgets($fp);
			$LineIn = trim(substr($LineIn, strpos($LineIn, " ") + 1));
			$LineIn = trim(substr($LineIn, 0, strpos($LineIn, " ")));
			if(is_numeric($LineIn))
			{
				$ReturnValue = floatval($LineIn);
				break;
			}
		}

		fclose($fp);

		return $ReturnValue;

 	}

        function GetPackageUsageStats(&$PackageStatsArray, &$ArrayCount, $ResellerID)
        {

                $PackageStatsArray = array();
                $ArrayCount = 0;

   
   		try
		{
			
			if($ResellerID == 0) // Get reseller less stats
			{
				$query = $this->DatabaseConnection->prepare("SELECT COUNT( package_id ) AS package_count, package_id FROM domains WHERE deleted =0 AND domain_type =  'primary' AND client_id NOT IN (SELECT client_id FROM reseller_relationships WHERE deleted = 0) GROUP BY package_id");
			}
			else if($ResellerID < 0) // get stats for ALL reseller associated packages
			{
				$query = $this->DatabaseConnection->prepare("SELECT COUNT( package_id ) AS package_count, package_id FROM domains WHERE deleted =0 AND domain_type =  'primary' AND client_id IN (SELECT client_id FROM reseller_relationships WHERE deleted = 0) GROUP BY package_id");
			}
			else // get stats for specific reseller
			{
				$query = $this->DatabaseConnection->prepare("SELECT COUNT( package_id ) AS package_count, package_id FROM domains WHERE deleted =0 AND domain_type =  'primary' AND client_id IN (SELECT client_id FROM reseller_relationships WHERE reseller_id = :reseller_id1 AND deleted = 0 UNION SELECT :reseller_id2 AS client_id) GROUP BY package_id");
				$query->bindParam(":reseller_id1", $ResellerID);
				$query->bindParam(":reseller_id2", $ResellerID);
			}

			$query->execute();
	
			while($result = $query->fetch(PDO::FETCH_ASSOC))
			{
				$PackageStatsArray[$ArrayCount]["PackageID"] =  $result["package_id"];
				$PackageStatsArray[$ArrayCount]["PackageCount"] = $result["package_count"];
				$PackageStatsArray[$ArrayCount]["Traffic"] = $this->GetPackageAllowance("Traffic", $result["package_id"]);
				$PackageStatsArray[$ArrayCount++]["DiskSpace"] = $this->GetPackageAllowance("DiskSpace", $result["package_id"]);
			}
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Package.php -> GetPackageUsageStats(); Error = ".$e);
		}	
		
        }


        function GetMySQLUsage($DomainUserName)
        {

		try
		{
			$query = $this->DatabaseConnection->prepare("SELECT COUNT(*) as count FROM mysql WHERE domain_username = :domain_user_name AND deleted = 0");
			$query->bindParam(":domain_user_name", $DomainUserName);
			$query->execute();
	
			if($result = $query->fetch(PDO::FETCH_ASSOC))
			{
				return $result["count"];
			}
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Package.php -> GetMySQLUsage(); Error = ".$e);
		}	


                return 0;
        }
	


        function GetFTPUsage($DomainID)
        {

		try
		{
			$query = $this->DatabaseConnection->prepare("SELECT COUNT(*) as count FROM ftpd WHERE domain_id = :domain_id AND deleted = 0");
			$query->bindParam(":domain_id", $DomainID);
			$query->execute();
	
			if($result = $query->fetch(PDO::FETCH_ASSOC))
			{
				return $result["count"];
			}
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Package.php -> GetFTPUsage(); Error = ".$e);
		}	


                return 0;
        }
	
	function GetEmailUsage($DomainID)
	{

		require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.Domain.php");
		$oDomain = new Domain();
		$DomainIDChain = $oDomain->GetDomainIDChain($DomainID);

		try
		{
			$query = $this->DatabaseConnection->prepare("SELECT COUNT(*) as count FROM mailboxes WHERE active = 1 AND domain_id IN (".$DomainIDChain.")");
			$query->execute();
	
			if($result = $query->fetch(PDO::FETCH_ASSOC))
			{
				return $result["count"];
			}
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Package.php -> GetFTPUsage(); Error = ".$e);
		}	
		
		$query = ";";
	

		return 0;	
	}



	function GetParkedDomainUsage($UserName)
	{


		try
		{
			$query = $this->DatabaseConnection->prepare("SELECT COUNT(*) as count FROM domains WHERE deleted = 0 AND username = :user_name AND domain_type = 'parked'");
			$query->bindParam(":user_name", $UserName);
			$query->execute();
	
			if($result = $query->fetch(PDO::FETCH_ASSOC))
			{
				return $result["count"];
			}
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Package.php -> GetParkedDomainUsage(); Error = ".$e);
		}	
		

		return 0;	
	}



	function GetSubDomainUsage($UserName)
	{


		try
		{
			$query = $this->DatabaseConnection->prepare("SELECT COUNT(*) as count FROM domains WHERE deleted = 0 AND username = :user_name AND domain_type = 'subdomain'");
			$query->bindParam(":user_name", $UserName);
			$query->execute();
	
			if($result = $query->fetch(PDO::FETCH_ASSOC))
			{
				return $result["count"];
			}
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Package.php -> GetSubDomainUsage(); Error = ".$e);
		}	
		

		return 0;	
	}


	function GetPackageAllowance($SettingName, $PackageID)
	{

		if($PackageID == "")
		{
			return 0;	
		}

		try
		{
			$query = $this->DatabaseConnection->prepare("SELECT value FROM package_options WHERE deleted = 0 AND setting = :setting AND package_id = :package_id");
			$query->bindParam(":setting", $SettingName);
			$query->bindParam(":package_id", $PackageID);
			$query->execute();
	
			if($result = $query->fetch(PDO::FETCH_ASSOC))
			{
				return floatval($result["value"]);
			}
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Package.php -> GetPackageAllowance(); Error = ".$e);
		}	
		
		return 0;	
	}
   

	function PackageExists($PackageName)
	{
		$PackageName = strtolower($PackageName);

		try
		{
			$query = $this->DatabaseConnection->prepare("SELECT package_id FROM packages WHERE deleted = 0 AND LCASE(package_name) = :package_name");
			$query->bindParam(":package_name", $PackageName);
			
			$query->execute();
	
			if($result = $query->fetch(PDO::FETCH_ASSOC))
			{
				return 1;
			}
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Package.php -> PackageExists(); Error = ".$e);
		}	


		return 0;	
	}
   

	function GetPackageID($PackageName)
	{

		$PackageName = trim($PackageName);
		
		try
		{
			$query = $this->DatabaseConnection->prepare("SELECT package_id FROM packages WHERE deleted = 0 AND package_name = :package_name");
			$query->bindParam(":package_name", $PackageName);
			
			$query->execute();
	
			if($result = $query->fetch(PDO::FETCH_ASSOC))
			{
				return $result["package_id"];
			}
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Package.php -> GetPackageID(); Error = ".$e);
		}			

		return -1;	
	}
   
	
	function GetPackageName($PackageID)
	{


		try
		{
			$query = $this->DatabaseConnection->prepare("SELECT package_name FROM packages WHERE deleted = 0 AND package_id = :package_id");
			$query->bindParam(":package_id", $PackageID);
			$query->execute();
	
			if($result = $query->fetch(PDO::FETCH_ASSOC))
			{
				return $result["package_name"];
			}
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Package.php -> GetPackageName(); Error = ".$e);
		}
		
		return "";	
	}
   
	function GetPackageOwnerID($PackageID)
	{
		try
		{
			$query = $this->DatabaseConnection->prepare("SELECT user_id FROM packages WHERE deleted = 0 AND package_id = :package_id");
			$query->bindParam(":package_id", $PackageID);
			
			$query->execute();
	
			if($result = $query->fetch(PDO::FETCH_ASSOC))
			{
				return $result["user_id"];
			}
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Package.php -> GetPackageOwnerID(); Error = ".$e);
		}			


		return -1;	
	}
   
	function GetPackageDetails($PackageID, &$PackageSettingValues, &$ArrayCount, $Role, $UserID)
	{
		$ArrayCount = -1;

		if($Role == "client")
		{
			// client's should never be here
			return;
		}

		if($Role == "reseller")
		{
			if($this->GetPackageOwnerID($PackageID) != $UserID)
			{
				return;
			}
		}

		$ArrayCount = 0;

		$PackageSettingValues = array( "PackageName" => $this->GetPackageName($PackageID));

		try
		{
			$query = $this->DatabaseConnection->prepare("SELECT * FROM package_options WHERE deleted = 0 AND package_id = :package_id");
			$query->bindParam(":package_id", $PackageID);
			
			$query->execute();
	
			while($result = $query->fetch(PDO::FETCH_ASSOC))
			{
				$PackageSettingValues[$result["setting"]] = floatval($result["value"]);
				$ArrayCount++;
			}
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Package.php -> GetPackageDetails(); Error = ".$e);
		}	
		
	
	}


	function CreateDiskQuotaScriptForDomain($DomainID, $DiskAllowance)
	{
		$ArrayCount = 0;

                $DiskSpace = $DiskAllowance / 1024; // Diskallowance is in b, Diskspace is in 1Kb blocks, so 5Mb = 5242880 / 1024 = 5120
                
		try
		{
			$query = $this->DatabaseConnection->prepare("SELECT domains.UserName as user_name FROM domains WHERE domains.deleted = 0 AND id = :domain_id AND domain_type = 'primary';");
			
			$query->bindParam(":domain_id", $DomainID);
			
			$query->execute();
	
			while($result = $query->fetch(PDO::FETCH_ASSOC))
			{
				$myfile = $_SERVER["DOCUMENT_ROOT"]."/nm/".$result["user_name"].".uquota";
				$fh = fopen($myfile, 'w') or die("can't open file");
				fwrite($fh, "setquota -u ".$result["user_name"]." ".$DiskSpace." ".$DiskSpace." 0 0 -a ext4");
				fclose($fh);
			}
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Package.php -> CreateDiskQuotaScriptForDomain(); Error = ".$e);
		}
	

	}

	function CreateDiskQuotaScripts(&$PackageID, $DiskAllowance)
	{
		//$PackageArray = array();
		$ArrayCount = 0;
		
                $DiskSpace = $DiskAllowance / 1024; // Diskallowance is in b, Diskspace is in 1Kb blocks, so 5Mb = 5242880 / 1024 = 5120
			
			
		try
		{
			$query = $this->DatabaseConnection->prepare("SELECT domains.UserName as user_nameFROM domains WHERE domains.deleted = 0 AND  domains.package_id = :package_id AND domain_type = 'primary';");

			
			$query->bindParam(":package_id", $PackageID);
			
			$query->execute();
	
			while($result = $query->fetch(PDO::FETCH_ASSOC))
			{
				$myfile = $_SERVER["DOCUMENT_ROOT"]."/nm/".$result["user_name"].".uquota";
				$fh = fopen($myfile, 'w') or die("can't open file");
				fwrite($fh, "setquota -u ".$result["user_name"]." ".$DiskSpace." ".$DiskSpace." 0 0 -a ext4");
				fclose($fh); 
			}
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Package.php -> CreateDiskQuotaScripts(); Error = ".$e);
		}
	
	}


	function GetPackageList(&$PackageArray, &$ArrayCount, $Role, $UserID)
	{
		$PackageArray = array();
		$ArrayCount = 0;
		
		
		try
		{
		
			if($Role == "admin")
			{
				$query = $this->DatabaseConnection->prepare("SELECT * FROM packages WHERE deleted = 0 ORDER BY package_name;");
			}
			else
			{
				$query = $this->DatabaseConnection->prepare("SELECT * FROM packages WHERE deleted = 0 AND user_id = :user_id ORDER BY package_name");
				
				$query->bindParam(":user_id", $UserID);
			}
			
			$query->execute();
	
			while($result = $query->fetch(PDO::FETCH_ASSOC))
			{
				$PackageArray[$ArrayCount]["package_id"] = $result["package_id"];
				$PackageArray[$ArrayCount]["package_name"] = $result["package_name"];
				$PackageArray[$ArrayCount]["active"] = $result["active"];
				$PackageArray[$ArrayCount++]["UserID"] = $result["user_id"];
			}
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Package.php -> GetPackageList(); Error = ".$e);
		}
				
	}
	
	
	function AddPackage($PackageName, $SettingsArray, $UserID)
	{

		try
		{
			$query = $this->DatabaseConnection->prepare("INSERT INTO packages VALUES (0, :package_name, :user_id, 1, 0)");
			
			$query->bindParam(":package_name", $PackageName);
			$query->bindParam(":user_id", $UserID);
			
			$query->execute();
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Package.php -> AddPackage(); Error = ".$e);
		}
		
	
		$x = $this->DatabaseConnection->lastInsertId();

		foreach($SettingsArray as $key=>$value)
		{
			//print "Key: ".$key."<br>";
			//print "Val: ".$value." - ".gettype($value)."<p>";

			$query = ";";
	
			try
			{
				$query = $this->DatabaseConnection->prepare("INSERT INTO package_options VALUES (0, :x, :key, :value, 0)");
				
				$query->bindParam(":x", $x);
				$query->bindParam(":key", $key);
				$query->bindParam(":value", $value);
				
				$query->execute();
		
			}
			catch(PDOException $e)
			{
				$oLog = new Log();
				$oLog->WriteLog("error", "/class.Package.php -> AddPackage 2(); Error = ".$e);
			}
		
		
		}
		
		//exit();
		return $x;
		
	}
	
	
	function EditPackage($PackageID, $PackageName, $SettingsArray)
	{

		require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.FTP.php");
		
		$oFTP = new FTP();

		
		try
		{
			$query = $this->DatabaseConnection->prepare("UPDATE packages SET package_name = :package_name WHERE package_id = :package_id");
			
			$query->bindParam(":package_name", $PackageName);
			$query->bindParam(":package_id", $PackageID);
			
			$query->execute();

		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Package.php -> EditPackage 1(); Error = ".$e);
		}
		
		foreach($SettingsArray as $key=>$value)
		{
			try
			{
				$query = $this->DatabaseConnection->prepare("UPDATE package_options SET value = :value WHERE setting = :key AND package_id = :package_id");
				
				$query->bindParam(":value", $value);
				$query->bindParam(":key", $key);
				$query->bindParam(":package_id", $PackageID);
				
				$query->execute();

			}
			catch(PDOException $e)
			{
				$oLog = new Log();
				$oLog->WriteLog("error", "/class.Package.php -> EditPackage 2(); Error = ".$e);
			}
			
			if($key == "DiskSpace")
			{
				$oFTP->UpdateFTPQuotas($PackageID, $value);
				$this->CreateDiskQuotaScripts($PackageID, $value);
			}
			
		}

		return 1;
		
	}	
	
	function DeletePackage($PackageID, $Role, $UserID)
	{
		try
		{
			
			if($Role == "admin")
			{
				$query = $this->DatabaseConnection->prepare("UPDATE packages SET deleted = 1 WHERE package_id = :package_id");
				$query->bindParam(":package_id", $PackageID);
			}
			else
			{
				$query = $this->DatabaseConnection->prepare("UPDATE packages SET deleted = 1 WHERE package_id = :package_id AND user_id = :user_id");
				$query->bindParam(":package_id", $PackageID);
				$query->bindParam(":user_id", $UserID);
			}
		
			$query->execute();
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Package.php -> DeletePackage(); Error = ".$e);
		}	
		
		return 1;	
	}


    
}

