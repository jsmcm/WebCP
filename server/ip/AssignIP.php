<?php

require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.DNS.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.SSL.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.Domain.php");



                if( ! file_exists($_SERVER["DOCUMENT_ROOT"]."/nm/"))
                {
                        mkdir($_SERVER["DOCUMENT_ROOT"]."/nm/", 0755);
                }

$oSSL = new SSL();
$oDNS = new DNS();
$oDomain = new Domain();

$DomainName = $oDomain->GetDomainNameFromDomainID($_POST["DomainID"]);

if($oDNS->AssignIP($_POST["IPAddress"], $DomainName) == true)
{
	$oSSL->GetCertificatesChainName($DomainName);
	touch($_SERVER["DOCUMENT_ROOT"]."/nm/".$_POST["DomainID"].".subdomain");
}

header("Location: index.php?NoteType=success&Notes=IP Address Assigned");
?>
