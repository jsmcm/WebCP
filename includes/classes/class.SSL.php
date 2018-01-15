<?php

if(!isset($_SESSION))
{
     session_start();
}

class SSL
{
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

?>

