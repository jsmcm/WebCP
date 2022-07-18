<?php

include_once($_SERVER["DOCUMENT_ROOT"]."/vendor/autoload.php");


$oSSL = new SSL();
$oDNS = new DNS();
$oDomain = new Domain();

                if( ! file_exists($_SERVER["DOCUMENT_ROOT"]."/nm/"))
                {
                        mkdir($_SERVER["DOCUMENT_ROOT"]."/nm/", 0755);
                }

if($oDNS->RemoveAssignment($_REQUEST["IPAddress"], $_REQUEST["DomainName"]) == true)
{        
	$oSSL->GetCertificatesChainName($_REQUEST["DomainName"]);
	touch($_SERVER["DOCUMENT_ROOT"]."/nm/".$oDomain->GetDomainIDFromDomainName($_REQUEST["DomainName"]).".subdomain");
}

header("Location: index.php?NoteType=success&Notes=IP Assignment Removed");
