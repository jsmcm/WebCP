<?php
/*********************************************************************
*********************************************************************/
if(!isset($_SESSION)) 
{
     session_start();
}

include_once(dirname(__FILE__)."/class.Log.php");
include_once(dirname(__FILE__)."/class.Database.php");


class Firewall
{
	var $oDatabase = null;
	var $DatabaseConnection = null;
	
	var $LastErrorDescription = "";
	
	function __construct() 
	{

		$this->oDatabase = new Database();
		$this->DatabaseConnection = $this->oDatabase->GetConnection();
				
		if(!file_exists($_SERVER["DOCUMENT_ROOT"]."/fail2ban/tmp"))
		{
			mkdir($_SERVER["DOCUMENT_ROOT"]."/fail2ban/tmp", 0755);
		}
	}
   
  

        function IPExists($IP, &$CountryCode, &$CountryName, &$ReverseDNS)
        {
		
		try
		{
			$query = $this->DatabaseConnection->prepare("SELECT id, reverse, country_code, country FROM csf WHERE ip = :ip");

			$query->bindParam(":ip", $IP);
			$query->execute();

			if($result = $query->fetch(PDO::FETCH_ASSOC))
			{
				$CountryCode = $result["country_code"];
				$CountryName = $result["country"];
				$ReverseDNS = $result["reverse"];
				return $result["id"];
			}

		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Firewall.php -> IPExists(); Error = ".$e);
		}

		
		return -1;
		
	}

 
        function GetBanList(&$BanArray, &$ArrayCount, $Role, $UserID)
        {
		
		$ArrayCount = 0;
                $BanArray = array();
	
		include_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.Reseller.php");
		$oReseller = new Reseller();
		if($Role != "admin")
		{
		        $FirewallControl = "";
		        if($Role == "reseller")
		        {
		                $FirewallControl = $oReseller->GetResellerSetting($UserID, "FirewallControl");
		        }
		        if($FirewallControl != "on")
		        {
		                return;
		        }
		}


                
		$ArrayCount = 0;
		
		try
		{
			$query = $this->DatabaseConnection->prepare("SELECT * FROM csf ORDER BY triggered ASC;");
			
			$query->execute();
	
			while($result = $query->fetch(PDO::FETCH_ASSOC))
			{
				$BanArray[$ArrayCount]["ID"] = $result["id"];
				$BanArray[$ArrayCount]["IP"] = $result["ip"];
				$BanArray[$ArrayCount]["Service"] = $result["triggered"];
				$BanArray[$ArrayCount]["Reverse"] = $result["reverse"];
				$BanArray[$ArrayCount]["Port"] = $result["port"];
				$BanArray[$ArrayCount]["Direction"] = $result["direction"];


				$BanArray[$ArrayCount]["BanTime"] = $result["ban_time"];
				$BanArray[$ArrayCount]["Timeout"] = $result["timeout"];
				$BanArray[$ArrayCount]["Message"] = $result["message"];
				$BanArray[$ArrayCount]["Logs"] = $result["logs"];


		
				$timeFirst  = strtotime($result["ban_time"]);
				$timeSecond = strtotime(date("Y-m-d H:i:s"));
				$differenceInSeconds = $timeSecond - $timeFirst;

				$TimeLeft = $result["timeout"] - $differenceInSeconds;

				$BanArray[$ArrayCount]["TimeLeft"] = $TimeLeft;

				if($result["type"] == "0")
				{
					$BanArray[$ArrayCount]["Type"] = "Temp";
				}
				else
				{
					$BanArray[$ArrayCount]["Type"] = "Perm";
				}

				
				if($BanArray[$ArrayCount]["Reverse"] == "")
				{
					//gethostbyaddr print "Looking up IP<p>";
					$BanArray[$ArrayCount]["Reverse"] = gethostbyaddr($result["ip"]);
					$this->EditInfo($BanArray[$ArrayCount]["ID"], $BanArray[$ArrayCount]["Reverse"], "reverse");
					
				}

				$BanArray[$ArrayCount]["CountryCode"] = $result["country_code"];

				$BanArray[$ArrayCount]["Country"] = $result["country"];
				
				if($BanArray[$ArrayCount]["CountryCode"] == "")
				{
					$options = array(
					'uri' => 'http://api.webcp.pw',
					'location' => 'http://api.webcp.pw/Country.php',
					'trace' => 1);
				
					$client = new SoapClient(NULL, $options);
					
					$CountryData = json_decode($client->GetCountryData($result["ip"]));
					
					$CountryCode = $CountryData->CountryCode;
					$CountryName = $CountryData->CountryName;
					

					$BanArray[$ArrayCount]["CountryCode"] = $CountryCode;
					$this->EditInfo($BanArray[$ArrayCount]["ID"], $CountryCode, "country_code");



					$BanArray[$ArrayCount]["Country"] = $CountryName;
					$this->EditInfo($BanArray[$ArrayCount]["ID"], $CountryName, "country");

				}
				
				$ArrayCount++;
			}
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Firewall.php -> GetBanList(); Error = ".$e);
		}

	

        }


	function ManualBan($IP)
	{

		$FileName = $_SERVER["DOCUMENT_ROOT"]."/fail2ban/tmp/add.ban";
		
		$fh = fopen($FileName, 'a') or die("can't open file");
		fwrite($fh, $IP.",604800\n");
		fclose($fh);

	}


	function ManualUnban($IP)
	{
	
		$FileName = $_SERVER["DOCUMENT_ROOT"]."/fail2ban/tmp/remove.ban";
		
		$fh = fopen($FileName, 'a') or die("can't open file");
		fwrite($fh, $IP."\n");
		fclose($fh);

	}

	

	function EditInfo($ID, $Info, $Field)
	{

		try
		{
			$query = $this->DatabaseConnection->prepare("UPDATE csf SET ".$Field." = :info WHERE id = :id;");
			
			$query->bindParam(":info", $Info);
			$query->bindParam(":id", $ID);
			
			$query->execute();
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.FireWall.php -> EditInfo(); Error = ".$e);
		}


		return 1;
		
	}

        function GetModsecWhiteListDetail($ModsecWhiteListID, &$ModsecID, &$HostName, &$URI, $Role, $UserID)
        {

                include_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.Reseller.php");
                $oReseller = new Reseller();
                if($Role != "admin")
                {
                        $FirewallControl = "";
                        if($Role == "reseller")
                        {
                                $FirewallControl = $oReseller->GetResellerSetting($UserID, "FirewallControl");
                        }
                        if($FirewallControl != "on")
                     	{
				return;
                        }
                }


		
		$ModsecID = "";	
		$HostName = "";	
		$URI = "";	


		try
		{
			$query = $this->DatabaseConnection->prepare("SELECT * FROM server_settings WHERE setting = 'modsec_whitelist' AND deleted = 0 AND id = :modsec_white_list_id;");
			
			$query->bindParam(":modsec_white_list_id", $ModsecWhiteListID);
			
			$query->execute();
	
			if($result = $query->fetch(PDO::FETCH_ASSOC))
			{
				$ModsecID = $result["value"];	
				$HostName = $result["extra1"];	
				$URI = $result["extra2"];	
			}
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Firewall.php -> GetModsecWhiteListDetail(); Error = ".$e);
		}
		
        }

	
	function GetModsecWhiteList(&$WhiteListArray, &$WhiteListArrayCount, $Role, $UserID)
        {
		$WhiteListArray = array();
		$WhiteListArrayCount = 0;

                include_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.Reseller.php");
                $oReseller = new Reseller();
                if($Role != "admin")
                {
                        $FirewallControl = "";
                        if($Role == "reseller")
                        {
                                $FirewallControl = $oReseller->GetResellerSetting($UserID, "FirewallControl");
                        }
                        if($FirewallControl != "on")
                        {
				return;
                        }
                }


		try
		{
			$query = $this->DatabaseConnection->prepare("SELECT * FROM server_settings WHERE setting = 'modsec_whitelist' AND deleted = 0;");
			
			$query->execute();
	
			while($result = $query->fetch(PDO::FETCH_ASSOC))
			{
				$WhiteListArray[$WhiteListArrayCount]["ModsecID"] = $result["value"];	
				$WhiteListArray[$WhiteListArrayCount]["ID"] = $result["id"];	
				$WhiteListArray[$WhiteListArrayCount]["HostName"] = $result["extra1"];	
				$WhiteListArray[$WhiteListArrayCount++]["URI"] = $result["extra2"];	
			}
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Firewall.php -> GetModsecWhiteList(); Error = ".$e);
		}

	}


        function ModsecWhitelistExists($ModsecID, $HostName, $URI)
        {
		
		try
		{
			$query = $this->DatabaseConnection->prepare("SELECT id FROM server_settings WHERE setting = 'modsec_whitelist' AND value = :modsec_id AND extra1 = :host_name AND extra2 = :uri AND deleted = 0");
			
			$query->bindParam(":modsec_id", $ModsecID);
			$query->bindParam(":host_name", $HostName);
			$query->bindParam(":uri", $URI);
			
			$query->execute();
	
			if($result = $query->fetch(PDO::FETCH_ASSOC))
			{
				return true;
			}
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Firewall.php -> ModsecWhitelistExists(); Error = ".$e);
		}		

		return false;
		
	}


	function DeleteModsecWhitelistHostName($FQDN)
	{
		try
		{
			$query = $this->DatabaseConnection->prepare("UPDATE server_settings SET deleted = 1 WHERE setting = 'modsec_whitelist' AND extra1 = :fqdn");
			$query->bindParam(":fqdn", $FQDN);
			$query->execute();
	
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Firewall.php -> DeleteModsecWhitelistHostName(); Error = ".$e);
		}
	}



	function DeleteModsecWhitelist($ID)
	{
		try
		{
			$query = $this->DatabaseConnection->prepare("UPDATE server_settings SET deleted = 1 WHERE setting = 'modsec_whitelist' AND id = :id");
			$query->bindParam(":id", $ID);
			$query->execute();
	
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Firewall.php -> DeleteModsecWhitelist(); Error = ".$e);
		}
		
		
	}
	

	function ModsecWhitelist($ModsecID, $HostName, $URI)
	{

		if($this->ModsecWhitelistExists($ModsecID, $HostName, $URI) == false)
		{

			require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.Domain.php");
			$oDomain = new Domain();

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




			// hacking attempts sometimes come through with invalid domain names...
			if( ($oDomain->DomainExists($HostName) > 0) || ($HostName == 'global') )
			{
				try
				{
					$query = $this->DatabaseConnection->prepare("INSERT INTO server_settings VALUES (0, 'modsec_whitelist', :modsec_id, :host_name, :uri, 0)");
					
					$query->bindParam(":modsec_id", $ModsecID);
					$query->bindParam(":host_name", $HostName);
					$query->bindParam(":uri", $URI);
					
					$query->execute();
			
					return $this->DatabaseConnection->lastInsertId();
			
				}
				catch(PDOException $e)
				{
					$oLog = new Log();
					$oLog->WriteLog("error", "/class.Firewall.php -> ModsecWhitelist(); Error = ".$e);
				}
			
			}
			else
			{
				return -2;
			}

		}
		else
		{
			return -1;
		}
	}
    
}


?>
