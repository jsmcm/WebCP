<?php
$ip = "";
if(isset($_REQUEST["ip"]))
{
	$ip = $_REQUEST["ip"];
}
else
{
	exit();	
}

include_once($_SERVER["DOCUMENT_ROOT"]."/vendor/autoload.php");
$oFirewall = new Firewall();

$CountryCode = "";
$CountryName = "";
$ReverseDNS = "";


$ID = $oFirewall->IPExists($ip, $CountryCode, $CountryName, $ReverseDNS);
if($ID > 0)
{
	if($ReverseDNS == "")
	{
		$ReverseDNS = gethostbyaddr($ip);
                $oFirewall->EditInfo($ID, $ReverseDNS, "reverse");
	}


	if($CountryCode == "")
	{
		$options = array(
		'uri' => 'https://api.webcp.io',
		'location' => 'https://api.webcp.io/Country.php',
		'trace' => 1);

		$client = new SoapClient(NULL, $options);
		$CountryCode = $client->GetCountryCode($ip);
		$CountryName = $client->GetCountryName($ip);

		$oFirewall->EditInfo($ID, $CountryCode, "country_code");
		$oFirewall->EditInfo($ID, $CountryName, "country");
	}

}

?>
