<?php

require_once($_SERVER["DOCUMENT_ROOT"]."/skel/editor/includes/functions.inc.php");

$DirectoryName = $_GET["DirectoryName"];

$x = DeleteDirectory($DirectoryName);

if($x == 1)
{
	print "deleted";
}
else if($x == -1)
{
	print "Directory expected but file name given!";
}
else if($x == -2)
{
	print "No such file or directory";
}
else
{
	print "Unknown error while deleting folder!";
}

?>
