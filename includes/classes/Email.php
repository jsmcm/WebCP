<?php
/*********************************************************************
*********************************************************************/
if(!isset($_SESSION)) 
{
     session_start();
}

include_once("/var/www/html/webcp/vendor/autoload.php");

class Email
{
	var $LastErrorDescription = "";
	
	var $loggedInEmailId = false;
	var $loggedInEmailDomainUserName = "";
	var $loggedInEmailDomain = "";
	var $loggedInEmailLocalPart = 0;

	function __construct() 
	{
	
		$DomainID = 0;

		$this->oDatabase = new Database();
		$this->DatabaseConnection = $this->oDatabase->GetConnection();

		if( ! file_exists($_SERVER["DOCUMENT_ROOT"]."/nm/"))
		{
			mkdir($_SERVER["DOCUMENT_ROOT"]."/nm/", 0755);
		}


                if(isset($_SESSION["email_client_id"]))
              	{
                        if($_SESSION["email_client_id"] > 0)
                      	{
				$this->loggedInEmailId = $_SESSION["email_client_id"];
                        	$this->GetEmailInfo($_SESSION["email_client_id"], $this->loggedInEmailDomainUserName, $this->loggedInEmailLocalPart, $this->loggedInEmailDomain, $DomainID);
                	}
          	}


	}
	
	function getLoggedInEmailId()
	{
		return $this->loggedInEmailId;
	}	

	function GetMailBoxID($EmailAddress)
	{
		$LocalPart = substr($EmailAddress, 0, strpos($EmailAddress, "@"));
		$Domain = substr($EmailAddress, strpos($EmailAddress, "@") + 1);
		
		try
		{
			$query = $this->DatabaseConnection->prepare("SELECT mailboxes.id AS id FROM mailboxes, domains WHERE mailboxes.active = 1 AND domains.deleted = 0 AND mailboxes.domain_id = domains.id AND mailboxes.local_part = :local_part AND domains.fqdn = :domain");
			$query->bindParam(":local_part", $LocalPart);
			$query->bindParam(":domain", $Domain);
			$query->execute();
	
			if($result = $query->fetch(PDO::FETCH_ASSOC))
			{
				return $result["id"];
			}
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Email.php -> GetMailBoxID(); Error = ".$e);
		}
		

		return 0;
		
	}



	function AddCatchAll($ClientID, $Role, $DomainName, $EmailAddress)
	{
	
		$this->DeleteCatchAll($ClientID, $Role, $DomainName);

		$DomainOwnerID = $ClientID;

		if($Role == 'admin')
		{
			$DomainOwnerID = $this->GetClientIDFromEmailAddress($EmailAddress);
		}
	
		try
		{
			$query = $this->DatabaseConnection->prepare("INSERT INTO email_options VALUES (0, 'catchall', :domain_name, :email_address, :domain_owner_id, 0)");
			$query->bindParam(":domain_name", $DomainName);
			$query->bindParam(":email_address", $EmailAddress);
			$query->bindParam(":domain_owner_id", $DomainOwnerID);
			
			$query->execute();

			file_put_contents($_SERVER["DOCUMENT_ROOT"]."/nm/".$DomainName.".catchall", $EmailAddress);
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Email.php -> AddCatchAll(); Error = ".$e);
		}

		return 1;
	}

	function DeleteCatchAll($ClientID, $Role, $DomainName)
	{
		
		$oDomain = new Domain();
		

		$DomainOwnerID = $ClientID;

		if($Role == 'admin')
		{
			$DomainOwnerID = $oDomain->GetClientIDFromDomainName($DomainName);
		}
		

		try
		{
			$query = $this->DatabaseConnection->prepare("UPDATE email_options SET deleted = 1 WHERE extra2 = :domain_owner_id AND option_name = 'catchall' AND option_value = :domain_name");
			$query->bindParam(":domain_owner_id", $DomainOwnerID);
			$query->bindParam(":domain_name", $DomainName);
			$query->execute();
			
			file_put_contents($_SERVER["DOCUMENT_ROOT"]."/nm/".$DomainName.".catchall", "");

		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Email.php -> DeleteCatchAll(); Error = ".$e);
		}
		
		return 1;
	}



	function makeTransactionalEmailEximSettings($random, $nonceArray)
	{

		
		if ( $random == "" ) {
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Email.php -> makeTransactionalEmailEximSettings(); random cannot be blank in Email::makeTransactionalEmailEximSettings");
			throw new Exception("<p><b>random cannot be blank in Email::makeTransactionalEmailEximSettings</b><p>");
		}


		if ( ! (is_array($nonceArray) && !empty($nonceArray) ) ) {
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Email.php -> makeTransactionalEmailEximSettings(); Nonce not set");
			throw new Exception("<p><b>Nonce not set in Email::makeTransactionalEmailEximSettings</b><p>");
		}
		
		$oUser = new User();
		$ClientID = $oUser->getClientId();

		$nonceMeta = [
			$oUser->Role,
			$oUser->ClientID,
			$random
		];

		$oSimpleNonce = new SimpleNonce();
		$nonceResult = $oSimpleNonce->VerifyNonce($nonceArray["Nonce"], "makeTransactionalEmailEximSettings", $nonceArray["TimeStamp"], $nonceMeta);

		if ( ! $nonceResult ) {
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Email.php -> makeTransactionalEmailEximSettings(); Nonce failed 1");
			throw new Exception("<p><b>Nonce failed in Email::makeTransactionalEmailEximSettings</b></p>");
		}

		if ( ! file_exists("/var/www/html/mail/TransactionalEmail")) {
			mkdir("/var/www/html/mail/TransactionalEmail", 0755);
		}

		$settings = $this->getTransactionalEmailSettings();

		$hostName = "";
		$username = "";
		$password = "";

		if (isset($settings["hostname"]) ) {
			$hostName = $settings["hostname"];
		}

		if (isset($settings["username"]) ) {
			$username = $settings["username"];
		}

		if (isset($settings["password"]) ) {
			$password = $settings["password"];
		}

		if ( $username != "" && $password != "" ) {

	
			file_put_contents("/var/www/html/mail/TransactionalEmail/hostname", $hostName);
			file_put_contents("/var/www/html/mail/TransactionalEmail/username", $username);
			file_put_contents("/var/www/html/mail/TransactionalEmail/password", $password);

			$random = random_int(1, 1000000);
			$oUser = new User();
			$oSimpleNonce = new SimpleNonce();
			$nonceArray = [	
				$oUser->Role,
				$oUser->ClientID,
				$random
			];
			$nonce = $oSimpleNonce->GenerateNonce("getTransactionalEmailDomains", $nonceArray);
			
			$domains = $this->getTransactionalEmailDomains($random, $nonce);

			file_put_contents("/var/www/html/mail/TransactionalEmail/domains", "");
			if (!empty($domains) ) {
				file_put_contents("/var/www/html/mail/TransactionalEmail/domains", implode(":", $domains));
			}
		}

	}

	function getTransactionalEmailDomains($random, $nonceArray)
	{


		if ( $random == "" ) {
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Email.php -> getTransactionalEmailDomains(); random cannot be blank in Email::getTransactionalEmailDomains");
			throw new Exception("<p><b>random cannot be blank in Email::getTransactionalEmailDomains</b><p>");
		}


		if ( ! (is_array($nonceArray) && !empty($nonceArray) ) ) {
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Email.php -> getTransactionalEmailDomains(); Nonce not set");
			throw new Exception("<p><b>Nonce not set in Email::getTransactionalEmailDomains</b><p>");
		}
		
		$oUser = new User();
		$ClientID = $oUser->getClientId();

		$nonceMeta = [
			$oUser->Role,
			$ClientID,
			$random
		];

		$oSimpleNonce = new SimpleNonce();
		$nonceResult = $oSimpleNonce->VerifyNonce($nonceArray["Nonce"], "getTransactionalEmailDomains", $nonceArray["TimeStamp"], $nonceMeta);

		if ( ! $nonceResult ) {
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Email.php -> getTransactionalEmailDomains(); Nonce failed");
			throw new Exception("<p><b>Nonce failed in Email::getTransactionalEmailDomains</b></p>");
		}

		$domains = array();

		try {
			$query = $this->DatabaseConnection->prepare("SELECT fqdn FROM email_options, domains WHERE option_name = 'domain_transactional_email' AND email_options.deleted = 0 AND domains.deleted = 0 AND option_value = 'transactional' AND extra1 = domains.id;");
			$query->execute();
	
			while ($result = $query->fetch(PDO::FETCH_ASSOC)) {
				array_push($domains, $result["fqdn"]);
			}
	
		} catch(PDOException $e) {
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Email.php -> getTransactionalEmailDomains(); Error = ".$e);
		}		

		return $domains;

        }


	function deleteTransactionalEmailSettings()
	{

		try {
			$query = $this->DatabaseConnection->prepare("UPDATE email_options SET deleted = 1 WHERE option_name LIKE 'transactional_email_setting_%'");
				
			$query->execute();
	
			
		} catch(PDOException $e) {
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Email.php -> deleteTransactionalEmailSettings(); Error = ".$e);
		}
		
	}

	function getTransactionalEmailSettings()
	{

		$settings = array();

		try {
			$query = $this->DatabaseConnection->prepare("SELECT option_name, option_value FROM email_options WHERE option_name LIKE 'transactional_email_setting_%' AND deleted = 0");
			$query->execute();
	
			while ($result = $query->fetch(PDO::FETCH_ASSOC)) {
				
				if ($result["option_name"] == "transactional_email_setting_username") {
					$settings["username"] = $result["option_value"];
				} else if ($result["option_name"] == "transactional_email_setting_password") {
					$settings["password"] = $result["option_value"];
				} else if ($result["option_name"] == "transactional_email_setting_default") {
					$settings["default"] = $result["option_value"];
				} else if ($result["option_name"] == "transactional_email_setting_service_name") {
					$settings["servicename"] = $result["option_value"];
				} else if ($result["option_name"] == "transactional_email_setting_host_name") {
					$settings["hostname"] = $result["option_value"];
				}

			}
	
		} catch(PDOException $e) {
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Email.php -> getTransactionalEmailSettings(); Error = ".$e);
		}		

		return $settings;

    }

	function getDomainTransactionalSetting($domainId)
	{

		try {
			$query = $this->DatabaseConnection->prepare("SELECT option_value FROM email_options WHERE option_name = 'domain_transactional_email' AND deleted = 0 and extra1 = :extra1");
			$query->bindParam(":extra1", $domainId);
			$query->execute();
	
			if ($result = $query->fetch(PDO::FETCH_ASSOC)) {
				return $result["option_value"];
			}
	
		} catch(PDOException $e) {
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Email.php -> getDomainTransactionalSetting(); Error = ".$e);
		}		

		return "none";

        }



	function deleteTransactionalDomain( $domainId )
	{
		
		try {

			$query = $this->DatabaseConnection->prepare("UPDATE email_options SET deleted = 1 WHERE extra1 = :domain_id AND option_name = 'domain_transactional_email'");
			$query->bindParam(":domain_id", $domainId);		
			$query->execute();
	
		} catch(PDOException $e) {
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Email.php -> deleteTransactionalDomain(); Error = ".$e);
		}	
	}


	function saveTransactionalDomain($setting, $domainId)
	{
		$this->deleteTransactionalDomain($domainId);
		$this->insertEmailOptions("domain_transactional_email", $setting, $domainId, "");
	}


	function saveTransactionalEmailSettings($serviceName, $hostName, $userName, $password, $default)
	{
		$this->insertEmailOptions("transactional_email_setting_service_name", $serviceName, "", "");
		$this->insertEmailOptions("transactional_email_setting_host_name", $hostName, "", "");
		$this->insertEmailOptions("transactional_email_setting_username", $userName, "", "");
		$this->insertEmailOptions("transactional_email_setting_password", $password, "", "");
		$this->insertEmailOptions("transactional_email_setting_default", $default, "", "");
	}


	function insertEmailOptions($optionName, $optionValue, $extra1 = "", $extra2 = "")
	{

		try {
		

			$query = $this->DatabaseConnection->prepare("INSERT INTO email_options VALUES (0, :email_option, :option_value, :extra1, :extra2, 0)");
				
			$query->bindParam(":email_option", $optionName);
			$query->bindParam(":option_value", $optionValue);
			$query->bindParam(":extra1", $extra1);
			$query->bindParam(":extra2", $extra2);
			
			$query->execute();
	
		} catch(PDOException $e) {
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Email.php -> insertEmailOptions(); Error = ".$e);
		}	

	}



        function GetCatcher($DomainName)
        {


		try
		{
			$query = $this->DatabaseConnection->prepare("SELECT extra1 FROM email_options WHERE option_name = 'catchall' AND option_value = :domain_name AND deleted = 0");
			$query->bindParam(":domain_name", $DomainName);
			$query->execute();
	
			if($result = $query->fetch(PDO::FETCH_ASSOC))
			{
				return $result["extra1"];
			}
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Email.php -> GetCatcher(); Error = ".$e);
		}		

		return "";

        }

        function GetCatchAllDetail($CatchAllID, $ClientID, $Role, &$DomainName, &$EmailAddress)
        {
	
		$DomainName = "";
		$EmailAddress = "";


		try
		{
			if($Role == 'admin')
			{
				$query = $this->DatabaseConnection->prepare("SELECT * FROM email_options WHERE id = :catch_all_id");
				$query->bindParam(":catch_all_id", $CatchAllID);				
			}
			else
			{
				$query = $this->DatabaseConnection->prepare("SELECT * FROM email_options WHERE extra2 = :client_id AND id = :catch_all_id");
				$query->bindParam(":client_id", $ClientID);	
				$query->bindParam(":catch_all_id", $CatchAllID);				
			}		

			$query->execute();
	
			if($result = $query->fetch(PDO::FETCH_ASSOC))
			{
				$DomainName = $result["option_value"];
				$EmailAddress = $result["extra1"];
			}
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Email.php -> GetCatchAllDetail(); Error = ".$e);
		}		



        }



        function GetCatchAllList(&$CatchAllArray, &$ArrayCount, $ClientID, $Role)
        {
                $CatchAllArray = array();
		$ArrayCount = 0;


		try
		{
		

			if($Role == 'admin')
			{
				$query = $this->DatabaseConnection->prepare("SELECT * FROM email_options WHERE option_name = 'catchall' AND extra1 != 'global' AND deleted = 0 ORDER BY option_value;");
			}
			else
			{
				$query = $this->DatabaseConnection->prepare("SELECT * FROM email_options WHERE option_name = 'catchall' AND extra2 = :client_id AND extra1 != 'global' AND deleted = 0;");
				$query->bindParam(":client_id", $ClientID);

			}


			$query->execute();
	
			while($result = $query->fetch(PDO::FETCH_ASSOC))
			{
				$CatchAllArray[$ArrayCount]["ID"] = $result["id"];

				$CatchAllArray[$ArrayCount]["Domain"] = $result["option_value"];
				$CatchAllArray[$ArrayCount++]["Catcher"] = $result["extra1"];
			}
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Email.php -> GetCatchAllList(); Error = ".$e);
		}			


        }






	function DeleteBlackListByValues($ClientID, $ClientEmailAddress, $BlackListAddress)
	{


		try
		{
			$query = $this->DatabaseConnection->prepare("UPDATE email_options SET deleted = 1 WHERE extra2 = :client_id AND extra1 = :client_email_address AND option_value = :black_list_address");
			$query->bindParam(":client_id", $ClientID);	
			$query->bindParam(":client_email_address", $ClientEmailAddress);	
			$query->bindParam(":black_list_address", $BlackListAddress);				
				
			$query->execute();
	
			
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Email.php -> DeleteBlackListByValues(); Error = ".$e);
		}
		
		return 1;
	}



	function AddBlackWhiteList($EmailAddressOwnerID, $EmailAddress, $BlackListAddress, $Colour)
	{

		$this->DeleteBlackListByValues($EmailAddressOwnerID, $EmailAddress, $BlackListAddress);

		try
		{
			$query = $this->DatabaseConnection->prepare("INSERT INTO email_options VALUES (0, 'user_".$Colour."list', :black_list_address, :email_address, :email_address_owner_id, 0)");

			$query->bindParam(":black_list_address", $BlackListAddress);	
			$query->bindParam(":email_address", $EmailAddress);		
			$query->bindParam(":email_address_owner_id", $EmailAddressOwnerID);				
				
			$query->execute();
	
			
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Email.php -> AddBlackWhiteList(); Error = ".$e);
		}
		
	
		if($Colour == "black")
		{
			$this->RecreateUserForwardFile($EmailAddressOwnerID);
		}
		else
		{
			$this->MakeWhiteListFile();
		}

		return 1;
		
	}


	function GetBlackWhiteListOwner($BlackListID)
	{


		try
		{
			$query = $this->DatabaseConnection->prepare("SELECT extra2 FROM email_options WHERE deleted = 0 AND id = :black_list_id;");
			$query->bindParam(":black_list_id", $BlackListID);
			$query->execute();
	
			if($result = $query->fetch(PDO::FETCH_ASSOC))
			{
				return $result["extra2"];
			}
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Email.php -> GetBlackWhiteListOwner(); Error = ".$e);
		}			

		return 0;
		
	}



	function AddBlackListsToForwardFile($ClientID, $Domain, $FilePointer)
	{

		try
		{
			$query = $this->DatabaseConnection->prepare("SELECT DISTINCT(extra1) FROM email_options WHERE (extra1 LIKE '%@".$Domain."' OR extra1 = '".$Domain."') AND extra2 = :client_id AND option_name = 'user_blacklist' AND deleted = 0 ORDER BY extra1;");
			
			$query->bindParam(":client_id", $ClientID);
			
			$query->execute();
	
			while($result = $query->fetch(PDO::FETCH_ASSOC))
			{
				fwrite($FilePointer, $this->GetEmailAddressBlackLists($ClientID, $result["extra1"]));
				fwrite($FilePointer, "\n\n");

			}
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Email.php -> AddBlackListsToForwardFile(); Error = ".$e);
		}			
	}

	function GetEmailAddressBlackLists($ClientID, $EmailAddress)
	{

		$LocalPart = substr($EmailAddress, 0, strpos($EmailAddress, '@'));

		$Text = "";
		
		try
		{
			$query = $this->DatabaseConnection->prepare("SELECT option_value FROM email_options WHERE deleted = 0 AND option_name = 'user_blacklist' AND extra2 = :client_id AND extra1 = :email_address;");
			
			$query->bindParam(":client_id", $ClientID);
			$query->bindParam(":email_address", $EmailAddress);
			
			$query->execute();
	
			while($result = $query->fetch(PDO::FETCH_ASSOC))
			{
				$Text = $Text."\tif \$sender_address contains \"".$result["option_value"]."\" then\n\t\tseen finish\n\tendif\n\n";
			}
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Email.php -> GetEmailAddressBlackLists(); Error = ".$e);
		}	
		


		if(strlen($Text) > 0)
		{
			if($LocalPart == "")
			{
				return $Text."\n";
			}
			else
			{
				return "if \$local_part is \"".$LocalPart."\"\nthen\n".$Text."endif\n";
			}
		}
		
		return "";	

	}













	function DeleteBlackWhiteList($ClientID, $Role, $BlackListID, $Colour)
	{
		

		$BlackListOwner = $ClientID;

		try
		{

			if($Role == 'admin')
			{
				$BlackListOwner = $this->GetBlackWhiteListOwner($BlackListID);

				$query = $this->DatabaseConnection->prepare("UPDATE email_options SET deleted = 1 WHERE id = :black_list_id");
				
				$query->bindParam(":black_list_id", $BlackListID);
				
				
			}
			else if( $Role == "email" )
			{		
				$ClientID = $this->GetEmailOwner($ClientID);
				$BlackListOwner = $ClientID;
				$query = $this->DatabaseConnection->prepare("UPDATE email_options SET deleted = 1 WHERE extra2 = :client_id AND id = :black_list_id");
				
				$query->bindParam(":client_id", $ClientID);
				$query->bindParam(":black_list_id", $BlackListID);
				
				
			}
			else
			{		
				$query = $this->DatabaseConnection->prepare("UPDATE email_options SET deleted = 1 WHERE extra2 = :client_id AND id = :black_list_id");
				
				$query->bindParam(":client_id", $ClientID);
				$query->bindParam(":black_list_id", $BlackListID);
				
				
			}
		
			$query->execute();
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Email.php -> DeleteBlackWhiteList(); Error = ".$e);
		}	
		
		
		
		if($Colour == "black")
		{
			$this->RecreateUserForwardFile($BlackListOwner);
		}
		else
		{
			$this->MakeWhiteListFile();
		}

		return 1;
	}

	function MakeWhiteListFile()
	{
		$WhiteListArray = array();
		$ArrayCount = 0;

		$this->GetUserBlackWhiteList($WhiteListArray, $ArrayCount, 0, 'admin', "white");
		
		$Buffer = "";

		$f = fopen($_SERVER["DOCUMENT_ROOT"]."/nm/whitelist", "w");

		for($x = 0; $x < $ArrayCount; $x++)
		{
			if($Buffer != $WhiteListArray[$x]["ListedEmail"])
			{
				$Buffer = $WhiteListArray[$x]["ListedEmail"];
				fwrite($f, $Buffer."\n");
			}
		}
		
		fclose($f);
	}


        function GetUserBlackWhiteList(&$BlackListArray, &$ArrayCount, $ClientID, $Role, $Colour)
        {
                $BlackListArray = array();
		$ArrayCount = 0;
 
                
		try
		{
		

			if($Role == 'admin')
			{
				$query = $this->DatabaseConnection->prepare("SELECT * FROM email_options WHERE option_name = 'user_".$Colour."list' AND extra1 != 'global' AND deleted = 0 ORDER BY option_value;");
			}
			else if ($Role == "email")
			{
                        	
				$userName = "";
                               	$localPart = "";
                               	$domainName = "";
                               	$domainId = 0;


                                $this->GetEmailInfo($ClientID, $userName, $localPart, $domainName, $domainId);

				$ClientID = $this->GetEmailOwner($ClientID);


				$query = $this->DatabaseConnection->prepare("SELECT * FROM email_options WHERE option_name = 'user_".$Colour."list' AND extra2 = :client_id AND extra1 = '".$localPart."@".$domainName."' AND deleted = 0;");
				$query->bindParam(":client_id", $ClientID);
			
			}
			else
			{
				$query = $this->DatabaseConnection->prepare("SELECT * FROM email_options WHERE option_name = 'user_".$Colour."list' AND extra2 = :client_id AND extra1 != 'global' AND deleted = 0;");
				$query->bindParam(":client_id", $ClientID);
			
			}


			$query->execute();
	
			while($result = $query->fetch(PDO::FETCH_ASSOC))
			{
				$BlackListArray[$ArrayCount]["ID"] = $result["id"];

				if(strstr($result["extra1"], "@"))
				{
					$BlackListArray[$ArrayCount]["ClientEmail"] = $result["extra1"];
					$BlackListArray[$ArrayCount]["ClientDomain"] = "";
				}
				else
				{
					$BlackListArray[$ArrayCount]["ClientEmail"] = "";
					$BlackListArray[$ArrayCount]["ClientDomain"] = $result["extra1"];
				}

				$BlackListArray[$ArrayCount++]["ListedEmail"] = $result["option_value"];
			}
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Email.php -> GetUserBlackWhiteList(); Error = ".$e);
		}			


        }




        function AddSpamAssassin($ClientID, $EmailAddress, $SpamBlockLevel, $SpamWarnLevel, $SpamSubjectModifier)
	{
		$this->AddSpamBlockLevel($EmailAddress, $ClientID, $SpamBlockLevel);
		$this->AddSpamWarnLevel($EmailAddress, $ClientID, $SpamWarnLevel);

		if($SpamSubjectModifier != "")
		{
			$this->AddSpamSubjectModifier($EmailAddress, $ClientID, $SpamSubjectModifier);
			$this->AddUseSpamSubjectModifier($EmailAddress, $ClientID, "1");
		}

		file_put_contents($_SERVER["DOCUMENT_ROOT"]."/nm/".$EmailAddress.".add_spamassassin", "block=".$SpamBlockLevel."\r\nwarn=".$SpamWarnLevel."\r\nsubject=".$SpamSubjectModifier."\r\nlocal_part=".substr($EmailAddress, 0, strpos($EmailAddress, "@"))."\r\ndomain=".substr($EmailAddress, strpos($EmailAddress, "@") + 1)."\r\n");

 		return true;
	}

	function AddUseSpamSubjectModifier($EmailAddress, $ClientID, $Use)
	{

		try
		{
			$query = $this->DatabaseConnection->prepare("INSERT INTO email_options VALUES (0, 'use_spam_subject', :use, :email_address, :client_id, 0)");
			
			$query->bindParam(":use", $Use);
			$query->bindParam(":email_address", $EmailAddress);
			$query->bindParam(":client_id", $ClientID);
			
			$query->execute();

	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Email.php -> AddUseSpamSubjectModifier(); Error = ".$e);
		}
		
		
		return 1;
	}

	function AddSpamSubjectModifier($EmailAddress, $ClientID, $SpamSubjectModifier)
	{
	
		try
		{
			$query = $this->DatabaseConnection->prepare("INSERT INTO email_options VALUES (0, 'spam_subject_modifier', :spam_subject_modifier, :email_address, :client_id, 0)");
			
			$query->bindParam(":spam_subject_modifier", $SpamSubjectModifier);
			$query->bindParam(":email_address", $EmailAddress);
			$query->bindParam(":client_id", $ClientID);
			
			$query->execute();
	
			if($result = $query->fetch(PDO::FETCH_ASSOC))
			{
				return $result["option_value"];
			}
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Email.php -> AddSpamSubjectModifier(); Error = ".$e);
		}
		
		return 1;
	}


	function AddSpamWarnLevel($EmailAddress, $ClientID, $SpamWarnLevel)
	{
	
		try
		{
			$query = $this->DatabaseConnection->prepare("INSERT INTO email_options VALUES (0, 'spam_score_warn', :spam_subject_modifier, :email_address, :client_id, 0)");
			
			$query->bindParam(":spam_subject_modifier", $SpamWarnLevel);
			$query->bindParam(":email_address", $EmailAddress);
			$query->bindParam(":client_id", $ClientID);
			
			$query->execute();
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Email.php -> AddSpamWarnLevel(); Error = ".$e);
		}

		return 1;
	}


	function AddSpamBlockLevel($EmailAddress, $ClientID, $SpamBlockLevel)
	{

		try
		{
			$query = $this->DatabaseConnection->prepare("INSERT INTO email_options VALUES (0, 'spam_score_block', :spam_block_level, :email_address, :client_id, 0)");
			
			$query->bindParam(":spam_block_level", $SpamBlockLevel);
			$query->bindParam(":email_address", $EmailAddress);
			$query->bindParam(":client_id", $ClientID);
			
			$query->execute();
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Email.php -> AddSpamBlockLevel(); Error = ".$e);
		}
		
		return 1;
	}

	function DeleteSpamAssassin($ClientID, $Role, $EmailAddress)
	{
			
		file_put_contents($_SERVER["DOCUMENT_ROOT"]."/nm/".$EmailAddress.".delete_spamassassin", "local_part=".substr($EmailAddress, 0, strpos($EmailAddress, "@"))."\r\ndomain=".substr($EmailAddress, strpos($EmailAddress, "@") + 1)."\r\n");

		try
		{
			
			if($Role == 'admin')
			{
				$query = $this->DatabaseConnection->prepare("UPDATE email_options SET deleted = 1 WHERE extra1 = :email_address AND option_name LIKE '%spam%'");
				
				$query->bindParam(":email_address", $EmailAddress);
			}
			if($Role == 'email')
			{
				$ClientID = $this->GetEmailOwner($ClientID);

				$query = $this->DatabaseConnection->prepare("UPDATE email_options SET deleted = 1 WHERE extra2 = :client_id AND extra1 = :email_address AND option_name LIKE '%spam%'");
		
				$query->bindParam(":client_id", $ClientID);
				$query->bindParam(":email_address", $EmailAddress);
			}
			else
			{
				$query = $this->DatabaseConnection->prepare("UPDATE email_options SET deleted = 1 WHERE extra2 = :client_id AND extra1 = :email_address AND option_name LIKE '%spam%'");
				
				$query->bindParam(":client_id", $ClientID);
				$query->bindParam(":email_address", $EmailAddress);
			}
			
			$query->execute();
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Email.php -> DeleteSpamAssassin(); Error = ".$e);
		}
	
	
		return 1;
	}


	function GetModifySpamSubject($EmailAddress)
	{

		try
		{
			$query = $this->DatabaseConnection->prepare("SELECT option_value FROM email_options WHERE extra1 = :email_address AND option_name = 'use_spam_subject' AND deleted = 0");
			
			$query->bindParam(":email_address", $EmailAddress);
			
			$query->execute();
	
			if($result = $query->fetch(PDO::FETCH_ASSOC))
			{
				return $result["option_value"];
			}
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Email.php -> GetModifySpamSubject(); Error = ".$e);
		}

		
		return 0;
		
	}





	function GetSpamSubjectModifier($EmailAddress)
	{
		try
		{
			$query = $this->DatabaseConnection->prepare("SELECT option_value FROM email_options WHERE extra1 = :email_address AND option_name = 'spam_subject_modifier' AND deleted = 0");
			
			$query->bindParam(":email_address", $EmailAddress);
			
			$query->execute();
	
			if($result = $query->fetch(PDO::FETCH_ASSOC))
			{
				return $result["option_value"];
			}
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Email.php -> GetSpamSubjectModifier(); Error = ".$e);
		}	
		
		
		return $this->GetGlobalSpamSubjectModifier();
		
	}


	function GetSpamWarnLevel($EmailAddress)
	{

		try
		{
			$query = $this->DatabaseConnection->prepare("SELECT option_value FROM email_options WHERE extra1 = :email_address AND option_name = 'spam_score_warn' AND deleted = 0");
			
			$query->bindParam(":email_address", $EmailAddress);
			
			$query->execute();
	
			if($result = $query->fetch(PDO::FETCH_ASSOC))
			{
				return $result["option_value"];
			}
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Email.php -> GetSpamWarnLevel(); Error = ".$e);
		}
				
		return $this->GetGlobalSpamWarnLevel();
		
	}


	function GetSpamBlockLevel($EmailAddress)
	{

		try
		{
			$query = $this->DatabaseConnection->prepare("SELECT option_value FROM email_options WHERE extra1 = :email_address AND option_name = 'spam_score_block' AND deleted = 0");
			
			$query->bindParam(":email_address", $EmailAddress);
			
			$query->execute();
	
			if($result = $query->fetch(PDO::FETCH_ASSOC))
			{
				return $result["option_value"];
			}
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Email.php -> GetSpamBlockLevel(); Error = ".$e);
		}
		
		return $this->GetGlobalSpamBlockLevel();
		
	}




        function GetGlobalSpamSubjectModifier()
        {
		try
		{
			$query = $this->DatabaseConnection->prepare("SELECT option_value FROM email_options WHERE extra1 = 'global' AND option_name = 'spam_subject_modifier' AND deleted = 0");
			
		
			$query->execute();
	
			if($result = $query->fetch(PDO::FETCH_ASSOC))
			{
				return $result["option_value"];
			}
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Email.php -> GetGlobalSpamSubjectModifier(); Error = ".$e);
		}
		
		return "***spam***";
                
        }
	
	


        function GetGlobalSpamBlockLevel()
        {

		try
		{
			$query = $this->DatabaseConnection->prepare("SELECT option_value FROM email_options WHERE extra1 = 'global' AND option_name = 'spam_score_block' AND deleted = 0");
			
			$query->execute();
	
			if($result = $query->fetch(PDO::FETCH_ASSOC))
			{
				return $result["option_value"];
			}
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Email.php -> GetGlobalSpamBlockLevel(); Error = ".$e);
		}	
                
		return 55;
                
        }
	
	
	
        function GetGlobalSpamWarnLevel()
        {

		try
		{
			$query = $this->DatabaseConnection->prepare("SELECT option_value FROM email_options WHERE extra1 = 'global' AND option_name = 'spam_score_warn' AND deleted = 0");
			
			$query->execute();
	
			if($result = $query->fetch(PDO::FETCH_ASSOC))
			{
				return $result["option_value"];
			}
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Email.php -> GetGlobalSpamWarnLevel(); Error = ".$e);
		}
                
		return 50;
                
        }



        function GetUnusedSpamAssassinEmailAddress(&$EmailListArray, &$EmailListCount, $ClientID, $Role)
        {
                $EmailListArray = array();
		$EmailListCount = 0;
                $ArrayCount = 0;

 
 		try
		{
		
			if($Role == 'admin')
			{
				$query = $this->DatabaseConnection->prepare("SELECT CONCAT(mailboxes.local_part, '@', domains.fqdn) AS email_address FROM mailboxes, domains WHERE mailboxes.domain_id = domains.id AND mailboxes.active = 1 AND domains.deleted = 0 AND CONCAT(mailboxes.local_part, '@', domains.fqdn) NOT IN (SELECT extra1 FROM email_options WHERE deleted = 0 AND option_name LIKE '%spam%')");
			}
			else
			{
				$query = $this->DatabaseConnection->prepare("SELECT CONCAT(mailboxes.local_part, '@', domains.fqdn) AS email_address FROM mailboxes, domains WHERE mailboxes.domain_id = domains.id AND mailboxes.active = 1 AND domains.deleted = 0 AND CONCAT(mailboxes.local_part, '@', domains.fqdn) NOT IN (SELECT extra1 FROM email_options WHERE option_name LIKE '%spam%' AND deleted = 0) AND domains.client_id = :client_id");
				
				$query->bindParam(":client_id", $ClientID);
			}
			
			
			$query->execute();
	
			while($result = $query->fetch(PDO::FETCH_ASSOC))
			{
				$EmailListArray[$EmailListCount++]["EmailAddress"] = $result["email_address"];
			}
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Email.php -> GetUnusedSpamAssassinEmailAddress(); Error = ".$e);
		}	
		

        }





        function GetSpamAssassinSummaryList(&$SpamAssassinArray, &$ArrayCount, $ClientID, $Role)
        {
                $SpamAssassinArray = array();

		$ArrayCount = 0;
 
 		try
		{
		
			if($Role == 'admin')
			{
				$query = $this->DatabaseConnection->prepare("SELECT DISTINCT(extra1) FROM email_options WHERE option_name LIKE '%spam%' AND extra1 != 'global' AND deleted = 0");
			}
			else if( $Role == "email") 
			{
                        	$userName = "";
                               	$localPart = "";
                               	$domainName = "";
                               	$domainId = 0;


                                $this->GetEmailInfo($ClientID, $userName, $localPart, $domainName, $domainId);
				$ClientID = $this->GetEmailOwner($ClientID);

				$query = $this->DatabaseConnection->prepare("SELECT DISTINCT(extra1) FROM email_options WHERE option_name LIKE '%spam%' AND extra2 = :client_id AND extra1 = '".$localPart."@".$domainName."' AND deleted = 0");
			

				$query->bindParam(":client_id", $ClientID);
			}
			else
			{
				$query = $this->DatabaseConnection->prepare("SELECT DISTINCT(extra1) FROM email_options WHERE option_name LIKE '%spam%' AND extra2 = :client_id AND extra1 != 'global' AND deleted = 0");
				
				$query->bindParam(":client_id", $ClientID);
			}
			
			
			$query->execute();
	
			while($result = $query->fetch(PDO::FETCH_ASSOC))
			{
				$SpamAssassinArray[$ArrayCount]["WarnLevel"] = $this->GetSpamWarnLevel($result["extra1"]);
				$SpamAssassinArray[$ArrayCount]["BlockLevel"] = $this->GetSpamBlockLevel($result["extra1"]);
				$SpamAssassinArray[$ArrayCount]["SpamSubjectModifier"] = $this->GetSpamSubjectModifier($result["extra1"]);
				$SpamAssassinArray[$ArrayCount]["UseSpamSubjectModifier"] = $this->GetModifySpamSubject($result["extra1"]);
				$SpamAssassinArray[$ArrayCount++]["EmailAddress"] = $result["extra1"];
			}
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Email.php -> GetSpamAssassinSummaryList(); Error = ".$e);
		}			


        }






        function GetSpamAssassinList(&$SpamAssassinArray, &$ArrayCount, $ClientID, $Role)
        {
                $SpamAssassinArray = array();
		$ArrayCount = 0;
 
 		try
		{
		
			if($Role == 'admin')
			{
				$query = $this->DatabaseConnection->prepare("SELECT * FROM email_options WHERE option_name LIKE '%spam%' AND extra1 != 'global' AND deleted = 0");
			}
			else
			{
				$query = $this->DatabaseConnection->prepare("SELECT * FROM email_options WHERE option_name LIKE '%spam%' AND extra2 = :client_id AND extra1 != 'global' AND deleted = 0");
				
				$query->bindParam(":client_id", $ClientID);
			}
			
			
			$query->execute();
	
			while($result = $query->fetch(PDO::FETCH_ASSOC))
			{
				$SpamAssassinArray[$ArrayCount]["ID"] = $result["id"];
				$SpamAssassinArray[$ArrayCount]["Option"] = $result["option_name"];
				$SpamAssassinArray[$ArrayCount]["Value"] = $result["option_value"];
				$SpamAssassinArray[$ArrayCount]["EmailAddress"] = $result["extra1"];
				$SpamAssassinArray[$ArrayCount++]["ClientID"] = $result["extra2"];
			}
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Email.php -> GetSpamAssassinList(); Error = ".$e);
		}			



        }





	function DeleteEmailAccountOptions($ClientID, $Role, $EmailAddress)
	{
		
		try
		{

			if($Role == 'admin')
			{
			
				$query = $this->DatabaseConnection->prepare("UPDATE email_options SET deleted = 1 WHERE extra1 = :email_address");
				$query->bindParam(":email_address", $EmailAddress);
				
			}
			else
			{
			
				$query = $this->DatabaseConnection->prepare("UPDATE email_options SET deleted = 1 WHERE extra2 = :client_id AND extra1 = :email_address");
				$query->bindParam(":client_id", $ClientID);
				$query->bindParam(":email_address", $EmailAddress);
				
			}
		
			$query->execute();
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Email.php -> DeleteEmailAccountOptions(); Error = ".$e);
		}		
	
		return 1;
	}






	function AddAutoReplyStanza($ClientID, $DomainID, $FilePointer)
	{

		try
		{
			$query = $this->DatabaseConnection->prepare("SELECT local_part, domain_user_name, fqdn, subject, body, frequency FROM vacations, mailboxes, domains WHERE vacations.deleted = 0 AND mailboxes.active = 1 AND domains.deleted = 0 AND vacations.client_id = :client_id and mailboxes.domain_id = :domain_id AND vacations.mailbox_id = mailboxes.id AND mailboxes.domain_id = domains.id ORDER by domain_user_name;");
			
			$query->bindParam(":client_id", $ClientID);
			$query->bindParam(":domain_id", $DomainID);
			
			$query->execute();
	
			while($result = $query->fetch(PDO::FETCH_ASSOC))
			{
				$Text = "if \$local_part is \"".$result["local_part"]."\"\n";
				$Text = $Text."then\n";
					$Text = $Text."\t"."if (\$h_subject: does not contain \"SPAM?\" and personal) then\n";

						$Text = $Text."\n\t\t"."mail\n";

						$Text = $Text."\n\t\t"."expand file /home/".$result["domain_user_name"]."/home/".$result["domain_user_name"]."/mail/".$result["fqdn"]."/".$result["local_part"]."/.vacation.msg\n";
						$Text = $Text."\t\t"."once /home/".$result["domain_user_name"]."/home/".$result["domain_user_name"]."/mail/".$result["fqdn"]."/".$result["local_part"]."/.vacation.db\n";
						$Text = $Text."\t\t"."log /home/".$result["domain_user_name"]."/home/".$result["domain_user_name"]."/mail/".$result["fqdn"]."/".$result["local_part"]."/.vacation.log\n";

						$Text = $Text."\n\t\t"."once_repeat ".$result["frequency"]."\n";
						$Text = $Text."\t\t"."to \$reply_address\n";
						$Text = $Text."\t\t"."from ".$result["local_part"]."@".$result["fqdn"]."\n";
						$Text = $Text."\t\t"."subject \"".$result["subject"]."\"\n";
					$Text = $Text."\t"."endif\n";
				$Text = $Text."endif\n";
			
				$FileName = $_SERVER["DOCUMENT_ROOT"]."/nm/_home_". $result["domain_user_name"]."_home_". $result["domain_user_name"]."_mail_".$result["fqdn"]."_".$result["local_part"]."_.vacation.autoreply";

				$f = fopen($FileName, "w");
				fwrite($f, $result["body"]);
				fclose($f);

				fwrite($FilePointer, $Text);
				fwrite($FilePointer, "\n");
			}

	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Email.php -> AddAutoReplyStanza(); Error = ".$e);
		}			

		
		
	}

        function RecreateUserForwardFile($ClientID)
        {
			$oLog = new Log();

			$oLog->WriteLog("debug", "In RecreateUserForwardFile, calling GenerateUserForwardFile(".$ClientID.")");
			$this->GenerateUserForwardFile($ClientID);		
        }

	function GenerateUserForwardFile($ClientID)
	{

		try {
			$query = $this->DatabaseConnection->prepare("SELECT id, fqdn, UserName FROM domains WHERE deleted = 0 AND client_id = :client_id");
			$query->bindParam(":client_id", $ClientID);
			$query->execute();
	
			while($result = $query->fetch(PDO::FETCH_ASSOC)) {

				$FileName = $_SERVER["DOCUMENT_ROOT"]."/nm/_home_". $result["UserName"]."_home_". $result["UserName"]."_mail_".$result["fqdn"]."_.singleforward";

				$f = fopen($FileName, "w");
				fwrite($f, "# Exim filter\n\n\n");
				
				$this->AddBlackListsToForwardFile($ClientID, $result["fqdn"], $f);
				
				$this->AddEmailForwarding($ClientID, $result["id"], $f);

				$this->AddAutoReplyStanza($ClientID, $result["id"], $f);
			
				fclose($f);
		
			}
	
		} catch(PDOException $e) {
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Email.php -> GenerateUserForwardFile(); Error = ".$e);
		}			


	}







	function GetSeenUnseenText($DomainID, $LocalPart)
	{
		try
		{
			$query = $this->DatabaseConnection->prepare("SELECT id FROM mailboxes WHERE domain_id = :domain_id AND local_part = :local_part AND active = 1");
			
			$query->bindParam(":domain_id", $DomainID);
			$query->bindParam(":local_part", $LocalPart);
			
			$query->execute();
	
			if($result = $query->fetch(PDO::FETCH_ASSOC))
			{
				return "unseen";
			}
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Email.php -> GetSeenUnseenText(); Error = ".$e);
		}			

		
		return "seen";

	}

	function GetLocalPartEmailForwardingText($ClientID, $DomainID, $LocalPart)
	{


		$Text = "";
		
		try
		{
			$query = $this->DatabaseConnection->prepare("SELECT destination_address FROM email_forwarding, domains WHERE email_forwarding.deleted = 0 AND domains.deleted = 0 AND email_forwarding.client_id = :client_id AND email_forwarding.domain_id = :domain_id and email_forwarding.source_local_part = :local_part AND email_forwarding.domain_id = domains.id;");
			
			$query->bindParam(":client_id", $ClientID);
			$query->bindParam(":domain_id", $DomainID);
			$query->bindParam(":local_part", $LocalPart);
			
			$query->execute();
	
			while($result = $query->fetch(PDO::FETCH_ASSOC))
			{
				$Text = $Text.$this->GetSeenUnseenText($DomainID, $LocalPart)." deliver \"".$result["destination_address"]."\"\n";
			}
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Email.php -> GetLocalPartEmailForwardingText(); Error = ".$e);
		}			


		if(strlen($Text) > 0)
		{
			return "if \$local_part is \"".$LocalPart."\"\nthen\n".$Text."endif\n";
		}
		
		return "";	

	}

	function AddEmailForwarding($ClientID, $DomainID, $FilePointer)
	{
	
		try
		{
			$query = $this->DatabaseConnection->prepare("SELECT DISTINCT(source_local_part) FROM email_forwarding WHERE domain_id = :domain_id AND deleted = 0;");
			
			$query->bindParam(":domain_id", $DomainID);
			
			$query->execute();
	
			while($result = $query->fetch(PDO::FETCH_ASSOC))
			{
				fwrite($FilePointer, $this->GetLocalPartEmailForwardingText($ClientID, $DomainID, $result["source_local_part"]));
				fwrite($FilePointer, "\n\n");
			}
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Email.php -> AddEmailForwarding(); Error = ".$e);
		}			
	
	}



        function AddEmail($LocalPart, $DomainID, $Password, $ClientID)
        {

			$UserName = "";

			$oUser = new User();
			$oPackage = new Package();
			$oDomain = new Domain();

			$DomainInfoArray = array();

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
			$oDomain->GetDomainInfo($DomainID, $random, $DomainInfoArray, $nonce);

			$DomainUserName = $DomainInfoArray["UserName"];
			$EmailAllowance = $oPackage->GetPackageAllowance("Emails", $DomainInfoArray["PackageID"]);
			$EmailUsage = $oPackage->GetEmailUsage($DomainID);

			if( (($EmailAllowance - $EmailUsage) < 1) && ($oAccount->Role != "admin") ) {
				// No More Mails Left
				return -1;
			}


			$lastInsertId = 0;
			
			try {
				$query = $this->DatabaseConnection->prepare("INSERT INTO mailboxes VALUES (0, :domain_id, :domain_user_name, :local_part, :password, '', 1, :create_date, :modify_date)");
				
				$Password = md5($Password);
				$Date = date('Y-m-d H:i:s');
				
				$query->bindParam(":domain_id", $DomainID);
				$query->bindParam(":domain_user_name", $DomainUserName);
				$query->bindParam(":local_part", $LocalPart);
				$query->bindParam(":password", $Password);
				
				$query->bindParam(":create_date", $Date);
				$query->bindParam(":modify_date", $Date);
				
				$query->execute();
				$lastInsertId = $this->DatabaseConnection->lastInsertId();

			} catch(PDOException $e) {
				$oLog = new Log();
				$oLog->WriteLog("error", "/class.Email.php -> AddEmail(); Error = ".$e);
			}			


			$this->GetDomainInfo($DomainID, $UserName, $DomainName);

			$this->MakeNEAFile($LocalPart, $DomainName, $UserName);
			$this->RecreateUserForwardFile($ClientID);
			return $lastInsertId;

        }




        function AddEmailPlainPassword($LocalPart, $DomainID, $Password, $ClientID)
        {


                $oUser = new User();
                $oPackage = new Package();
                $oDomain = new Domain();

				$DomainInfoArray = array();
				
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
                $oDomain->GetDomainInfo($DomainID, $random, $DomainInfoArray, $nonce);

                $DomainUserName = $DomainInfoArray["UserName"];
                $EmailAllowance = $oPackage->GetPackageAllowance("Emails", $DomainInfoArray["PackageID"]);
                $EmailUsage = $oPackage->GetEmailUsage($DomainID);

		$lastInsertId = 0;
		
		try
		{
			$query = $this->DatabaseConnection->prepare("INSERT INTO mailboxes VALUES (0, :domain_id, :domain_user_name, :local_part, :password, '', 1, :create_date, :modify_date);");
			
			$Date = date('Y-m-d H:i:s');
			
			$query->bindParam(":domain_id", $DomainID);
			$query->bindParam(":domain_user_name", $DomainUserName);
			$query->bindParam(":local_part", $LocalPart);
			$query->bindParam(":password", $Password);
			
			$query->bindParam(":create_date", $Date);
			$query->bindParam(":modify_date", $Date);
			
			$query->execute();

			$lastInsertId = $this->DatabaseConnection->lastInsertId();
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Email.php -> AddEmail(); Error = ".$e);
		}			



                $this->GetDomainInfo($DomainID, $UserName, $DomainName);

                $this->MakeNEAFile($LocalPart, $DomainName, $UserName);
                $this->RecreateUserForwardFile($ClientID);
                return $lastInsertId;

        }

        function GetDomainIDFromEmailID($ID)
        {
		try
		{
			$query = $this->DatabaseConnection->prepare("SELECT domain_id FROM mailboxes WHERE id = :id AND active = 1");
			$query->bindParam(":id", $ID);
			$query->execute();
	
			if($result = $query->fetch(PDO::FETCH_ASSOC))
			{
				return $result["domain_id"];
			}
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Email.php -> GetDomainIDFromEmailID(); Error = ".$e);
		}			



                return -1;
        }
   


        function DeleteEmailOptions($EmailOption, $DomainName = "")
        {

		try
		{
			

			if(trim($DomainName) == "")
			{
				$query = $this->DatabaseConnection->prepare("UPDATE email_options SET deleted = 1 WHERE option_name = :email_option AND extra1 is NULL;");
				$query->bindParam(":email_option", $EmailOption);
			}
			else
			{
				$query = $this->DatabaseConnection->prepare("UPDATE email_options SET deleted = 1 WHERE option_name = :email_option AND extra1 = :domain_name");
				$query->bindParam(":email_option", $EmailOption);
				$query->bindParam(":domain_name", $DomainName);
			}
			
			$query->execute();
	

		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Email.php -> DeleteEmailOptions(); Error = ".$e);
		}			



        }


        function SaveEmailOptions($EmailOption, $OptionValue, $DomainName = "")
        {

		$this->DeleteEmailOptions($EmailOption, $DomainName);
		try
		{
		

			if(trim($DomainName) == "")
			{
				$query = $this->DatabaseConnection->prepare("INSERT INTO  email_options VALUES (0, :email_option, :option_value, NULL, NULL, 0)");
				
				$query->bindParam(":email_option", $EmailOption);
				$query->bindParam(":option_value", $OptionValue);
			
			}
			else
			{
				$query = $this->DatabaseConnection->prepare("INSERT INTO  email_options VALUES (0, :email_option, :option_value, :domain_name, NULL, 0)");
				
				$query->bindParam(":email_option", $EmailOption);
				$query->bindParam(":option_value", $OptionValue);
				$query->bindParam(":domain_name", $DomainName);
			
			}
		
			
			$query->execute();

		} catch(PDOException $e) {
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Email.php -> SaveEmailOptions(); Error = ".$e);
		}	

        }

	function GetEmailOptions($EmailOption, $DomainName = "")
        {

		try
		{

			if(trim($DomainName) == "")
			{
				$query = $this->DatabaseConnection->prepare("SELECT option_value FROM email_options WHERE extra1 is NULL AND option_name = :email_option AND deleted = 0");
				$query->bindParam(":email_option", $EmailOption);
			
			}
			else
			{
				$query = $this->DatabaseConnection->prepare("SELECT option_value FROM email_options WHERE extra1 = :domain_name AND option_name = :email_option AND deleted = 0");
				
				$DomainName = trim($DomainName);
				
				$query->bindParam(":domain_name", $DomainName);
				$query->bindParam(":email_option", $EmailOption);
			
			}
		
		
			$query->execute();
	
			if($result = $query->fetch(PDO::FETCH_ASSOC))
			{
				return $result["option_value"];
			}
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Email.php -> GetEmailOptions(); Error = ".$e);
		}			



		if(trim($DomainName) != "")
		{
			// Return the general limits
			return $this->GetEmailOptions($EmailOption, "");
		}
                
		switch($EmailOption)
		{
			case "max_per_hour":
			{
				return 300;
			}
		
			case "max_recipients":
			{
				return 50;
			}
		}

		return 20;
        }
   
   

        function GetDomainIDFromSingleForwardID($ID)
        {

		try
		{
			$query = $this->DatabaseConnection->prepare("SELECT domain_id FROM email_forwarding WHERE id = :id AND deleted = 0;");
			
			$query->bindParam(":id", $ID);
			
			$query->execute();
	
			if($result = $query->fetch(PDO::FETCH_ASSOC))
			{
				return $result["domain_id"];
			}
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Email.php -> GetDomainIDFromSingleForwardID(); Error = ".$e);
		}	

                return -1;
        }
   

        function logInEmailAccount($emailAddress, $password)
        {
		
		$_SESSION["email_client_id"] = 0;

		$localPart = substr($emailAddress, 0, strpos($emailAddress, "@"));
		$domain = substr($emailAddress, strpos($emailAddress, "@") + 1);
		$password = md5($password);				

		try
		{
			$query = $this->DatabaseConnection->prepare("SELECT mailboxes.id FROM mailboxes,domains WHERE mailboxes.local_part=:local_part AND mailboxes.password=:password AND mailboxes.active=1 AND mailboxes.domain_id=domains.id AND domains.fqdn=:domain AND domains.active=1 AND domains.suspended=0;");
			
			$query->bindParam(":local_part", $localPart);
			$query->bindParam(":password", $password);
			$query->bindParam(":domain", $domain);
			
			$query->execute();
	
			if($result = $query->fetch(PDO::FETCH_ASSOC))
			{
				$_SESSION["email_client_id"] = $result["id"];
				return $result["id"];
			}
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Email.php -> logInEmailAccount(); Error = ".$e);
		}	
		


                return false;
        }
   
	function Logout()
	{
		$_SESSION["email_client_id"] = 0;
        	$this->loggedInEmailId = false;
        	$this->loggedInEmailDomainUserName = "";
        	$this->loggedInEmailDomain = "";
        	$this->loggedInEmailLocalPart = 0;
	}


        function GetEmailInfo($id, &$user_name, &$local_part, &$domain_name, &$domainId)
        {

		try
		{
			$query = $this->DatabaseConnection->prepare("SELECT local_part, UserName, fqdn, mailboxes.domain_id AS domain_id  FROM mailboxes, domains WHERE domains.deleted = 0 AND domains.active = 1 AND mailboxes.active = 1 AND mailboxes.id = :id AND mailboxes.domain_id = domains.id;");
			
			$query->bindParam(":id", $id);
			
			$query->execute();
	
			if($result = $query->fetch(PDO::FETCH_ASSOC))
			{
				$domain_name = $result["fqdn"];
				$user_name = $result["UserName"];
				$local_part = $result["local_part"];
				$domainId = $result["domain_id"];
				return 1;
			}
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Email.php -> GetEmailInfo(); Error = ".$e);
		}	
		


                return 0;
        }
   

        function GetDomainInfo($ID, &$UserName, &$DomainName)
        {
		try
		{
			$query = $this->DatabaseConnection->prepare("SELECT fqdn, UserName FROM domains WHERE id = :id AND deleted = 0;");
			$query->bindParam(":id", $ID);
			$query->execute();
	
			if($result = $query->fetch(PDO::FETCH_ASSOC))
			{
				$DomainName = $result["fqdn"];
				$UserName = $result["UserName"];
				return 1;
			}
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Email.php -> GetDomainInfo(); Error = ".$e);
		}			


                return 0;
        }
   
	function SingleForwardExists($LocalPart, $DomainID, $ForwardTo) 
	{
		
		try
		{
			$query = $this->DatabaseConnection->prepare("SELECT id FROM email_forwarding WHERE source_local_part = :local_part AND domain_id = :domain_id AND destination_address = :forward_to AND deleted = 0;");
			
			$query->bindParam(":local_part", $LocalPart);
			$query->bindParam(":domain_id", $DomainID);
			$query->bindParam(":forward_to", $ForwardTo);
			
			$query->execute();
	
			if($result = $query->fetch(PDO::FETCH_ASSOC))
			{
				return $result["id"];
			}
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Email.php -> SingleForwardExists(); Error = ".$e);
		}			

		
		return -1;
		
	}
	
	
	
		
	function EmailExists($LocalPart, $DomainID) 
	{

		try
		{
			$query = $this->DatabaseConnection->prepare("SELECT id FROM mailboxes WHERE local_part = :local_part AND domain_id = :domain_id AND Active = 1;");
			
			$query->bindParam(":local_part", $LocalPart);
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
			$oLog->WriteLog("error", "/class.Email.php -> EmailExists(); Error = ".$e);
		}
		
	
		return -1;
	
	}

        function GetEmailTraceListTotalCount($SearchFor, $SearchTerm, $UserName, $Role)
        {

		$SearchTerm = strtolower($SearchTerm);


		$query = "SELECT COUNT(*) as count FROM email_trace ";

		$Where = "WHERE ";
		$And = "";
                if($Role != 'admin')
                {
       			$query = $query.$Where." (from_user = '".$UserName."' OR to_user = '".$UserName."') ";
			$And = "AND ";
			$Where = "";
		}

		if(trim($SearchTerm) != "")
		{
			$query = $query.$Where;
			if($SearchFor == "both")
			{
				if(strstr($SearchTerm, ","))
				{
					$SearchTerm = str_replace(",", "|", $SearchTerm);
					$query = $query.$And." LOWER(from_address) RLIKE '".$SearchTerm."' OR LOWER(for_address) RLIKE '".$SearchTerm."'";
				}
				else
				{
					$query = $query.$And." (LOWER(from_address) like '%".$SearchTerm."%' OR LOWER(for_address) like '%".$SearchTerm."%')";
				}
			}
			else if($SearchFor == "sender")
			{
				if(strstr($SearchTerm, ","))
				{
					$SearchTerm = str_replace(",", "|", $SearchTerm);
					$query = $query.$And." LOWER(from_address) RLIKE '".$SearchTerm."'";
				}
				else
				{
					$query = $query.$And." LOWER(from_address) like '%".$SearchTerm."%'";
				}
			}
			else if($SearchFor == "receiver")
			{
				if(strstr($SearchTerm, ","))
				{
					$SearchTerm = str_replace(",", "|", $SearchTerm);
					$query = $query.$And." LOWER(for_address) RLIKE '".$SearchTerm."'";
				}
				else
				{
					$query = $query.$And." LOWER(for_address) like '%".$SearchTerm."%'";
				}
			}
			else if($SearchFor == "subject")
			{
				if(strstr($SearchTerm, ","))
				{
					$SearchTerm = str_replace(",", "|", $SearchTerm);
					$query = $query.$And." LOWER(subject) RLIKE '".$SearchTerm."'";
				}
				else
				{
					$query = $query.$And." LOWER(subject) like '%".$SearchTerm."%'";
				}
			}
			else if($SearchFor == "id")
			{
				if(strstr($SearchTerm, ","))
				{
					$SearchTerm = str_replace(",", "|", $SearchTerm);
					$query = $query.$And." LOWER(mail_queue_id) RLIKE '".$SearchTerm."'";
				}
				else
				{
					$query = $query.$And." LOWER(mail_queue_id) like '%".$SearchTerm."%'";
				}
			}
		}

		$query = $query." ORDER BY start_date DESC;";

    		try
		{
			$query = $this->DatabaseConnection->prepare($query);
			
			$query->bindParam(":local_part", $LocalPart);
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
			$oLog->WriteLog("error", "/class.Email.php -> GetEmailTraceListTotalCount(); Error = ".$e);
		}
   

		return 0;

        }

        function GetEmailTraceList(&$EmailTraceArray, &$ArrayCount, $SearchFor, $SearchTerm, $UserName, $Role, $Start, $QuerySize, &$TotalResults, $SortBy, $SortOrder)
        {

		if($SortBy < 1 || $SortBy > 6)
		{
			$SortBy = 1;
		}

	
		$SortByColumn = "start_date";
		switch($SortBy)
		{
			case 1:
			{
				$SortByColumn = "start_date";
				break;
			}
			case 2:
			{
				$SortByColumn = "from_address";
				break;
			}
			case 3:
			{
				$SortByColumn = "for_address";
				break;
			}
			case 4:
			{
				$SortByColumn = "subject";
				break;
			}

			case 5:
			{
				$SortByColumn = "confirmation";
				break;
			}

			case 6:
			{
				$SortByColumn = "status";
				break;
			}


		}

		$TotalResults = $this->GetEmailTraceListTotalCount($SearchFor, $SearchTerm, $UserName, $Role);

                $EmailTraceArray = array();

		$SearchTerm = strtolower($SearchTerm);

		$query = "SELECT * FROM email_trace ";

		$Where = "WHERE ";
		$And = "";
                if($Role != 'admin')
                {
       			$query = $query.$Where." (from_user = '".$UserName."' OR to_user = '".$UserName."') ";
			$And = "AND ";
			$Where = "";
		}

		if(trim($SearchTerm) != "")
		{
			$query = $query.$Where;
			if($SearchFor == "both")
			{
				if(strstr($SearchTerm, ","))
				{
					$SearchTerm = str_replace(",", "|", $SearchTerm);
					$query = $query.$And." LOWER(from_address) RLIKE '".$SearchTerm."' OR LOWER(for_address) RLIKE '".$SearchTerm."'";
				}
				else
				{
					$query = $query.$And." (LOWER(from_address) like '%".$SearchTerm."%' OR LOWER(for_address) like '%".$SearchTerm."%')";
				}
			}
			else if($SearchFor == "sender")
			{
				if(strstr($SearchTerm, ","))
				{
					$SearchTerm = str_replace(",", "|", $SearchTerm);
					$query = $query.$And." LOWER(from_address) RLIKE '".$SearchTerm."'";
				}
				else
				{
					$query = $query.$And." LOWER(from_address) like '%".$SearchTerm."%'";
				}
			}
			else if($SearchFor == "receiver")
			{
				if(strstr($SearchTerm, ","))
				{
					$SearchTerm = str_replace(",", "|", $SearchTerm);
					$query = $query.$And." LOWER(for_address) RLIKE '".$SearchTerm."'";
				}
				else
				{
					$query = $query.$And." LOWER(for_address) like '%".$SearchTerm."%'";
				}
			}
			else if($SearchFor == "subject")
			{
				if(strstr($SearchTerm, ","))
				{
					$SearchTerm = str_replace(",", "|", $SearchTerm);
					$query = $query.$And." LOWER(subject) RLIKE '".$SearchTerm."'";
				}
				else
				{
					$query = $query.$And." LOWER(subject) like '%".$SearchTerm."%'";
				}
			}
			else if($SearchFor == "id")
			{
				if(strstr($SearchTerm, ","))
				{
					$SearchTerm = str_replace(",", "|", $SearchTerm);
					$query = $query.$And." LOWER(mail_queue_id) RLIKE '".$SearchTerm."'";
				}
				else
				{
					$query = $query.$And." LOWER(mail_queue_id) like '%".$SearchTerm."%'";
				}
			}
		}

		$query = $query." ORDER BY ".$SortByColumn." ".$SortOrder." LIMIT ".$Start.", ".$QuerySize.";";


                $ArrayCount = 0;



		try
		{
			$query = $this->DatabaseConnection->prepare($query);
		
			$query->execute();
	
			while($result = $query->fetch(PDO::FETCH_ASSOC))
			{
				$EmailTraceArray[$ArrayCount]["ID"] = $result["id"];
				$EmailTraceArray[$ArrayCount]["MailQueueID"] = $result["mail_queue_id"];
				$EmailTraceArray[$ArrayCount]["ToUser"] = $result["to_user"];
				$EmailTraceArray[$ArrayCount]["FromUser"] = $result["from_user"];
				$EmailTraceArray[$ArrayCount]["StartDate"] = $result["start_date"];
				$EmailTraceArray[$ArrayCount]["UpdateDate"] = $result["update_date"];
				$EmailTraceArray[$ArrayCount]["SenderHost"] = $result["sender_host"];
				$EmailTraceArray[$ArrayCount]["ReceiverHost"] = $result["receiver_host"];
				$EmailTraceArray[$ArrayCount]["Protocol"] = $result["protocol"];
				$EmailTraceArray[$ArrayCount]["AuthType"] = $result["auth_type"];
				$EmailTraceArray[$ArrayCount]["SenderSize"] = $result["sender_size"];
				$EmailTraceArray[$ArrayCount]["ReceiverSize"] = $result["receiver_size"];
				$EmailTraceArray[$ArrayCount]["Subject"] = $result["subject"];
				$EmailTraceArray[$ArrayCount]["FromAddress"] = $result["from_address"];
				$EmailTraceArray[$ArrayCount]["ToAddress"] = $result["for_address"];
				$EmailTraceArray[$ArrayCount]["ReturnPath"] = $result["return_path"];
				$EmailTraceArray[$ArrayCount]["Transport"] = $result["transport"];
				$EmailTraceArray[$ArrayCount]["Router"] = $result["router"];
				$EmailTraceArray[$ArrayCount]["QueueTime"] = $result["queue_time"];

				if( ($result["mail_queue_id"] == "") && (substr($result["status"], 0, 5) == "DNSBL") )
				{
					$EmailTraceArray[$ArrayCount]["Confirmation"] = substr($result["status"], 8);
					$EmailTraceArray[$ArrayCount++]["Status"] = "SPAM";
				}
				else
				{
					$EmailTraceArray[$ArrayCount]["Confirmation"] = $result["confirmation"];
					$EmailTraceArray[$ArrayCount++]["Status"] = $result["status"];
				}
			}

	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Email.php -> GetEmailTraceList(); Error = ".$e);
		}		
		
		
		

        }

	function DeleteStaleEmailTrace($DeleteBeforeDate)
	{

		try
		{
		
			$query = $this->DatabaseConnection->prepare("DELETE FROM email_trace WHERE start_date < :delete_before_date");
			
			$query->bindParam(":delete_before_date", $DeleteBeforeDate);
			
			$query->execute();
			
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Email.php -> DeleteStaleEmailTrace(); Error = ".$e);
		}		
		
	}

	
        function GetEmailTraceDetail(&$EmailTraceArray, $ID, $UserName, $Role)
        {
                $EmailTraceArray = array();

		try
		{
		
			$query = "SELECT * FROM email_trace WHERE id = ".$ID;

			if($Role != 'admin')
			{
				$query = $query." AND (from_user = '".$UserName."' OR to_user = '".$UserName."') ";
			}
		
			$query = $this->DatabaseConnection->prepare($query);
		
			
			$query->execute();
	
			if($result = $query->fetch(PDO::FETCH_ASSOC))
			{

				$EmailTraceArray["ID"] = $result["id"];
				$EmailTraceArray["MailQueueID"] = $result["mail_queue_id"];
				$EmailTraceArray["ToUser"] = $result["to_user"];
				$EmailTraceArray["FromUser"] = $result["from_user"];
				$EmailTraceArray["StartDate"] = $result["start_date"];
				$EmailTraceArray["UpdateDate"] = $result["update_date"];
				$EmailTraceArray["SenderHost"] = $result["sender_host"];
				$EmailTraceArray["ReceiverHost"] = $result["receiver_host"];
				$EmailTraceArray["Protocol"] = $result["protocol"];
				$EmailTraceArray["AuthType"] = $result["auth_type"];
				$EmailTraceArray["SenderSize"] = $result["sender_size"];
				$EmailTraceArray["ReceiverSize"] = $result["receiver_size"];
				$EmailTraceArray["Subject"] = $result["subject"];
				$EmailTraceArray["FromAddress"] = $result["from_address"];
				$EmailTraceArray["ToAddress"] = $result["for_address"];
				$EmailTraceArray["ReturnPath"] = $result["return_path"];
				$EmailTraceArray["Transport"] = $result["transport"];
				$EmailTraceArray["Router"] = $result["router"];
				$EmailTraceArray["QueueTime"] = $result["queue_time"];

				if( ($result["mail_queue_id"] == "") && (substr($result["status"], 0, 5) == "DNSBL") )
				{
					$EmailTraceArray["Confirmation"] = substr($result["status"], 8);
					$EmailTraceArray["Status"] = "SPAM";
				}
				else
				{
					$EmailTraceArray["Confirmation"] = $result["confirmation"];
					$EmailTraceArray["Status"] = $result["status"];
				}

				return true;

			}
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Email.php -> GetEmailTraceDetail(); Error = ".$e);
		}		
		
		return false;

        }


	
        function GetAutoReplyList(&$AutoReplyArray, &$ArrayCount, $ClientID, $Role)
        {
                $AutoReplyArray = array();

                $ArrayCount = 0;


		try
		{

			if($Role == 'admin')
			{
				$query = $this->DatabaseConnection->prepare("SELECT * FROM vacations WHERE deleted = 0;");
			}
			else if($Role == 'email')
			{
				$query = $this->DatabaseConnection->prepare("SELECT * FROM vacations WHERE mailbox_id = :client_id AND deleted = 0");
				$query->bindParam(":client_id", $ClientID);
			
			}
			else
			{
				$query = $this->DatabaseConnection->prepare("SELECT * FROM vacations WHERE client_id = :client_id AND deleted = 0;");
				$query->bindParam(":client_id", $ClientID);
			}

	       
	       
			$query->execute();
	
			while($result = $query->fetch(PDO::FETCH_ASSOC))
			{
				$AutoReplyArray[$ArrayCount]["ID"] = $result["id"];
				$AutoReplyArray[$ArrayCount]["ClientID"] = $result["client_id"];
				$AutoReplyArray[$ArrayCount]["MailBoxID"] = $result["mailbox_id"];
				$AutoReplyArray[$ArrayCount]["Subject"] = $result["subject"];
				$AutoReplyArray[$ArrayCount]["Body"] = $result["body"];
				$AutoReplyArray[$ArrayCount]["StartDate"] = $result["start_date"];
				$AutoReplyArray[$ArrayCount]["EndDate"] = $result["end_date"];
				$AutoReplyArray[$ArrayCount]["Active"] = $result["active"];
				$AutoReplyArray[$ArrayCount]["Frequency"] = $result["frequency"];
				$AutoReplyArray[$ArrayCount++]["EmailAddress"] = $this->GetEmailAddress($result["mailbox_id"]);
			}
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Email.php -> GetAutoReplyList(); Error = ".$e);
		}	
		

        }

	
        function GetAutoReplyDetail(&$AutoReplyArray, $AutoReplyID, $ClientID, $Role)
        {
                $AutoReplyArray = array();


		try
		{
		
			if($Role == 'admin')
			{
				$query = $this->DatabaseConnection->prepare("SELECT * FROM vacations WHERE id = :auto_reply_id AND deleted = 0");
				$query->bindParam(":auto_reply_id", $AutoReplyID);
			
			}
			else if($Role == 'email')
			{
				$query = $this->DatabaseConnection->prepare("SELECT * FROM vacations WHERE mailbox_id = :client_id AND deleted = 0");
				$query->bindParam(":client_id", $ClientID);
			
			}
			else
			{
				$query = $this->DatabaseConnection->prepare("SELECT * FROM vacations WHERE id = :auto_reply_id AND client_id = :client_id AND deleted = 0");
				$query->bindParam(":auto_reply_id", $AutoReplyID);
				$query->bindParam(":client_id", $ClientID);
			}


			$query->execute();
	
			if($result = $query->fetch(PDO::FETCH_ASSOC))
			{
				$AutoReplyArray["ID"] = $result["id"];
				$AutoReplyArray["ClientID"] = $result["client_id"];
				$AutoReplyArray["MailBoxID"] = $result["mailbox_id"];
				$AutoReplyArray["Subject"] = $result["subject"];
				$AutoReplyArray["Body"] = $result["body"];
				$AutoReplyArray["StartDate"] = $result["start_date"];
				$AutoReplyArray["EndDate"] = $result["end_date"];
				$AutoReplyArray["Active"] = $result["active"];
				$AutoReplyArray["Frequency"] = $result["frequency"];
				$AutoReplyArray["EmailAddress"] = $this->GetEmailAddress($result["mailbox_id"]);
			}
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Email.php -> GetAutoReplyDetail(); Error = ".$e);
		}			



	


        }



        function GetEmailListForDomain(&$EmailArray, &$ArrayCount, $ClientID, $Role, $DomainName)
        {
                $EmailArray = array();
		$ArrayCount = 0;

        
		try
		{
		

			if($Role == 'admin')
			{
				$query = $this->DatabaseConnection->prepare("SELECT mailboxes.id, client_id, UserName, Uid, Gid, fqdn, local_part, domain_id FROM domains, mailboxes WHERE mailboxes.active = 1 and domains.deleted = 0 AND domains.active = 1 AND mailboxes.domain_id = domains.id AND fqdn = :domain_name");
				
				$query->bindParam(":domain_name", $DomainName);

			}
			else
			{
				$query = $this->DatabaseConnection->prepare("SELECT mailboxes.id, client_id, UserName, Uid, Gid, fqdn, local_part, domain_id FROM domains, mailboxes WHERE mailboxes.active = 1 and domains.deleted = 0 AND domains.active = 1 AND mailboxes.domain_id = domains.id AND domains.client_id = :client_id AND fqdn = :domain_name");

				$query->bindParam(":client_id", $ClientID);
				$query->bindParam(":domain_name", $DomainName);

			}

			$query->execute();
	
			while($result = $query->fetch(PDO::FETCH_ASSOC))
			{
				$EmailArray[$ArrayCount]["id"] = $result["id"];
				$EmailArray[$ArrayCount]["client_id"] = $result["client_id"];
				$EmailArray[$ArrayCount]["UserName"] = $result["UserName"];
				$EmailArray[$ArrayCount]["Uid"] = $result["Uid"];
				$EmailArray[$ArrayCount]["Gid"] = $result["Gid"];
				$EmailArray[$ArrayCount]["fqdn"] = $result["fqdn"];
				$EmailArray[$ArrayCount]["DomainID"] = $result["domain_id"];
				$EmailArray[$ArrayCount++]["local_part"] = $result["local_part"];
			}

	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Email.php -> GetUserRole(); Error = ".$e);
		}			


        }







	function getSingleEmail(&$EmailArray, $emailClientId)	
	{

                $EmailArray = array();

		try
		{
        		$query = $this->DatabaseConnection->prepare("SELECT mailboxes.id, client_id, UserName, Uid, Gid, fqdn, local_part, domain_id FROM domains, mailboxes WHERE mailboxes.active = 1 and domains.deleted = 0 AND domains.active = 1 AND mailboxes.domain_id = domains.id AND mailboxes.id = :email_client_id");
                
			$query->bindParam(":email_client_id", $emailClientId);

                        $query->execute();

                        if($result = $query->fetch(PDO::FETCH_ASSOC))
                        {
                                $EmailArray[0]["id"] = $result["id"];
                                $EmailArray[0]["client_id"] = $result["client_id"];
                                $EmailArray[0]["UserName"] = $result["UserName"];
                                $EmailArray[0]["Uid"] = $result["Uid"];
                                $EmailArray[0]["Gid"] = $result["Gid"];
                                $EmailArray[0]["fqdn"] = $result["fqdn"];
                                $EmailArray[0]["DomainID"] = $result["domain_id"];
                                $EmailArray[0]["local_part"] = $result["local_part"];
                        }

                }
                catch(PDOException $e)
                {
                        $oLog = new Log();
                        $oLog->WriteLog("error", "/class.Email.php -> getSingleEmail(); Error = ".$e);
                }


	}

        function GetEmailList(&$EmailArray, &$ArrayCount, $ClientID, $Role)
        {
                $EmailArray = array();

		try
		{
			if($Role == 'admin')
			{
				$query = $this->DatabaseConnection->prepare("SELECT mailboxes.id, client_id, UserName, Uid, Gid, fqdn, local_part, domain_id FROM domains, mailboxes WHERE mailboxes.active = 1 and domains.deleted = 0 AND domains.active = 1 AND mailboxes.domain_id = domains.id");

			}
			else if($Role == "reseller")
			{
				$query = $this->DatabaseConnection->prepare("SELECT mailboxes.id, client_id, UserName, Uid, Gid, fqdn, local_part, domain_id FROM domains, mailboxes WHERE mailboxes.active = 1 and domains.deleted = 0 AND domains.active = 1 AND mailboxes.domain_id = domains.id AND domains.client_id IN (SELECT client_id FROM reseller_relationships WHERE deleted = 0 AND reseller_id = :client_id UNION SELECT :client_id2 AS client_id);");
				$query->bindParam(":client_id", $ClientID);
				$query->bindParam(":client_id2", $ClientID);
			}

			else
			{
				$query = $this->DatabaseConnection->prepare("SELECT mailboxes.id, client_id, UserName, Uid, Gid, fqdn, local_part, domain_id FROM domains, mailboxes WHERE mailboxes.active = 1 and domains.deleted = 0 AND domains.active = 1 AND mailboxes.domain_id = domains.id AND domains.client_id = :client_id");
				$query->bindParam(":client_id", $ClientID);
			}

			$query->execute();
	
			$ArrayCount = 0;
			while($result = $query->fetch(PDO::FETCH_ASSOC))
			{
				$EmailArray[$ArrayCount]["id"] = $result["id"];
				$EmailArray[$ArrayCount]["client_id"] = $result["client_id"];
				$EmailArray[$ArrayCount]["UserName"] = $result["UserName"];
				$EmailArray[$ArrayCount]["Uid"] = $result["Uid"];
				$EmailArray[$ArrayCount]["Gid"] = $result["Gid"];
				$EmailArray[$ArrayCount]["fqdn"] = $result["fqdn"];
				$EmailArray[$ArrayCount]["DomainID"] = $result["domain_id"];
				$EmailArray[$ArrayCount++]["local_part"] = $result["local_part"];
			}
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Email.php -> GetEmailList(); Error = ".$e);
		}			


        }


        function GetDomainEmailList(&$EmailArray, &$ArrayCount, $UserName)
        {
                $EmailArray = array();


		try
		{
			$query = $this->DatabaseConnection->prepare("SELECT mailboxes.id, mailboxes.password, client_id, UserName, Uid, Gid, fqdn, local_part FROM domains, mailboxes WHERE mailboxes.active = 1 and domains.deleted = 0 AND domains.active = 1 AND mailboxes.domain_id = domains.id AND domains.UserName = :user_name");
			$query->bindParam(":user_name", $UserName);
			$query->execute();
	
			$ArrayCount = 0;

			while($result = $query->fetch(PDO::FETCH_ASSOC))
			{
				$EmailArray[$ArrayCount]["id"] = $result["id"];
				$EmailArray[$ArrayCount]["client_id"] = $result["client_id"];
				$EmailArray[$ArrayCount]["UserName"] = $result["UserName"];
				$EmailArray[$ArrayCount]["Uid"] = $result["Uid"];
				$EmailArray[$ArrayCount]["Gid"] = $result["Gid"];
				$EmailArray[$ArrayCount]["fqdn"] = $result["fqdn"];
				$EmailArray[$ArrayCount]["Password"] = $result["password"];
				$EmailArray[$ArrayCount++]["local_part"] = $result["local_part"];
			}
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Email.php -> GetDomainEmailList(); Error = ".$e);
		}
		

        }


	
        function GetSingleForwardList(&$EmailArray, &$ArrayCount, $ClientID, $Role)
        {
                $EmailArray = array();

                $ArrayCount = 0;


		try
		{

			if($Role == 'admin')
			{
				$query = $this->DatabaseConnection->prepare("SELECT email_forwarding.id, email_forwarding.client_id, fqdn, source_local_part, destination_address FROM domains, email_forwarding WHERE email_forwarding.deleted = 0 and domains.deleted = 0 AND domains.active = 1 AND email_forwarding.domain_id = domains.id;");


			}
			else if($Role == "reseller")
			{
				$query = $this->DatabaseConnection->prepare("SELECT email_forwarding.id, email_forwarding.client_id, UserName, fqdn, source_local_part, destination_address FROM domains, email_forwarding WHERE email_forwarding.deleted = 0 and domains.deleted = 0 AND domains.active = 1 AND email_forwarding.domain_id = domains.id AND domains.client_id IN (SELECT client_id FROM reseller_relationships WHERE deleted = 0 AND reseller_id = :client_id UNION SELECT :client_id1 AS client_id);");
				
				$query->bindParam(":client_id", $ClientID);
				$query->bindParam(":client_id1", $ClientID);
			
			}
			else if( $Role == "email" )
			{
				$query = $this->DatabaseConnection->prepare("SELECT  email_forwarding.id, email_forwarding.client_id, UserName, fqdn, source_local_part, destination_address FROM mailboxes, email_forwarding, domains WHERE mailboxes.domain_user_name = domains.UserName AND mailboxes.domain_id = email_forwarding.domain_id AND mailboxes.local_part = email_forwarding.source_local_part AND mailboxes.id = :client_id AND mailboxes.active = 1 AND email_forwarding.deleted = 0 AND domains.deleted = 0;");
				$query->bindParam(":client_id", $ClientID);
			}

			else
			{
				$query = $this->DatabaseConnection->prepare("SELECT email_forwarding.id, email_forwarding.client_id, UserName, fqdn, source_local_part, destination_address FROM domains, email_forwarding WHERE email_forwarding.deleted = 0 and domains.deleted = 0 AND domains.active = 1 AND email_forwarding.domain_id = domains.id AND domains.client_id = :client_id");
				$query->bindParam(":client_id", $ClientID);
	
			}


			$query->execute();
	
			while($result = $query->fetch(PDO::FETCH_ASSOC))
			{
				$EmailArray[$ArrayCount]["ID"] = $result["id"];
				$EmailArray[$ArrayCount]["ClientID"] = $result["client_id"];
				$EmailArray[$ArrayCount]["FQDN"] = $result["fqdn"];
				$EmailArray[$ArrayCount]["LocallPart"] = $result["source_local_part"];
				$EmailArray[$ArrayCount]["ForwardTo"] = $result["destination_address"];
				$EmailArray[$ArrayCount++]["EmailAddress"] = $result["source_local_part"]."@".$result["fqdn"];
			}
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Email.php -> GetSingleForwardList(); Error = ".$e);
		}			

        }

        function GetDomainSingleForwardList(&$EmailArray, &$ArrayCount, $UserName)
        {
                $EmailArray = array();

                $ArrayCount = 0;


		try
		{
			$query = $this->DatabaseConnection->prepare("SELECT email_forwarding.id, email_forwarding.client_id, UserName, fqdn, source_local_part, destination_address FROM domains, email_forwarding WHERE email_forwarding.deleted = 0 and domains.deleted = 0 AND domains.active = 1 AND email_forwarding.domain_id = domains.id AND domains.UserName = :user_name");
			
			$query->bindParam(":user_name", $UserName);
			
			$query->execute();
	
			while($result = $query->fetch(PDO::FETCH_ASSOC))
			{
				$EmailArray[$ArrayCount]["ID"] = $result["id"];
				$EmailArray[$ArrayCount]["ClientID"] = $result["client_id"];
				$EmailArray[$ArrayCount]["FQDN"] = $result["fqdn"];
				$EmailArray[$ArrayCount]["LocalPart"] = $result["source_local_part"];
				$EmailArray[$ArrayCount]["ForwardTo"] = $result["destination_address"];
				$EmailArray[$ArrayCount++]["EmailAddress"] = $result["source_local_part"]."@".$result["fqdn"];

			}
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Email.php -> GetDomainSingleForwardList(); Error = ".$e);
		}			

	}


        function GetDomainEmailOptionsList(&$EmailArray, &$ArrayCount, $DomainName)
        {
                $EmailArray = array();
		$ArrayCount = 0;


		try
		{
			$query = $this->DatabaseConnection->prepare("SELECT * FROM email_options WHERE deleted = 0 AND extra1 = :domain_name");
			
			$query->bindParam(":domain_name", $DomainName);
			
			$query->execute();
	
			while($result = $query->fetch(PDO::FETCH_ASSOC))
			{
				$EmailArray[$ArrayCount]["ID"] = $result["id"];
				$EmailArray[$ArrayCount]["Name"] = $result["option_name"];
				$EmailArray[$ArrayCount]["Value"] = $result["option_value"];
				$EmailArray[$ArrayCount]["Extra1"] = $result["extra1"];
				$EmailArray[$ArrayCount++]["Extra2"] = $result["extra2"];
			}
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Email.php -> GetDomainEmailOptionsList(); Error = ".$e);
		}			

	}


	function GetEmailAddress($id)
	{
		

		try
		{
			$query = $this->DatabaseConnection->prepare("SELECT local_part, fqdn FROM mailboxes, domains WHERE mailboxes.domain_id = domains.id AND domains.deleted = 0 AND mailboxes.active = 1 AND mailboxes.id = :id;");
			
			$query->bindParam(":id", $id);
			
			$query->execute();
	
			if($result = $query->fetch(PDO::FETCH_ASSOC))
			{
				return $result["local_part"]."@".$result["fqdn"];
			}
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Email.php -> GetEmailAddress(); Error = ".$e);
		}			



		return "";
		
	}

	function GetSingleForwarderOwner($ForwarderID)
	{
		

		try
		{
			$query = $this->DatabaseConnection->prepare("SELECT email_forwarding.client_id FROM domains, email_forwarding WHERE email_forwarding.deleted = 0 AND email_forwarding.id = :forwarder_id AND email_forwarding.domain_id = domains.id AND domains.deleted = 0;");
			
			$query->bindParam(":forwarder_id", $ForwarderID);
			
			$query->execute();
	
			if($result = $query->fetch(PDO::FETCH_ASSOC))
			{
				return $result["client_id"];
			}
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Email.php -> GetSingleForwarderOwner(); Error = ".$e);
		}			



		return 0;
		
	}

	function GetEmailOwnerFromEmailAddress($EmailAddress)
	{

		try
		{
			$query = $this->DatabaseConnection->prepare("SELECT domains.client_id FROM domains, mailboxes WHERE mailboxes.active = 1 AND CONCAT(mailboxes.local_part, '@', domains.fqdn) = '".$EmailAddress."' AND mailboxes.domain_id = domains.id AND domains.deleted = 0;");
			
			$query->bindParam(":email_address", $EmailAddress);
			
			$query->execute();
	
			if($result = $query->fetch(PDO::FETCH_ASSOC))
			{
				return $result["client_id"];
			}
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Email.php -> GetEmailOwnerFromEmailAddress(); Error = ".$e);
		}			

		return 0;
		
	}
	
	function GetEmailOwner($email_id)
	{
		try
		{
			$query = $this->DatabaseConnection->prepare("SELECT client_id FROM domains, mailboxes WHERE mailboxes.active = 1 AND mailboxes.id = :email_id AND mailboxes.domain_id = domains.id AND domains.deleted = 0");
			
			$query->bindParam(":email_id", $email_id);
			$query->execute();
	
			if($result = $query->fetch(PDO::FETCH_ASSOC))
			{
				return $result["client_id"];
			}
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Email.php -> GetEmailOwner(); Error = ".$e);
		}	

		return 0;
		
	}
	
	function UserNameExists($UserName)
	{
	

		try
		{
			$query = $this->DatabaseConnection->prepare("SELECT UserName FROM emails WHERE UserName = :user_name AND deleted = 0");
			
			$query->bindParam(":user_name", $UserName);
			$query->execute();
	
			if($result = $query->fetch(PDO::FETCH_ASSOC))
			{
				return 1;
			}
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Email.php -> UserNameExists(); Error = ".$e);
		}	
		
		return 0;
	}



	function MakeDMAFile($LocalPart, $DomainName, $UserName)
	{
		
		$x = 1;
		$FileName = $_SERVER["DOCUMENT_ROOT"]."/nm/".$UserName."_".$x.".dma";
		while(file_exists($FileName))
		{
			$x++;
			$FileName = $_SERVER["DOCUMENT_ROOT"]."/nm/".$UserName."_".$x.".dma";
		}

		$myfile = $FileName;
		$fh = fopen($myfile, 'a') or die("can't open file");
		fwrite($fh, "/home/".$UserName."/home/".$UserName."/mail/".$DomainName."/".$LocalPart);
		fclose($fh);

	}


	function MakeNEAFile($LocalPart, $DomainName, $UserName)
	{
		
		$x = 1;
		$FileName = $_SERVER["DOCUMENT_ROOT"]."/nm/".$UserName."_".$x.".nma";
		while(file_exists($FileName)) {
			$x++;
			$FileName = $_SERVER["DOCUMENT_ROOT"]."/nm/".$UserName."_".$x.".nma";
		}

		$myfile = $FileName;
		$fh = fopen($myfile, 'a') or die("can't open file");
		fwrite($fh, "/home/".$UserName."/home/".$UserName."/mail/".$DomainName."/".$LocalPart);
		fclose($fh);

	}


	function CreateUserName($EmailName)
	{
		$UserName = "";

		for($x = 0; $x < strlen($EmailName); $x++)
		{
			if(ctype_alnum(substr($EmailName, $x, 1)))
			{
				$UserName = $UserName.substr($EmailName, $x, 1);
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



	function EditEmailPassword($ID, $Password)
	{

		try
		{
			$query = $this->DatabaseConnection->prepare("UPDATE mailboxes SET Password =  :password WHERE id = :id");

			$Password = md5($Password);
			
			$query->bindParam(":password", $Password);	
			$query->bindParam(":id", $ID);				
				
			$query->execute();
	
			
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Email.php -> EditEmailPassword(); Error = ".$e);
		}

		return 1;
		
	}

	
	
	function AddSingleForward($LocalPart, $DomainID, $ForwardTo, $ClientID)
	{
	
		$oUser = new User();
		$oDomain = new Domain();
       
		$DomainInfoArray = array();

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
		$oDomain->GetDomainInfo($DomainID, $random, $DomainInfoArray, $nonce);
		
		$DomainUserName = $DomainInfoArray["UserName"];

		$lastInsertId = 0;
		try {
			$query = $this->DatabaseConnection->prepare("INSERT INTO email_forwarding VALUES (0, :client_id, :domain_id, :local_part, :forward_to, 0)");

			$query->bindParam(":client_id", $ClientID);	
			$query->bindParam(":domain_id", $DomainID);
			$query->bindParam(":local_part", $LocalPart);	
			$query->bindParam(":forward_to", $ForwardTo);				
				
			$query->execute();
	
			$lastInsertId = $this->DatabaseConnection->lastInsertId();
			
		} catch(PDOException $e) {
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Email.php -> AddSingleForward(); Error = ".$e);
		}

		$this->RecreateUserForwardFile($ClientID);
		return $lastInsertId;
		
	}

	function DeleteDomainEmailForwarders($DomainID, $ClientID)
	{
		
		$oUser = new User();
		$oDomain = new Domain();
		$oSimpleNonce = new SimpleNonce();

		$random = random_int(1, 100000);
		$nonceArray = [
			$oUser->Role,
			$oUser->ClientID,
			$DomainID,
			$random
		];
		
		$nonce = $oSimpleNonce->GenerateNonce("getDomainOwner", $nonceArray);
		if($ClientID != $oDomain->GetDomainOwner($DomainID, $random, $nonce)) {
			return 0;
		}

		

		try
		{
			$query = $this->DatabaseConnection->prepare("UPDATE email_forwarding SET deleted = 1 WHERE client_id = :client_id AND domain_id = :domain_id");

			$query->bindParam(":client_id", $ClientID);	
			$query->bindParam(":domain_id", $DomainID);
			$query->execute();
	
			
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Email.php -> DeleteDomainEmailForwarders(); Error = ".$e);
		}


		return 1;
	}



	function DeleteSingleForwarder($ClientID, $Role, $ForwarderID)
	{
		

		$ForwardFileOwner = $this->GetSingleForwarderOwner($ForwarderID);
	
		$DeleteOK = 0;
		if($Role == 'admin')
		{
			$DeleteOK = 1;
		}
		else if( $Role == "email" )
		{
			$DeleteOK = 1;
		}
		else if($ClientID == $ForwardFileOwner)
		{
			$DeleteOK = 1;
		}


		if($DeleteOK == 1)
		{

			try
			{
				$query = $this->DatabaseConnection->prepare("UPDATE email_forwarding SET deleted = 1 WHERE id = :forwarder_id");

				$query->bindParam(":forwarder_id", $ForwarderID);
				$query->execute();
		
				
			}
			catch(PDOException $e)
			{
				$oLog = new Log();
				$oLog->WriteLog("error", "/class.Email.php -> DeleteSingleForwarder(); Error = ".$e);
			}



			$this->RecreateUserForwardFile($ForwardFileOwner);
			return 1;
		}

		return 0;
	}

	function GetClientIDFromEmailAddress($EmailAddress)
	{


		try
		{
			$query = $this->DatabaseConnection->prepare("SELECT domains.client_id AS ClientID FROM mailboxes, domains WHERE domains.deleted = 0 AND mailboxes.active = 1 AND mailboxes.domain_user_name = domains.UserName AND CONCAT(mailboxes.local_part, '@', domains.fqdn) = :email_address");
			
			$query->bindParam(":email_address", $EmailAddress);
			
			$query->execute();
	
			if($result = $query->fetch(PDO::FETCH_ASSOC))
			{
				return $result["ClientID"];
			}
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Email.php -> GetClientIDFromEmailAddress(); Error = ".$e);
		}			

		return -1;
	}

	function GetClientIDFromMailBoxID($MailBoxID)
	{
		try
		{
			$query = $this->DatabaseConnection->prepare("SELECT domains.client_id AS ClientID FROM mailboxes, domains WHERE domains.deleted = 0 AND mailboxes.active = 1 AND mailboxes.domain_user_name = domains.UserName AND mailboxes.id = :mail_box_id");
			
			$query->bindParam(":mail_box_id", $MailBoxID);
			
			$query->execute();
	
			if($result = $query->fetch(PDO::FETCH_ASSOC))
			{
				return $result["ClientID"];
			}
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Email.php -> GetClientIDFromMailBoxID(); Error = ".$e);
		}			


		return -1;
	}

	function AddAutoReply($ClientID, $MailBoxID, $Subject, $MessageBody, $Frequency, $StartDate, $EndDate)
	{
		$InsertID = 0;
		try
		{
			$query = $this->DatabaseConnection->prepare("INSERT INTO vacations VALUES (0, :client_id, :mail_box_id, :subject, :message_body, :start_date, :end_date, 1, :frequency, 0);");
			
			$query->bindParam(":client_id", $ClientID);
			$query->bindParam(":mail_box_id", $MailBoxID);
			$query->bindParam(":subject", $Subject);
			$query->bindParam(":message_body", $MessageBody);
			$query->bindParam(":start_date", $StartDate);
			$query->bindParam(":end_date", $EndDate);
			$query->bindParam(":frequency", $Frequency);
			
			$query->execute();
	
			$InsertID = $this->DatabaseConnection->lastInsertId();
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Email.php -> AddAutoReply(); Error = ".$e);
		}			


		$this->RecreateUserForwardFile($ClientID);
	
		return $InsertID;
		
	}

	function EditAutoReply($ID, $Subject, $MessageBody, $Frequency, $StartDate, $EndDate, $ClientID)
	{

		try
		{
			$query = $this->DatabaseConnection->prepare("UPDATE vacations SET start_date = :start_date, end_date = :end_date, frequency = :frequency, subject = :subject, body = :message_body WHERE id = :id");

			$query->bindParam(":start_date", $StartDate);
			$query->bindParam(":end_date", $EndDate);
			$query->bindParam(":frequency", $Frequency);
			$query->bindParam(":subject", $Subject);
			$query->bindParam(":message_body", $MessageBody);
			$query->bindParam(":id", $ID);
			$query->execute();
	
			
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Email.php -> EditAutoReply(); Error = ".$e);
		}


		$this->RecreateUserForwardFile($ClientID);
	
		return 1;
		
	}

	function AutoReplyExists($ID)
	{

		try
		{
			$query = $this->DatabaseConnection->prepare("SELECT id FROM vacations WHERE deleted = 0 AND mailbox_id = :id");
			
			$query->bindParam(":id", $ID);
			$query->execute();
	
			if($result = $query->fetch(PDO::FETCH_ASSOC))
			{
				return true;
			}
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Email.php -> AutoReplyExists(); Error = ".$e);
		}
		

		return false;
	}

	function DeleteAutoReply($ClientID, $Role, $AutoReplyID)
	{
	
		try
		{

			if($Role == 'admin')
			{
				$query = $this->DatabaseConnection->prepare("UPDATE vacations SET deleted = 1 WHERE id = :auto_reply_id");
				$query->bindParam(":auto_reply_id", $AutoReplyID);
			}
			else if($Role == "email" )
			{

				$query = $this->DatabaseConnection->prepare("UPDATE vacations SET deleted = 1 WHERE mailbox_id = :client_id AND id = :auto_reply_id");

				$query->bindParam(":client_id", $ClientID);
				$query->bindParam(":auto_reply_id", $AutoReplyID);
				
			}
			else
			{
				$query = $this->DatabaseConnection->prepare("UPDATE vacations SET deleted = 1 WHERE client_id = :client_id AND id = :auto_reply_id");
				$query->bindParam(":client_id", $ClientID);
				$query->bindParam(":auto_reply_id", $AutoReplyID);
			}
		
			$query->execute();
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Email.php -> DeleteAutoReply(); Error = ".$e);
		}			


		if( $Role == "email" )
		{
			// Client ID is set to the mailbox ID for email users
			// Set it to the account Client ID so that remaking the 
			// forwarders below will work
			$ClientID = $this->GetEmailOwner($ClientID);
		}


		$this->RecreateUserForwardFile($ClientID);
				
		return 1;
	}


    

	function DeleteMailBoxAutoReply($ClientID, $Role, $MailBoxID)
	{
		try
		{
		
			if($Role == 'admin')
			{
				$query = $this->DatabaseConnection->prepare("UPDATE vacations SET deleted = 1 WHERE mailbox_id = :mail_box_id");
				$query->bindParam(":mail_box_id", $MailBoxID);
			}
			else
			{
				$query = $this->DatabaseConnection->prepare("UPDATE vacations SET deleted = 1 WHERE client_id = :client_id AND mailbox_id = :mail_box_id");
				$query->bindParam(":client_id", $ClientID);
				$query->bindParam(":mail_box_id", $MailBoxID);
			}

			$query->execute();

		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Email.php -> DeleteMailBoxAutoReply(); Error = ".$e);
		}			



		$this->RecreateUserForwardFile($ClientID);
				
		return 1;
	}


	function GetHighSubjectCountData($Subject, $StartDate, $EndDate, &$FireWallBlockList, &$EmailsToSuspend)
	{
		$FireWallBlockList = array();
		$EmailsToSuspend = array();


		try
		{
			$query = $this->DatabaseConnection->prepare("SELECT sender_host, auth_type FROM email_trace  WHERE subject IN (".$Subject.") AND start_date >= :start_date and start_date <= :end_date GROUP BY sender_host, auth_type;");
			
			$query->bindParam(":start_date", $StartDate);
			$query->bindParam(":end_date", $EndDate);
			
			
			
			$query->execute();
	
			while($result = $query->fetch(PDO::FETCH_ASSOC))
			{
				array_push($FireWallBlockList, $result["sender_host"]); 
				array_push($EmailsToSuspend, $result["auth_type"]);
			}
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Email.php -> GetHighSubjectCountData(); Error = ".$e);
		}			


		$FireWallBlockList = array_unique($FireWallBlockList);
		$EmailsToSuspend = array_unique($EmailsToSuspend);

	}

	function GetHighSubjectCount($HighCount, $StartDate, $EndDate, &$Subject, &$FireWallBlockList, &$EmailsToSuspend)
	{

		$Subject = "";
		$FireWallBlockList = array();
		$EmailsToSuspend = array();



		try
		{
			$query = $this->DatabaseConnection->prepare("SELECT COUNT(subject) AS subject_count, subject FROM email_trace WHERE auth_type != '' AND SUBSTR(LCASE(subject), 1, 20) != 'mail delivery failed' AND start_date >= :start_date AND start_date <= :end_date GROUP BY subject ORDER BY subject_count;");
			
			$query->bindParam(":start_date", $StartDate);
			$query->bindParam(":end_date", $EndDate);
			
			$query->execute();
	
			while($result = $query->fetch(PDO::FETCH_ASSOC))
			{
				if( intVal($result["subject_count"]) >= $HighCount)
				{
					$Subject = $Subject."'".$result["subject"]."',";
				}
			}
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Email.php -> GetHighSubjectCount(); Error = ".$e);
		}			




		if(strlen($Subject) > 1)
		{
			$Subject = substr($Subject, 0, strlen($Subject) - 1);
			$this->GetHighSubjectCountData($Subject, $StartDate, $EndDate, $FireWallBlockList, $EmailsToSuspend);
		}
	}


	function GetHigh550Count($HighCount, $StartDate, $EndDate, &$FireWallBlockList, &$EmailsToSuspend)
	{

		$FireWallBlockList = array();
		$EmailsToSuspend = array();


		try {
			$query = $this->DatabaseConnection->prepare("SELECT COUNT(confirmation) AS confirmation_count, auth_type, sender_host FROM email_trace WHERE auth_type != '' AND sender_host != '' AND SUBSTR(confirmation, 1,3) = '550'  AND start_date >= :start_date AND start_date <= :end_date GROUP BY subject, auth_type, sender_host ORDER BY confirmation_count;");

			$query->bindParam(":start_date", $StartDate);
			$query->bindParam(":end_date", $EndDate);
			
			$query->execute();
	
			while($result = $query->fetch(PDO::FETCH_ASSOC)) {
				if( intVal($line["confirmation_count"]) >= $HighCount) { 
					array_push($FireWallBlockList, $result["sender_host"]);
					array_push($EmailsToSuspend, $result["auth_type"]);
				}
			}
	
		} catch(PDOException $e) { 
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Email.php -> GetHigh550Count(); Error = ".$e);
		}			

	
		$FireWallBlockList = array_unique($FireWallBlockList);
		$EmailsToSuspend = array_unique($EmailsToSuspend);		

	}



	function RemoveAlreadySuspendedFromArray($EmailArray)
	{
		$Count = count($EmailArray);
		for($x = 0; $x < $Count; $x++)
		{
			if($this->IsEmailSuspended($EmailArray[$x]) == true)
			{
				$EmailArray[$x] = "";
			}
		}

		return array_unique($EmailArray);
	}

	function IsEmailSuspended($EmailAddress)
	{
		$EmailID = $this->GetMailBoxID($EmailAddress);
		

		try
		{
			$query = $this->DatabaseConnection->prepare("SELECT id FROM mailboxes WHERE id = :email_id AND password LIKE '%_suspended'");
			$query->bindParam(":email_id", $EmailID);
			$query->execute();
	
			if($result = $query->fetch(PDO::FETCH_ASSOC))
			{
				return true;
			}
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Email.php -> IsEmailSuspended(); Error = ".$e);
		}			

		return false;
	}


	function SuspendEmail($ClientID, $Role, $EmailAddress)
	{
	
		$EmailID = $this->GetMailBoxID($EmailAddress);	

		if($EmailID == 0)
		{
			return 0;
		}

		$SuspendOK = 0;
		if($Role == 'admin')
		{
			$SuspendOK = 1;
		}
		else if($ClientID == $this->GetEmailOwner($EmailID))
		{
			$SuspendOK = 1;
		}

		if($SuspendOK == 1)
		{
			$Random = rand(0,100000);
			$Random = $Random.rand(0,100000);
			$Random = $Random.date("YmdHis");
			$Random = md5($Random);
			$Random = $Random."_suspended";


			try
			{
				$query = $this->DatabaseConnection->prepare("UPDATE mailboxes SET description = password WHERE id = :email_id");
				
				$query->bindParam(":email_id", $EmailID);
				
				$query->execute();
				
			}
			catch(PDOException $e)
			{
				$oLog = new Log();
				$oLog->WriteLog("error", "/class.Email.php -> SuspendEmail() 1; Error = ".$e);
			}			


			try
			{
				$query = $this->DatabaseConnection->prepare("UPDATE mailboxes SET password = :random WHERE id = :email_id");
				
				$query->bindParam(":random", $Random);
				$query->bindParam(":email_id", $EmailID);
				
				$query->execute();
				
			}
			catch(PDOException $e)
			{
				$oLog = new Log();
				$oLog->WriteLog("error", "/class.Email.php -> SuspendEmail() 2; Error = ".$e);
			}			

			
			return 1;
		}

		return 0;
	}

	
	function DeleteEmail($ClientID, $Role, $email_id)
	{
		
		$DomainID = 0;
		$DeleteOK = 0;
		if($Role == 'admin')
		{
			$DeleteOK = 1;
		}
		else if($ClientID == $this->GetEmailOwner($email_id))
		{
			$DeleteOK = 1;
		}

		if($DeleteOK == 1)
		{
				
        		$this->GetEmailInfo($email_id, $user_name, $local_part, $domain_name, $DomainID);
			

			$this->MakeDMAFile($local_part, $domain_name, $user_name);
			

			try
			{
				$query = $this->DatabaseConnection->prepare("UPDATE mailboxes SET active = 0 WHERE id = :email_id");
				$query->bindParam(":email_id", $email_id);
				$query->execute();
	
			}
			catch(PDOException $e)
			{
				$oLog = new Log();
				$oLog->WriteLog("error", "/class.Email.php -> DeleteEmail(); Error = ".$e);
			}			


			$this->DeleteEmailAccountOptions($ClientID, $Role, $local_part."@".$domain_name);
			$this->DeleteMailBoxAutoReply($ClientID, $Role, $email_id);
			$this->RecreateUserForwardFile($ClientID);
	
			return 1;
		}

		return 0;
	}

	function DeleteDomainEmails($DomainID, $ClientID)
	{
		
		$oUser = new User();
		$oSimpleNonce = new SimpleNonce();
		$oDomain = new Domain();

		$random = random_int(1, 100000);
		$nonceArray = [
			$oUser->Role,
			$oUser->ClientID,
			$DomainID,
			$random
		];
		
		$nonce = $oSimpleNonce->GenerateNonce("getDomainOwner", $nonceArray);
		if($ClientID != $oDomain->GetDomainOwner($DomainID, $random, $nonce)) {
			return 0;
		}

		$EmailArray = array();
		$ArrayCount = 0;

		$this->GetEmailList($EmailArray, $ArrayCount, $ClientID, "admin");
	
		for($x = 0; $x < $ArrayCount; $x++)
		{

			if($EmailArray[$x]["DomainID"] == $DomainID)
			{
				$this->DeleteMailBoxAutoReply($EmailArray[$x]["client_id"], "admin", $EmailArray[$x]["id"]);
			}
		}

	
		try
		{
			$query = $this->DatabaseConnection->prepare("UPDATE mailboxes SET active = 0 WHERE domain_id = :domain_id");

			$query->bindParam(":domain_id", $DomainID);
			$query->execute();
	
			
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.Email.php -> DeleteDomainEmails(); Error = ".$e);
		}
	
			
		return 1;
	}


    
}

