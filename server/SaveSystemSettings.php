<?php

//foreach($_POST as $key => $val)
//{
//	print $key." = ".$val."<br>";
//}
//exit();

session_start();

function __autoload($classname)
{
	require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.".$classname.".php");
}

$oUser = new User();
$oUtils = new Utils();

$ClientID = $oUser->getClientId();
if($ClientID < 1)
{
	header("Location: /index.php");
	exit();
}

if($oUser->Role != "admin")
{
        header("Location: /index.php");
        exit();
}


$oSettings = new Settings();

if( ! isset($_POST["ForwardSystemEmailsTo"]))
{
	header("location: settings.php?Notes=Something went wrong, settings not saved!");
	exit();
}

$WebCPTitle = "";
if(isset($_POST["WebCPTitle"]))
{
	$WebCPTitle = $_POST["WebCPTitle"];
}

$WebCPName = "";
if(isset($_POST["WebCPName"]))
{
	$WebCPName = $_POST["WebCPName"];
}

$WebCPLink = "";
if(isset($_POST["WebCPLink"]))
{
	$WebCPLink = $_POST["WebCPLink"];
}

$SendSystemEmails = "off";
if(isset($_POST["SendSystemEmails"]))
{
	$SendSystemEmails = $_POST["SendSystemEmails"];
}

$PrivateNS1 = "";
if(isset($_POST["PrivateNS1"]))
{
	$PrivateNS1 = $_POST["PrivateNS1"];
}

$PrivateNS2 = "";
if(isset($_POST["PrivateNS2"]))
{
	$PrivateNS2 = $_POST["PrivateNS2"];
}

$TrafficQuota = 0;
if(isset($_POST["TrafficQuota"]))
{
	$TrafficQuota = floatval($_POST["TrafficQuota"]);

	$TrafficQuota = $oUtils->ConvertToBytes($TrafficQuota, "Gb");
}

$UpgradeAction = 100;
if(isset($_POST["UpgradeAction"]))
{
	$UpgradeAction = $_POST["UpgradeAction"];
}

$UpgradeType = 100;
if(isset($_POST["UpgradeType"]))
{
	$UpgradeType = $_POST["UpgradeType"];
}

$oSettings->SetUpgradeAction($UpgradeAction);
$oSettings->SetUpgradeType($UpgradeType);

$oSettings->SetServerTrafficAllowance($TrafficQuota);

$oSettings->SetPrivateNS1($PrivateNS1);
$oSettings->SetPrivateNS2($PrivateNS2);

$oSettings->SetSendSystemEmails($SendSystemEmails);
$oSettings->SetWebCPTitle($WebCPTitle);
$oSettings->SetWebCPLink($WebCPLink);
$oSettings->SetWebCPName($WebCPName);

$oSettings->SetForwardSystemEmailsTo($_POST["ForwardSystemEmailsTo"]);
header("location: settings.php?Notes=Settings saved!");

?>
