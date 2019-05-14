<?php
session_start();

include_once($_SERVER["DOCUMENT_ROOT"]."/vendor/autoload.php");
$oUser = new User();
$oDNS = new DNS();
$oSettings = new Settings();
$oUtils = new Utils();
$oDatabase = new Database();

require($_SERVER["DOCUMENT_ROOT"]."/includes/License.inc.php");

$ServerType = $oDNS->GetSetting("server_type");

if($ServerType != "master")
{
	print "error: Incorrect Server Type";
	exit();
}

$ID = 0;
if(isset($_GET["ID"]))
{
	$ID = intVal($_GET["ID"]);
}

if($ID < 1)
{
	print "error: No id given";
	exit();
}

$HostName = "";
$IPAddress = "";
$Password = "";
$PublicKey = "";

if($oDNS->GetSlaveData($ID, $HostName, $IPAddress, $Password, $PublicKey) != $ID)
{
	print "error: Invalid ID given";
	exit();
}

     	$options = array(
     	'uri' => $IPAddress,
     	'location' => 'http://'.$HostName.':8880/API/dns/DNS.php',
     	'trace' => 1);

	$EncryptedPassword = "";
	openssl_public_encrypt($Password, $EncryptedPassword, $PublicKey);
	
	$Password = base64_encode($EncryptedPassword);
try
{	
	$client = new SoapClient(NULL, $options);
     	$Result = $client->TestAuth($Password);
}
catch (Exception $e)
{
}

if($Result == 1)
{
	print "success: Connection passed. This means that your DNS slave is correctly set up!";
	$oDNS->SetSlaveStatus($ID, "success");
}
else if($Result == -2)
{
	print "error: Remote server is not a slave.\r\nLog into your slave name server and set it to a slave in DNS -> Settings.";
	$oDNS->SetSlaveStatus($ID, "error");
}
else if($Result == -3)
{
	print "error: Remote server does not have a private key";
	$oDNS->SetSlaveStatus($ID, "error");
}
else if($Result == -4)
{
	$oDNS->SetSlaveStatus($ID, "error");
	print "error: Remote server private key is blank";
}
else
{
	print "error: Remote server gave unspecified error code";
	$oDNS->SetSlaveStatus($ID, "error");
}
?>

