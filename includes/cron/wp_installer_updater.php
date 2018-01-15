<?php

if(file_exists($_SERVER["DOCUMENT_ROOT"]."/includes/cron/tmp/wp_".date("ymd")))
{
	exit();
}

touch($_SERVER["DOCUMENT_ROOT"]."/includes/cron/tmp/wp_".date("ymd"));

$c = curl_init();
curl_setopt($c, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($c, CURLOPT_URL, "https://api.wordpress.org/core/version-check/1.7/");

$ResultString = trim(curl_exec($c));
curl_close($c);

$JsonArray = json_decode($ResultString);

$CurrentVersion = 0;
if(file_exists($_SERVER["DOCUMENT_ROOT"]."/installer/wordpress/current_version.txt"))
{
	$CurrentVersion = file_get_contents($_SERVER["DOCUMENT_ROOT"]."/installer/wordpress/current_version.txt");
}


$RemoteVersion = 0;
$DownloadURL = "";

$Count = count($JsonArray->offers);
for($x = 0; $x < $Count; $x++)
{
	if($JsonArray->offers[$x]->response == "upgrade")
	{
		$RemoteVersion = $JsonArray->offers[$x]->current;	
		$DownloadURL = $JsonArray->offers[$x]->packages->full;
		break;
	}
}

if($RemoteVersion != $CurrentVersion)
{
	if($DownloadURL != "")
	{

		$c = curl_init();
		curl_setopt($c, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
	
     		curl_setopt($c, CURLOPT_BINARYTRANSFER, 1);
		curl_setopt($c, CURLOPT_URL, $DownloadURL);
	
		$ResultString = curl_exec($c);
		curl_close($c);

		if(file_exists($_SERVER["DOCUMENT_ROOT"]."/installer/wordpress/WP.zip"))
		{
			unlink($_SERVER["DOCUMENT_ROOT"]."/installer/wordpress/WP.zip");
		}

		file_put_contents($_SERVER["DOCUMENT_ROOT"]."/installer/wordpress/current_version.txt", $RemoteVersion);

		file_put_contents($_SERVER["DOCUMENT_ROOT"]."/installer/wordpress/WP.zip", $ResultString); 
	}
}
exit();
?>
