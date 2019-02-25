<?php

$Run = intVal(date("H") % 4);
if($Run != 0)
{
	exit();
}

if(file_exists($_SERVER["DOCUMENT_ROOT"]."/includes/cron/tmp/Editor_".date("ymdH")))
{
        exit();
}

touch($_SERVER["DOCUMENT_ROOT"]."/includes/cron/tmp/Editor_".date("ymdH"));


require_once($_SERVER["DOCUMENT_ROOT"]."/Editor/functions.inc.php");

FetchEditableListFromRemoteServer();
FetchIconListFromRemoteServer();
