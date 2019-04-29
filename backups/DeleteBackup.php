<?php

require($_SERVER["DOCUMENT_ROOT"]."/includes/License.inc.php");

$Type = "adhoc";

if(isset($_REQUEST["Type"]))
{
	$Type = $_REQUEST["Type"];
}

if(isset($_REQUEST["File"]))
{
	$File = $_REQUEST["File"];
}
else
{
	header("location: index.php?Notes=No file selected");
	exit();
}

unlink($_SERVER["DOCUMENT_ROOT"]."/backups/".$Type."/".$File);

if($Type == "adhoc")
{
	header("Location: index.php?Notes=File deleted!");
}
else 
{
	header("Location: ".$Type.".php?Notes=File deleted!");
}


