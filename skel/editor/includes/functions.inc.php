<?php


	function GetExtension($FileName)
	{
		$x = strrpos($FileName, ".");

		if($x > 0)
		{
			return trim(strtolower(substr($FileName, $x + 1)));
		}
		else
		{
			return "";
		}
	}

	function FileIsEditable($FileName)
	{

		$Extension = GetExtension($FileName);

		$a = array();
	
		$a = file($_SERVER["DOCUMENT_ROOT"]."/skel/editor/editable_list.txt");
		
		foreach($a as $val)
		{
			if($Extension == trim($val))
			{
				return true;
			}
		}

		return false;
	}

	function GetFileTypeIcon($FileName)
	{
		$Extension = GetExtension($FileName);
		
		if(file_exists($_SERVER["DOCUMENT_ROOT"]."/skel/editor/images/icons/".$Extension.".png"))
		{
			return $Extension.".png";
		}
		else if(file_exists($_SERVER["DOCUMENT_ROOT"]."/skel/editor/images/icons/".$Extension.".gif"))
		{
			return $Extension.".gif";
		}

		return "file.gif";
	}

	function chmod_R($path, $filemode, $dirmode) 
	{
    		if (is_dir($path) ) 
		{
        		if (!chmod($path, $dirmode)) 
			{
            			$dirmode_str=decoct($dirmode);
            			print "Failed applying filemode '$dirmode_str' on directory '$path'\n";
            			print "  `-> the directory '$path' will be skipped from recursive chmod\n";
            			return;
        		}

			$dh = opendir($path);

			while (($file = readdir($dh)) !== false) 
			{
            			if($file != '.' && $file != '..') 
				{  // skip self and parent pointing directories 
                			$fullpath = $path.'/'.$file;
                			chmod_R($fullpath, $filemode,$dirmode);
            			}
        		}

			closedir($dh);
    		} 
		else 
		{
        		if (is_link($path)) 
			{
            			print "link '$path' is skipped\n";
            			return;
        		}
       
			if (!chmod($path, $filemode)) 
			{
            			$filemode_str=decoct($filemode);
            			print "Failed applying filemode '$filemode_str' on file '$path'\n";
            			return;
        		}
    		}
	}

function DeleteFile($FileName)
{
	if(file_exists($FileName))
	{
	        if(unlink($FileName))
	        {
			//print "Unlink (".$FileName.")<br>";
	              	return 1;
	        }
	        else
	        {
			//print "delete failed: (".$FileName.")<br>";
			return 0;
	        }
	}	
	else
	{
		//print "File Does not exists(".$FileName.")<br>";
		return -1;
	}

}

function DeleteDirectoryRecursive($dir)
{
        if (!file_exists($dir))
        {
                return true;
        }

        if (!is_dir($dir) || is_link($dir))
        {
                return unlink($dir);
        }

        foreach (scandir($dir) as $item)
        {
                if ($item == '.' || $item == '..')
                {
                        continue;
                }

                if (!DeleteDirectoryRecursive($dir . "/" . $item))
                {
                        chmod($dir . "/" . $item, 0777);

                        if (!DeleteDirectoryRecursive($dir . "/" . $item))
                        {
                                return false;
                        }
                }
        }

        return rmdir($dir);
}


function DeleteDirectory($DirectoryName)
{

	if(file_exists($DirectoryName))
	{
	        if(is_dir($DirectoryName))
	        {
	                // delete       
	                if(DeleteDirectoryRecursive($DirectoryName))
	                {
				return 1;
	                }
	                else
	                {
				return 0;
	                }
	        }
	        else
	        {
			return -1;
	        }
	}
	else
	{
		return -2;
	}

}
        function ConvertToBytes($Value, $Scale)
        {
                if( ($Scale == "T") || ($Scale == "Tb") )
                {
                        $Value = $Value * 1024;
                        $Value = ConvertToBytes($Value, "G");
                }
                else if( ($Scale == "G") || ($Scale == "Gb") )
                {
                        $Value = $Value * 1024;
                        $Value = ConvertToBytes($Value, "M");
                }
                else if( ($Scale == "M") || ($Scale == "Mb") )
                {
                        $Value = $Value * 1024;
                        $Value = ConvertToBytes($Value, "K");
                }
                else if( ($Scale == "K") || ($Scale == "Kb") )
                {
                        $Value = $Value * 1024;
                        $Value = ConvertToBytes($Value, "b");
                }

                return $Value;

        }

	

        function ConvertFromBytes($Value, $Scale = "b")
        {

                if($Value < 1024)
                {
			if($Scale != "b")
			{
                        	return number_format($Value, 2)." ".$Scale;
			}
			else
			{
                        	return number_format($Value, 0)." ".$Scale;
			}
                }

                if( ($Scale == "b") )
                {
                        $Value = $Value / 1024;
                        return ConvertFromBytes($Value, "Kb");
                }
                else if( ($Scale == "K") || ($Scale == "Kb") )
                {
                        $Value = $Value / 1024;
                        return ConvertFromBytes($Value, "Mb");
                }
                else if( ($Scale == "M") || ($Scale == "Mb") )
                {
                        $Value = $Value / 1024;
                        return ConvertFromBytes($Value, "Gb");
                }
                else if( ($Scale == "G") || ($Scale == "Gb") )
                {
                        $Value = $Value / 1024;
                        return ConvertFromBytes($Value, "Tb");
                }

        }

	function CheckLogin()
	{
		if(isset($_SESSION["LoggedIn"]))
		{
		        if($_SESSION["LoggedIn"] == $_SERVER["SERVER_NAME"])
		        {
		                return true;
		       	}
		}

		return false;
	}



	function SetLogin($ServerName)
	{
		$_SESSION["LoggedIn"] = $ServerName;
	}
?>
