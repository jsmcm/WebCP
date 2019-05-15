<?php
/*********************************************************************
*********************************************************************/
if(!isset($_SESSION)) 
{
     session_start();
}

include_once("/var/www/html/webcp/vendor/autoload.php");


class MySQL 
{
	
	
	function __construct()
        {
                $this->oDatabase = new Database();
                $this->DatabaseConnection = $this->oDatabase->GetConnection();
	}
	
	function UpdatePassword($UserName, $Password)
	{

		try
		{
			$pdo_query = $this->DatabaseConnection->prepare("UPDATE mysql SET password = :password WHERE username = :user_name AND deleted = 0");
			
			$pdo_query->bindParam(":password", $Password);
			$pdo_query->bindParam(":user_name", $UserName);
			
			$pdo_query->execute();
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.MySQL.php -> UpdatePassword(); Error = ".$e);
		}

	
	}
	
	
	function ChangePassword($Uname, $Pwd)
	{
	
		try
		{
			$pdo_query = $this->DatabaseConnection->prepare("UPDATE mysql.user SET authentication_string=PASSWORD(\"".$Pwd."\") where User='".$Uname."';");
			
			$pdo_query->execute();
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.MySQL.php -> ChangePassword1(); Error = ".$e);
		}	
		


		try
		{
			$pdo_query = $this->DatabaseConnection->prepare("flush privileges");
			
			$pdo_query->execute();
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.MySQL.php -> ChangePassword2(); Error = ".$e);
		}	
		
		
		
		if($Uname != "root")
		{
			$this->UpdatePassword($Uname, $Pwd);
		}

		return 1;
	}
	
	
	function unSuspendUserAccounts($domainUserName)
	{

		try {
			$pdo_query = $this->DatabaseConnection->prepare("UPDATE mysql.user SET User = substr(User, 11) where User LIKE 'suspended_".$domainUserName."_%';");	
			$pdo_query->execute();
	
		} catch(PDOException $e) {
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.MySQL.php -> unSuspendUserAccounts 1; Error = ".$e);
		}	
		


		try {
			$pdo_query = $this->DatabaseConnection->prepare("flush privileges");
			
			$pdo_query->execute();
	
		} catch(PDOException $e) {
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.MySQL.php -> unSuspendUserAccounts 2(); Error = ".$e);
		}	
		
		return true;
	}

	function suspendUserAccounts($domainUserName)
	{

		try {
			$pdo_query = $this->DatabaseConnection->prepare("UPDATE mysql.user SET User = concat('suspended_', User) where User LIKE '".$domainUserName."_%';");	
			$pdo_query->execute();
	
		} catch(PDOException $e) {
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.MySQL.php -> suspendUserAccounts 1; Error = ".$e);
		}	
		


		try {
			$pdo_query = $this->DatabaseConnection->prepare("flush privileges");
			
			$pdo_query->execute();
	
		} catch(PDOException $e) {
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.MySQL.php -> suspendUserAccounts 2(); Error = ".$e);
		}	
		
		return true;
	}

	
	 
	function DeleteUserNameHosts($UserName)
	{

		try
		{
			$pdo_query = $this->DatabaseConnection->prepare("DELETE FROM mysql.user WHERE User = :user_name AND Host != 'localhost';");
			$pdo_query->bindParam(":user_name", $UserName);
			$pdo_query->execute();
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.MySQL.php -> DeleteUserNameHosts(); Error = ".$e);
		}	
		
	}
	
 
	function GetUserNameHosts($UserName)
	{
		$ReturnArray = array();

		try
		{
			$pdo_query = $this->DatabaseConnection->prepare("SELECT Host FROM mysql.user WHERE User = :user_name AND Host != 'localhost';");
			
			$pdo_query->bindParam(":user_name", $UserName);
			
			$pdo_query->execute();
	
			while($result = $pdo_query->fetch(PDO::FETCH_ASSOC))
			{
				array_push($ReturnArray, $result["Host"]);
			}
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.MySQL.php -> GetUserNameHosts(); Error = ".$e);
		}
		
		
		return $ReturnArray;
		
	}
	  
	function DBUserNameCount($UserName)
	{
		
		try
		{
			$pdo_query = $this->DatabaseConnection->prepare("SELECT COUNT(username) as count FROM mysql WHERE username = :user_name AND deleted = 0");
			
			$pdo_query->bindParam(":user_name", $UserName);
			
			$pdo_query->execute();
	
			if($result = $pdo_query->fetch(PDO::FETCH_ASSOC))
			{
				return $result["count"];
			}
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.MySQL.php -> DBUserNameCount(); Error = ".$e);
		}


		return 0;

	}

	function MySQLExists($cpDatabaseName) 
	{
		
		try
		{
			$pdo_query = $this->DatabaseConnection->prepare("SELECT id FROM mysql WHERE database_name = :cp_database_name AND deleted = 0");
			
			$pdo_query->bindParam(":cp_database_name", $cpDatabaseName);
			
			$pdo_query->execute();
	
			if($result = $pdo_query->fetch(PDO::FETCH_ASSOC))
			{
				return $result["id"];
			}
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.MySQL.php -> MySQLExists(); Error = ".$e);
		}

		return -1;
		
	}
	

	function GetMySQLOwner($ID) 
	{
		try
		{
			$pdo_query = $this->DatabaseConnection->prepare("SELECT client_id FROM mysql WHERE id = :id AND deleted = 0");
			
			$pdo_query->bindParam(":id", $ID);
			
			$pdo_query->execute();
	
			if($result = $pdo_query->fetch(PDO::FETCH_ASSOC))
			{
				return $result["client_id"];
			}
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.MySQL.php -> GetMySQLOwner(); Error = ".$e);
		}

		
		return -1;
		
	}
    
    
    
    

	
	
	
	function GetDatabasePassword($ID, $DomainUserName, $UserName, $MySQLDatabaseName, $ClientID)
	{

		try
		{
			$pdo_query = $this->DatabaseConnection->prepare("SELECT password FROM mysql WHERE deleted = 0 AND id = :id AND domain_username = :domain_user_name AND username = :user_name AND database_name = :mysql_database_name AND client_id = :client_id;");
			
			$pdo_query->bindParam(":id", $ID);
			$pdo_query->bindParam(":domain_user_name", $DomainUserName);
			$pdo_query->bindParam(":user_name", $UserName);
			$pdo_query->bindParam(":mysql_database_name", $MySQLDatabaseName);
			$pdo_query->bindParam(":client_id", $ClientID);
			
			$pdo_query->execute();
	
			if($result = $pdo_query->fetch(PDO::FETCH_ASSOC))
			{
				return $result["password"];
			}
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.MySQL.php -> GetDatabasePassword(); Error = ".$e);
		}


		return "";
	}

	function GetMySQLInfo($ID, &$UserUserName, &$UserDatabaseName)
	{
		
		$UserUserName = "";
		$UserDatabaseName = "";

		try
		{
			$pdo_query = $this->DatabaseConnection->prepare("SELECT username, database_name FROM mysql WHERE deleted = 0 AND id = :id;");
			
			$pdo_query->bindParam(":id", $ID);
			
			$pdo_query->execute();
	
			if($result = $pdo_query->fetch(PDO::FETCH_ASSOC))
			{
				$UserUserName = $result["username"];
				$UserDatabaseName = $result["database_name"];
			}
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.MySQL.php -> GetMySQLInfo(); Error = ".$e);
		}
	
	}


	function GetMySQLList(&$MySQLArray, &$ArrayCount, $ClientID, $Role)
	{
		$MySQLArray = array();
		
		$ArrayCount = 0;
		
		
		try
		{
		
			if($Role == "admin")
			{
			
				$pdo_query = $this->DatabaseConnection->prepare("SELECT * FROM mysql WHERE deleted = 0;");
						
			}
			else if($Role == "reseller")
			{

				$pdo_query = $this->DatabaseConnection->prepare("SELECT * FROM mysql  WHERE deleted = 0 AND client_id IN (SELECT client_id FROM reseller_relationships WHERE deleted = 0 AND reseller_id = :client_id UNION SELECT :client_id2 AS client_id)");
				
				$pdo_query->bindParam(":client_id", $ClientID);
				$pdo_query->bindParam(":client_id2", $ClientID);
			
			}
			else
			{

				$pdo_query = $this->DatabaseConnection->prepare("SELECT * FROM mysql WHERE deleted = 0 AND client_id = :client_id");
				
				$pdo_query->bindParam(":client_id", $ClientID);
			
			}

			
			$pdo_query->execute();
	
			while($result = $pdo_query->fetch(PDO::FETCH_ASSOC))
			{
				$MySQLArray[$ArrayCount]["ID"] = $result["id"];
				$MySQLArray[$ArrayCount]["cpDatabaseName"] = $result["database_name"];
				$MySQLArray[$ArrayCount]["Created"] = $result["created"];
				$MySQLArray[$ArrayCount++]["DatabaseUsername"] = $result["username"];

			}
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.MySQL.php -> GetMySQLList(); Error = ".$e);
		}


		

		
	}
	

	function GetMySQLDomainList(&$MySQLArray, &$ArrayCount, $DomainUserName)
	{
		$MySQLArray = array();

		$ArrayCount = 0;
		
		try
		{
			$pdo_query = $this->DatabaseConnection->prepare("SELECT * FROM mysql WHERE deleted = 0 AND domain_username = :domain_user_name");
			
			$pdo_query->bindParam(":domain_user_name", $DomainUserName);
			
			$pdo_query->execute();
	
			while($result = $pdo_query->fetch(PDO::FETCH_ASSOC))
			{
				$MySQLArray[$ArrayCount]["ID"] = $result["id"];
				$MySQLArray[$ArrayCount]["DatabaseName"] = $result["database_name"];
				$MySQLArray[$ArrayCount]["Password"] = $result["password"];
				$MySQLArray[$ArrayCount]["Created"] = $result["created"];
				$MySQLArray[$ArrayCount++]["DatabaseUsername"] = $result["username"];
			}
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.MySQL.php -> GetMySQLDomainList(); Error = ".$e);
		}


				
	}
	

	function DropUserName($UserUsername)
	{

		try
		{
			$pdo_query = $this->DatabaseConnection->prepare("DROP USER '".$UserUsername."'@'%';");
			
			$pdo_query->execute();
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.MySQL.php -> DropUserName1(); Error = ".$e);
		}
		
		
		
		try
		{
			$pdo_query = $this->DatabaseConnection->prepare("DROP USER '".$UserUsername."'@'localhost';");
			
			$pdo_query->execute();
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.MySQL.php -> DropUserName1(); Error = ".$e);
		}
		

	}

	function DropDatabase($DBName)
	{
		try
		{
			$pdo_query = $this->DatabaseConnection->prepare( "DROP DATABASE ".$DBName);
			
			$pdo_query->execute();
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.MySQL.php -> DropDatabase(); Error = ".$e);
		}
		
	}
	


	
	function CreateUserUserName($AccountPrefix, $Username, $Password, $Host)
	{

		try
		{
			$pdo_query = $this->DatabaseConnection->prepare( "CREATE USER '".trim($Username)."'@'".trim($Host)."' IDENTIFIED BY  '".filter_var($Password, FILTER_SANITIZE_STRING)."'");
			
			$pdo_query->execute();
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.MySQL.php -> CreateUserUserName1(); Error = ".$e);
		}
	
	
	
		try
		{
			$pdo_query = $this->DatabaseConnection->prepare("GRANT USAGE ON *.* TO  '".trim($Username)."'@'".trim($Host)."' IDENTIFIED BY :password WITH MAX_QUERIES_PER_HOUR 0 MAX_CONNECTIONS_PER_HOUR 0 MAX_UPDATES_PER_HOUR 0 MAX_USER_CONNECTIONS 0 ;");
			
			$pdo_query->bindParam(":password", $Password);
			$pdo_query->execute();
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.MySQL.php -> CreateUserUserName2(); Error = ".$e);
		}
		
	

		try
		{
			$pdo_query = $this->DatabaseConnection->prepare("GRANT ALL PRIVILEGES ON  `".$AccountPrefix."\_%` . * TO  '".$Username."'@'".$Host."';");
			
			$pdo_query->execute();
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.MySQL.php -> CreateUserUserName3(); Error = ".$e);
		}
		
		
	}

	function FlushPrivileges()
	{
		try
		{
			$pdo_query = $this->DatabaseConnection->prepare("flush privileges;");
			
			$pdo_query->execute();
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.MySQL.php -> FlushPrivileges(); Error = ".$e);
		}
		
	}

	function CreateUserDatabase($DBName)
	{

		try
		{
			$pdo_query = $this->DatabaseConnection->prepare("CREATE DATABASE ".$DBName);
			
			$pdo_query->execute();
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.MySQL.php -> CreateUserDatabase(); Error = ".$e);
		}
				
	}
	
	function AddMySQL($DomainID, $cpDatabaseName, $Username, $Password, $ClientID, $PackageID)
	{
                $oUser = new User();
                $oDomain = new Domain();
                $oPackage = new Package();

		$DomainInfoArray = array();
	        $oDomain->GetDomainInfo($DomainID, $DomainInfoArray);
		$PackageID = $DomainInfoArray["PackageID"];
		$DomainUserName = $DomainInfoArray["UserName"];

                $MySQLUsage = $oPackage->GetMySQLUsage($DomainUserName);
                $MySQLAllowance = $oPackage->GetPackageAllowance("MySQL", $PackageID);
              
		if( (($MySQLAllowance - $MySQLUsage) < 1) && ($oUser->Role != "admin") )
                {
                        return -1;
                }


		try
		{
			$pdo_query = $this->DatabaseConnection->prepare("INSERT INTO mysql VALUES (0, :client_id, :domain_username, :username, :database_name, :password, '".date("Y-m-d H:i:s")."', 0);");
			$pdo_query->bindParam(":client_id", $ClientID);
			$pdo_query->bindParam(":domain_username", $DomainUserName);
			$pdo_query->bindParam(":username", $Username);
			$pdo_query->bindParam(":database_name", $cpDatabaseName);
			$pdo_query->bindParam(":password", $Password);

			$pdo_query->execute();
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.MySQL.php -> AddMySQL(); Error = ".$e);
		}
		
		$MySQLInsertID = $this->DatabaseConnection->lastInsertId();

		$this->CreateUserDatabase($cpDatabaseName);
		$this->CreateUserUserName($DomainUserName, $Username, $Password, "localhost");

		return $MySQLInsertID;
		
	}
	
	
	
	function DeleteDomainMySQL($DomainID, $ClientID, $DomainUserName)
	{

		$ArrayCount = 0;
		$MySQLArray = array();
		$this->GetMySQLDomainList($MySQLArray, $ArrayCount, $DomainUserName);

		for($x = 0; $x < $ArrayCount; $x++)
		{
			$this->DeleteMySQL($ClientID, 'client', $MySQLArray[$x]["ID"]);
		}
	}

	function DeleteMySQL($ClientID, $Role, $IDToDelete)
	{


		$this->GetMySQLInfo($IDToDelete, $UserUserName, $UserDatabaseName);

		try
		{
			$pdo_query = $this->DatabaseConnection->prepare("UPDATE mysql SET deleted = 1 WHERE id = :id_to_delete");
			
			$pdo_query->bindParam(":id_to_delete", $IDToDelete);
			
			$pdo_query->execute();
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.MySQL.php -> DeleteMySQL(); Error = ".$e);
		}
		
	
		$this->DropDatabase($UserDatabaseName);
		
	
		$x = 0;
		$x = $this->DBUserNameCount($UserUserName);

		//print "x = ".$x."<p>";

		if($x == 0)
		{
			$this->DropUserName($UserUserName);
		}

		return 1;	
	}


	
    
}

