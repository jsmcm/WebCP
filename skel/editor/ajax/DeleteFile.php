<?php

require_once($_SERVER["DOCUMENT_ROOT"]."/skel/editor/includes/functions.inc.php");

$FileName = $_GET["FileName"];

$x = DeleteFile($FileName);

if($x == 1)
{
	print "deleted";
}
else if($x == -1)
{
	print "That file does not exist";
}
else
{
	print "Cannot delete file (please check permissions)";
}

?>
