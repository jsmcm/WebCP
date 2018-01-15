<?php

$FileName = $_GET["FileName"];

if(file_exists($FileName))
{
	print "File already exists";
}
else
{
	$handle = fopen($FileName, 'w') or die('Cannot create file');
	fclose($handle);

	chmod($FileName, 0755);
	print "created";
}

?>
