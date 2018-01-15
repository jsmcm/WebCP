<?php


if(isset($_POST["FilesAndFolders"]))
{
	$OldFile = substr($_POST["FilesAndFolders"], 0, strpos($_POST["FilesAndFolders"], ";"));
	$NewFile = substr($_POST["FilesAndFolders"], strpos($_POST["FilesAndFolders"], ";") + 1);

	//print "OldFile: '".$OldFile."'<br>";
	//print "NewFile: '".$NewFile."'<br>";

	if( ($OldFile != "") && ($NewFile != "") )
	{
		if(substr($OldFile, 0, 4) == "file")
		{
			//print "FILE<p>";
			$OldFile = substr($OldFile, 4);

			$x = strrpos($OldFile, "/");
			//print "x = ".$x."<br>";
		
			$Path = substr($OldFile, 0, $x + 1);

			$NewFile = $Path.$NewFile;

			//print "OldFile: '".$OldFile."'<br>";
			//print "NewFile: '".$NewFile."'<br>";
		
			if(! file_exists($NewFile))
			{
				if(rename($OldFile, $NewFile))
				{
					$Message = "";
				}
				else
				{
					$Message = "File could not be renamed";
				}
			}
			else
			{
				$Message = "Error, that name already exists!";	
			}	
		}
		else if(substr($OldFile, 0, 9) == "directory")
		{
			//print "FOLDER<p>";
			$OldFile = substr($OldFile, 9);

			$x = strrpos($OldFile, "/");
			//print "x = ".$x."<br>";
		
			$Path = substr($OldFile, 0, $x + 1);

			$NewFile = $Path.$NewFile;

			//print "OldFile: '".$OldFile."'<br>";
			//print "NewFile: '".$NewFile."'<br>";
		
			if(! file_exists($NewFile))
			{
				if(rename($OldFile, $NewFile))
				{
					$Message = "";
				}
				else
				{
					$Message = "File could not be renamed";
				}
			}
			else
			{
				$Message = "Error, that name already exists!";	
			}	
			
		}
	
	
	}
	else
	{
		$Message = "There was a problem renaming this file / folder!";
	}
}
else
{
	$Message = "No file or folder selected!";
}

if($Message == "")
{
	header("location: index.php");
}
else
{
	header("location: index.php?Notes=".$Message);
}

?>
