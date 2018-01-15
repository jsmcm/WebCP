<?php
session_start();

function ValidateIP($ip)
{
	return inet_pton($ip) !== false;
}


require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.User.php");
$oUser = new User();

$ClientID = $oUser->getClientId();
if($ClientID < 1)
{
        header("Location: /domains/");
        exit();
}

if($oUser->Role != "admin")
{
        header("Location: /domains/");
        exit();
}

if( ! isset($_POST["RemoteIP"]))
{
	header("location: index.php?NoteType=Success&Notes=Error, IPs not found");
}

$IPArray = explode("\n", $_POST["RemoteIP"]);

$BadIPs = "";
$GoodIPs = "";

for($x = 0; $x < count($IPArray); $x++)
{
	if(ValidateIP(trim($IPArray[$x])))
	{
		$GoodIPs = $GoodIPs.trim($IPArray[$x])."\n";
	}
	else
	{
		$BadIPs = $BadIPs.trim($IPArray[$x])."<br>";
	}
}

if($BadIPs != "")
{
	$Notes = "Not all IPs saved. Incorrect IPs:<p>".$BadIPs;
	$NoteType = "Error";
}
else
{
	$NoteType = "Success";
	$Notes = "IPs saved";
}

file_put_contents($_SERVER["DOCUMENT_ROOT"]."/API/WHMCS/server_list.txt", $GoodIPs);

header("location: index.php?NoteType=".$NoteType."&Notes=".$Notes);
?>
