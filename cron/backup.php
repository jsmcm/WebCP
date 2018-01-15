<?php
session_start();

require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.User.php");
$oUser = new User();

require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.Domain.php");
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
curl_setopt($c, CURLOPT_URL, "http://".$URL.":20020/read.php");

$ResultString = trim(curl_exec($c));
curl_close($c);


$CronArray = explode("\n", $ResultString);

for($x = 0; $x < count($CronArray); $x++)
{
	print ($x + 1).") '".$CronArray[$x]."'<br>";
}

?>
