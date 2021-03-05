<?php

$FileName = $_REQUEST["FileName"];
$Permissions = base_convert($_REQUEST["Permissions"], 8, 10);


if(file_exists($FileName))
{
	chmod($FileName, $Permissions);
}

print substr(sprintf('%o', fileperms($FileName)), -4);
