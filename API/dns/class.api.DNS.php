<?php

include_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.DNS.php");

class API_DNS
{ 

	function DeleteSubDomainForSlave($Message)
	{
		$oDNS = new DNS();

		$ServerType = $oDNS->GetSetting("server_type");
		
		if( $ServerType != "master")
		{
			return -2;
		}

		if( ! file_exists($_SERVER["DOCUMENT_ROOT"]."/includes/protected/key.private"))
		{
			return -3;
		}

		$PrivateKey = file_get_contents($_SERVER["DOCUMENT_ROOT"]."/includes/protected/key.private");	
		if($PrivateKey == "")
		{
			return -4;
		}

		$MyPassword = $oDNS->GetSetting("password");

		$DecryptedMessage = "";

		$Message = base64_decode($Message);

		$Return = -7;
		if(openssl_private_decrypt($Message, $DecryptedMessage, $PrivateKey))
		{
			$Data = array();
			$Data = json_decode($DecryptedMessage);
			if($Data->Password != $MyPassword)
			{
				return -6;
			}

			$Return = $oDNS->DeleteSubDomain($Data->SubDomain, $Data->ParentDomainName);
			
		}

		return $Return;
	}

	function AddSubDomainForSlave($Message)
	{
		$oDNS = new DNS();

		$ServerType = $oDNS->GetSetting("server_type");
		
		if( $ServerType != "master")
		{
			return -2;
		}

		if( ! file_exists($_SERVER["DOCUMENT_ROOT"]."/includes/protected/key.private"))
		{
			return -3;
		}

		$PrivateKey = file_get_contents($_SERVER["DOCUMENT_ROOT"]."/includes/protected/key.private");	
		if($PrivateKey == "")
		{
			return -4;
		}

		$MyPassword = $oDNS->GetSetting("password");

		$DecryptedMessage = "";

		$Message = base64_decode($Message);

		$Return = -7;
		if(openssl_private_decrypt($Message, $DecryptedMessage, $PrivateKey))
		{
			$Data = array();
			$Data = json_decode($DecryptedMessage);
			if($Data->Password != $MyPassword)
			{
				return -6;
			}

			$Return = $oDNS->AddSubDomain($Data->SubDomain, $Data->ParentDomainName, $Data->IPv4, $Data->IPv6);
		
			return $Return;	
		}
	}

	function AddZoneForSlave($Message)
	{
		$oDNS = new DNS();


		$ServerType = $oDNS->GetSetting("server_type");
		
		if( $ServerType != "master")
		{
			return -2;
		}

		if( ! file_exists($_SERVER["DOCUMENT_ROOT"]."/includes/protected/key.private"))
		{
			return -3;
		}

		$PrivateKey = file_get_contents($_SERVER["DOCUMENT_ROOT"]."/includes/protected/key.private");	
		if($PrivateKey == "")
		{
			return -4;
		}

		$MyPassword = $oDNS->GetSetting("password");

		$DecryptedMessage = "";

		$Message = base64_decode($Message);

		$Return = -7;
		if(openssl_private_decrypt($Message, $DecryptedMessage, $PrivateKey))
		{
			$Data = array();
			$Data = json_decode($DecryptedMessage);
			if($Data->Password != $MyPassword)
			{
				return -6;
			}
			$Return = $oDNS->AddZone($Data->DomainName, $Data->IPv4, $Data->IPv6);
		}

		return $Return;
	}

	function DeleteZoneForSlave($Message)
	{
		$oDNS = new DNS();

		$ServerType = $oDNS->GetSetting("server_type");
		
		if( $ServerType != "master")
		{
			return -2;
		}

		if( ! file_exists($_SERVER["DOCUMENT_ROOT"]."/includes/protected/key.private"))
		{
			return -3;
		}

		$PrivateKey = file_get_contents($_SERVER["DOCUMENT_ROOT"]."/includes/protected/key.private");	
		if($PrivateKey == "")
		{
			return -4;
		}

		$MyPassword = $oDNS->GetSetting("password");

		$DecryptedMessage = "";

		$Message = base64_decode($Message);

		$Return = -7;
		if(openssl_private_decrypt($Message, $DecryptedMessage, $PrivateKey))
		{
			$Data = array();
			$Data = json_decode($DecryptedMessage);
			if($Data->Password != $MyPassword)
			{
				return -6;
			}

			$Return = $oDNS->DeleteZone($Data->DomainName);
		}
		return $Return;
	}

	public function TestSlaveAuth($Password)
     	{
		$oDNS = new DNS();

		$ServerType = $oDNS->GetSetting("server_type");

		if( $ServerType != "master")
		{
			return -2;
		}

		if( ! file_exists($_SERVER["DOCUMENT_ROOT"]."/includes/protected/key.private"))
		{
			return -3;
		}

		$PrivateKey = file_get_contents($_SERVER["DOCUMENT_ROOT"]."/includes/protected/key.private");	
		if($PrivateKey == "")
		{
			return -4;
		}

		$MyPassword = $oDNS->GetSetting("password");

		$DecryptedPassword = "";

		$Password = base64_decode($Password);
		openssl_private_decrypt($Password, $DecryptedPassword, $PrivateKey);

		if($DecryptedPassword == $MyPassword)
		{
			return 1;
		}

		return -5;	
        }

	public function TestAuth($Password)
     	{
		$oDNS = new DNS();

		$ServerType = $oDNS->GetSetting("server_type");

		if( $ServerType != "slave")
		{
			return -2;
		}

		if( ! file_exists($_SERVER["DOCUMENT_ROOT"]."/includes/protected/key.private"))
		{
			return -3;
		}

		$PrivateKey = file_get_contents($_SERVER["DOCUMENT_ROOT"]."/includes/protected/key.private");	
		if($PrivateKey == "")
		{
			return -4;
		}

		$MyPassword = $oDNS->GetSetting("password");

		$DecryptedPassword = "";

		$Password = base64_decode($Password);
		openssl_private_decrypt($Password, $DecryptedPassword, $PrivateKey);

		if($DecryptedPassword == $MyPassword)
		{
			return 1;
		}

		return -5;	
        }

	function CreateZoneOnSlave($Message)
	{
		$oDNS = new DNS();

		$ServerType = $oDNS->GetSetting("server_type");

		if( $ServerType != "slave")
		{
			return -2;
		}

		if( ! file_exists($_SERVER["DOCUMENT_ROOT"]."/includes/protected/key.private"))
		{
			return -3;
		}

		$PrivateKey = file_get_contents($_SERVER["DOCUMENT_ROOT"]."/includes/protected/key.private");	
		if($PrivateKey == "")
		{
			return -4;
		}

		$MyPassword = $oDNS->GetSetting("password");

		$DecryptedMessage = "";

		$Return = -5;

		$Message = base64_decode($Message);
		if(openssl_private_decrypt($Message, $DecryptedMessage, $PrivateKey))
		{
			$Data = array();

			$Data = json_decode($DecryptedMessage);
			if($Data->Password != $MyPassword)
			{
				return -6;
			}

			if($oDNS->DomainExists($Data->Domain) > 0)
			{	
				return -7;
			}

			$Return = $oDNS->__AddSOA(1, $Data->Domain);
			$oDNS->MakeZoneDataFile();
			
		}

		return $Return;
	}

        function DeleteZoneOnSlave($Message)
        {
                $oDNS = new DNS();

                $ServerType = $oDNS->GetSetting("server_type");

                if( $ServerType != "slave")
                {
                        return -2;
                }

                if( ! file_exists($_SERVER["DOCUMENT_ROOT"]."/includes/protected/key.private"))
                {
                        return -3;
                }

                $PrivateKey = file_get_contents($_SERVER["DOCUMENT_ROOT"]."/includes/protected/key.private");
                if($PrivateKey == "")
                {
                        return -4;
                }

                $MyPassword = $oDNS->GetSetting("password");

                $DecryptedMessage = "";

                $Return = -5;

                $Message = base64_decode($Message);
                if(openssl_private_decrypt($Message, $DecryptedMessage, $PrivateKey))
                {
                        $Data = array();

                        $Data = json_decode($DecryptedMessage);
                        if($Data->Password != $MyPassword)
                        {
                                return -6;
                        }

                        $Return = $oDNS->DeleteZone($Data->Domain);
                }

                return $Return;
        }

	

}
?>
