<?php
session_start();

include_once($_SERVER["DOCUMENT_ROOT"]."/vendor/autoload.php");
$oDomain = new Domain();
$oUser = new User();
$oSettings = new Settings();



if($oUser->Role != "admin") {
    header("Location: /index.php");
    exit();
}


$ClientID = $oUser->getClientId();
if($ClientID < 1) {
	header("Location: /index.php");
	exit();
}

$DomainID = filter_input(INPUT_POST, "DomainID", FILTER_SANITIZE_NUMBER_INT);
$DomainName = filter_input(INPUT_POST, "DomainName", FILTER_SANITIZE_STRING);

if($oDomain->DomainExists($DomainName) != $DomainID) {
    header("Location: index.php?NoteType=error&Notes=There was a problem with your request, please try again (id:icid!id)");
    exit();
}

$Certificate = trim(filter_input(INPUT_POST, "Certificate", FILTER_SANITIZE_STRING));


$CertificateInfoArray = openssl_x509_parse($Certificate);
if( ! is_array($CertificateInfoArray)) {
    header("Location: index.php?NoteType=error&Notes=There was a problem with your request, please try again (id:iccrt!ar)");
    exit();
}
$CertificateDomain = $CertificateInfoArray["name"];
$CertificateDomain = substr($CertificateDomain, strpos($CertificateDomain, "CN=") + 3);

if($CertificateDomain != $DomainName) {
    header("Location: index.php?NoteType=error&Notes=There was a problem with your request. That certificate is for a different domain name");
    exit();
}

$ChainName = $CertificateInfoArray["issuer"]["CN"];
$ChainName = str_replace(" ", "_", $ChainName).".cer";

file_put_contents($_SERVER["DOCUMENT_ROOT"]."/nm/".$DomainName.".crtchain", $ChainName);

$IssuerURL = $CertificateInfoArray["extensions"]["authorityInfoAccess"];
$IssuerURL = substr($IssuerURL, strpos($IssuerURL, "http", strpos($IssuerURL, "CA Issuers - URI")));
        
$x = strpos($IssuerURL, "\n");
if($x !== false) {
    $IssuerURL = substr($IssuerURL, 0, $x);
}

//print "<p>ChainName: ".$ChainName."<p>";

if( ! file_exists("/etc/nginx/ssl/".$ChainName)) {
    $IssuerName = $ChainName;
    
    set_time_limit(10);

    while( (strstr(strtolower($IssuerName), "root") == false) && ($IssuerName != "") ) {

        //print "Getting from IssuerURL: ".$IssuerURL."<p>";
        $c = curl_init();
        curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($c, CURLOPT_URL, $IssuerURL);
        curl_setopt($c, CURLOPT_BINARYTRANSFER,1);
        $ResultString = curl_exec($c);
        curl_close($c);

        //print "ResultStirng: '".$ResultString."'<p>";
        //print "chunk: ".chunk_split(base64_encode($ResultString), 64, PHP_EOL)."<p>";

        $IssuerURL = "";
        $IssuerName = "";
        
        if ( strlen(chunk_split(base64_encode($ResultString), 64, PHP_EOL)) > 10) {
            $certificateCApemContent =  '-----BEGIN CERTIFICATE-----'.PHP_EOL
            .chunk_split(base64_encode($ResultString), 64, PHP_EOL)
            .'-----END CERTIFICATE-----'.PHP_EOL;
            file_put_contents($_SERVER["DOCUMENT_ROOT"]."/nm/".$ChainName, $certificateCApemContent, FILE_APPEND);
        
            
            $CertificateInfoArray = openssl_x509_parse($certificateCApemContent);

            $IssuerName = $CertificateInfoArray["issuer"]["CN"];

            if ($IssuerName != "") {
                $IssuerName = str_replace(" ", "_", $IssuerName).".crt";
            }

            $IssuerURL = $CertificateInfoArray["extensions"]["authorityInfoAccess"];
            $IssuerURL = substr($IssuerURL, strpos($IssuerURL, "http", strpos($IssuerURL, "CA Issuers - URI")));
            
            $x = strpos($IssuerURL, "\n");
            if($x !== false)
            {
                $IssuerURL = substr($IssuerURL, 0, $x);
            }

        }

        //print "IssuerURL: ".$IssuerURL."<p>";
        //print "IssuerName: ".$IssuerName."<p>";

        //print "x: ".$x."<p>";
        
    }
}


file_put_contents($_SERVER["DOCUMENT_ROOT"]."/nm/".$DomainName.".crt", $Certificate);
touch($_SERVER["DOCUMENT_ROOT"]."/nm/".$DomainID.".subdomain");

sleep(2);
header("Location: index.php?NoteType=success&Notes=Certificate installed. It may take a few minutes to work correctly");
