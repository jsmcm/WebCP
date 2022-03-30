<?php
session_start();

include_once($_SERVER["DOCUMENT_ROOT"]."/vendor/autoload.php");

$oUser = new User();
$oDNS = new DNS();
$oSettings = new Settings();
$oUtils = new Utils();
$oDatabase = new Database();
$oLog = new Log();

require($_SERVER["DOCUMENT_ROOT"]."/includes/License.inc.php");

$ServerType = $oDNS->GetSetting("server_type");

if($ServerType != "slave") {
	print "error: Incorrect Server Type";
	exit();
}
$HostName = $oDNS->GetSetting("master_host_name");
$IPAddress = $oDNS->GetSetting("master_ip_address");
$Password = $oDNS->GetSetting("master_password");
$PublicKey = $oDNS->GetSetting("master_public_key");
$Result = 0;
$retried = false;
$port = "8443";
$sslNotice = false;

	function connectToRemote($port, $ipAddress, $hostName, $password, $publicKey, $oLog)
	{
     		$options = array(
     		'uri' => $ipAddress,
     		'location' => 'http'.(($port=="8443")?'s':'').'://'.$hostName.':'.$port.'/api/dns/DNS.php',
     		'trace' => 1);

		$EncryptedPassword = "";
		openssl_public_encrypt($password, $EncryptedPassword, $publicKey);
	
		$password = base64_encode($EncryptedPassword);

		try {	
			$client = new SoapClient(NULL, $options);
     			return $client->TestSlaveAuth($password);
		} catch (Exception $e) {
			$oLog->WriteLog("dns", $e->getMessage());
			return -5;
		}
	}

retry:
	$Result = connectToRemote($port, $IPAddress, $HostName, $Password, $PublicKey, $oLog);

	if($Result == 1) {
		print "success: Connection passed. This means that your DNS master is correctly set up!";
		if ( $sslNotice == true ) {
			print "\r\n\r\nNOTE: The remote master does not have an SSL certificate. We could connect to http but this is not secure!";
		}
	} else if($Result == -2) {
		print "error: Remote server is not a master.\r\nLog into your slave name server and set it to a master in DNS -> Settings.";
	} else if($Result == -3) {
		print "error: Remote server does not have a private key";
	} else if($Result == -4) {
		$oDNS->SetSlaveStatus($ID, "error");
	} else if ($Result == -5 && $retried == false ) {
		$port = "8880";
		$sslNotice = true;
		$retried = true;
		goto retry;
	} else {
		print "error: Remote server gave unspecified error code.";
	}


