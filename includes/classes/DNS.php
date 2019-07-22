<?php
/*********************************************************************
*********************************************************************/
if(!isset($_SESSION)) {
    session_start();
}

include_once("/var/www/html/webcp/vendor/autoload.php");

class DNS
{
	var $oDatabase = null;
	var $DatabaseConnection = null;

	function __construct()
	{
		$this->oDatabase = new Database();
		$this->DatabaseConnection = $this->oDatabase->GetConnection();
	}

	function FillIPArray()
	{
			
		$ipJson = `/usr/webcp/server_vars.sh`;
	
		$ipArray = array();
		if($ipJson != "") {
			$ipArray = json_decode($ipJson, true);
		}

		return $ipArray;
	}
		
	function ManageIPAddresses()
	{
	
		$oLog = new Log();
		$oDomain = new Domain();

		$IP = array();
		$IP = $this->FillIPArray();
	
		$ServerIPAddressCount = $this->GetServerIPAddressCount();
		$oLog->WriteLog("debug", "ServerIPAddressCount: ".$ServerIPAddressCount);
	
		if($ServerIPAddressCount == 0)
		{
			// There are no IPs in the server (might be new)...
			$oLog->WriteLog("debug", "count(\$IP): ".count($IP));
	
			if(count($IP["ipv4"]) > 0)
			{
				$oLog->WriteLog("debug", "AddIP(".$IP["ipv4"][0].", \"shared\"");
				$this->AddIP($IP["ipv4"][0], "ipv4", "shared");
	
				$oDomain->RecreateVHostFiles();
			}
			
			if(count($IP["ipv6"]) > 0)
			{
				$oLog->WriteLog("debug", "AddIP(".$IP["ipv6"][0].", \"shared\"");
				$this->AddIP($IP["ipv6"][0], "ipv6", "shared");
	
				$oDomain->RecreateVHostFiles();
			}
		}
		else
		{
			$oLog->WriteLog("debug", "count(\$IP['ipv4']): ".count($IP["ipv4"]));
			for($x = 0; $x < count($IP["ipv4"]); $x++)
			{
				$IPAddress = trim($IP["ipv4"][$x]);
	
				if($IPAddress != "")
				{
						$oLog->WriteLog("debug", "Checking if ".$IPAddress." exists...");
					if( ! $this->IPExists($IPAddress))
					{
						$oLog->WriteLog("debug", "Not, so adding it...");
						$this->AddIP($IPAddress, "ipv4",  "");
					}
					else
					{
						$oLog->WriteLog("debug", "Does exists...");
					}
				}
			}
	
			$oLog->WriteLog("debug", "count(\$IP['ipv6']): ".count($IP["ipv6"]));
			for($x = 0; $x < count($IP["ipv6"]); $x++)
			{
				$IPAddress = trim($IP["ipv6"][$x]);
	
				if($IPAddress != "")
				{
					$oLog->WriteLog("debug", "Checking if ".$IPAddress." exists...");
					if( ! $this->IPExists($IPAddress))
					{
						$oLog->WriteLog("debug", "Not, so adding it...");
						$this->AddIP($IPAddress, "ipv4", "");
					}
					else
					{
						$oLog->WriteLog("debug", "Does exists...");
					}
				}
			}

			if($this->GetSharedIP("ipv4") == "")
			{
				$oLog->WriteLog("debug", "Shared IPv4 is blanks..");

				// for some reason the shared ip does not exists, add it now
				$SharedIP = trim($this->GetUnusedIP("ipv4"));
	
				$oLog->WriteLog("debug", "Unused IP = ".$SharedIP);
	
				if($SharedIP == "") {
					// Problem!!! no IP available for sharing!
					// alert should come here...
				} else {
					$oLog->WriteLog("debug", "makeIpShared(".$SharedIP.")");
					$this->makeIpShared($SharedIP);
				}
			}
			
			if($this->GetSharedIP("ipv6") == "")
			{
				$oLog->WriteLog("debug", "Shared IPv6 is blanks..");

				// for some reason the shared ip does not exists, add it now
				$SharedIP = trim($this->GetUnusedIP("ipv6"));
	
				$oLog->WriteLog("debug", "Unused IP = ".$SharedIP);
	
				if($SharedIP == "") {
					$oLog->WriteLog("debug", "Problem!!! no IPv6 available for sharing!");
					// alert should come here...
				} else {
					$oLog->WriteLog("debug", "makeIpShared(".$SharedIP.")");
					$this->makeIpShared($SharedIP);
				}
			}
		}
	}


	function DeleteSetting($OptionName)
        {
                try
                {
                        $query = $this->DatabaseConnection->prepare("UPDATE dns_options SET deleted = 1 WHERE option_name = :option_name;");
                        $query->bindParam(":option_name", $OptionName);
                        $query->execute();

                }
                catch(PDOException $e)
                {
                        $oLog = new Log();
                        $oLog->WriteLog("error", "/class.DNS.php -> DeleteSetting(); Error = ".$e);
                }
        }

	function SaveSetting($OptionName, $OptionValue)
        {
        
		$this->DeleteSetting($OptionName);

	        try
                {
                        $query = $this->DatabaseConnection->prepare("INSERT INTO dns_options VALUES (0, :option_name, :option_value, '', '', 0);");
                        $query->bindParam(":option_name", $OptionName);
                        $query->bindParam(":option_value", $OptionValue);
                        $query->execute();

                }
                catch(PDOException $e)
                {
                        $oLog = new Log();
                        $oLog->WriteLog("error", "/class.DNS.php -> SaveSetting(); Error = ".$e);
                }
        }
	
	function DeleteSlave($ID)
	{
                try
                {
                        $query = $this->DatabaseConnection->prepare("UPDATE dns_slaves SET deleted = 1 WHERE id = :id;");
                        $query->bindParam(":id", $ID);
			$query->execute();

                }
                catch(PDOException $e)
                {
                        $oLog = new Log();
                        $oLog->WriteLog("error", "/class.DNS.php -> DeleteSlave(); Error = ".$e);
                }

	}
	
	function EditSlave($ID, $HostName, $IPAddress, $PublicKey, $Password)
	{
                try
                {

			
                        $query = $this->DatabaseConnection->prepare("UPDATE dns_slaves SET host_name = :host_name, ip_address = :ip_address, public_key = :public_key, password = :password WHERE id = :id;");
                        $query->bindParam(":id", $ID);
                        $query->bindParam(":host_name", $HostName);
                        $query->bindParam(":ip_address", $IPAddress);
                        $query->bindParam(":public_key", $PublicKey);
                        $query->bindParam(":password", $Password);
			$query->execute();

                }
                catch(PDOException $e)
                {
                        $oLog = new Log();
                        $oLog->WriteLog("error", "/class.DNS.php -> EditSlave(); Error = ".$e);
                }

	}


	function AddSlave($HostName, $IPAddress, $PublicKey, $Password)
	{
                try
                {

			
                        $query = $this->DatabaseConnection->prepare("INSERT INTO dns_slaves VALUES (0, :host_name, :ip_address, :public_key, :password, '', NULL, 0);");
                        $query->bindParam(":host_name", $HostName);
                        $query->bindParam(":ip_address", $IPAddress);
                        $query->bindParam(":public_key", $PublicKey);
                        $query->bindParam(":password", $Password);
			$query->execute();

                }
                catch(PDOException $e)
                {
                        $oLog = new Log();
                        $oLog->WriteLog("error", "/class.DNS.php -> AddSlave(); Error = ".$e);
                }

	}

        function SetSlaveStatus($ID, $Status)
        {
                try
                {
                        $query = $this->DatabaseConnection->prepare("UPDATE dns_slaves SET status = :status, status_date = '".date("Y-m-d H:i:s")."' WHERE id = :id;");
                        $query->bindParam(":status", $Status);
                        $query->bindParam(":id", $ID);
                        $query->execute();

                }
                catch(PDOException $e)
                {
                        $oLog = new Log();
                        $oLog->WriteLog("error", "/class.DNS.php -> SetSlaveStatus() Error = ".$e);
                }
        }
	
	function GetSlaveData($ID, &$HostName, &$IPAddress, &$Password, &$PublicKey)
	{

                try
                {
                        $query = $this->DatabaseConnection->prepare("SELECT * FROM dns_slaves WHERE id = :id AND deleted = 0");
			$query->bindParam(":id", $ID);

                        $query->execute();

                        if($result = $query->fetch(PDO::FETCH_ASSOC))
                        {
                                $HostName = $result["host_name"];
                                $IPAddress = $result["ip_address"];
                                $PublicKey = $result["public_key"];
                                $Password = $result["password"];
                                $Status = $result["status"];
                                $StatusDate = $result["status_date"];
				return $result["id"];
	
                        }
                }
                catch(PDOException $e)
                {
                        $oLog = new Log();
                        $oLog->WriteLog("error", "/class.DNS.php -> GetSlaveData(); Error = ".$e);
                }

		return -1;

        }

	function GetSlaveList(&$SlaveArray, &$SlaveArrayCount)
        {
		$SlaveArray = array();
		$SlaveArrayCount = 0;

                try
                {
                        $query = $this->DatabaseConnection->prepare("SELECT * FROM dns_slaves WHERE deleted = 0");
                        $query->execute();

                        while($result = $query->fetch(PDO::FETCH_ASSOC))
                        {
                                $SlaveArray[$SlaveArrayCount]["ID"] = $result["id"];
                                $SlaveArray[$SlaveArrayCount]["HostName"] = $result["host_name"];
                                $SlaveArray[$SlaveArrayCount]["IPAddress"] = $result["ip_address"];
                                $SlaveArray[$SlaveArrayCount]["PublicKey"] = $result["public_key"];
                                $SlaveArray[$SlaveArrayCount]["Password"] = $result["password"];
                                $SlaveArray[$SlaveArrayCount]["Status"] = $result["status"];
                                $SlaveArray[$SlaveArrayCount]["StatusDate"] = $result["status_date"];

				if($result["host_name"] != "")
				{
                                	$SlaveArray[$SlaveArrayCount++]["SlaveName"] = $result["host_name"];
				}
				else
				{
                                	$SlaveArray[$SlaveArrayCount++]["SlaveName"] = $result["ip_adress"];
				}
                        }
                }
                catch(PDOException $e)
                {
                        $oLog = new Log();
                        $oLog->WriteLog("error", "/class.DNS.php -> GetSlaveList(); Error = ".$e);
                }

        }

	function GetRRSList($SOAID, &$RRSArray, &$RRSArrayCount)
        {
		$RRSArray = array();
		$RRSArrayCount = 0;

                try
                {
                        $query = $this->DatabaseConnection->prepare("SELECT * FROM rrs WHERE deleted = 0 AND soa_id = :soa_id");
			$query->bindParam(":soa_id", $SOAID);
                        $query->execute();

                        while($result = $query->fetch(PDO::FETCH_ASSOC))
                        {
				$RRSArray[$RRSArrayCount]["ID"] = $result["id"];
				$RRSArray[$RRSArrayCount]["Domain"] = $result["domain"];
				$RRSArray[$RRSArrayCount]["TTL"] = $result["ttl"];
				$RRSArray[$RRSArrayCount]["Class"] = $result["class"];
				$RRSArray[$RRSArrayCount]["Type"] = $result["type"];
				$RRSArray[$RRSArrayCount]["Value1"] = stripslashes($result["value1"]);
				$RRSArray[$RRSArrayCount]["Value2"] = $result["value2"];
				$RRSArray[$RRSArrayCount]["Value3"] = $result["value3"];
				$RRSArray[$RRSArrayCount]["Value4"] = $result["value4"];
				$RRSArray[$RRSArrayCount]["Value5"] = $result["value5"];
				$RRSArray[$RRSArrayCount]["Value6"] = $result["value6"];
				$RRSArray[$RRSArrayCount]["Value7"] = $result["value7"];
				$RRSArray[$RRSArrayCount]["Value8"] = $result["value8"];
				$RRSArray[$RRSArrayCount]["Value9"] = $result["value9"];
				$RRSArray[$RRSArrayCount++]["Value10"] = $result["value10"];
                        }
                }
                catch(PDOException $e)
                {
                        $oLog = new Log();
                        $oLog->WriteLog("error", "/class.DNS.php -> GetRRSList(); Error = ".$e);
                }

        }


	function GetSetting($OptionName)
        {
                try
                {
                        $query = $this->DatabaseConnection->prepare("SELECT * FROM dns_options WHERE deleted = 0 AND option_name = :option_name;");
                        $query->bindParam(":option_name", $OptionName);
                        $query->execute();

                        if($result = $query->fetch(PDO::FETCH_ASSOC))
                        {
                                return $result["option_value"];
                        }
                }
                catch(PDOException $e)
                {
                        $oLog = new Log();
                        $oLog->WriteLog("error", "/class.DNS.php -> GetSetting(); Error = ".$e);
                }

                return "";
        }

	function GenerateKeyFiles()
	{
	
		if( ! file_exists($_SERVER["DOCUMENT_ROOT"]."/includes/protected") )
		{
			mkdir($_SERVER["DOCUMENT_ROOT"]."/includes/protected");
			chmod($_SERVER["DOCUMENT_ROOT"]."/includes/protected", 0755);


			$HTAccess = "<files key.private>\r\norder allow,deny\r\ndeny from all\r\n</files>\r\n\r\n";
			$HTAccess .= "<files key.public>\r\norder allow,deny\r\ndeny from all\r\n</files>\r\n\r\n";

			file_put_contents($_SERVER["DOCUMENT_ROOT"]."/includes/protected/.htaccess", $HTAccess);
		}

		if( ! file_exists($_SERVER["DOCUMENT_ROOT"]."/includes/protected/key.public"))
		{		
			if( file_exists($_SERVER["DOCUMENT_ROOT"]."/includes/protected/key.private"))
			{
				unlink($_SERVER["DOCUMENT_ROOT"]."/includes/protected/key.private");
			}
	
			$config = array(
				"digest_alg" => "sha512",
    				"private_key_bits" => 4096,
    				"private_key_type" => OPENSSL_KEYTYPE_RSA,
			);
   
			// Create the private and public key
			$res = openssl_pkey_new($config);

			// Extract the private key from $res to $privKey
			openssl_pkey_export($res, $PrivateKey);

			// Extract the public key from $res to $pubKey
			$pubKey = openssl_pkey_get_details($res);
			$PublicKey = $pubKey["key"];
		
			file_put_contents($_SERVER["DOCUMENT_ROOT"]."/includes/protected/key.private", $PrivateKey);
			file_put_contents($_SERVER["DOCUMENT_ROOT"]."/includes/protected/key.public", $PublicKey);
		}
	}

	function GetIPAddressList(&$IPAddressList, &$IPAddressCount)
	{

		$IPAddressList = array();
		$IPAddressCount = 0;

		try
		{
			$query = $this->DatabaseConnection->prepare("SELECT * FROM dns_options WHERE option_name = 'ip' AND deleted = 0");

			$query->execute();
	
			while($result = $query->fetch(PDO::FETCH_ASSOC))
			{
				$IPAddressList[$IPAddressCount]["ID"] = $result["id"];
				$IPAddressList[$IPAddressCount]["IPAddress"] = $result["option_value"];
				$IPAddressList[$IPAddressCount++]["Domain"] = $result["extra1"];

			}
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.DNS.php -> GetIPAddressList(); Error = ".$e);
		}


	}


	function GetServerIPAddressCount()
	{
	
		try
		{
			$query = $this->DatabaseConnection->prepare("SELECT COUNT(*) as count FROM dns_options WHERE deleted = 0");
			
			$query->bindParam(":email_address", $EmailAddress);
			
			$query->execute();
	
			if($result = $query->fetch(PDO::FETCH_ASSOC))
			{
				return $result["count"];
			}
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.DNS.php -> GetServerIPAddressCount(); Error = ".$e);
		}
		

		return 0;

	}

	function IPExists($IP)
	{

		try
		{
			$query = $this->DatabaseConnection->prepare("SELECT * FROM dns_options WHERE option_value = :ip AND deleted = 0");

			$query->bindParam(":ip", $IP);
			$query->execute();

			if($result = $query->fetch(PDO::FETCH_ASSOC))
			{
				return true;
			}

		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.DNS.php -> IPExists(); Error = ".$e);
		}

		return false;

	}
	
	function GetDomainIP($Domain)
	{
	   
		try
		{
			$query = $this->DatabaseConnection->prepare("SELECT option_value FROM dns_options WHERE option_name = 'ip' AND extra1 = :domain AND deleted = 0;");

			$query->bindParam(":domain", $Domain);
			$query->execute();

			if($result = $query->fetch(PDO::FETCH_ASSOC))
			{
				return $result["option_value"];
			}

		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.DNS.php -> GetDomainIP(); Error = ".$e);
		}

		return $this->GetSharedIP("ipv4");
	}

	function GetSharedIP($type="ipv4")
	{
		try
		{
			$query = $this->DatabaseConnection->prepare("SELECT option_value FROM dns_options WHERE option_name = 'ip' AND extra1 = 'shared' AND extra2 = :type AND deleted = 0");

			$query->bindParam(":type", $type);

			$query->execute();
	
			if($result = $query->fetch(PDO::FETCH_ASSOC))
			{
				return $result["option_value"];
			}
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.DNS.php -> GetSharedIP(); Error = ".$e);
		}
		

		return "";
	}

	function GetUnusedIP($type)
	{
	
		try
		{
			$query = $this->DatabaseConnection->prepare("SELECT option_value FROM dns_options WHERE extra1 = '' AND extra2 = :type AND deleted = 0 LIMIT 1");

			$query->bindParam(":type", $type);

			$query->execute();
	
			if($result = $query->fetch(PDO::FETCH_ASSOC))
			{
				return $result["option_value"];
			}
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.DNS.php -> GetUnusedIP(); Error = ".$e);
		}

		return "";
	}

	function GetIPValue($IP)
	{
		try
		{
			$query = $this->DatabaseConnection->prepare("SELECT extra1 FROM dns_options WHERE option_name = 'ip' AND option_value = :ip AND deleted = 0 LIMIT 1");
			
			$query->bindParam(":ip", $IP);
			
			$query->execute();
	
			if($result = $query->fetch(PDO::FETCH_ASSOC))
			{
				return $result["extra1"];
			}
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.DNS.php -> GetIPValue(); Error = ".$e);
		}

		return "";
	}

	function AddIP($IP, $type, $Domain)
	{
		try
		{
			$query = $this->DatabaseConnection->prepare("INSERT INTO dns_options VALUES (0, 'ip', :ip, :domain, :type, 0)");
			
			$query->bindParam(":ip", $IP);
			$query->bindParam(":domain", $Domain);
			$query->bindParam(":type", $type);
			
			$query->execute();
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.DNS.php -> AddIP(); Error = ".$e);
		}
	}

	function DeleteIPAddress($IP)
	{

		$SQL = ";";
		try
		{
			$query = $this->DatabaseConnection->prepare("DELETE FROM dns_options WHERE option_name = 'ip' AND option_value = :ip AND deleted = 0");
			
			$query->bindParam(":ip", $IP);
			
			$query->execute();
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.DNS.php -> DeleteIPAddress(); Error = ".$e);
		}
		
		return true;
	}

	function RemoveAssignment($IP, $Domain)
	{
		$oSettings = new Settings();


		$oDomain = new Domain();
		$DomainID = $oDomain->GetDomainIDFromDomainName($Domain);

	
		$oUser = new User();
		$ClientID = $oUser->getClientId();

		$ParkedDomainArray = array();
		$ParkedDomainCount = 0;

		$oDomain->GetParkedDomainList($ParkedDomainArray, $ParkedDomainCount, $DomainID, $ClientID, $oUser->Role);


                        $Hash = $oSettings->GetDNSHash();

                        if($Hash == "")
                        {
                                $Hash = md5(date("Y-m-d H:i:s").$EmailAddress);
                                $oSettings->SetDNSHash($Hash);
                      }


		try
		{
			$query = $this->DatabaseConnection->prepare("UPDATE dns_options SET extra1 = '' WHERE option_name = 'ip' AND option_value = :ip AND extra1 = :domain AND deleted = 0");
			
			$query->bindParam(":ip", $IP);
			$query->bindParam(":domain", $Domain);
			
			$query->execute();
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.DNS.php -> RemoveAssignment(); Error = ".$e);
		}
		
		
                $options = array(
			'uri' => 'http://'.$this->NameServerIP,
			'location' => 'http://'.$this->NameServerIP.'/api/hosts.php',
			'trace' => 1
		);

		$type = "ipv4";
		if ( strstr($IP, ":") ) {
			$type = "ipv6";
		}

               	$client = new SoapClient(NULL, $options);

               	$Result = $client->WebCP_UpdateARecords($Domain, $IP, $this->GetSharedIP($type), $Hash);
			
		for($x = 0; $x < $ParkedDomainCount; $x++)
		{
               		$Result = $client->WebCP_UpdateARecords($ParkedDomainArray[$x]["ParkedDomain"], $IP, $this->GetSharedIP($type), $Hash);
		}

		return true;
	}

	function makeIpShared($ip)
	{
		try
		{
			$query = $this->DatabaseConnection->prepare("UPDATE dns_options SET extra1 = 'shared' WHERE option_name = 'ip' AND option_value = :ip AND deleted = 0");
			
			$query->bindParam(":ip", $ip);
			
			$query->execute();
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.DNS.php -> makeIpShared(); Error = ".$e);
		}
		
		return true;
	}



	function AssignIP($IP, $Domain)
	{
		if($this->IPExists($IP) == false)
		{
			return false;
		}

		if($this->GetIPValue($IP) != "")
		{
			return false;
		}

		$oSettings = new Settings();

		$oDomain = new Domain();
		$DomainID = $oDomain->GetDomainIDFromDomainName($Domain);

	
		$oUser = new User();
		$ClientID = $oUser->getClientId();

		$ParkedDomainArray = array();
		$ParkedDomainCount = 0;
			

		$oDomain->GetParkedDomainList($ParkedDomainArray, $ParkedDomainCount, $DomainID, $ClientID, $oUser->Role);

		$CurrentIP = $this->GetDomainIP($Domain);

		try
		{
			$query = $this->DatabaseConnection->prepare("UPDATE dns_options SET extra1 = :domain WHERE option_name = 'ip' AND option_value = :ip AND deleted = 0");
			
			$query->bindParam(":domain", $Domain);
			$query->bindParam(":ip", $IP);
			
			$query->execute();
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.DNS.php -> AssignIP(); Error = ".$e);
		}
		
                $options = array(
			'uri' => 'http://'.$this->NameServerIP,
			'location' => 'http://'.$this->NameServerIP.'/api/hosts.php',
			'trace' => 1
		);


		$Hash = $oSettings->GetDNSHash();

		if($Hash == "")
		{
			$Hash = md5(date("Y-m-d H:i:s").$EmailAddress);
			$oSettings->SetDNSHash($Hash);
		}



               	$client = new SoapClient(NULL, $options);

               	$Result = $client->WebCP_UpdateARecords($Domain, $CurrentIP, $IP, $Hash);
			
		for($x = 0; $x < $ParkedDomainCount; $x++)
		{
               		$Result = $client->WebCP_UpdateARecords($ParkedDomainArray[$x]["ParkedDomain"], $CurrentIP, $IP, $Hash);
		}


		return true;
	}



	function ValidateDomainName($DomainName, $FQDN=true)
	{
	        $DomainName = strtolower($DomainName);
	
	        // check for double period
	        if(strstr($DomainName, ".."))
	        {
	                return -1;
	        }
	
	        // First char must be alphanum
	        if( (substr($DomainName, 0, 1) < 'a') || (substr($DomainName, 0, 1) > 'z') )
	        {
	                if( (substr($DomainName, 0, 1) < '0') || (substr($DomainName, 0, 1) > '9') )
	                {
	                        return -2;
	                }
	        }

	        for($x = 0; $x < strlen($DomainName); $x++)
	        {
	                if( (substr($DomainName, $x, 1) < 'a') || (substr($DomainName, $x, 1) > 'z') )
	                {
	                        if( (substr($DomainName, $x, 1) < '0') || (substr($DomainName, $x, 1) > '9') )
	                        {
	                                if( (substr($DomainName, $x, 1) != '-') && (substr($DomainName, $x, 1) != '.') && (substr($DomainName, $x, 1) != '_') )
	                                {
	                                        return -3;
	                                }
	                        }
	                }
	        }

	        if(strlen($DomainName) > 100)
	        {
	                return -4;
	        }

		if($FQDN == true)
		{
		        if(strlen($DomainName) < 4)
		        {
		                return -5;
		        }

		        // must contain at least 1 .
		        if(!strstr($DomainName, "."))
	        	{       
	         	       return -6;
		        }
		}

	
	        return 1;
	}


	function ValidateSubDomainName($DomainName)
	{
	        $DomainName = strtolower($DomainName);
	
	        // check for double period
	        if(strstr($DomainName, ".."))
	        {
	                return -1;
	        }
	
	        // First char must be alphanum
	        if( (substr($DomainName, 0, 1) < 'a') || (substr($DomainName, 0, 1) > 'z') )
	        {
	                if( (substr($DomainName, 0, 1) < '0') || (substr($DomainName, 0, 1) > '9') )
	                {
	                        return -2;
	                }
	        }

	        for($x = 0; $x < strlen($DomainName); $x++)
	        {
	                if( (substr($DomainName, $x, 1) < 'a') || (substr($DomainName, $x, 1) > 'z') )
	                {
	                        if( (substr($DomainName, $x, 1) < '0') || (substr($DomainName, $x, 1) > '9') )
	                        {
	                                if( (substr($DomainName, $x, 1) != '-')  )
	                                {
	                                        return -3;
	                                }
	                        }
	                }
	        }

	        if(strlen($DomainName) > 40)
	        {
	                return -4;
	        }


	        if(strstr($DomainName, "."))
	        {       
	                return -6;
	        }


	
	        return 1;
	}


	function HostBelongsToIP($DomainName, $IPAddress) 
	{
		
		try
		{
			$query = $this->DatabaseConnection->prepare("SELECT soa.id as id FROM soa, rrs WHERE soa.deleted = 0 AND rrs.deleted = 0 AND rrs.soa_id = soa.id AND rrs.type = 'A' AND soa.domain = :domain_name AND value1 = :ip_address;");
			
			$query->bindParam(":domain_name", $DomainName);
			$query->bindParam(":ip_address", $IPAddress);
			
			$query->execute();
	
			if($result = $query->fetch(PDO::FETCH_ASSOC))
			{
				return $result["id"];
			}
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.DNS.php -> HostBelongsToIP(); Error = ".$e);
		}
		

		
		return -1;
		
	}


 
	function DomainExists($DomainName) 
	{

		try
		{
			$query = $this->DatabaseConnection->prepare("SELECT id FROM soa WHERE domain = :domain_name AND deleted = 0;");
			
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
			$oLog->WriteLog("error", "/class.DNS.php -> DomainExists(); Error = ".$e);
		}
		
		return -1;
		
	}
	
   
	function SubDomainExists($SubDomain, $DomainID) 
	{
		
		try
		{
			$query = $this->DatabaseConnection->prepare("SELECT id FROM rrs WHERE soa_id = :domain_id AND type = 'A' AND domain = :sub_domain AND deleted = 0");
			
			$query->bindParam(":domain_id", $DomainID);
			$query->bindParam(":sub_domain", $SubDomain);
			
			$query->execute();
	
			if($result = $query->fetch(PDO::FETCH_ASSOC))
			{
				return $result["id"];
			}
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.DNS.php -> SubDomainExists(); Error = ".$e);
		}
		
		return -1;
		
	}
	
		
	function GetDomainID($DomainName) 
	{
		
		try
		{
			$query = $this->DatabaseConnection->prepare("SELECT id FROM soa WHERE domain = :domain_name AND deleted = 0;");
			
			$DomainName = $this->AddLastPeriod($DomainName);
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
			$oLog->WriteLog("error", "/class.DNS.php -> GetDomainID(); Error = ".$e);
		}

		
		return -1;
		
	}
	
	function GetSubDomainID($SubDomainName, $DomainID) 
	{
		
		try
		{
			$query = $this->DatabaseConnection->prepare("SELECT id FROM host_meta WHERE host_id = :domain_id AND setting = :sub_domain_name AND section = 'A' AND deleted = 0");
			
			$query->bindParam(":domain_id", $DomainID);
			$query->bindParam(":sub_domain_name", $SubDomainName);
			
			$query->execute();
	
			if($result = $query->fetch(PDO::FETCH_ASSOC))
			{
				return $result["id"];
			}
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.DNS.php -> GetSubDomainID(); Error = ".$e);
		}
		
		return -1;
		
	}
	
	function GetHostName($HostID) 
	{
		
		try
		{
			$query = $this->DatabaseConnection->prepare("SELECT domain FROM soa WHERE id = :host_id AND deleted = 0");
			
			$query->bindParam(":host_id", $HostID);
			
			$query->execute();
	
			if($result = $query->fetch(PDO::FETCH_ASSOC))
			{
				return $result["domain"];
			}
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.DNS.php -> GetHostName(); Error = ".$e);
		}
		

		return "";
		
	}
	
	function AddLastPeriod($Value)
	{
		if(substr($Value, strlen($Value) - 1, 1) != ".")
		{
			$Value = $Value.".";
		}
		return $Value;
	}


	function RemoveLastPeriod($Value)
	{
		if(substr($Value, strlen($Value) - 1, 1) == ".")
		{
			$Value = substr($Value, 0, strlen($Value) - 1);
		}
		return $Value;
	}

	function RecreateAllZoneInfo()
	{
		$ServerType = $this->GetSetting("server_type");

		if($ServerType == "master")
		{
			$DomainNameArray = array();
			$Count = 0;
	
			$this->GetSOAList($DomainNameArray, $Count, -1, 101);
	
			for($x = 0; $x < $Count; $x++)
			{
				$Domain = $DomainNameArray[$x]["Domain"];
				//print "Domain: ".$this->RemoveLastPeriod($Domain)."<br>";
				
				$this->MakeZoneFile($Domain);
			}
		}

		$this->MakeZoneDataFile();
	}
       


	function GetSOAList(&$SOAArray, &$ArrayCount)
        {
                $SOAArray = array();

                $ArrayCount = 0;
		

		try
		{
			$query = $this->DatabaseConnection->prepare("SELECT * FROM soa WHERE deleted = 0 ORDER BY domain;");
			
			
			$query->execute();
	
			while($result = $query->fetch(PDO::FETCH_ASSOC))
			{
				$SOAArray[$ArrayCount]["ID"] = $result["id"];
				$SOAArray[$ArrayCount]["Domain"] = $result["domain"];
				$SOAArray[$ArrayCount]["TTL"] = $result["ttl"];
				$SOAArray[$ArrayCount]["NameServer"] = $result["name_server"];
				$SOAArray[$ArrayCount]["EmailAddress"] = $result["email_address"];
				$SOAArray[$ArrayCount]["SerialNumber"] = $result["serial_number"];
				$SOAArray[$ArrayCount]["Refresh"] = $result["refresh"];
				$SOAArray[$ArrayCount]["Retry"] = $result["retry"];
				$SOAArray[$ArrayCount]["Expire"] = $result["expire"];
				$SOAArray[$ArrayCount]["NegativeTTL"] = $result["negative_ttl"];
				$SOAArray[$ArrayCount++]["Status"] = $result["status"];

			}
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.DNS.php -> GetSOAList(); Error = ".$e);
		}


        }
	
	
        function GetAAAARecord($DomainName)
        {

		$SOAID = $this->GetDomainID($DomainName);

		try
		{
			$query = $this->DatabaseConnection->prepare("SELECT value1 FROM rrs WHERE deleted = 0 AND domain = :domain_name AND type = 'AAAA' AND soa_id = :soa_id;");
			
			$query->bindParam(":domain_name", $DomainName);
			$query->bindParam(":soa_id", $SOAID);
			
			$query->execute();
	
			if($result = $query->fetch(PDO::FETCH_ASSOC))
			{
				return $result["value1"];
			}
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.DNS.php -> GetAAAARecord(); Error = ".$e);
		}
		
		return "";

	}

	
        function GetARecord($DomainName)
        {

		$SOAID = $this->GetDomainID($DomainName);


		try
		{
			$query = $this->DatabaseConnection->prepare("SELECT value1 FROM rrs WHERE deleted = 0 AND type = 'A' AND domain = :domain_name AND soa_id = :soa_id");
			
			$query->bindParam(":domain_name", $DomainName);
			$query->bindParam(":soa_id", $SOAID);
			
			$query->execute();
	
			if($result = $query->fetch(PDO::FETCH_ASSOC))
			{
				return $result["value1"];
			}
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.DNS.php -> GetARecord(); Error = ".$e);
		}
		


		return "";

	}




	function MakeDeleteZoneFile($DomainName)
	{
		
		if(file_exists($_SERVER["DOCUMENT_ROOT"]."/nm/".$this->RemoveLastPeriod($DomainName).".dnsadd"))
		{
			unlink($_SERVER["DOCUMENT_ROOT"]."/nm/".$this->RemoveLastPeriod($DomainName).".dnsadd");
		}

		touch($_SERVER["DOCUMENT_ROOT"]."/nm/".$this->RemoveLastPeriod($DomainName).".dnsdel");
	}


	function MakeZoneFile($DomainName)
	{
		$DomainName = $this->AddLastPeriod($DomainName);

		if(file_exists($_SERVER["DOCUMENT_ROOT"]."/nm/".$this->RemoveLastPeriod($DomainName).".dnsdel"))
		{
			unlink($_SERVER["DOCUMENT_ROOT"]."/nm/".$this->RemoveLastPeriod($DomainName).".dnsdel");
		}

		$SOAID = $this->GetDomainID($DomainName);

		$SOAArray = array();
		        
		$this->GetSOAInfo($SOAID, $SOAArray);

		$myfile = $_SERVER["DOCUMENT_ROOT"]."/nm/".$this->RemoveLastPeriod($DomainName).".dnsadd";
		$fh = fopen($myfile, 'w') or die("can't open file");

		$SOATTL = $SOAArray["TTL"];

		if($SOAArray["Status"] == "suspended")
		{
			$SOATTL = 30;
		}

		fwrite($fh, "\$TTL ".$SOATTL."	\r\n");
		fwrite($fh, $SOAArray["Domain"]." ".$SOATTL." IN SOA ".$SOAArray["NameServer"]." ".$SOAArray["EmailAddress"]." (	\r\n");
		fwrite($fh, $SOAArray["SerialNumber"]." ; serial	\r\n");
		fwrite($fh, $SOAArray["Refresh"]." ; refresh	\r\n");
		fwrite($fh, $SOAArray["Retry"]." ; retry	\r\n");
		fwrite($fh, $SOAArray["Expire"]." ; expire	\r\n");
		fwrite($fh, $SOAArray["NegativeTTL"]." ) ; minimum	\r\n");

		fwrite($fh, "\r\n");


		$RRSArray = array();
		$ArrayCount = 0;

		$this->GetRRSRecords($SOAID, $RRSArray, $ArrayCount);
		
		for($x = 0; $x < $ArrayCount; $x++)
		{
			fwrite($fh, $RRSArray[$x]."\r\n");
		}

		fclose($fh);

	}

	function MakeZoneDataFile()
	{
                $DomainArray = array();
                $ArrayCount = 0;

		$ServerType = $this->GetSetting("server_type");
		if( ($ServerType != "master") && ($ServerType != "slave") )
		{
			return;
		}

                $this->GetSOAList($DomainArray, $ArrayCount, -1, 101);


		$myfile = $_SERVER["DOCUMENT_ROOT"]."/nm/named.conf.options";
		$fh = fopen($myfile, 'w') or die ("cant open file");

		fwrite($fh, "//include \"/etc/rndc.key\";\r\n");
		fwrite($fh, "controls {\r\n");
		        fwrite($fh, "inet 127.0.0.1 allow {localhost; };\r\n");
		        fwrite($fh, "//keys {\"rndc-key\"; };\r\n");
		fwrite($fh, "};\r\n");
		fwrite($fh, "\r\n");
		fwrite($fh, "options {\r\n");
		        fwrite($fh, "listen-on port 53 { ".$_SERVER["SERVER_ADDR"]."; 127.0.0.1; };\r\n");

			/*
			if($this->MyIPv6 != "")
			{
		        	fwrite($fh, "listen-on-v6 port 53 { ".$this->MyIPv6."; ::1; };\r\n");
			}
			*/
		
		        fwrite($fh, "directory       \"/var/cache/bind\";\r\n");
		        fwrite($fh, "dump-file       \"/var/cache/bind/data/cache_dump.db\";\r\n");
		        fwrite($fh, "statistics-file \"/var/cache/bind/data/named_stats.txt\";\r\n");
		        fwrite($fh, "memstatistics-file \"/var/cache/bind/data/named_mem_stats.txt\";\r\n");
		        
			fwrite($fh, "//allow-query     { localhost; };\r\n");
		        fwrite($fh, "recursion no;\r\n");

			//if($this->SlaveServerIPv6 == "")
			//{
			
			if($ServerType == "master")
			{
				$SlaveArray = array();
				$SlaveArrayCount = 0;
				$this->GetSlaveList($SlaveArray, $SlaveArrayCount);
				
				if( (isset($SlaveArray[0]["IPAddress"])) && ($SlaveArray[0]["IPAddress"] != "") )
				{
				        	fwrite($fh, "allow-transfer {".$SlaveArray[0]["IPAddress"].";};\r\n");
				}
				else if( (isset($SlaveArray[0]["HostName"])) && ($SlaveArray[0]["HostName"] != "") )
				{
					$IP = gethostbyname($SlaveArray[0]["HostName"]);
					if($IP != "" && $IP != $SlaveArray[0]["HostName"])
					{
			        		fwrite($fh, "allow-transfer {".$IP.";};\r\n");
					}
				}
			}
	
		        fwrite($fh, "dnssec-enable yes;\r\n");
		        fwrite($fh, "dnssec-validation yes;\r\n");
		        fwrite($fh, "dnssec-lookaside auto;\r\n");
			fwrite($fh, "\r\n");
		        //fwrite($fh, "/* Path to ISC DLV key */\r\n");
		        //fwrite($fh, "bindkeys-file \"/etc/named.iscdlv.key\";\r\n");
		fwrite($fh, "\r\n");
		        //fwrite($fh, "managed-keys-directory \"/var/cache/bind/dynamic\";\r\n");
		fwrite($fh, "};\r\n");
		fwrite($fh, "\r\n");
		fwrite($fh, "\r\n");

                fwrite($fh, "logging {\r\n");
                        fwrite($fh, "channel default_debug {\r\n");
                                fwrite($fh, "file \"named.run\";\r\n");
                                //fwrite($fh, "severity dynamic;\r\n");
                        fwrite($fh, "};\r\n");

                        fwrite($fh, " category queries { default_debug; };\r\n");

                fwrite($fh, "};\r\n");
                fwrite($fh, "\r\n");

		fclose($fh);
		
		$myfile = $_SERVER["DOCUMENT_ROOT"]."/nm/named.conf.local";
		$fh = fopen($myfile, 'w') or die ("cant open file");

		fwrite($fh, "include \"/etc/bind/zones.rfc1918\";\r\n");


		for($x = 0; $x  < $ArrayCount; $x++)
		{
			fwrite($fh, "\r\n");
			fwrite($fh, "zone \"".$this->RemoveLastPeriod($DomainArray[$x]["Domain"])."\" IN {\r\n");
			fwrite($fh, "type ".$ServerType.";\r\n");
		
			fwrite($fh, "file \"/var/cache/bind/slaves/".$this->RemoveLastPeriod($DomainArray[$x]["Domain"])."\";\r\n");

			if($ServerType == "master")
			{
				if( (isset($SlaveArray[0]["IPAddress"])) && ($SlaveArray[0]["IPAddress"] != "") )
				{
			        	fwrite($fh, "also-notify {".$SlaveArray[0]["IPAddress"]."; };\r\n");
				}
				else if( (isset($SlaveArray[0]["HostName"])) && ($SlaveArray[0]["HostName"] != "") )
				{
					$IP = gethostbyname($SlaveArray[0]["HostName"]);
					if($IP != "" && $IP != $SlaveArray[0]["HostName"])
					{
				        	fwrite($fh, "also-notify {".$IP."; };\r\n");
					}
				}

			}
			else
			{
				fwrite($fh, "masters { ".$this->GetSetting("master_ip_address")."; };\r\n");
			}
			fwrite($fh, "};\r\n");
			fwrite($fh, "\r\n");
		}

		fwrite($fh, "\r\n");
		fwrite($fh, "//zone \"111.49.175.199.in-addr.arpa\" IN {\r\n");
		fwrite($fh, "//      type master;\r\n");
		fwrite($fh, "//      file \"slaves/111.49.175.199.rev\";\r\n");
		fwrite($fh, "//};\r\n");

		fclose($fh);
	}
				
	function CreateZoneOnSlaves($DomainName, $SlaveArray, $SlaveArrayCount)
	{

		for($x = 0; $x < $SlaveArrayCount; $x++) {
			
			$port = 8443;
			$result = $this->createSlaveZone( $DomainName, $SlaveArray[$x]["IPAddress"], $SlaveArray[$x]["HostName"], $port, $SlaveArray[$x]["Password"], $SlaveArray[$x]["PublicKey"]);
			
			if ( $result == false && $port == 8443 ) {
				//try without SSL
				$port = 8880;
				$result = $this->createSlaveZone( $DomainName, $SlaveArray[$x]["IPAddress"], $SlaveArray[$x]["HostName"], $port, $SlaveArray[$x]["Password"], $SlaveArray[$x]["PublicKey"]);
			}
		}
	}

	private function createSlaveZone($domainName, $ipAddress, $hostName, $port, $password, $publicKey)
	{
		$options = array(
		'uri' => $ipAddress,
		'location' => 'http'.(($port=="8443")?'s':'').'://'.$hostName.':'.$port.'/API/dns/DNS.php',
		'trace' => 1);

		$message = json_encode(array("Password" => $password, "Domain" => $domainName));

		$encryptedMessage = "";
		openssl_public_encrypt($message, $encryptedMessage, $publicKey);

		$message = base64_encode($encryptedMessage);

		try {
			
			$client = new SoapClient(NULL, $options);
			$Result = $client->CreateZoneOnSlave($message);

			$SlaveStatus = "error";
			if($Result > 0) {
				$SlaveStatus = "success";
			}

			$this->SetSlaveStatus($SlaveArray[$x]["ID"], $SlaveStatus);
		} catch (Exception $e) {
			return false
		}

		return true;

	}

	function AddZone($DomainName, $IPv4Address, $IPv6Address)
	{
		
	        $TTL = $this->GetSetting("ttl");
	        if( (is_numeric($TTL) == false) || ($TTL < 0) )
	        {
	                $TTL = 7200;
	        }
	
	        $NegativeTTL = $this->GetSetting("negative_ttl");
	        if( (is_numeric($NegativeTTL) == false) || ($NegativeTTL < 0) )
	        {
	                $NegativeTTL = 7200;
	        }
	
	        $Refresh = $this->GetSetting("refresh");
	        if( (is_numeric($Refresh) == false) || ($Refresh < 0) )
	        {
	                $Refresh = 1800;
	        }
	
	        $Retry = $this->GetSetting("retry");
	        if( (is_numeric($Retry) == false) || ($Retry < 0) )
	        {
	                $Retry = 7200;
		        }
	
	        $Expire = $this->GetSetting("expire");
	        if( (is_numeric($Expire) == false) || ($Expire < 0) )
	        {
	                $Expire = 1209600;
	        }

	        $EmailAddress = $this->GetSetting("email_address");
		if($EmailAddress == "")
		{
			$EmailAddress = "dns@admin.email";
		}
		
		$EmailAddress = str_replace("@", ".", $EmailAddress);
	        
		$PrimaryNameServer = $this->GetSetting("primary_name_server");
		if($PrimaryNameServer == "")
		{
			$PrimaryNameServer = $_SERVER["SERVER_NAME"];
		}
		

		if($DomainName == "")
		{
			return -3;
		}
		if( ($IPv4Address == "") && ($IPv6Address == "") )
		{
			return -4;
		}

		$x = $this->ValidateDomainName($DomainName);

		if($x < 1)
		{
			return $x;
		}
		
		$DomainName = $DomainName.".";

		if($this->DomainExists($DomainName) == -1)
		{


			$SlaveArray = array();
			$SlaveArrayCount = 0;
			$this->GetSlaveList($SlaveArray, $SlaveArrayCount);
			
			$this->CreateZoneOnSlaves($DomainName, $SlaveArray, $SlaveArrayCount);
			sleep(3);

			$SerialNumber = date("Ymd")."01";
			$SOAID = $this->__AddSOA(1, $DomainName, $TTL, $PrimaryNameServer.".", $EmailAddress, $SerialNumber, $Refresh, $Retry, $Expire, $NegativeTTL);
			if($SOAID > 0)
			{
				if($IPv4Address != "")
				{
					$this->AddRRS($SOAID, $DomainName, "A", $IPv4Address, "", "", "", "", "", "", "", "", "", $TTL);
				}

				if($IPv6Address != "")
				{
					$this->AddRRS($SOAID, $DomainName, "AAAA", $IPv6Address);
				}

				$this->AddRRS($SOAID, "www", "CNAME", $DomainName, "", "", "", "", "", "", "", "", "", $TTL);
				$this->AddRRS($SOAID, "ftp", "CNAME", $DomainName, "", "", "", "", "", "", "", "", "", $TTL);
				$this->AddRRS($SOAID, "mail", "CNAME", $DomainName, "", "", "", "", "", "", "", "", "", $TTL);
				$this->AddRRS($SOAID, "smtp", "CNAME", $DomainName, "", "", "", "", "", "", "", "", "", $TTL);
				$this->AddRRS($SOAID, "pop", "CNAME", $DomainName, "", "", "", "", "", "", "", "", "", $TTL);
				$this->AddRRS($SOAID, "imap", "CNAME", $DomainName, "", "", "", "", "", "", "", "", "", $TTL);
				$this->AddRRS($SOAID, "relay", "CNAME", $DomainName, "", "", "", "", "", "", "", "", "", $TTL);
				$this->AddRRS($SOAID, $DomainName, "MX", "0", $DomainName, "", "", "", "", "", "", "", "", $TTL);

				$this->AddRRS($SOAID, $DomainName, "NS", $this->AddLastPeriod($PrimaryNameServer), "", "", "", "", "", "", "", "", "", $TTL);


				if( (isset($SlaveArray[0]["HostName"])) && ($SlaveArray[0]["HostName"] != "") )
				{
					$this->AddRRS($SOAID, $DomainName, "NS", $this->AddLastPeriod($SlaveArray[0]["HostName"]), "", "", "", "", "", "", "", "", "", $TTL);
				}

				$this->MakeZoneDataFile();
				$this->MakeZoneFile($DomainName);

			

	
				return 1;
			}
			else
			{	
				return -1;
			}
		}
		else
		{
			return -2;
		}
	}


	function DeleteRRS($ID)
        {
                try
                {
                        $query = $this->DatabaseConnection->prepare("UPDATE rrs SET deleted = 1 WHERE id = :id;");
                        $query->bindParam(":id", $ID);
                        $query->execute();

                }
                catch(PDOException $e)
                {
                        $oLog = new Log();
                        $oLog->WriteLog("error", "/class.DNS.php -> DeleteRRS(); Error = ".$e);
                }
        }

	function AddRRS($SOAID, $Domain, $Type, $Value1, $Value2="", $Value3="", $Value4="", $Value5="", $Value6="", $Value7="", $Value8="", $Value9="", $Value10="", $TTL="", $Class="IN")
	{
		
		if($TTL == "")
		{
			$TTL = $this->GetSetting("ttl");
		}
	        
		if( (is_numeric($TTL) == false) || ($TTL < 0) )
	        {
	                $TTL = 7200;
	        }
	

		try
		{
			$query = $this->DatabaseConnection->prepare("INSERT INTO rrs VALUES (0, :soa_id, :domain, :ttl, :class, :type, :value1, :value2, :value3, :value4, :value5, :value6, :value7, :value8, :value9, :value10, 0)");
			
			$Value1 = html_entity_decode($Value1);
			$Value2 = html_entity_decode($Value2);
			$Value3 = html_entity_decode($Value3);
			$Value4 = html_entity_decode($Value4);
			$Value5 = html_entity_decode($Value5);
			
			$Value6 = html_entity_decode($Value6);
			$Value7 = html_entity_decode($Value7);
			$Value8 = html_entity_decode($Value8);
			$Value9 = html_entity_decode($Value9);
			$Value10 = html_entity_decode($Value10);
			
			$query->bindParam(":soa_id", $SOAID);
			$query->bindParam(":domain", $Domain);
			$query->bindParam(":ttl", $TTL);
			$query->bindParam(":class", $Class);
			$query->bindParam(":type", $Type);
			
			$query->bindParam(":value1", $Value1);
			$query->bindParam(":value2", $Value2);
			$query->bindParam(":value3", $Value3);
			$query->bindParam(":value4", $Value4);
			$query->bindParam(":value5", $Value5);
			$query->bindParam(":value6", $Value6);
			$query->bindParam(":value7", $Value7);
			$query->bindParam(":value8", $Value8);
			$query->bindParam(":value9", $Value9);
			$query->bindParam(":value10", $Value10);
			
		
			$query->execute();
			return $this->DatabaseConnection->lastInsertId();
		}
                catch(PDOException $e)
                {
                        $oLog = new Log();
                        $oLog->WriteLog("error", "/class.DNS.php -> AddRRS(); Error = ".$e);
                }
	
		return 0;	
	}

	function EditRRS($ID, $Domain, $Type, $Value1, $Value2="", $Value3="", $Value4="", $Value5="", $Value6="", $Value7="", $Value8="", $Value9="", $Value10="", $TTL=7200, $Class="IN")
	{
		try
		{
			$query = $this->DatabaseConnection->prepare("UPDATE rrs SET domain = :domain, ttl = :ttl, class = :class, type = :type, value1 = :value1, value2 = :value2, value3 = :value3, value4 = :value4, value5 = :value5, value6 = :value6, value7 = :value7, value8 = :value8, value9 = :value9, value10 = :value10 WHERE id = :id;");
			
			$Value1 = html_entity_decode($Value1);
			$Value2 = html_entity_decode($Value2);
			$Value3 = html_entity_decode($Value3);
			$Value4 = html_entity_decode($Value4);
			$Value5 = html_entity_decode($Value5);
			
			$Value6 = html_entity_decode($Value6);
			$Value7 = html_entity_decode($Value7);
			$Value8 = html_entity_decode($Value8);
			$Value9 = html_entity_decode($Value9);
			$Value10 = html_entity_decode($Value10);


			$query->bindParam(":id", $ID);
			$query->bindParam(":domain", $Domain);
			$query->bindParam(":ttl", $TTL);
			$query->bindParam(":class", $Class);
			$query->bindParam(":type", $Type);
			
			$query->bindParam(":value1", $Value1);
			$query->bindParam(":value2", $Value2);
			$query->bindParam(":value3", $Value3);
			$query->bindParam(":value4", $Value4);
			$query->bindParam(":value5", $Value5);
			$query->bindParam(":value6", $Value6);
			$query->bindParam(":value7", $Value7);
			$query->bindParam(":value8", $Value8);
			$query->bindParam(":value9", $Value9);
			$query->bindParam(":value10", $Value10);
			
			$query->execute();
		}
                catch(PDOException $e)
                {
                        $oLog = new Log();
                        $oLog->WriteLog("error", "/class.DNS.php -> EditRRS(); Error = ".$e);
                }
		
	}

	function __AddSOA($AdminID, $DomainName, $TTL=7200, $NameServer="", $EmailAddress="admin.notreal.address.", $SerialNumber=0, $Refresh=1800, $Retry=7200, $Expire=1209600, $NegativeTTL=7200)
	{

		$ServerType = $this->GetSetting("server_type");
		if($NameServer == "")
		{	
			if($ServerType == "master")
			{
				return -1;
			}
		}

		if($SerialNumber == 0)
		{
			$SerialNumber = date("Ymd")."01";
		}

		try
		{
			$query = $this->DatabaseConnection->prepare("INSERT INTO soa VALUES (0, :admin_id, :domain_name, :ttl, :name_server, :email_address, :serial_number, :refresh, :retry, :expire, :negative_ttl, 'active', 0)");
			
			$query->bindParam(":admin_id", $AdminID);
			$query->bindParam(":domain_name", $DomainName);
			$query->bindParam(":ttl", $TTL);
			$query->bindParam(":name_server", $NameServer);
			$query->bindParam(":email_address", $EmailAddress);
			$query->bindParam(":serial_number", $SerialNumber);
			$query->bindParam(":refresh", $Refresh);
			$query->bindParam(":retry", $Retry);
			$query->bindParam(":expire", $Expire);
			$query->bindParam(":negative_ttl", $NegativeTTL);
			
			$query->execute();
	
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.DNS.php -> _AddSOA(); Error = ".$e);
		}
		
		return $this->DatabaseConnection->lastInsertId();
	}

	function GetRRSRecords($SOAID, &$RRSArray, &$ArrayCount)
	{

		$RRSArray = array();
		$ArrayCount = 0;
		$NextLine = "";	

		try
		{
			$query = $this->DatabaseConnection->prepare("SELECT * FROM rrs WHERE soa_id = :soa_id AND deleted = 0 ORDER BY type;");
			
			$query->bindParam(":soa_id", $SOAID);
			
			$query->execute();
	
			while($result = $query->fetch(PDO::FETCH_ASSOC))
			{
				$Domain = $result["domain"];

				$Value1 = stripslashes($result["value1"]);

				$Value2 = $result["value2"];
				
				$NextLine = $Domain."   ".$result["ttl"]."   ".$result["class"]."   ".$result["type"]."   ".$Value1;
				
				if(trim($Value2) != "")
				{
					$NextLine = $NextLine."   ".$Value2;
				}

				if(trim($result["value3"]) != "")
				{
					$NextLine = $NextLine."   ".$result["value3"];
				}

				if(trim($result["value4"]) != "")
				{
					$NextLine = $NextLine."   ".$result["value4"];
				}

				if(trim($result["value5"]) != "")
				{
					$NextLine = $NextLine."   ".$result["value5"];
				}

				if(trim($result["value6"]) != "")
				{
					$NextLine = $NextLine."   ".$result["value6"];
				}

				if(trim($result["value7"]) != "")
				{
					$NextLine = $NextLine."   ".$result["value7"];
				}

				if(trim($result["value8"]) != "")
				{
					$NextLine = $NextLine."   ".$result["value8"];
				}

				if(trim($result["value9"]) != "")
				{
					$NextLine = $NextLine."   ".$result["value9"];
				}

				if(trim($result["value10"]) != "")
				{
					$NextLine = $NextLine."   ".$result["value10"];
				}

				//print "NextLine: ".$NextLine."<br>";
				array_push($RRSArray, $NextLine);
				$ArrayCount++;

			}
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.DNS.php -> GetRRSRecords(); Error = ".$e);
		}
		
		
	}
	
	function GetSOAInfo($SOAID, &$SOAInfoArray) 
	{
		$SOAInfoArray = array();
		
		$SOAInfoArray["ID"] = "";
		$SOAInfoArray["Domain"] = "";
		$SOAInfoArray["TTL"] = "";
		$SOAInfoArray["NameServer"] = "";
		$SOAInfoArray["EmailAddress"] = "";
		$SOAInfoArray["SerialNumber"] = "";
		$SOAInfoArray["Refresh"] = "";
		$SOAInfoArray["Retry"] = "";
		$SOAInfoArray["Expire"] = "";
		$SOAInfoArray["NegativeTTL"] = "";
		$SOAInfoArray["Status"] = "";
		

		try
		{
			$query = $this->DatabaseConnection->prepare("SELECT * FROM soa WHERE id = :soa_id AND deleted = 0");
			
			$query->bindParam(":soa_id", $SOAID);
			
			$query->execute();
	
			if($result = $query->fetch(PDO::FETCH_ASSOC))
			{
				$SOAInfoArray["ID"] = $result["id"];
				$SOAInfoArray["Domain"] = $result["domain"];
				$SOAInfoArray["TTL"] = $result["ttl"];
				$SOAInfoArray["NameServer"] = $result["name_server"];
				$SOAInfoArray["EmailAddress"] = $result["email_address"];
				$SOAInfoArray["SerialNumber"] = $result["serial_number"];
				$SOAInfoArray["Refresh"] = $result["refresh"];
				$SOAInfoArray["Retry"] = $result["retry"];
				$SOAInfoArray["Expire"] = $result["expire"];
				$SOAInfoArray["NegativeTTL"] = $result["negative_ttl"];
				$SOAInfoArray["Status"] = $result["status"];
			}
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.DNS.php -> GetSOAInfo(); Error = ".$e);
		}
	
	}
	



	function DeleteSubDomain($SubDomain, $DomainName)
	{

		$SOAID = $this->GetDomainID($this->AddLastPeriod($DomainName));
		
		if($SOAID < 1)
		{
			return 0;
		}


		try
		{
			$query = $this->DatabaseConnection->prepare("UPDATE rrs SET deleted = 1 WHERE soa_id = :soa_id AND domain = :sub_domain AND type IN ('A', 'AAAA');");
			
			$query->bindParam(":soa_id", $SOAID);
			$query->bindParam(":sub_domain", $SubDomain);
			
			$query->execute();
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.DNS.php -> DeleteSubDomain(); Error = ".$e);
		}
		

		$this->IncrementSerialNumber($SOAID);
		$this->MakeZoneFile($this->AddLastPeriod($DomainName));
		return 1;
		
	}


	
	function AddSubDomain($SubDomain, $DomainName, $IPv4, $IPv6)
	{

		$SOAID = $this->GetDomainID($this->AddLastPeriod($DomainName));
		
		if($SOAID < 1)
		{
			return 0;
		}

		if($this->SubDomainExists($SubDomain, $SOAID) > 0)
		{
			return 0;
		}

		$ReturnValue = $this->AddRRS($SOAID, $SubDomain, "A", $IPv4);

		if($IPv6 != "")
		{
			$ReturnValue = $this->AddRRS($SOAID, $SubDomain, "AAAA", $IPv6);
		}

		$this->IncrementSerialNumber($SOAID);
		$this->MakeZoneFile($this->AddLastPeriod($DomainName));

		return $ReturnValue;
	}
	


	function UpdateAllRRS($SOAID, $Type, $ValuePosition, $OldValue, $NewValue)
	{

		try
		{
			$query = $this->DatabaseConnection->prepare("UPDATE rrs SET value".$ValuePosition." = '".$NewValue."' WHERE value".$ValuePosition." = '".$OldValue."' AND deleted = 0 AND soa_id = ".$SOAID." AND type = '".$Type."';");
			
			$query->execute();
		
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.DNS.php -> UpdateAllRRS(); Error = ".$e);
		}
		

		
	}	

	function DeleteZoneOnSlaves($DomainName, $SlaveArray, $SlaveArrayCount)
	{
		for($x = 0; $x < $SlaveArrayCount; $x++) {

			$port = 8443;
			$result = $this->deleteSlaveZone( $DomainName, $SlaveArray[$x]["IPAddress"], $SlaveArray[$x]["HostName"], $port, $SlaveArray[$x]["Password"], $SlaveArray[$x]["PublicKey"]);

			if ( $result == false && $port == 8443 ) {
				// retry without SSL

				$port = 8880;
				$result = $this->deleteSlaveZone( $DomainName, $SlaveArray[$x]["IPAddress"], $SlaveArray[$x]["HostName"], $port, $SlaveArray[$x]["Password"], $SlaveArray[$x]["PublicKey"]);
			}
		}
	}
	
	private function deleteSlaveZone( $domainName, $ipAddress, $hostName, $port, $password, $publicKey)
	{
		$options = array(
			'uri' => $ipAddress,
			'location' => 'http'.(($port=="8443")?'s':'').'://'.$hostName.':'.$port.'/API/dns/DNS.php',
			'trace' => 1
		);

		$message = json_encode(array("Password" => $password, "Domain" => $domainName));

		$encryptedMessage = "";
		openssl_public_encrypt($message, $encryptedMessage, $publicKey);

		$message = base64_encode($encryptedMessage);

		try {
			$client = new SoapClient(NULL, $options);
			$Result = $client->DeleteZoneOnSlave($message);

			$SlaveStatus = "error";
			if($Result > 0) {
				$SlaveStatus = "success";
			}
			$this->SetSlaveStatus($SlaveArray[$x]["ID"], $SlaveStatus);
		} catch (Exception $e) {
			//print_r($e);

			return false;
		}

		return true;
	}

	function DeleteZone($DomainName)
	{
	
		$DeleteOK = 1;

		if($DeleteOK == 1)
		{
			$ServerType = $this->GetSetting("server_type");

			if($ServerType == "master")
			{
				$SlaveArray = array();
				$SlaveArrayCount = 0;
				$this->GetSlaveList($SlaveArray, $SlaveArrayCount);
			
				$this->DeleteZoneOnSlaves($this->RemoveLastPeriod($DomainName), $SlaveArray, $SlaveArrayCount);
				sleep(3);
			}

			$this->MakeDeleteZoneFile($this->RemoveLastPeriod($DomainName));

			$DomainName = $this->AddLastPeriod($DomainName);
			$DomainID = $this->GetDomainID($DomainName);
			
			try
			{
				$query = $this->DatabaseConnection->prepare("UPDATE rrs SET deleted = 1 WHERE soa_id = :domain_id");
				
				$query->bindParam(":domain_id", $DomainID);
				
				$query->execute();
		
			}
			catch(PDOException $e)
			{
				$oLog = new Log();
				$oLog->WriteLog("error", "/class.DNS.php -> DeleteZone 1 (); Error = ".$e);
			}
			
			

			try
			{
				$query = $this->DatabaseConnection->prepare("UPDATE soa SET deleted = 1 WHERE domain = :domain_name");
				
				$query->bindParam(":domain_name", $DomainName);
				
				$query->execute();
		
			}
			catch(PDOException $e)
			{
				$oLog = new Log();
				$oLog->WriteLog("error", "/class.DNS.php -> DeleteZone 2(); Error = ".$e);
			}
			
			
			$this->MakeZoneDataFile();

			return 1;
		}

		return 0;
	}


	function IncrementSerialNumber($SOAID)
        {

		$SOAInfoArray = array();
		$this->GetSOAInfo($SOAID, $SOAInfoArray);
		
		$DatePart = substr($SOAInfoArray["SerialNumber"], 0, 8);
		$IncrementPart = substr($SOAInfoArray["SerialNumber"], 8);

		if(intVal($DatePart) < intVal(date("Ymd")) )
		{
			$DatePart = date("Ymd");
			$IncrementPart = "01";
		}
		else
		{
			$IncrementPart = intVal($IncrementPart) + 1;
		}

		$SerialNumber = $DatePart.str_pad($IncrementPart, 2, "0", STR_PAD_LEFT);

                try
                {
                        $query = $this->DatabaseConnection->prepare("UPDATE soa SET serial_number = :serial_number;");
                        $query->bindParam(":serial_number", $SerialNumber);
                        $query->execute();

			$this->MakeZoneFile($SOAInfoArray["Domain"]);
                }
                catch(PDOException $e)
                {
                        $oLog = new Log();
                        $oLog->WriteLog("error", "/class.DNS.php -> IncrementSerialNumber(); Error = ".$e);
                }
        }
}
