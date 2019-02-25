<?php

if(!isset($_SESSION))
{
     session_start();
}

include_once($_SERVER["DOCUMENT_ROOT"]."/vendor/autoload.php");

class SSL
{
        var $oDatabase = null;
        var $DatabaseConnection = null;
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





    function getExpiringLetsEncrypts()
    {
        try
        {
            $date = new DateTime(date("Y-m-d H:i:s"));
            $date->sub(new DateInterval('P75D'));

            $query = $this->DatabaseConnection->prepare("SELECT domain_id FROM domain_settings WHERE deleted = 0 AND setting_name = 'letsencrypt' AND DATE(setting_value) <= '".$date->format("Y-m-d H:i:s")."'");
            $query->execute();

            if ($result = $query->fetchAll(PDO::FETCH_ASSOC)) {
                return $result;
            }
        } catch(PDOException $e) {
            $oLog = new Log();
            $oLog->WriteLog("error", "/class.Domain.php -> getExpiringLetsEncrypts(); Error = ".$e);
        }

        return false;
    }

	function GetCertificatesChainName($DomainName)
	{

		if($DomainName == "")
		{
			return 0;
		}


		if( ! file_exists("/etc/httpd/conf/ssl/".$DomainName.".crt"))
		{
			return 0;
		}

		$Certificate = file_get_contents("/etc/httpd/conf/ssl/".$DomainName.".crt");
		  
		$CertificateInfoArray = openssl_x509_parse($Certificate);
		if( ! is_array($CertificateInfoArray))
		{
		        return 0;
		}
		$CertificateDomain = $CertificateInfoArray["name"];
		$CertificateDomain = substr($CertificateDomain, strpos($CertificateDomain, "CN=") + 3);

		if($CertificateDomain != $DomainName)
		{
		        return 0;
		}

		$ChainName = "";

		if(isset($CertificateInfoArray["issuer"]["CN"]))
		{
		        $ChainName = $CertificateInfoArray["issuer"]["CN"];
		        $ChainName = str_replace(" ", "_", $ChainName).".cer";
		}

		if($ChainName != "")
		{
		        file_put_contents($_SERVER["DOCUMENT_ROOT"]."/nm/".$DomainName.".crtchain", $ChainName);
		        return 1;
		}

		return 0;
	}
}
