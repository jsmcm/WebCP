<?php
session_start();

                if( ! file_exists($_SERVER["DOCUMENT_ROOT"]."/nm/"))
                {
                        mkdir($_SERVER["DOCUMENT_ROOT"]."/nm/", 0755);
                }


require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.User.php");
$oUser = new User();

require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.MySQL.php");
$oMySQL = new MySQL();

$ClientID = $oUser->getClientId();
if($ClientID < 1)
{
        header("Location: /index.php");
        exit();
}

if($oUser->Role != 'admin')
{
	header("location: /index.php");	
	exit();
}


$DBUser = filter_var($_POST["DBUser"], FILTER_SANITIZE_STRING);
$DBName = filter_var($_POST["DBName"], FILTER_SANITIZE_STRING);
$Password = trim(filter_var($_POST["Password"], FILTER_SANITIZE_STRING));

$Role = $oUser->Role;

$oMySQL->ChangePassword($DBUser, $Password);

include_once($_SERVER["DOCUMENT_ROOT"]."/vendor/autoload.php");

use \Matomo\Ini\IniReader;

$reader = new IniReader();

// Read a file
$array = $reader->readFile($_SERVER["DOCUMENT_ROOT"]."/../config.php");

$array["DATABASE_PASSWORD"] = $Password;

// Write to a file
unlink($_SERVER["DOCUMENT_ROOT"]."/../config.php");

foreach ($array as $key=>$value) {
    file_put_contents($_SERVER["DOCUMENT_ROOT"]."/../config.php", $key."=".$value."\r\n", FILE_APPEND);
}

touch($_SERVER["DOCUMENT_ROOT"]."/nm/root.password");

while (file_exists($_SERVER["DOCUMENT_ROOT"]."/nm/root.password")) {
    sleep(1);
}


header("location: index.php?Notes=Password changed");

?>


