<?php
session_start();

require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.User.php");
$oUser = new User();

require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.DNS.php");
$oDNS = new DNS();

require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.Settings.php");
$oSettings = new Settings();

require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.Utils.php");
$oUtils = new Utils();

require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.Database.php");
$oDatabase = new Database();

require($_SERVER["DOCUMENT_ROOT"]."/includes/License.inc.php");

$ServerType = $oDNS->GetSetting("server_type");

if($ServerType != "slave")
{
	print "error: Incorrect Server Type";
	exit();
}
$HostName = $oDNS->GetSetting("master_host_name");
$IPAddress = $oDNS->GetSetting("master_ip_address");
$Password = $oDNS->GetSetting("master_password");
$PublicKey = $oDNS->GetSetting("master_public_key");


     	$options = array(
     	'uri' => $IPAddress,
     	'location' => 'http://'.$HostName.':10025/API/dns/DNS.php',
     	'trace' => 1);

	$EncryptedPassword = "";
	openssl_public_encrypt($Password, $EncryptedPassword, $PublicKey);
	
	$Password = base64_encode($EncryptedPassword);
try
{	
	$client = new SoapClient(NULL, $options);
     	$Result = $client->TestSlaveAuth($Password);
}
catch (Exception $e)
{
}

if($Result == 1)
{
	print "success: Connection passed. This means that your DNS master is correctly set up!";
}
else if($Result == -2)
{
	print "error: Remote server is not a master.\r\nLog into your slave name server and set it to a master in DNS -> Settings.";
}
else if($Result == -3)
{
	print "error: Remote server does not have a private key";
}
else if($Result == -4)
{
	$oDNS->SetSlaveStatus($ID, "error");
}
else
{
	print "error: Remote server gave unspecified error code";
}
?>

