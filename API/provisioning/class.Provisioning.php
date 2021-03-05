<?php

include_once($_SERVER["DOCUMENT_ROOT"]."/vendor/autoload.php");

class Provisioning
{ 

	private function ValidateUser($UserName, $Password)
	{
        	$oUser = new User();

		$UserID = $oUser->CheckLoginCredentials($UserName, $Password);		

		if($UserID < 1)
		{
			return false;
		}

		if($oUser->GetUserRole($UserID) != "admin")
		{
			return false;
		}

		return true;
	}


	private function RemoteAddressAllowed($RemoteAddress)
	{
		if( ! file_exists($_SERVER["DOCUMENT_ROOT"]."/API/WHMCS/server_list.txt"))
		{
			return false;
		}

		$DomainArray = file($_SERVER["DOCUMENT_ROOT"]."/API/WHMCS/server_list.txt");

		for($x = 0; $x < count($DomainArray); $x++)
		{
			
			if($RemoteAddress == trim($DomainArray[$x]))
			{
				return true;
			}
		}


		return false;
	}



	public function ChangePackage($LoginEmailAddress, $LoginPassword, $EmailAddress, $DomainName, $DomainUserName, $PackageName)
	{


		$ReturnArray = array();
		
		/*****************************************************************************
		*	
		* CHECK IF CREDENTIALS VALIDATE
		*
		*****************************************************************************/
		
		if($this->ValidateUser($LoginEmailAddress, $LoginPassword) == false)
		{
			$ReturnArray["Status"] = "false";
			$ReturnArray["ErrorMessage"] = "Login failed. Please check your server setting's username and password.";
			return $ReturnArray;
		}
		
        	$oDomain = new Domain();
     		
        	$oUser = new User();

		/*****************************************************************************
		*	
		* CHECK IF REMOTE SERVER IS ALLOWED HERE!
		*
		*****************************************************************************/
		
		if($this->RemoteAddressAllowed($_SERVER["REMOTE_ADDR"]) == false)
		{
			$ReturnArray["Status"] = "false";
			$ReturnArray["ErrorMessage"] = "Remote IP address has no permission to access this server. Please login your WebCP and go to -- Modules--Billing--WHMCS and enter this server's IP address (".$_SERVER["REMOTE_ADDR"].") into the Allowed IP field";
			return $ReturnArray;
		}

		$DomainID = $oDomain->GetDomainIDFromDomainName($DomainName);

		if($DomainID < 1)
		{
			$ReturnArray["Status"] = "false";
			$ReturnArray["ErrorMessage"] = "I cannot find that domain at that server!";
			return $ReturnArray;
		}

		
		$InfoArray = array();

		$random = random_int(1, 1000000);
		$nonceArray = [	
				$oUser->Role,
				$oUser->ClientID,
				$DomainID,
				$random
		];
		$nonce = $oSimpleNonce->GenerateNonce("getDomainInfo", $nonceArray);
		$oDomain->GetDomainInfo($DomainID, $random, $InfoArray, $nonce);
	
		if($DomainUserName != $InfoArray["UserName"])
		{
			$ReturnArray["Status"] = "false";
			$ReturnArray["ErrorMessage"] = "Remote server and this server's Usernames are not the same!";
			return $ReturnArray;
		}
		
		

		$ClientID = $oUser->UserExistsByEmail($EmailAddress);
			
		if($ClientID < 1)
		{
			$ReturnArray["Status"] = "false";
			$ReturnArray["ErrorMessage"] = "I cannot find that client email address on this server!";
			return $ReturnArray;
		}


		if($ClientID != $InfoArray["ClientID"])
		{
			$ReturnArray["Status"] = "false";
			$ReturnArray["ErrorMessage"] = "Remote server and this server's client info are not the same!";
			return $ReturnArray;
		}

		$oPackage = new Package();
		$oFTP = new FTP();


		$PackageID = $oPackage->GetPackageID($PackageName);

		if($PackageID < 1)
		{
			$ReturnArray["Status"] = "false";
			$ReturnArray["ErrorMessage"] = "That package does not exist on the server, please go there and create it first!";
			return $ReturnArray;
		}

		if($InfoArray["PackageID"] == $PackageID)
		{
			$ReturnArray["Status"] = "false";
			$ReturnArray["ErrorMessage"] = "Domain is already on that package!";
			return $ReturnArray;
		}

		$oDomain->UpdateDomainPackage($DomainID, $PackageID);

		$DiskSpace = $oPackage->GetPackageAllowance("DiskSpace", $PackageID);
		$oFTP->UpdateDomainFTPDiskQuotas($DomainID, $DiskSpace);
		$oPackage->CreateDiskQuotaScriptForDomain($DomainID, $DiskSpace);


		$ReturnArray["Status"] = "true";
		$ReturnArray["ErrorMessage"] = "";
		return $ReturnArray;

	}




	public function TerminateDomain($LoginEmailAddress, $LoginPassword, $EmailAddress, $DomainName, $DomainUserName)
	{

		$ReturnArray = array();

		
		/*****************************************************************************
		*	
		* CHECK IF CREDENTIALS VALIDATE
		*
		*****************************************************************************/
		
		if($this->ValidateUser($LoginEmailAddress, $LoginPassword) == false)
		{
			$ReturnArray["Status"] = "false";
			$ReturnArray["ErrorMessage"] = "Login failed. Please check your server setting's username and password.";
			return $ReturnArray;
		}
		
        	$oDomain = new Domain();
        	$oUser = new User();

		/*****************************************************************************
		*	
		* CHECK IF REMOTE SERVER IS ALLOWED HERE!
		*
		*****************************************************************************/
		
		if($this->RemoteAddressAllowed($_SERVER["REMOTE_ADDR"]) == false)
		{
			$ReturnArray["Status"] = "false";
			$ReturnArray["ErrorMessage"] = "Remote IP address has no permission to access this server. Please login your WebCP and go to -- Modules--Billing--WHMCS and enter this server's IP address (".$_SERVER["REMOTE_ADDR"].") into the Allowed IP field";
			return $ReturnArray;
		}

		$DomainID = $oDomain->GetDomainIDFromDomainName($DomainName);

		if($DomainID < 1)
		{
			$ReturnArray["Status"] = "false";
			$ReturnArray["ErrorMessage"] = "I cannot find that domain at that server!";
			return $ReturnArray;
		}

		
		$InfoArray = array();
		$random = random_int(1, 1000000);
		$nonceArray = [	
				$oUser->Role,
				$oUser->ClientID,
				$DomainID,
				$random
		];
		$nonce = $oSimpleNonce->GenerateNonce("getDomainInfo", $nonceArray);
		$oDomain->GetDomainInfo($DomainID, $random, $InfoArray, $nonce);
	
		if($DomainUserName != $InfoArray["UserName"])
		{
			$ReturnArray["Status"] = "false";
			$ReturnArray["ErrorMessage"] = "Remote server and this server's Usernames are not the same!";
			return $ReturnArray;
		}
		
		

		$ClientID = $oUser->UserExistsByEmail($EmailAddress);
			
		if($ClientID < 1)
		{
			$ReturnArray["Status"] = "false";
			$ReturnArray["ErrorMessage"] = "I cannot find that client email address on this server!";
			return $ReturnArray;
		}


		if($ClientID != $InfoArray["ClientID"])
		{
			$ReturnArray["Status"] = "false";
			$ReturnArray["ErrorMessage"] = "Remote server and this server's client info are not the same!";
			return $ReturnArray;
		}

		if($oDomain->DeleteDomain($ClientID, $DomainID, $Error) < 1)
		{
			$ReturnArray["Status"] = "false";
			$ReturnArray["ErrorMessage"] = "Unknown error occurred: ".$Error;
			return $ReturnArray;
		}
		
		$ReturnArray["Status"] = "true";
		$ReturnArray["ErrorMessage"] = "";
		return $ReturnArray;

	}




	public function UnsuspendDomain($LoginEmailAddress, $LoginPassword, $EmailAddress, $DomainName, $DomainUserName)
	{

		$ReturnArray = array();

		/*****************************************************************************
		*	
		* CHECK IF CREDENTIALS VALIDATE
		*
		*****************************************************************************/
		
		if($this->ValidateUser($LoginEmailAddress, $LoginPassword) == false)
		{
			$ReturnArray["Status"] = "false";
			$ReturnArray["ErrorMessage"] = "Login failed. Please check your server setting's username and password.";
			return $ReturnArray;
		}
		
        	$oDomain = new Domain();
        	$oUser = new User();

		/*****************************************************************************
		*	
		* CHECK IF REMOTE SERVER IS ALLOWED HERE!
		*
		*****************************************************************************/
		
		if($this->RemoteAddressAllowed($_SERVER["REMOTE_ADDR"]) == false)
		{
			$ReturnArray["Status"] = "false";
			$ReturnArray["ErrorMessage"] = "Remote IP address has no permission to access this server. Please login your WebCP and go to -- Modules--Billing--WHMCS and enter this server's IP address (".$_SERVER["REMOTE_ADDR"].") into the Allowed IP field";
			return $ReturnArray;
		}

		$DomainID = $oDomain->GetDomainIDFromDomainName($DomainName);

		if($DomainID < 1)
		{
			$ReturnArray["Status"] = "false";
			$ReturnArray["ErrorMessage"] = "I cannot find that domain at that server!";
			return $ReturnArray;
		}

		
		$InfoArray = array();
		$random = random_int(1, 1000000);
		$nonceArray = [	
				$oUser->Role,
				$oUser->ClientID,
				$DomainID,
				$random
		];
		$nonce = $oSimpleNonce->GenerateNonce("getDomainInfo", $nonceArray);
		$oDomain->GetDomainInfo($DomainID, $random, $InfoArray, $nonce);
	
		if($DomainUserName != $InfoArray["UserName"])
		{
			$ReturnArray["Status"] = "false";
			$ReturnArray["ErrorMessage"] = "Remote server and this server's Usernames are not the same!";
			return $ReturnArray;
		}
		
		

		$ClientID = $oUser->UserExistsByEmail($EmailAddress);
			
		if($ClientID < 1)
		{
			$ReturnArray["Status"] = "false";
			$ReturnArray["ErrorMessage"] = "I cannot find that client email address on this server!";
			return $ReturnArray;
		}


		if($ClientID != $InfoArray["ClientID"])
		{
			$ReturnArray["Status"] = "false";
			$ReturnArray["ErrorMessage"] = "Remote server and this server's client info are not the same!";
			return $ReturnArray;
		}

		if($oDomain->Unsuspend($DomainID) < 1)
		{
			$ReturnArray["Status"] = "false";
			$ReturnArray["ErrorMessage"] = "Unknown error occurred";
			return $ReturnArray;
		}
		
		$ReturnArray["Status"] = "true";
		$ReturnArray["ErrorMessage"] = "";
		return $ReturnArray;

	}









	public function SuspendDomain($LoginEmailAddress, $LoginPassword, $EmailAddress, $DomainName, $DomainUserName)
	{

		$ReturnArray = array();

		/*****************************************************************************
		*	
		* CHECK IF CREDENTIALS VALIDATE
		*
		*****************************************************************************/
		
		if($this->ValidateUser($LoginEmailAddress, $LoginPassword) == false)
		{
			$ReturnArray["Status"] = "false";
			$ReturnArray["ErrorMessage"] = "Login failed. Please check your server setting's username and password.";
			return $ReturnArray;
		}
		
		
        	$oDomain = new Domain();
        	$oUser = new User();

		/*****************************************************************************
		*	
		* CHECK IF REMOTE SERVER IS ALLOWED HERE!
		*
		*****************************************************************************/
		
		if($this->RemoteAddressAllowed($_SERVER["REMOTE_ADDR"]) == false)
		{
			$ReturnArray["Status"] = "false";
			$ReturnArray["ErrorMessage"] = "Remote IP address has no permission to access this server. Please login your WebCP and go to -- Modules--Billing--WHMCS and enter this server's IP address (".$_SERVER["REMOTE_ADDR"].") into the Allowed IP field";
			return $ReturnArray;
		}

		$DomainID = $oDomain->GetDomainIDFromDomainName($DomainName);

		if($DomainID < 1)
		{
			$ReturnArray["Status"] = "false";
			$ReturnArray["ErrorMessage"] = "I cannot find that domain at that server!";
			return $ReturnArray;
		}

		
		$InfoArray = array();
		$random = random_int(1, 1000000);
		$nonceArray = [	
				$oUser->Role,
				$oUser->ClientID,
				$DomainID,
				$random
		];
		$nonce = $oSimpleNonce->GenerateNonce("getDomainInfo", $nonceArray);
		$oDomain->GetDomainInfo($DomainID, $random, $InfoArray, $nonce);
	
		if($DomainUserName != $InfoArray["UserName"])
		{
			$ReturnArray["Status"] = "false";
			$ReturnArray["ErrorMessage"] = "Remote server and this server's Usernames are not the same!";
			return $ReturnArray;
		}
		
		

		$ClientID = $oUser->UserExistsByEmail($EmailAddress);
			
		if($ClientID < 1)
		{
			$ReturnArray["Status"] = "false";
			$ReturnArray["ErrorMessage"] = "I cannot find that client email address on this server!";
			return $ReturnArray;
		}


		if($ClientID != $InfoArray["ClientID"])
		{
			$ReturnArray["Status"] = "false";
			$ReturnArray["ErrorMessage"] = "Remote server and this server's client info are not the same!";
			return $ReturnArray;
		}

		if($oDomain->Suspend($DomainID) < 1)
		{
			$ReturnArray["Status"] = "false";
			$ReturnArray["ErrorMessage"] = "Unknown error occurred";
			return $ReturnArray;
		}
		
		$ReturnArray["Status"] = "true";
		$ReturnArray["ErrorMessage"] = "";
		return $ReturnArray;

	}

        public function ProvisionAccount($LoginEmailAddress, $LoginPassword, $FirstName, $Surname, $EmailAddress, $Password, $PackageName, $DomainName, $DomainUserName)
        {  

		//file_put_contents(dirname(__FILE__)."/log.log", "loginEmailAddress: ".$LoginEmailAddress."\r\nLoginPassword: ".$LoginPassword."\r\nFirstName: ".$FirstName."\r\nSurname: ".$Surname."\r\nEmailAddress: ".$EmailAddress."\r\nPassword: ".$Password."\r\nPackageName: ".$PackageName."\r\nDomainName: ".$DomainName."\r\nDomainUserName: ".$DomainUserName."\r\n\r\n", FILE_APPEND);

        	$oLog = new Log();

		$ReturnArray = array();

		/*****************************************************************************
		*	
		* CHECK IF CREDENTIALS VALIDATE
		*
		*****************************************************************************/
		
		if($this->ValidateUser($LoginEmailAddress, $LoginPassword) == false)
		{
			$ReturnArray["Status"] = "false";
			$ReturnArray["ErrorMessage"] = "Login failed. Please check your server setting's username and password.";
			return $ReturnArray;
		}
		
	
		
		/*****************************************************************************
		*	
		* CHECK IF REMOTE SERVER IS ALLOWED HERE!
		*
		*****************************************************************************/
		
		if($this->RemoteAddressAllowed($_SERVER["REMOTE_ADDR"]) == false)
		{
			$ReturnArray["Status"] = "false";
			$ReturnArray["ErrorMessage"] = "Remote IP address has no permission to access this server. Please login your WebCP and go to -- Modules--Billing--WHMCS and enter this server's IP address (".$_SERVER["REMOTE_ADDR"].") into the Allowed IP field";
			return $ReturnArray;
		}	 
			



		/*****************************************************************************
		*	
		* CHECK IF WE HAVE A VALID PACKAGE NAME
		*
		*****************************************************************************/
        	$oPackage = new Package();

        	$PackageID = $oPackage->GetPackageID($PackageName);

        	$oLog->WriteLog("DEBUG", "PackageID = ".$PackageID);

        	if($PackageID < 1)
        	{
			$ReturnArray["Status"] = "false";
			$ReturnArray["ErrorMessage"] = "The package '".$PackageName."' is not valid. Please create the package on the hosting server, or check your setting in WHMCS";
			return $ReturnArray;
		}



		
		/*****************************************************************************
		*	
		* ADD NEW USER IF NOT EXISTS
		*
		*****************************************************************************/
        	$oUser = new User();

        	$UserID = $oUser->UserExistsByEmail($EmailAddress);

        	$oLog->WriteLog("DEBUG", "UserID = ".$UserID);

        	if($UserID < 1)
        	{
                	$oLog->WriteLog("DEBUG", "AddUser('".$FirstName."', '".$Surname."', '".$EmailAddress."', '".$Password."', '".$Role."', '".$UserName."', 0)");

                	$UserID = $oUser->AddUser($FirstName, $Surname, $EmailAddress, $Password, 'client', $oUser->CreateUserName($FirstName.$Surname), 0);
               		$oLog->WriteLog("DEBUG", "UserID = ".$UserID);

			if($UserID < 1)
			{
				$ReturnArray["Status"] = "false";
				$ReturnArray["ErrorMessage"] = "Remote IP address has no permission to access this server. Please login your WebCP and go to -- Modules--Billing--WHMCS and enter this server's IP address (".$_SERVER["REMOTE_ADDR"].") into the Allowed IP field";
				return $ReturnArray;
			}
	
			$ReturnArray["Password"] = $Password; // Used to confirm to WHMCS that the user was created...
       		}
		else
		{
			$ReturnArray["Password"] = ""; // Used to confirm to WHMCS that the user existed and we don't know its password
		}


		/*****************************************************************************
		*	
		* FINALLY, ADD THE DOMAIN!
		*
		*****************************************************************************/
        	$oDomain = new Domain();

		if($oDomain->ValidateDomainName($DomainName) < 1)
		{
			$ReturnArray["Status"] = "false";
			$ReturnArray["ErrorMessage"] = "The domain name is invalid!";
			return $ReturnArray;
		}

		if(substr($DomainName, 0, 7) == "http://")
		{
		        $DomainName = substr($DomainName, 7);
		}

		if(substr($DomainName, 0, 4) == "www.")
		{
		        $DomainName = substr($DomainName, 4);
		}

		for($x = 0; $x < strlen($DomainName); $x++)
		{

		        if(!ctype_alnum($DomainName[$x]))
		        {
		                if($DomainName[$x] != '_' && $DomainName[$x] != '-' && $DomainName[$x] != '.')
		                {
					$ReturnArray["Status"] = "false";
					$ReturnArray["ErrorMessage"] = "The domain name is invalid!";
					return $ReturnArray;
		                }
		        }
		}

		if($oDomain->DomainExists($DomainName) > 0)
		{
				$ReturnArray["Status"] = "false";
				$ReturnArray["ErrorMessage"] = "The domain name already exists on this server!";
				return $ReturnArray;
		}


		$DomainID = $oDomain->AddDomain($DomainName, 'primary', $PackageID, $UserID, $Error);
		if($DomainID < 1)
		{
			$ReturnArray["Status"] = "false";
			$ReturnArray["ErrorMessage"] = "Unknown error (".$Error.")";
			return $ReturnArray;
		}

		$oSimpleNonce = new SimpleNonce();

		$InfoArray = array();
		$random = random_int(1, 1000000);
		$nonceArray = [	
				$oUser->Role,
				$oUser->ClientID,
				$DomainID,
				$random
		];
		$nonce = $oSimpleNonce->GenerateNonce("getDomainInfo", $nonceArray);
		$oDomain->GetDomainInfo($DomainID, $random, $InfoArray, $nonce);
	
		$ReturnArray["DomainUserName"] = $InfoArray["UserName"];

		/*****************************************************************************
		*	
		* GOT HERE?? EVERTHING WORKED!
		*
		*****************************************************************************/



		$ReturnArray["Status"] = "true";
		$ReturnArray["ErrorMessage"] = "";
		return $ReturnArray;
        }
}
