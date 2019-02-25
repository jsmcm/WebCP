<?php

require_once($_SERVER["DOCUMENT_ROOT"]."/skel/editor/includes/functions.inc.php");

if(isset($_POST["FilesAndFolders"]))
{
	$FilesAndFolder = array();
	$FilesAndFolders = json_decode($_POST["FilesAndFolders"], true);

	for($x = 0; $x < count($FilesAndFolders["Files"]); $x++)
	{
		//print "Delete File: ".$FilesAndFolders["Files"][$x]."<br>";
		DeleteFile($FilesAndFolders["Files"][$x]);
	}

	for($x = 0; $x < count($FilesAndFolders["Folders"]); $x++)
	{
		//print "Delete Directory: ".$FilesAndFolders["Folders"][$x]."<br>";
		DeleteDirectory($FilesAndFolders["Folders"][$x]);
	}
}



header("Location: index.php?Path=".$_POST["Path"]);
