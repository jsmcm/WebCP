<?php
session_start();

function __autoload($classname)
{
        require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.".$classname.".php");
}

                if( ! file_exists($_SERVER["DOCUMENT_ROOT"]."/nm/"))
                {
                        mkdir($_SERVER["DOCUMENT_ROOT"]."/nm/", 0755);
                }

$oUser = new User();
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


$DBUser = $_POST["DBUser"];
$DBName = $_POST["DBName"];
$Password = $_POST["Password"];

$Role = $oUser->Role;

$oMySQL->ChangePassword($DBUser, $Password);

$fp = fopen($_SERVER["DOCUMENT_ROOT"]."/nm/root.password", "w");
fwrite($fp, $Password);
fclose($fp);

while(file_exists($_SERVER["DOCUMENT_ROOT"]."/nm/root.password"))
{
	sleep(3);
}


header("location: index.php?Notes=Password changed");

?>


