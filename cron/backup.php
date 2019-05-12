<?php
session_start();

include_once($_SERVER["DOCUMENT_ROOT"]."/vendor/autoload.php");
$oUser = new User();
$oDomain = new Domain();

$ClientID = $oUser->getClientId();
if($ClientID < 1)
{
        header("Location: /index.php");
        exit();
}

$URL = "nodecp.rsa.pw";
$PostData = "Server=WebCP";

$DomainOwnerID = $oDomain->GetDomainOwnerFromDomainName($URL);

print "URL: ".$URL."<br>";
print "ClientID: ".$ClientID."<br>";
print "DomainOwnerID: ".$DomainOwnerID."<br>";
print "Role: ".$oUser->Role."<br>";

if($DomainOwnerID != $ClientID)
{
	print "Domain owner != Logged in client!!!<p>";

	if($oUser->Role != "admin")
	{
		header("location: /index.php");
		exit();
	}
}

$c = curl_init();
curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($c, CURLOPT_POSTFIELDS,  $PostData);
curl_setopt($c, CURLOPT_POST, 1);


if ( file_exists("/etc/letsencrypt/renewal/".$_POST["URL"].".conf") ) {
        curl_setopt($c, CURLOPT_URL, "https://".$_POST["URL"].":2083/read.php");
        curl_setopt($c, CURLOPT_SSL_VERIFYPEER, false);  
} else {
        curl_setopt($c, CURLOPT_URL, "http://".$_POST["URL"].":2082/read.php");
}

$ResultString = trim(curl_exec($c));
curl_close($c);


$CronArray = explode("\n", $ResultString);

for($x = 0; $x < count($CronArray); $x++)
{
	print ($x + 1).") '".$CronArray[$x]."'<br>";
}

?>
