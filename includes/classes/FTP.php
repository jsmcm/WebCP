<?php
/*********************************************************************
*********************************************************************/
if(!isset($_SESSION)) 
{
     session_start();
}

include_once($_SERVER["DOCUMENT_ROOT"]."/vendor/autoload.php");



class FTP
{
	var $LastErrorDescription = "";
	
	function __construct() 
	{
                $this->oDatabase = new Database();
                $this->DatabaseConnection = $this->oDatabase->GetConnection();
		
                if( ! file_exists($_SERVER["DOCUMENT_ROOT"]."/nm/"))
                {
                        mkdir($_SERVER["DOCUMENT_ROOT"]."/nm/", 0755);
                }
	}
   
        function UpdateDomainFTPDiskQuotas($DomainID, $Quota)
        {

		try
		{
			$query = $this->DatabaseConnection->prepare("UPDATE ftpd SET QuotaSize = :quota WHERE domain_id = :domain_id;");
			
			$query->bindParam(":quota", $Quota);
			$query->bindParam(":domain_id", $DomainID);
			
			$query->execute();
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.FTP.php -> UpdateDomainFTPDiskQuotas(); Error = ".$e);
		}
		
                return 1;


        }

 
	function UpdateFTPQuotas($PackageID, $Quota)
	{
	
		try
		{
			$query = $this->DatabaseConnection->prepare("UPDATE ftpd SET QuotaSize = :quota WHERE client_id IN (SELECT id FROM admin WHERE package_id = :package_id)");
			
			$query->bindParam(":quota", $Quota);
			$query->bindParam(":package_id", $PackageID);
			
			$query->execute();

		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.FTP.php -> UpdateFTPQuotas(); Error = ".$e);
		}
		
		return 1;
		

	}
		
	function FTPExists($UserName) 
	{
		
		try
		{
			$query = $this->DatabaseConnection->prepare("SELECT id FROM ftpd WHERE User = :user_name");
			
			$query->bindParam(":user_name", $UserName);
			
			$query->execute();
	
			if($result = $query->fetch(PDO::FETCH_ASSOC))
			{
				return $result["id"];
			}
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.FTP.php -> FTPExists(); Error = ".$e);
		}
		

		return -1;
		
	}
	
        function GetFTPAccount($ID)
        {
                $FTPArray = array();

                $ArrayCount = 0;
		try
		{
			$query = $this->DatabaseConnection->prepare("SELECT fqdn FROM ftpd, domains WHERE ftpd.id = :id AND domains.deleted = 0 AND ftpd.domain_id = domains.id ORDER BY User ASC;");
			
			$query->bindParam(":id", $ID);
			
			$query->execute();
	
			if($result = $query->fetch(PDO::FETCH_ASSOC))
			{
				return $result["fqdn"];
			}
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.FTP.php -> GetFTPAccount(); Error = ".$e);
		}
		
		return "";

        }

	
        function GetFTPList(&$FTPArray, &$ArrayCount, $ClientID, $Role)
        {
                $FTPArray = array();

                $ArrayCount = 0;



		try
		{
		
			
			if($Role == 'admin')
			{

				$query = $this->DatabaseConnection->prepare("SELECT ftpd.id, User, fqdn, Dir FROM ftpd, domains WHERE ftpd.domain_id = domains.id ORDER BY User ASC;");
			
			}
			else if($Role == "reseller")
			{

				$query = $this->DatabaseConnection->prepare("SELECT ftpd.id, User, fqdn, Dir  FROM ftpd, domains WHERE ftpd.domain_id = domains.id AND ftpd.client_id IN (SELECT client_id FROM reseller_relationships WHERE deleted = 0 AND reseller_id = :client_id UNION SELECT :client_id1 AS client_id) ORDER BY User ASC ;");
				
				$query->bindParam(":client_id", $ClientID);
				$query->bindParam(":client_id1", $ClientID);
				
			}
			else
			{

				$query = $this->DatabaseConnection->prepare("SELECT ftpd.id, User, fqdn, Dir FROM ftpd, domains WHERE ftpd.client_id = :client_id AND ftpd.domain_id = domains.id ORDER BY User ASC;");
				
				$query->bindParam(":client_id", $ClientID);
			}

			$query->execute();
	
	
			while($result = $query->fetch(PDO::FETCH_ASSOC))
			{
				$FTPArray[$ArrayCount]["id"] = $result["id"];
				$FTPArray[$ArrayCount]["User"] = $result["User"];
				$FTPArray[$ArrayCount]["Dir"] = $result["Dir"];
				$FTPArray[$ArrayCount++]["fqdn"] = $result["fqdn"];

			}
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.FTP.php -> GetFTPList(); Error = ".$e);
		}
		
	
        }


	
        function GetDomainFTPList(&$FTPArray, &$ArrayCount, $DomainID)
        {
                $FTPArray = array();
		$ArrayCount = 0;


		try
		{
			$query = $this->DatabaseConnection->prepare("SELECT * FROM ftpd WHERE ftpd.domain_id = :domain_id;");
			
			$query->bindParam(":domain_id", $DomainID);
			
			$query->execute();
	
			while($result = $query->fetch(PDO::FETCH_ASSOC))
			{
				$FTPArray[$ArrayCount]["ID"] = $result["id"];
				$FTPArray[$ArrayCount]["UserName"] = $result["User"];
				$FTPArray[$ArrayCount]["Dir"] = $result["Dir"];
				$FTPArray[$ArrayCount]["Status"] = $result["status"];
				$FTPArray[$ArrayCount]["Password"] = $result["Password"];
				$FTPArray[$ArrayCount++]["QuotaSize"] = $result["QuotaSize"];

			}
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.FTP.php -> GetDomainFTPList(); Error = ".$e);
		}

        }


	function GetFTPUser($id)
	{
		
		try
		{
			$query = $this->DatabaseConnection->prepare("SELECT User FROM ftpd WHERE id = :id");
			
			$query->bindParam(":id", $id);
			
			$query->execute();
	
			if($result = $query->fetch(PDO::FETCH_ASSOC))
			{
				return $result["User"];
			}
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.FTP.php -> GetFTPUser(); Error = ".$e);
		}

		return "";
		
	}


	function GetFTPOwner($ftp_id)
	{
		try
		{
			$query = $this->DatabaseConnection->prepare("SELECT client_id FROM ftpd WHERE id = :ftp_id;");
			
			$query->bindParam(":ftp_id", $ftp_id);
			
			$query->execute();
	
			if($result = $query->fetch(PDO::FETCH_ASSOC))
			{
				return $result["client_id"];
			}
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.FTP.php -> GetFTPOwner(); Error = ".$e);
		}

		return 0;
		
	}
	
	function GetDomainInfo($DomainUserName, &$ID, &$Uid)
	{
	
		try
		{
			$query = $this->DatabaseConnection->prepare("SELECT id, Uid FROM domains WHERE UserName = :domain_user_name AND deleted = 0;");
			
			$query->bindParam(":domain_user_name", $DomainUserName);
			
			$query->execute();
	
			if($result = $query->fetch(PDO::FETCH_ASSOC))
			{
				$Uid = $result["Uid"];
				$ID = $result["id"];
				return 1;
			}
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.FTP.php -> GetDomainInfo(); Error = ".$e);
		}
		

		return 0;
	}

	

	function MakeNMLFile($UserName, $UID)
	{

		$myfile = $_SERVER["DOCUMENT_ROOT"]."/nm/".$UserName.".nml";
		$fh = fopen($myfile, 'a') or die("can't open file");
		fwrite($fh, $UID);
		fclose($fh);

	}

	function MakeNVHFile($FTPName, $UserName)
	{

		$myfile = $_SERVER["DOCUMENT_ROOT"]."/nm/".$FTPName.".nvh";
		$fh = fopen($myfile, 'a') or die("can't open file");
		fwrite($fh, $UserName);
		fclose($fh);

	}


	function CreateUserName($FTPName)
	{
		$UserName = "";

		for($x = 0; $x < strlen($FTPName); $x++)
		{
			if(ctype_alnum(substr($FTPName, $x, 1)))
			{
				$UserName = $UserName.substr($FTPName, $x, 1);
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


	function PlainPasswordEdit($ID, $Password)
	{

		try
		{
			$query = $this->DatabaseConnection->prepare("UPDATE ftpd SET Password = :password WHERE id = :id;");
			
			$query->bindParam(":password", $Password);
			$query->bindParam(":id", $ID);
			
			$query->execute();
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.FTP.php -> PlainPasswordEdit(); Error = ".$e);
		}

		return 1;
		
	}

	function EditFTPPassword($ID, $Password)
	{

		try
		{
			$query = $this->DatabaseConnection->prepare("UPDATE ftpd SET Password = :password WHERE id = :id;");
			
			$Password = md5($Password);
			
			$query->bindParam(":password", $Password);
			$query->bindParam(":id", $ID);
			
			$query->execute();
	
			if($result = $query->fetch(PDO::FETCH_ASSOC))
			{
				return $result["option_value"];
			}
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.FTP.php -> EditFTPPassword(); Error = ".$e);
		}


		return 1;
		
	}


	function AddFTP($UserName, $DomainID, $Password, $Quota, $ClientID)
	{
		//print "AddFTP<p>";



                $oUser = new User();
                $oDomain = new Domain();
                $oPackage = new Package();
		

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
		
			

		//print "1<p>";

		$PackageID = $DomainInfoArray["PackageID"];

	        $DomainUserName = $DomainInfoArray["UserName"];

		//print "<p>10<p>";

	        $FTPAllowance = $oPackage->GetPackageAllowance("FTP", $DomainInfoArray["PackageID"]);

		//print "<p>15<p>";

        	$FTPUsage = $oPackage->GetFTPUsage($DomainID);	

		//print "<p>20<p>";
	
                $FTPUsage = $oPackage->GetFTPUsage($ClientID);
                $FTPAllowance = $oPackage->GetPackageAllowance("FTP", $PackageID);

		//print "<p>In class->AddFTP<p>";
		//print "PackageID: ".$PackageID."<br>";
		//print "FTPUsage: ".$FTPUsage."<br>";
		//print "FTPAllowance: ".$FTPAllowance."<br>";

		if( (($FTPAllowance - $FTPUsage) < 1) && ($oUser->Role != "admin") )
                {
                        // No More Mails Left
                        return -1;
                }

		$this->GetDomainInfo($DomainUserName, $DomainID, $Uid);

		
		$oUtils = new Utils();
		$Quota = $oUtils->ConvertToScale($Quota, 'b', 'm');
		
		try
		{
			$query = $this->DatabaseConnection->prepare("INSERT INTO ftpd VALUES (0, :domain_user, :domain_id, :client_id, '1', :password, :uid, :uid1, :path, 0, 0, '', '*', :quota, 0);");
		
	
			$DomainUser = $DomainUserName."_".$UserName;
			$Path = "/home/".$DomainUserName."/home/".$DomainUserName."/public_html";
			$Password = md5($Password);
			
			$query->bindParam(":domain_user", $DomainUser);
			$query->bindParam(":domain_id", $DomainID);
			$query->bindParam(":client_id", $ClientID);
			$query->bindParam(":password", $Password);
			$query->bindParam(":uid", $Uid);
			$query->bindParam(":uid1", $Uid);
			$query->bindParam(":path", $Path);
			$query->bindParam(":quota", $Quota);


			
			$query->execute();
	
			return $this->DatabaseConnection->lastInsertId();
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.FTP.php -> AddFTP(); Error = ".$e);
		}
		
		return 0;
		
	}
	
	
	function DeleteDomainFTP($DomainID, $ClientID)
	{
		
		try
		{
			$query = $this->DatabaseConnection->prepare("DELETE FROM ftpd WHERE client_id = :client_id AND domain_id = :domain_id");
			
			$query->bindParam(":client_id", $DomainID);
			$query->bindParam(":domain_id", $ClientID);
			
			$query->execute();
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.FTP.php -> DeleteDomainFTP(); Error = ".$e);
		}
		
		return 1;
	}


	
	function DeleteFTP($ClientID, $Role, $ftp_id)
	{
		


		//print "client_id: ".$ClientID."<p>";
		//print "Role: ".$Role."<p>";
		//print "ftp_id: ".$ftp_id."<p>";
	
		$DeleteOK = 0;
		if($Role == 'admin') {
			$DeleteOK = 1;
		} else if($Role == "reseller") {
			$oReseller = new Reseller();

			$FTPOwnerID = $this->GetFTPOwner($ftp_id);

			$random = random_int(1, 100000);
			$nonceArray = [
				$oUser->Role,
				$ClientID,
				$FTPOwnerID,
				$random
			];
			
			$oSimpleNonce = new SimpleNonce();
			
			$nonce = $oSimpleNonce->GenerateNonce("getClientResellerID", $nonceArray);
			$ResellerID = $oReseller->GetClientResellerID($FTPOwnerID, $random, $nonce);

        	if($ResellerID == $ClientID) {			
				$DeleteOK = 1;
			}
		}
		else if($ClientID == $this->GetFTPOwner($ftp_id))
		{
			$DeleteOK = 1;
		}

		//print "DeleteOK: ".$DeleteOK."<p>";
		//exit();
	
		if($DeleteOK == 1)
		{
		
			try
			{
				$query = $this->DatabaseConnection->prepare("DELETE FROM ftpd WHERE id = :ftp_id");
				
				$query->bindParam(":ftp_id", $ftp_id);
				
				$query->execute();

			}
			catch(PDOException $e)
			{
				$oLog = new Log();
				$oLog->WriteLog("error", "/class.FTP.php -> DeleteFTP(); Error = ".$e);
			}
		

			return 1;
		}

		return 0;
	}


	function ManageSuspension($DomainID, $State)
	{
		
		try
		{
			$query = $this->DatabaseConnection->prepare("UPDATE ftpd SET status = :state WHERE domain_id = :domain_id");
			
			$query->bindParam(":state", $State);
			$query->bindParam(":domain_id", $DomainID);
			
			$query->execute();
	
			if($result = $query->fetch(PDO::FETCH_ASSOC))
			{
				return $result["option_value"];
			}
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.FTP.php -> ManageSuspension(); Error = ".$e);
		}
		
		return 1;
	}
    
}

