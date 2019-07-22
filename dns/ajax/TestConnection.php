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

if($ServerType != "master") {
	print "error: Incorrect Server Type";
	exit();
}

$ID = 0;
if(isset($_GET["ID"])) {
	$ID = intVal($_GET["ID"]);
}

if($ID < 1) {
	print "error: No id given";
	exit();
}

$HostName = "";
$IPAddress = "";
$Password = "";
$PublicKey = "";

$Result = 0;
$retried = false;
$port = "8443";
$sslNotice = false;

if($oDNS->GetSlaveData($ID, $HostName, $IPAddress, $Password, $PublicKey) != $ID) {
	print "error: Invalid ID given";
	exit();
}

        function connectToRemote($port, $ipAddress, $hostName, $password, $publicKey, $oLog)
        {
                $options = array(
                'uri' => $ipAddress,
                'location' => 'http'.(($port=="8443")?'s':'').'://'.$hostName.':'.$port.'/API/dns/DNS.php',
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
	print "success: Connection passed. This means that your DNS slave is correctly set up!";
        if ( $sslNotice == true ) {
        	print "\r\n\r\nNOTE: The remote slave does not have an SSL certificate. We could connect to http but this is not secure!";
        }
	$oDNS->SetSlaveStatus($ID, "success");
} else if($Result == -2) {
	print "error: Remote server is not a slave.\r\nLog into your slave name server and set it to a slave in DNS -> Settings.";
	$oDNS->SetSlaveStatus($ID, "error");
} else if($Result == -3) {
	print "error: Remote server does not have a private key";
	$oDNS->SetSlaveStatus($ID, "error");
} else if($Result == -4) {
	$oDNS->SetSlaveStatus($ID, "error");
	print "error: Remote server private key is blank";
} else if ($Result == -5 && $retried == false ) {
	$port = "8880";
	$sslNotice = true;
	$retried = true;
	goto retry;
} else {
	print "error: Remote server gave unspecified error code";
	$oDNS->SetSlaveStatus($ID, "error");
}

