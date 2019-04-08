<?php


include_once($_SERVER["DOCUMENT_ROOT"]."/vendor/autoload.php");

$oUser = new User();

$ClientID = $oUser->getClientId();
if($ClientID < 1)
{
        header("Location: /index.php");
        exit();
}

if($oUser->Role != "admin")
{
        header("Location: /domains/index.phpNotes=No permission");
        exit();
}



require_once($_SERVER["DOCUMENT_ROOT"]."/skel/editor/includes/functions.inc.php");


if(isset($_REQUEST["URL"]))
{
	$URL = $_REQUEST["URL"];
}
else
{
	$URL = $_SERVER["SERVER_NAME"];
}


if(isset($_REQUEST["Path"]))
{
	$Path = $_REQUEST["Path"];
}
else
{
	$Path = $_SERVER["DOCUMENT_ROOT"]."/skel/public_html";
}

if(substr($Path, strlen($Path) - 1) != "/")
{
	$Path = $Path."/";
}

	print "<form name=\"ListingForm\" id=\"ListingForm\" method=\"post\">";

	print "<div style=\"visibility: hidden; display: none;\">";
	print "<input type=\"hidden\" name=\"Path\" value=\"".$Path."\">";
	print "<input type=\"hidden\" name=\"URL\" value=\"".$URL."\">";

	print "<textarea name=\"FilesAndFolders\"></textarea>";
	print "</div>";
	
	print "<table id=\"FilesAndFolders\" width=\"100%\" border=\"0\" style=\"border:0px solid blue;\" cellpadding=\"0\" cellspacing=\"0\">";

	print "<thead>";
	print "<tr>";
	print "<td width=\"15\">&nbsp;</td>";
	print "<td width=\"60%\"><b>Path</b></td>";
	print "<td width=\"10%\"><b>Size</b></td>";
	print "<td width=\"10%\"><b>Perm</b></td>";
	print "<td width=\"*\">&nbsp;</td>";
	print "<td width=\"10%\">&nbsp;</td>";
	print "</tr>";

	print "<tr>";
	print "<td colspan=\"6\"><img border=\"0\" src=\"./js/images/folder.gif\"> <b>".$Path."</b></td>";
	print "</tr>";
	print "</thead>";

 	if ($handle = opendir($Path)) 
	{
		
		print "<tbody>";
	
		$x = 0;

	        /* This is the correct way to loop over the directory. */
	        while (false !== ($file = readdir($handle))) 
		{
			if($file != "." && $file != "..")
			{
				if(is_dir($Path.$file))
				{
					//print "<tr onmousedown=\"RowClick(this);\" id=\"directory".str_replace("/", "_", $Path.$file)."\">";
					print "<tr onmousedown=\"RowClick(this);\" id=\"directory".$Path.$file."\">";
					print "<td width=\"5\">&nbsp;</td>";
			               	print "<td width=\"*\"><a href=\"index.php?Path=".$Path.$file."/\" style=\"text-decoration: none;\"><img border=0 src=\"./js/images/folder-closed.gif\"> ".$file."</a></td>";
					print "<td width=\"10%\">&nbsp;</td>";
					print "<td width=\"10%\"><span onclick=\"MakeDivVisible('perms_".$x."');\" id=\"perms_span_".$x."\">".substr(sprintf('%o', fileperms($Path.$file)), -4)."</span><div id=\"perms_".$x."\" style=\"padding: 10px; background-color: white; width: 80px; height: 80px; visibility: hidden; border: 1px solid red; position: absolute; margin-top: -25px; margin-left: -35px; display: none;\"><table border=\"0\"><tr><td><input id=\"perms_input_".$x."\" type=\"text\" style=\"border:1px solid #000099; height:25px; width:50px;\" value=\"".substr(sprintf('%o', fileperms($Path.$file)), -4)."\"></td></tr><tr><td><span onclick=\"SavePermissions('".$Path.$file."', document.getElementById('perms_input_".$x."').value, 'perms_span_".$x."', 'perms_".$x."');\">Save</span></td></tr><tr><td><span onclick=\"MakeDivInvisible('perms_".$x."');\">Cancel</span></td></tr></table></div></td>";
					print "<td width=\"5%\">&nbsp;</td>";
					print "<td width=\"5%\"><a onclick=\"DeleteDirectory('".$Path.$file."');\"><img src=\"./images/delete.png\" title=\"Delete\"></a></td>";
					print "</tr>";
				}
				else
				{
					//print "<tr onmousedown=\"RowClick(this);\" id=\"file".str_replace("/", "_", $Path.$file)."\">";
					print "<tr onmousedown=\"RowClick(this);\" id=\"file".$Path.$file."\">";
					print "<td width=\"5\">&nbsp;</td>";
			               	print "<td width=\"*\"><img border=0 src=\"./images/icons/".GetFileTypeIcon($file)."\"> ".$file."</td>";
					print "<td width=\"10%\">".ConvertFromBytes(filesize($Path.$file))."</td>";
					print "<td width=\"10%\"><span onclick=\"MakeDivVisible('perms_".$x."');\" id=\"perms_span_".$x."\">".substr(sprintf('%o', fileperms($Path.$file)), -4)."</span><div id=\"perms_".$x."\" style=\"padding: 10px; background-color: white; width: 80px; height: 80px; visibility: hidden; border: 1px solid red; position: absolute; margin-top: -25px; margin-left: -35px; display: none;\"><table border=\"0\"><tr><td><input id=\"perms_input_".$x."\" type=\"text\" style=\"border:1px solid #000099; height:25px; width:50px;\" value=\"".substr(sprintf('%o', fileperms($Path.$file)), -4)."\"></td></tr><tr><td><span onclick=\"SavePermissions('".$Path.$file."', document.getElementById('perms_input_".$x."').value, 'perms_span_".$x."', 'perms_".$x."');\">Save</span></td></tr><tr><td><span onclick=\"MakeDivInvisible('perms_".$x."');\">Cancel</span></td></tr></table></div></td>";
					if(FileIsEditable($file))
					{
						print "<td width=\"5%\"><a href=\"text_editor.php?FileName=".$Path.$file."\" target=\"_blank\"><img src=\"./images/edit.png\" title=\"Edit\"></a></td>";
					}
					else
					{
						print "<td width=\"5%\">&nbsp;</td>";
					}
					print "<td width=\"5%\"><a onclick=\"DeleteSingleFile('".$Path.$file."');\"><img src=\"./images/delete.png\" title=\"Delete\"></a></td>";
					print "</tr>";
				}
				
				$x++;
	            	}
			  
		}
	

		//print "<input type=\"button\" onclick=\"CheckForms();\" value=\"BUTTON\">";
		print "</tbody>";
	       	closedir($handle);

    	}

	print "</table>";
  		
	print "</form>"; 