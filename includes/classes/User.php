<?php
/*********************************************************************
*********************************************************************/
if(!isset($_SESSION)) 
{
     session_start();
}

include_once($_SERVER["DOCUMENT_ROOT"]."/vendor/autoload.php");


class User 
{
	
	var $FirstName = '';
	var $Surname = '';
	var $ClientID = -1;
	var $EmailAddress = 0;
	var $Role = "";
	var $UserName = "";
	var $PackageID = 0;

	var $LastErrorNumber = 0;
	var $LastErrorDescription = "";

	var $UserInfoArray = array();

	var $oDatabase = null;
	var $DatabaseConnection = null;


	function __construct() 
	{

		$this->oDatabase = new Database();
		$this->DatabaseConnection = $this->oDatabase->GetConnection();

		if(!file_exists($_SERVER["DOCUMENT_ROOT"]."/tmp"))
		{
			mkdir($_SERVER["DOCUMENT_ROOT"]."/tmp", 0755);
		}

		if(isset($_SESSION["client_id"]))
		{
			if($_SESSION["client_id"] > 0)
			{
				$this->GetUserInfo($_SESSION["client_id"]);
			}
		}
   	}

        function GetUserRole($ClientID)
        {

		try
		{
			$query = $this->DatabaseConnection->prepare("SELECT role FROM admin WHERE id = :client_id AND deleted = 0");
			$query->bindParam(":client_id", $ClientID);
			$query->execute();
	
			if($result = $query->fetch(PDO::FETCH_ASSOC))
			{
				return $result["role"];
			}
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.User.php -> GetUserRole(); Error = ".$e);
		}			

		return "";
        }

        function UserNameExists($UserName)
        {

				$UserName = trim(strtolower($UserName));

				try
				{
					$query = $this->DatabaseConnection->prepare("SELECT username FROM admin WHERE username = :user_name AND deleted = 0");
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
					$oLog->WriteLog("error", "/class.User.php -> UserNameExists(); Error = ".$e);
				}

                return 0;
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

                return trim(strtolower($UserName));


        }

   
	function getClientId()
	{
		return $this->ClientID;
	}
	
	
	
	function UserExistsByEmail($EmailAddress) 
	{
		
		try
		{
			$query = $this->DatabaseConnection->prepare("SELECT id FROM admin WHERE email_address = :email_address AND deleted = 0");
			$query->bindParam(":email_address", $EmailAddress);
			$query->execute();
	
			if($result = $query->fetch(PDO::FETCH_ASSOC))
			{
				return $result["id"];
			}
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.User.php -> UserExistsByEmail(); Error = ".$e);
		}


		return -1;

	}
	

		
	function UserExists($Username) 
	{
		$Username = trim($Username);

		try
		{
			$query = $this->DatabaseConnection->prepare("SELECT id FROM admin WHERE username = :user_name AND deleted = 0");
			$query->bindParam(":user_name", $Username);
			$query->execute();
	
			if($result = $query->fetch(PDO::FETCH_ASSOC))
			{
				return $result["id"];
			}
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.User.php -> UserExists(); Error = ".$e);
		}


		return -1;

	}
	

	function GetUseremail_address($ClientID) 
	{
		

		try
		{
			$query = $this->DatabaseConnection->prepare("SELECT username FROM admin WHERE id = :client_id AND deleted = 0");
			$query->bindParam(":client_id", $ClientID);
			$query->execute();
	
			if($result = $query->fetch(PDO::FETCH_ASSOC))
			{
				return $result["email_address"];
			}
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.User.php -> GetUseremail_address(); Error = ".$e);
		}

		
		return "";
		
	}
    
    
    
    

	function ResetPassword($EmailAddress)
	{

		$password = rand(0,9);
		$password = $password.rand(0,9);
		$password = $password.rand(0,9);
		$password = $password.rand(0,9);
		$password = $password.rand(0,9);
		$password = $password.rand(0,9);

		try
		{
			$query = $this->DatabaseConnection->prepare("UPDATE admin SET password = :password WHERE email_address = :email_address AND deleted = 0");
			
			$md5Password = md5($password);

			$query->bindParam(":password", $md5Password);
			$query->bindParam(":email_address", $EmailAddress);
			$query->execute();
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.User.php -> ResetPassword(); Error = ".$e);
		}
		return $password;

	}
	


	function GetUserID($UserName)
	{
		
		$UserName = trim($UserName);
		try
		{
			$query = $this->DatabaseConnection->prepare("SELECT id FROM admin WHERE username = :user_name AND deleted = 0");

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
			$oLog->WriteLog("error", "/class.User.php -> GetUserID(); Error = ".$e);
		}

		return -1;
	}


	function GetPackageID($ClientID)
	{
		
		try
		{
			$query = $this->DatabaseConnection->prepare("SELECT package_id FROM admin WHERE id = :client_id AND deleted = 0");

			$query->bindParam(":client_id", $ClientID);
			$query->execute();
	
			if($result = $query->fetch(PDO::FETCH_ASSOC))
			{
				return $result["package_id"];
			}
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.User.php -> GetPackageID(); Error = ".$e);
		}
	

		return 0;
	}
	
	function GetUserName($ClientID)
	{
		
		
		try
		{
			$query = $this->DatabaseConnection->prepare("SELECT username FROM admin WHERE id = :client_id AND deleted = 0");

			$query->bindParam(":client_id", $ClientID);
			$query->execute();
	
			if($result = $query->fetch(PDO::FETCH_ASSOC))
			{
				return $result["username"];
			}
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.User.php -> GetUserName(); Error = ".$e);
		}
	

		return "";
	}

	
	function __GetUserInfoArray($ClientID, &$ClientArray)
	{
		
		$ClientArray = array();
		$ClientArray["ClientID"] = 0;
		$ClientArray["FirstName"] = "";
		$ClientArray["Surname"] = "";
		$ClientArray["EmailAddress"] = "";
		$ClientArray["Role"] = "";
		$ClientArray["UserName"] = "";
		$ClientArray["Password"] = "";
		
		
	
		
		try
		{
			$query = $this->DatabaseConnection->prepare("SELECT * FROM admin WHERE id = :client_id AND deleted = 0");

			$query->bindParam(":client_id", $ClientID);
			$query->execute();
	
			if($result = $query->fetch(PDO::FETCH_ASSOC))
			{
				$ClientArray["ClientID"] = $ClientID;
				$ClientArray["FirstName"] = $result["first_name"];
				$ClientArray["Surname"] = $result["surname"];
				$ClientArray["EmailAddress"] = $result["email_address"];
				$ClientArray["Role"] = $result["role"];
				$ClientArray["UserName"] = $result["username"];
				$ClientArray["Password"] = $result["password"];
			}
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.User.php -> __GetUserInfoArray(); Error = ".$e);
		}
	
	}
	
	function GetUserInfoArray($ClientID, &$ClientArray)
	{
		if(isset($this->UserInfoArray[$ClientID]))
		{
			$ClientArray = $this->UserInfoArray[$ClientID];
			return;
		}

		$this->__GetUserInfoArray($ClientID, $ClientArray);
		$this->UserInfoArray[$ClientID] = $ClientArray;
	}
		
	function GetUserInfo($ClientID)
	{
		
		try
		{
			$query = $this->DatabaseConnection->prepare("SELECT * FROM admin WHERE id = :client_id AND deleted = 0");

			$query->bindParam(":client_id", $ClientID);
			$query->execute();
	
			if($result = $query->fetch(PDO::FETCH_ASSOC))
			{
				$this->ClientID = $ClientID;
				$this->FirstName = $result["first_name"];
				$this->Surname = $result["surname"];
				$this->EmailAddress = $result["email_address"];
				$this->Role = $result["role"];
				$this->UserName = $result["username"];
	
				return $this->ClientID;
			}
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.User.php -> GetUserInfo(); Error = ".$e);
		}
	

		return -1;
		
	}
	
	
	
	function GetLoggedInclient_id()
	{
		return $_SESSION["client_id"];
	}
	
	

	function CheckLoginCredentials($EmailAddress, $Password) 
	{
		

		try
		{
			$query = $this->DatabaseConnection->prepare("SELECT * FROM admin WHERE (email_address = :email_address OR username = :user_name) AND password = :password AND deleted = 0");

			$Password = md5($Password);
			$query->bindParam(":email_address", $EmailAddress);
			$query->bindParam(":user_name", $EmailAddress);
			$query->bindParam(":password", $Password);
			$query->execute();
	
			if($result = $query->fetch(PDO::FETCH_ASSOC))
			{
				$this->ClientID = $result["id"];
				$_SESSION["client_id"] = $this->ClientID;
				
		
				$this->GetUserInfo($this->ClientID);

				touch($_SERVER["DOCUMENT_ROOT"]."/tmp/".session_id(), 0755);
	
				return $this->ClientID;
			}
			else
			{
	
				$this->Logout();
				return -1;
			}			
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.User.php -> CheckLoginCredentials(); Error = ".$e);
		}

	}
    
	function Logout()
	{
		$this->ClientID = -1;
		$this->FirstName = "";
		$this->Surname = "";
		$this->EmailAddress = "";
		$this->Role ="";
		$this->UserName = "";
		$this->PackageID = 0;
		$_SESSION["client_id"] = -1;

		if( file_exists($_SERVER["DOCUMENT_ROOT"]."/tmp/".session_id() ) )
		{
			unlink($_SERVER["DOCUMENT_ROOT"]."/tmp/".session_id());
		}

	}
	
	
	
	function GetUserDetails($UserID, &$FirstName, &$Surname, &$EmailAddress, &$Username, &$UserRole)
	{

		$FirstName = "";
		$Surname = "";
		$EmailAddress = "";
		$Username = "";
		$UserRole = "";

		$UserID = trim($UserID);

		try
		{
			$query = $this->DatabaseConnection->prepare("SELECT * FROM admin WHERE deleted = 0 AND id = :id");

			$query->bindParam(":id", $UserID);
			$query->execute();
	
			if($result = $query->fetch(PDO::FETCH_ASSOC))
			{
				$FirstName = $result["first_name"];
				$Surname = $result["surname"];
				$EmailAddress = $result["email_address"];
				$Surname = $result["surname"];		
				$Username = $result["username"];
				$EmailAddress = $result["email_address"];
				$UserRole = $result["role"];
			}
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.User.php -> GetUserDetails(); Error = ".$e);
		}
	
	}


	function GetUserListFromPackageID(&$UserArray, &$ArrayCount, $PackageID)
	{
		$UserArray = array();

		$ArrayCount = 0;
		
		try
		{

			if($PackageID == "0")
			{
				$query = $this->DatabaseConnection->prepare("SELECT * FROM admin WHERE deleted = 0");
			}
			else
			{
				$query = $this->DatabaseConnection->prepare("SELECT * FROM admin WHERE deleted = 0 AND id IN (SELECT DISTINCT(client_id) FROM domains WHERE domain_type = 'primary' AND package_id IN (".$PackageID."))");
			}
						
			
			$query->execute();
	
			while($result = $query->fetch(PDO::FETCH_ASSOC))
			{ 
				$UserArray[$ArrayCount]["id"] = $result["id"];
				$UserArray[$ArrayCount]["role"] = $result["role"];
				$UserArray[$ArrayCount]["first_name"] = $result["first_name"];
				$UserArray[$ArrayCount]["surname"] = $result["surname"];
				$UserArray[$ArrayCount]["username"] = $result["username"];
				$UserArray[$ArrayCount++]["email_address"] = $result["email_address"];
			}
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.User.php -> GetUserListFromPackageID(); Error = ".$e);
		}

		
	}
	
	
	function GetUserList(&$UserArray, &$ArrayCount, $ClientID, $Role)
	{
		$UserArray = array();
		$ArrayCount = 0;
		
		$ClientID = trim($ClientID);

		try
		{

			if($Role == 'admin')
			{
				$query = $this->DatabaseConnection->prepare("SELECT * FROM admin WHERE deleted = 0 ORDER BY username");
			}
			else if($Role == 'reseller')
			{
				$query = $this->DatabaseConnection->prepare("SELECT * FROM admin WHERE id in (SELECT client_id FROM reseller_relationships WHERE reseller_id = :client_id AND deleted = 0 UNION SELECT :client_id2 AS client_id);");

				$query->bindParam(":client_id", $ClientID);
				$query->bindParam(":client_id2", $ClientID);
			}
			else
			{	
				$query = $this->DatabaseConnection->prepare("SELECT * FROM admin WHERE id = :client_id AND deleted = 0 ORDER BY username");

				$query->bindParam(":client_id", $ClientID);
			}

			$query->execute();
	
			while($result = $query->fetch(PDO::FETCH_ASSOC))
			{
				$UserArray[$ArrayCount]["id"] = $result["id"];
				$UserArray[$ArrayCount]["role"] = $result["role"];
				$UserArray[$ArrayCount]["first_name"] = $result["first_name"];
				$UserArray[$ArrayCount]["surname"] = $result["surname"];
				$UserArray[$ArrayCount]["username"] = $result["username"];
				$UserArray[$ArrayCount++]["email_address"] = $result["email_address"];
			}
	
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.User.php -> GetUserList(); Error = ".$e);
		}

	}
	
	
	function SendNewUserEmail($FirstName, $Surname, $EmailAddress, $Password, $Username)
	{
	
    	require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.phpmailer.php");
				
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
		$somecontent = $somecontent."<div style=\"font-weight: bold; height:35px; background-color:blue; font-size:18px; padding-top:8px; padding-left:85px; color:white; font-family: 'Droid Sans', Verdana;\">User User Details</div>";
		$somecontent = $somecontent."";
		$somecontent = $somecontent."";

		$somecontent = $somecontent."";
		$somecontent = $somecontent."        <div id=\"wrap\">";
		$somecontent = $somecontent."";
                $somecontent = $somecontent."<b>Good day ".$FirstName." ".$Surname.",</b>";
                $somecontent = $somecontent."<p>";
                $somecontent = $somecontent."A login to your web hosting control panel has been created. Here you will be able to manage your web hosting account, ";
                $somecontent = $somecontent."including being able to setup email accounts, FTP accounts and MySQL databases.";
                $somecontent = $somecontent."";
                $somecontent = $somecontent."<p>";
                $somecontent = $somecontent."";
                $somecontent = $somecontent."<h3>Login Details</h3>";
                $somecontent = $somecontent."Username: <b>".$Username."</b> or <b>".$EmailAddress."</b><br>";
                $somecontent = $somecontent."Password: <b>".$Password."</b> (please change this when you log in).";
                $somecontent = $somecontent."";
                $somecontent = $somecontent."<p>";
                $somecontent = $somecontent."";
                $somecontent = $somecontent."<h3>Important Links</h3>";
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

		$AltMessage = "Hello ".$FirstName." ".$Surname.",\r\n\r\n";
		$AltMessage = $AltMessage."A login to your web hosting control panel has been created. Here you will be able to manage your web hosting account, including being able to setup email accounts, FTP accounts and MySQL databases.\r\n\r\n";

		$AltMessage = $AltMessage."Login Details\r\n";
		$AltMessage = $AltMessage."Username: ".$Username." or ".$EmailAddress."\r\n";
		$AltMessage = $AltMessage."Password: ".$Password." (please change this when you log in).\r\n\r\n";

		$AltMessage = $AltMessage."Important Links\r\n";
		$AltMessage = $AltMessage."Web Control Panel: http://".$_SERVER["SERVER_NAME"]."/webcp\r\n";
		$AltMessage = $AltMessage."Web Mail: http://".$_SERVER["SERVER_NAME"]."/mail\r\n";
		$AltMessage = $AltMessage."phpMyAdmin: http://".$_SERVER["SERVER_NAME"]."/mysql\r\n";

		$AltMessage = $AltMessage."Once your domain is live, you can use your own domain name to access these features.\r\n\r\n";

		$AltMessage = $AltMessage."Regards....\r\n";
		$AltMessage = $AltMessage."test.com";

            	$mail = new PHPMailer();

		$mail->IsSMTP();
            	$mail->ClearAddresses();
            	$mail->ClearAttachments();
            	$mail->IsHTML(true);

		$MailFrom = $_SERVER["SERVER_NAME"];
		if(strstr($MailFrom, "http://"))
		{
			$MailFrom = substr($MailFrom, 7);
		}

		if(strstr($MailFrom, "www."))
		{
			$MailFrom = substr($MailFrom, 4);
		}

            	$mail->AddReplyTo("noreply@".$MailFrom, $_SERVER["SERVER_NAME"]);
            	$mail->From = "noreply@".$MailFrom;
            	$mail->FromName = $_SERVER["SERVER_NAME"];

            	$mail->AddAddress($EmailAddress);

		if(strlen(trim($BCC)) > 0)
		{
			$mail->AddBCC($BCC);
		}

            	$mail->Subject = "Web Hosting User Login";
            	$mail->Body = $message;
		$mail->AltBody = $AltMessage;
	        $mail->WordWrap = 80;

      		$mail->Send();



	}
	
	function AddUser($FirstName, $Surname, $EmailAddress, $Password, $UserRole, $Username, $ClientID)
	{

		$Password = md5($Password);

		try
		{
			$query = $this->DatabaseConnection->prepare("INSERT INTO admin VALUES (0, :user_role, :user_name, :first_name, :surname, :email_address, :password, '".date("Y-m-d H:i:s")."', 0)");
			
			$query->bindParam(":user_role", $UserRole);
			$query->bindParam(":user_name", $Username);
			$query->bindParam(":first_name", $FirstName);
			$query->bindParam(":surname", $Surname);
			$query->bindParam(":email_address", $EmailAddress);
			$query->bindParam(":password", $Password);

			$query->execute();
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "class.User.php -> AddUser(); Error = ".$e);
		}

		$this->SendNewUserEmail($FirstName, $Surname, $EmailAddress, $Password, $Username);	

		return $this->DatabaseConnection->lastInsertId();
		
	}
	
	
	function PlainTextChangePassword($Password, $ClientID)
	{

		
		try
		{
			$query = $this->DatabaseConnection->prepare("UPDATE admin SET password = :password WHERE id = :client_id");
			
			$query->bindParam(":client_id", $ClientID);
			$query->bindParam(":password", $Password);

			$query->execute();
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "class.User.php -> PlainTextChangePassword(); Error = ".$e);
		}

		return 1;
		
	}
	
	function EditUser($FirstName, $Surname, $EmailAddress, $Password, $UserRole, $UserIDToChange, $Username, $ClientID)
	{
		if ($UserRole == "") {
                    $UserRole = $this->GetUserRole($ClientID);
                }

		$oPackage = new Package();

		$oFTP = new FTP();
	
		
		try
		{


			if(trim($Password) == "")
			{
				$query = $this->DatabaseConnection->prepare("UPDATE admin SET username = :user_name, role = :user_role, first_name = :first_name, surname = :surname, email_address = :email_address WHERE id = :user_id");
				
				$query->bindParam(":user_name", $Username);
				$query->bindParam(":user_role", $UserRole);
				$query->bindParam(":first_name", $FirstName);
				$query->bindParam(":surname", $Surname);
				$query->bindParam(":email_address", $EmailAddress);
				$query->bindParam(":user_id", $UserIDToChange);			
			}
			else
			{
				$query = $this->DatabaseConnection->prepare("UPDATE admin SET username = :user_name, role = :user_role, first_name = :first_name, surname = :surname, email_address = :email_address, password = :password WHERE id = :user_id");
			
				$Password = md5($Password);

				$query->bindParam(":password", $Password);
				$query->bindParam(":user_name", $Username);
				$query->bindParam(":user_role", $UserRole);
				$query->bindParam(":first_name", $FirstName);
				$query->bindParam(":surname", $Surname);
				$query->bindParam(":email_address", $EmailAddress);
				$query->bindParam(":user_id", $UserIDToChange);	

			}


			$query->execute();
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "class.User.php -> EditUser(); Error = ".$e);
		}

		//$DiskSpace = $oPackage->GetPackageAllowance("DiskSpace", $PackageID);
		//$oFTP->UpdateUserFTPDiskQuotas($UserIDToChange, $DiskSpace);
             	//$oPackage->CreateDiskQuotaScriptForUser($UserIDToChange, $DiskSpace);

		return 1;
		
	}	
	
	function DeleteUser($ClientID, $Role, $IDToDelete)
	{
		
		try
		{
			$query = $this->DatabaseConnection->prepare("UPDATE admin SET deleted = 1 WHERE id = :id");
			
			$query->bindParam(":id", $IDToDelete);

			$query->execute();
		}
		catch(PDOException $e)
		{
			$oLog = new Log();
			$oLog->WriteLog("error", "class.User.php -> DeleteUser(); Error = ".$e);
		}

		return 1;	
	}


    
}

