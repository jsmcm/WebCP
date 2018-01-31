<?php
/*********************************************************************
*********************************************************************/
if(!isset($_SESSION)) 
{
     session_start();
}

include_once(dirname(__FILE__)."/class.Log.php");  
include_once(dirname(__FILE__)."/class.Database.php"); 

class Settings 
{
     	var $oDatabase = null;
     	var $DatabaseConnection = null;

     	function __construct()
     	{
          	$this->oDatabase = new Database();
          	$this->DatabaseConnection = $this->oDatabase->GetConnection();
     	}

        function GetLicenseKey()
        {
		// This function is in the settings class, not the utils class
		// as the other license function are simply because it will be called in 
		// every page, and the setting page is already loaded in every page....

                $LicenseKey = file_get_contents($_SERVER["DOCUMENT_ROOT"]."/includes/license.conf");
                $LicenseKey = trim($LicenseKey);

                return $LicenseKey;
        }


	function SetWebCPName($Value)
	{
		file_put_contents($_SERVER["DOCUMENT_ROOT"]."/includes/PageParts/WebCPName.inc", $Value);
	}

	function SetWebCPTitle($Value)
	{
		file_put_contents($_SERVER["DOCUMENT_ROOT"]."/includes/PageParts/WebCPTitle.inc", $Value);
	}

	function SetWebCPLink($Value)
	{
		file_put_contents($_SERVER["DOCUMENT_ROOT"]."/includes/PageParts/WebCPLink.inc", $Value);
	}

	function GetWebCPName()
	{
		$ReturnValue = "";
		if(file_exists($_SERVER["DOCUMENT_ROOT"]."/includes/PageParts/WebCPName.inc"))
		{
			$ReturnValue = file_get_contents($_SERVER["DOCUMENT_ROOT"]."/includes/PageParts/WebCPName.inc");
		}

		if($ReturnValue == "")
		{
			$ReturnValue = "Web <i class=\"clip-globe\"></i> CP";
		}

		return $ReturnValue;
	}

	function GetWebCPTitle()
	{
		$ReturnValue = "";
		if(file_exists($_SERVER["DOCUMENT_ROOT"]."/includes/PageParts/WebCPTitle.inc"))
		{
			$ReturnValue = file_get_contents($_SERVER["DOCUMENT_ROOT"]."/includes/PageParts/WebCPTitle.inc");
		}

		if($ReturnValue == "")
		{
			$ReturnValue = "WebCP";
		}

		return $ReturnValue;
	}

	function GetWebCPLink()
	{
		$ReturnValue = "";
		if(file_exists($_SERVER["DOCUMENT_ROOT"]."/includes/PageParts/WebCPLink.inc"))
		{
			$ReturnValue = file_get_contents($_SERVER["DOCUMENT_ROOT"]."/includes/PageParts/WebCPLink.inc");
		}

		if($ReturnValue == "")
		{
			$ReturnValue = "<a href=\"http://webcp.pw\" target=\"_new\">Web <i class=\"clip-globe\"></i> CP</a>";
		}

		return $ReturnValue;
	}

	function SetSendSystemEmails($SendSystemEmails)
	{

		$SettingsValue = array();
		$SettingsValue["SendSystemEmails"][0] = $SendSystemEmails;

		$this->DeleteSettings("SendSystemEmails");

		if(strlen(trim($SendSystemEmails)) > 0)
		{
			$this->AddSettings($SettingsValue);
		}

	}

	function SetForwardSystemEmailsTo($EmailAddress)
	{

		$SettingsValue = array();
		$SettingsValue["ForwardSystemEmailsTo"][0] = $EmailAddress;

		$this->DeleteSettings("ForwardSystemEmailsTo");

		if(strlen(trim($EmailAddress)) > 0)
		{
			$this->AddSettings($SettingsValue);
		}

	}

	function GetSendSystemEmails()
	{

		$ArrayCount = 0;
		$SettingsValues = array();
		$this->GetSettings("SendSystemEmails", $SettingsValues, $ArrayCount);
		
		$ReturnValue = "";

		if($ArrayCount > 1)
		{
			$this->DeleteSettings("SendSystemEmails");

			for($x = 1; $x < count($SettingsValues); $x++)
			{
				unset($SettingsValues["SendSystemEmails"][$x]);
			}

			$this->AddSettings("SendSystemEmails", $SettingsValues);
		
			$ReturnValue = $SettingsValues["SendSystemEmails"][0];
		
		}
		else if($ArrayCount == 1)
		{
			$ReturnValue = $SettingsValues["SendSystemEmails"][0];
		}
		
		if($ReturnValue == "")
		{	
			$ReturnValue = "on";
		}

		return $ReturnValue;
	
	}
	
	function GetForwardSystemEmailsTo()
	{

		$ArrayCount = 0;
		$SettingsValues = array();
		$this->GetSettings("ForwardSystemEmailsTo", $SettingsValues, $ArrayCount);


		if($ArrayCount > 1)
		{
			$this->DeleteSettings("ForwardSystemEmailsTo");

			for($x = 1; $x < count($SettingsValues); $x++)
			{
				unset($SettingsValues["ForwardSystemEmailsTo"][$x]);
			}

			$this->AddSettings("ForwardSystemEmailsTo", $SettingsValues);
		
			return $SettingsValues["ForwardSystemEmailsTo"][0];
		
		}
		else if($ArrayCount == 1)
		{
			return $SettingsValues["ForwardSystemEmailsTo"][0];
		}
			
		return "";
	
	}
	
	
	function GetDNSHash()
	{

		$ArrayCount = 0;
		$SettingsValues = array();
		$this->GetSettings("DNSHash", $SettingsValues, $ArrayCount);


		if($ArrayCount > 1)
		{
			$this->DeleteSettings("DNSHash");

			for($x = 1; $x < count($SettingsValues); $x++)
			{
				unset($SettingsValues["DNSHash"][$x]);
			}

			$this->AddSettings("DNSHash", $SettingsValues);
		
			return $SettingsValues["DNSHash"][0];
		
		}
		else if($ArrayCount == 1)
		{
			return $SettingsValues["DNSHash"][0];
		}
			
		return "";
	
	}
	
   
	function SetDNSHash($Hash)
	{

		$SettingsValue = array();
		$SettingsValue["DNSHash"][0] = $Hash;

		$this->DeleteSettings("DNSHash");

		if(strlen(trim($Hash)) > 0)
		{
			$this->AddSettings($SettingsValue);
		}

	}

        function GetPrivateNS1()
        {

                $ArrayCount = 0;
                $SettingsValues = array();
                $this->GetSettings("PrivateNS1", $SettingsValues, $ArrayCount);


                if($ArrayCount > 1)
        	{
                        $this->DeleteSettings("PrivateNS1");

                        for($x = 1; $x < count($SettingsValues); $x++)
                        {
                                unset($SettingsValues["PrivateNS1"][$x]);
                        }

                        $this->AddSettings("PrivateNS1", $SettingsValues);

                        return $SettingsValues["PrivateNS1"][0];

                }
                else if($ArrayCount == 1)
                {
                        return $SettingsValues["PrivateNS1"][0];
                }

                return "";

        }

	function SetPrivateNS1($Hash)
        {

                $SettingsValue = array();
                $SettingsValue["PrivateNS1"][0] = $Hash;

                $this->DeleteSettings("PrivateNS1");

                if(strlen(trim($Hash)) > 0)
                {
                        $this->AddSettings($SettingsValue);
                }

        }

	
	





        function GetPrivateNS2()
        {

                $ArrayCount = 0;
                $SettingsValues = array();
                $this->GetSettings("PrivateNS2", $SettingsValues, $ArrayCount);


                if($ArrayCount > 1)
                {
                        $this->DeleteSettings("PrivateNS2");

                        for($x = 1; $x < count($SettingsValues); $x++)
                        {
                                unset($SettingsValues["PrivateNS2"][$x]);
                        }

                        $this->AddSettings("PrivateNS2", $SettingsValues);

                        return $SettingsValues["PrivateNS2"][0];

                }
                else if($ArrayCount == 1)
                {
                        return $SettingsValues["PrivateNS2"][0];
                }

                return "";

        }

	function SetPrivateNS2($Hash)
        {

                $SettingsValue = array();
                $SettingsValue["PrivateNS2"][0] = $Hash;

                $this->DeleteSettings("PrivateNS2");

                if(strlen(trim($Hash)) > 0)
                {
                        $this->AddSettings($SettingsValue);
                }

        }

	
	


	function SetServerTrafficAllowance($Gb)
	{		

		if(! is_numeric($Gb))
		{
			print "Rturing<p>";
			return 0;
		}

		$this->DeleteSettings("traffic");


		$SettingsArray = array();
		$SettingsArray["traffic"][0] = $Gb;
		
		return $this->AddSettings($SettingsArray);
	}

     function GetFTPBackupSettings(&$FTPSettingsArray)
     {
          $FTPSettingsArray = array();

          try
          {
               $query = $this->DatabaseConnection->prepare("SELECT setting, value FROM server_settings WHERE deleted = 0 AND setting IN ('FTPRemotePath', 'FTPHost', 'FTPUserName', 'FTPPassword');");
               $query->execute();

               while($result = $query->fetch(PDO::FETCH_ASSOC))
               {
                    $FTPSettingsArray[trim($result["setting"])] = trim($result["value"]);
               }
          }
          catch(PDOException $e)
          {
               $oLog = new Log();
               $oLog->WriteLog("error", "/class.Settings.php -> GetFTPBackupSettings(); Error = ".$e);
          }
     }




     function GetBackupSettings($Frequency, &$BackupSettingsArray)
     {
          $BackupSettingsArray = array();

          try
          {
               $query = $this->DatabaseConnection->prepare("SELECT setting, value FROM server_settings WHERE deleted = 0 AND extra1 = :frequency AND setting IN ('BackupStatus', 'BackupWhat', 'BackupUseFTP', 'BackupFTPCount');");     
		$query->bindParam(":frequency", $Frequency);
		  
               $query->execute();

               while($result = $query->fetch(PDO::FETCH_ASSOC))
               {
                    $BackupSettingsArray[trim($result["setting"])] = trim($result["value"]);
               }
          }
          catch(PDOException $e)
          {
               $oLog = new Log();
               $oLog->WriteLog("error", "/class.Settings.php -> GetBackupSettings(); Error = ".$e);
          }
     }


     function DeleteFTPBackupSettings()
     {

          try
          {
               $query = $this->DatabaseConnection->prepare("UPDATE server_settings SET deleted = 1 WHERE setting IN ('FTPRemotePath', 'FTPHost', 'FTPUserName', 'FTPPassword');");
               $query->execute();
          }
          catch(PDOException $e)
          {
               $oLog = new Log();
               $oLog->WriteLog("error", "/class.Settings.php -> DeleteFTPBackupSettings(); Error = ".$e);
          }
     }
     
     
     function SaveBackupFTPSettings($FTPHost, $FTPRemotePath, $FTPUserName, $FTPPassword)
     {

	  $this->DeleteFTPBackupSettings();
	     
          try
          {
		
               $query = $this->DatabaseConnection->prepare("INSERT INTO server_settings VALUES (0, 'FTPHost', :ftp_host, '', '', 0);");
               $query->bindParam(":ftp_host", $FTPHost);
	       $query->execute();
		  
               $query = $this->DatabaseConnection->prepare("INSERT INTO server_settings VALUES (0, 'FTPRemotePath', :ftp_remote_path, '', '', 0);");
               $query->bindParam(":ftp_remote_path", $FTPRemotePath);
	       $query->execute();		  
		  
               $query = $this->DatabaseConnection->prepare("INSERT INTO server_settings VALUES (0, 'FTPUserName', :ftp_user_name, '', '', 0);");
               $query->bindParam(":ftp_user_name", $FTPUserName);
	       $query->execute();

               $query = $this->DatabaseConnection->prepare("INSERT INTO server_settings VALUES (0, 'FTPPassword', :ftp_password, '', '', 0);");
               $query->bindParam(":ftp_password", $FTPPassword);
	       $query->execute();		  
  
		
          }
          catch(PDOException $e)
          {
               $oLog = new Log();
               $oLog->WriteLog("error", "/class.Settings.php -> SaveBackupFTPSettings(); Error = ".$e);
          }
     }

     
     
     
     
     
     function DeleteBackupSettings($Frequency)
     {

          try
          {
               $query = $this->DatabaseConnection->prepare("UPDATE server_settings SET deleted = 1 WHERE extra1 = :frequency AND setting IN ('BackupStatus', 'BackupWhat', 'BackupUseFTP', 'BackupFTPCount');");
	       $query->bindParam(":frequency", $Frequency);
               $query->execute();
          }
          catch(PDOException $e)
          {
               $oLog = new Log();
               $oLog->WriteLog("error", "/class.Settings.php -> DeleteBackupSettings(); Error = ".$e);
          }
     }
     
 
     function SaveBackupSettings($Frequency, $BackupStatus, $BackupWhat, $BackupUseFTP, $BackupFTPCount)
     {

	  $this->DeleteBackupSettings($Frequency);
	     
          try
          {
		
               $query = $this->DatabaseConnection->prepare("INSERT INTO server_settings VALUES (0, 'BackupStatus', :backup_status, :frequency, '', 0);");
               $query->bindParam(":backup_status", $BackupStatus);
	       $query->bindParam(":frequency", $Frequency);
	       $query->execute();
		  
               $query = $this->DatabaseConnection->prepare("INSERT INTO server_settings VALUES (0, 'BackupWhat', :backup_what, :frequency, '', 0);");
               $query->bindParam(":backup_what", $BackupWhat);
	       $query->bindParam(":frequency", $Frequency);
	       $query->execute();		  
		  
               $query = $this->DatabaseConnection->prepare("INSERT INTO server_settings VALUES (0, 'BackupUseFTP', :backup_use_ftp, :frequency, '', 0);");
               $query->bindParam(":backup_use_ftp", $BackupUseFTP);
	       $query->bindParam(":frequency", $Frequency);
	       $query->execute();

               $query = $this->DatabaseConnection->prepare("INSERT INTO server_settings VALUES (0, 'BackupFTPCount', :backup_ftp_count, :frequency, '', 0);");
               $query->bindParam(":backup_ftp_count", $BackupFTPCount);
	       $query->bindParam(":frequency", $Frequency);
	       $query->execute();		  
  
		
          }
          catch(PDOException $e)
          {
               $oLog = new Log();
               $oLog->WriteLog("error", "/class.Settings.php -> SaveBackupSettings(); Error = ".$e);
          }
     }

     



	function GetServerTrafficAllowance()
	{



		try
		{
			$query = $this->DatabaseConnection->prepare("SELECT value FROM server_settings WHERE deleted = 0 AND setting = 'traffic'");
			$query->execute();
	
			if($result = $query->fetch(PDO::FETCH_ASSOC))
			{
				return floatval($result["value"]);
			}
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Settings.php -> GetServerTrafficAllowance(); Error = ".$e);
		}


		return 0;
	
	}
	
	function SetUpgradeAction($UpgradeAction)
	{
		/*
		0 = do nothing (don't upgrade)
		50 = warn, but don't upgrade = beta release
		100 = automatically upgrade
		*/

		$this->DeleteSettings("upgrade_action");

		$SettingsArray = array();
		$SettingsArray["upgrade_action"][0] = $UpgradeAction;
		
		return $this->AddSettings($SettingsArray);
	}

	function GetUpgradeAction()
	{
		/*
		0 = do nothing (don't upgrade)
		50 = warn, but don't upgrade = beta release
		100 = automatically upgrade
		*/

		try
		{
			$query = $this->DatabaseConnection->prepare("SELECT value FROM server_settings WHERE deleted = 0 AND setting = 'upgrade_action'");
			$query->execute();
	
			if($result = $query->fetch(PDO::FETCH_ASSOC))
			{
				return $line["value"];
			}
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Settings.php -> GetUpgradeAction(); Error = ".$e);
		}	
		
		return 100;
	
	}
	
	function SetUpgradeType($UpgradeType)
	{
		/*
		0 = not for release
		50 = beta release
		100 = production release	
		*/

		if( ($UpgradeType != 50) && ($UpgradeType != 100) )
		{
			$UpgradeType = 100;
		}

		$this->DeleteSettings("upgrade_type");

		$SettingsArray = array();
		$SettingsArray["upgrade_type"][0] = $UpgradeType;
		
		return $this->AddSettings($SettingsArray);
	}

	function GetUpgradeType()
	{
		/*
		0 = not for release
		50 = beta release
		100 = production release	
		*/
		
		try
		{
			$query = $this->DatabaseConnection->prepare("SELECT value FROM server_settings WHERE deleted = 0 AND setting = 'upgrade_type'");
			$query->execute();
	
			if($result = $query->fetch(PDO::FETCH_ASSOC))
			{
				return $line["value"];
			}
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Settings.php -> GetUpgradeType(); Error = ".$e);
		}	
		
		// default to production release
		return 100;
	
	}
	


        function SetOutBoundMail550Count($Count)
        {
                $this->DeleteSettings("OutBoundMail550Count");
  
                $SettingsArray = array();
                $SettingsArray["OutBoundMail550Count"][0] = $Count;
  
                return $this->AddSettings($SettingsArray);
        }

        function GetOutBoundMail550Count()
        {

		try
		{
			$query = $this->DatabaseConnection->prepare("SELECT value FROM server_settings WHERE deleted = 0 AND setting = 'OutBoundMail550Count'");
			$query->execute();
	
			if($result = $query->fetch(PDO::FETCH_ASSOC))
			{
				return $line["value"];
			}
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Settings.php -> GetOutBoundMail550Count(); Error = ".$e);
		}	
		

                return 10;

        }



        function SetOutBoundMailAction($Count)
        {
                $this->DeleteSettings("OutBoundMailAction");
  
                $SettingsArray = array();
                $SettingsArray["OutBoundMailAction"][0] = $Count;
  
                return $this->AddSettings($SettingsArray);
        }

        function GetOutBoundMailAction()
        {
			 
			try
			{
				$query = $this->DatabaseConnection->prepare("SELECT value FROM server_settings WHERE deleted = 0 AND setting = 'OutBoundMailAction'");
	
				$query->execute();
	
				if($result = $query->fetch(PDO::FETCH_ASSOC))
				{
					return $result["value"];
				}
	
			}
			catch(PDOException $e)
			{
				$oLog = new Log();
				$oLog->WriteLog("error", "/class.Settings.php -> GetOutBoundMailAction(); Error = ".$e);
			}

                
            return "block";

        }

	
	
	function SetOutBoundMailSubjectCount($Count)
	{
		$this->DeleteSettings("OutBoundMailSubjectCount");

		$SettingsArray = array();
		$SettingsArray["OutBoundMailSubjectCount"][0] = $Count;
		
		return $this->AddSettings($SettingsArray);
	}

        function GetOutBoundMailSubjectCount()
        {
		try
		{
			$query = $this->DatabaseConnection->prepare("SELECT value FROM server_settings WHERE deleted = 0 AND setting = 'OutBoundMailSubjectCount'");
			$query->execute();
	
			if($result = $query->fetch(PDO::FETCH_ASSOC))
			{
				return $line["value"];
			}
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Settings.php -> GetOutBoundMailSubjectCount(); Error = ".$e);
		}	
		

                return 50;

        }

	function GetSettings($SettingName, &$SettingsValues, &$ArrayCount)
	{

		$ArrayCount = 0;

		try
		{
		
			if($SettingName != "")
			{
				$query = $this->DatabaseConnection->prepare("SELECT * FROM server_settings WHERE deleted = 0 AND setting = :setting_name");
				$query->bindParam(":setting_name",  $SettingName);
			}
			else
			{
				$query = $this->DatabaseConnection->prepare("SELECT * FROM server_settings WHERE deleted = 0");
			}
		
			
			$query->execute();
	
			while($result = $query->fetch(PDO::FETCH_ASSOC))
			{
				$SettingsValues[$result["setting"]][$ArrayCount++] = $result["value"];
			}
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Settings.php -> GetSettings(); Error = ".$e);
		}	
	
	}

	
	function AddSettings($SettingsArray)
	{

		$x = 0;
		foreach($SettingsArray as $key=>$value)
		{

			foreach($value as $IndexValue)
			{
				try
				{
				
					$query = $this->DatabaseConnection->prepare("INSERT INTO server_settings VALUES (0, :key, :index_value, '', '', 0)");
					$query->bindParam(":key",  $key);
					$query->bindParam(":index_value",  $IndexValue);
			

					$query->execute();
					
					if( $this->DatabaseConnection->lastInsertId() > 0 )
					{
						$x++;
					}
			
				}
				catch(PDOException $e)
				{
					$oLog = new Log();
					$oLog->WriteLog("error", "/class.Settings.php -> AddSettings(); Error = ".$e);
				}	
			

			}
		}
		
		return $x;
		
	}
	
	

	private function DeleteSettings($SettingName)
	{

		try
		{
		
			$query = $this->DatabaseConnection->prepare("UPDATE server_settings SET deleted = 1 WHERE setting = :setting_name");
			$query->bindParam(":setting_name",  $SettingName);
			
			$query->execute();
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Settings.php -> DeleteSettings(); Error = ".$e);
		}
		
		return 1;	
	}

    
}


?>
