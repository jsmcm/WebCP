<?php

if(file_exists($_SERVER["DOCUMENT_ROOT"]."/includes/cron/tmp/Upgrader_".date("ymd")))
{
        exit();
}

touch($_SERVER["DOCUMENT_ROOT"]."/includes/cron/tmp/Upgrader_".date("ymd"));


	require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.Settings.php");
	$oSettings = new Settings();

	$UpgradeAction = $oSettings->GetUpgradeAction();
	
	if($UpgradeAction == 0)
	{
		exit();
	}

	$options = array(
        'uri' => 'http://api.webcp.pw',
        'location' => 'http://api.webcp.pw/versions/versions.php',
        'trace' => 1);

       	$client = new SoapClient(NULL, $options);
  
	$ClientVersion = file_get_contents($_SERVER["DOCUMENT_ROOT"]."/includes/version.inc");
	$ReleaseType = $oSettings->GetUpgradeType();
	

	$UpgradeVersion = "";
	$FullFileMD5 = "";
	$PatchFileMD5 = "";
	$ReleaseDate = "";
	$ChangeLogURL = "";
      
	$UpgradeVersion = $client->CanClientUpgrade($ClientVersion, $ReleaseType);

	
	if($UpgradeVersion != "")
	{

		$ChangeLogURL = $client->GetChangeLogURL($UpgradeVersion);

		$FullFileMD5 = $client->GetFullFileMD5($UpgradeVersion);

		$PatchFileMD5 = $client->GetPatchFileMD5($UpgradeVersion);

		$ReleaseType = $client->GetReleaseType($UpgradeVersion);
	
		$ReleaseDate = $client->GetReleaseDate($UpgradeVersion);

		if(file_exists("./Tmpfile.zip"))
		{
			unlink("./Tmpfile.zip");
		}

		if($UpgradeAction == 100)
		{	
			file_put_contents("Tmpfile.zip", fopen("http://api.webcp.pw/versions/downloads/".$UpgradeVersion."_patch.zip", 'r'));

			sleep(5);
			chmod("./Tmpfile", 0755);

			$FileMD5 = md5_file("./Tmpfile.zip");
			if($PatchFileMD5 == $FileMD5)
			{
				header("Location: DoUploadFile.php?ZipFile=Tmpfile.zip");
			}
			else
			{
				unlink("./Tmpfile");
			}
		}
		else if($UpgradeAction == 50)
		{
			// Warn...
		}
	}
exit();
?>
