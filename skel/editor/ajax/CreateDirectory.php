<?php

$DirectoryName = $_GET["DirectoryName"];

if(file_exists($DirectoryName))
{
	if(is_dir($DirectoryName))
	{
		print "Directory already exists";
	}
	else
	{
		print "A file with the same name already exists (on Linux machines you cannot have an exact match in names for files and folders";
	}
}
else
{
	mkdir($DirectoryName, 0755);
	chmod($DirectoryName, 0755);
	print "created";
}

?>
