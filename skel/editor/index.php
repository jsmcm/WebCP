<?php
session_start();

function __autoload($classname)
{
        require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.".$classname.".php");
}

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



$URL = $_SERVER["SERVER_NAME"];

if(isset($_REQUEST["Path"]))
{
	$Path = $_REQUEST["Path"];
}
else
{
	$Path = $_SERVER["DOCUMENT_ROOT"]."/skel/public_html/";
}

if(substr($Path, strlen($Path) - 1) != "/")
{
	$Path = $Path."/";
}

$LeftNavPath = substr($Path, 0, strpos($Path, "public_html") + 12);

function PrintDirectories($Path, $Iteration, $URL)
{

	$ULPrinted = false;

	if ($handle = opendir($Path))	
	{

		/* This is the correct way to loop over the directory. */
	        while (false !== ($file = readdir($handle)))
	        {
	                if($file != "." && $file != "..")
	                {
	                        if(is_dir($Path.$file))
	                        {
					if($ULPrinted == false)
					{
						if($Iteration == 0)
						{
							print "<ul id=\"browser\" class=\"filetree\">";
						}
						else
						{
							print "<ul>";
						}
					}
 
	                                print "<li class=\"closed\" ondblclick=\"ExpandFolder('".$URL."', '".$Path.$file."/');\"><span class=\"folder\" ondblclick=\"ExpandFolder('".$URL."', '".$Path.$file."/');\">".$file."</span>";
	
					PrintDirectories($Path.$file."/", $Iteration++, $URL);

					print "</li>";

					if($ULPrinted == false)
					{
						$ULPrinted = true;
					}
	                        }
	                }
	        }

	        closedir($handle);
	}

	if($ULPrinted == true)
	{
		print "</ul>";
	}
}
	

?>
		


<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"> 
<html xmlns="http://www.w3.org/1999/xhtml"> 
<head> 
 
	<meta http-equiv="content-type" content="text/html; charset=iso-8859-1"/> 
	
	 
	<link rel="stylesheet" href="js/jquery.treeview.css" />
    	<link rel="stylesheet" href="js/red-treeview.css" />
	<link rel="stylesheet" href="js/screen.css" />
	
	<script src="js/lib/jquery.js" type="text/javascript"></script>
	<script src="js/lib/jquery.cookie.js" type="text/javascript"></script>
	<script src="js/jquery.treeview.js" type="text/javascript"></script>

	<style>	 
	td
	{
		border-bottom-color:grey; border-bottom-style:solid; border-bottom-width:1px;
	}
    
	.selected 
	{
    		background: lightBlue;
	}

	</style>

	<script src="js/MainFunctions.js" type="text/javascript"></script>

	<title>Dashboard | Web Control Panel</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />

	
	<style type="text/css">
	<!--
	@import url("/includes/styles/tablestyle.css");
	-->
	</style>

	<?php
	include($_SERVER["DOCUMENT_ROOT"]."/skel/editor/includes/styles/Styles.inc");
	?>
	

	<script language="javascript">
	
	CurrentlyVisiblePermsDiv = "";
       	xmlhttp = null;
        count = 0;
        var d = new Date();



	function ValidatePermissions(Permissions)
	{
		if(Permissions.length != 4)
		{
			return false;
		}

		for(x = 0; x < Permissions.length; x++)
		{
			if(isNaN(Permissions.substr(x, 1)))
			{
				return false;
			}
			else if( (parseInt(Permissions.substr(x, 1)) < 0) || (parseInt(Permissions.substr(x, 1)) > 7) )
			{
				return false;
			}
		}


		return true;
	}

        function WritePermissions(Path, Permissions)
        {
                if(ValidatePermissions(Permissions) == true)
                {

                        xmlhttp = null;
                        //alert("at top");
                        if (window.XMLHttpRequest)
                        {
                                // code for IE7+, Firefox, Chrome, Opera, Safari
                                xmlhttp=new XMLHttpRequest();
                        }
                        else
                        {
                                // IE6, IE5
                                xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
                        }

                        RndString = d.getFullYear() + "" + d.getMonth() + "" + d.getDate() + ""  + d.getHours() + "" + d.getMinutes() + "" + d.getSeconds() + "" + count++;

                        xmlhttp.open("GET",'/skel/editor/ajax/ChangePermissions.php?FileName=' + Path + '&Permissions=' + Permissions + '&C=' + RndString,false);
                        xmlhttp.send(null);

                        //alert("xmlhttp.responseText: " + xmlhttp.responseText);

                        return xmlhttp.responseText;
                        
			
		}
		
		return "";
	}

	function SavePermissions(FileName, Permissions, Span, DivName)
	{
		//alert("FileName: " + FileName);
		//alert("Permissions: " + Permissions);
		//alert("Span: " + Span);
		//alert("DivName: " + DivName);

		Reply = WritePermissions(FileName, Permissions);

		if(Reply != "")
		{
			document.getElementById(Span).innerHTML = Reply;
		}
		else
		{
			alert("Permissions must be 4 numbers between 0 and 7!\r\n\r\nA common setting is 0755");
		}

		MakeDivInvisible(DivName);

	}

	function MakeDivVisible(LinkName)
	{
		//alert(CurrentlyVisiblePermsDiv);

		if(CurrentlyVisiblePermsDiv != "")
		{
			
			MakeDivInvisible(CurrentlyVisiblePermsDiv);
		}

		CurrentlyVisiblePermsDiv = LinkName;

		elem = document.getElementById(LinkName);
		elem.style.visibility = "visible";
		elem.style.display = "inline";
	}


	function MakeDivInvisible(LinkName)
	{

		//alert("Making Invisible: " + LinkName);

		if ( T = document.getElementById(LinkName) )
		{
			// exists
		}
		else
		{
			// does not exist
			return;
		}
	
		elem = document.getElementById(LinkName);
		elem.style.visibility = "hidden";
		elem.style.display = "none";
	
		if(LinkName == CurrentlyVisiblePermsDiv)
		{
			CurrentlyVisiblePermsDiv = "";
		}

	}

	</script>



	</head> 
	<body style="margin:0; background: #ededed"> 
		 


	<div align="center" style="width:95%; min-height:100%; background:white; margin: 15px 25px 25px; padding-top:20px; padding-bottom:20px; border-style:dotted; border-width:1px">
	
	<?php
	if(isset($_REQUEST["Notes"]))
	{
	        print "<font color=\"red\">".$_REQUEST["Notes"]."</font><p>";
	}
	?>
	<table border="1" cellpadding="0" cellspacing="0" width="100%">
	<tr>
		<td align="center" valign="middle"><a onclick="AddNewFile('<?php print $Path; ?>');"><img src="./images/NewFile.png" border="0" title="New File"><br>New File</a></td>
		<td align="center" valign="middle"><a onclick="AddNewDirectory('<?php print $Path; ?>');"><img src="./images/NewFolder.png" title="New Folder"><br>New Folder</a></td>
		<td align="center" valign="middle"><a onclick="selectAll();"><img src="./images/SelectAll.png" title="Select All"><br>Select  All</a></td>
		<td align="center" valign="middle"><a onclick="clearAll();"><img src="./images/DeselectAll.png" title="Unselect All"><br>Unselect All</a></td>
		<td align="center" valign="middle"><a onclick="BulkDelete();"><img src="./images/Delete.png" title="Delete Selected"><br>Delete Selected</a></td>
		<td align="center" valign="middle"><a onclick="Rename();"><img src="./images/Rename.png" title="Rename"><br>Rename</a></td>
		<td align="center" valign="middle"><a onclick="Copy(0);"><img src="./images/Copy.png" title="Copy"><br>Copy</a></td>
		<td align="center" valign="middle"><a onclick="Copy(1);"><img src="./images/Move.png" title="Move"><br>Move</a></td>
		<td align="center" valign="middle"><a href="./UploadFile.php?Path=<?php print $Path; ?>"><img src="./images/Upload.png" title="Upload"><br>Upload</a></td>
		<td align="center" valign="middle"><a onclick="Download();"><img src="./images/Download.png" title="Download"><br>Download</a></td>
		<td align="center" valign="middle"><a onclick="UnzipFile();"><img src="./images/Unzip.png" title="Unzip file"><br>Unzip File</a></td>
		<td align="center" valign="middle"><a onclick="ZipFiles();"><img src="./images/Zip.png" title="Zip"><br>Zip Selected</a></td>
	</tr>

	</table>
		
	<br>

	<div id="SuccessDiv" style="visibility:hidden; display:none;background-color:green; width:100%; height:75px; border:1px solid black; font-weight: bold; color:white; font-size:48px;">Success, please wait while I refresh.....</div><p>

	<table border="0" cellpadding="0" cellspacing="0" width="100%" height="100%">
	<tr>
	<td width="300" valign="top" style="padding: 10px 5px 5px; border-right-width: 1px; border-right-color: black; border-right-style: solid;">
		<div style="width:300px; overflow:auto;">		 

		<?php
		

		print "<b><img src=\"./js/images/folder-closed.gif\"> ".$LeftNavPath."</b><br>";
		PrintDirectories($LeftNavPath, 0, $URL);
		?>



		</div> 
	 </td>
	<td width="*" valign="top" style="color:#0000cc; font-size:14px;padding:10px 10px 10px; line-height:200%;">



            

        <?php

	if(strlen($Path) > strpos($Path, "public_html") + 12)
	{

		$x = strrpos($Path, "/", -2) + 1;
	

		print "<a href=\"index.php?URL=".$URL."&Path=".substr($Path, 0, $x)."\" style=\"text-decoration:none;\"><img width=\"16\" height=\"16\" src=\"./js/images/folder-up.gif\"> Back...</a><br>";
	}

      	?>
	<div id="ListingTable"><?php include("./Listing.php"); ?></div>

	</td>
	</table>
	 





<p>

</div> 












<script type='text/javascript'>
var lastSelectedRow;
var trs = document.getElementById('FilesAndFolders').tBodies[0].getElementsByTagName('tr');

// disable text selection
document.onselectstart = function() {
    return false;
}

function RowClick(currenttr) 
{

    if (window.event.ctrlKey) {
        toggleRow(currenttr);
    }
    
    if (window.event.button === 0) {
        if (!window.event.ctrlKey && !window.event.shiftKey) {
            clearAll();
            toggleRow(currenttr);
        }
    
        if (window.event.shiftKey) {
            selectRowsBetweenIndexes([lastSelectedRow.rowIndex, currenttr.rowIndex])
        }
    }
}

function toggleRow(row) 
{

	Selected = row.className == 'selected' ? '' : 'selected';

	row.className = Selected;
	lastSelectedRow = row;

	if(Selected == "selected")
	{
		AddToFileArray(row.id);
	}
	else
	{
		//alert("Off: " + row.id);
		for(x = 0; x < FileArray.length; x++)
		{
			if(FileArray[x] == row.id)
			{
				FileArray[x] = "";
				FileArray.splice(x, 1);
				break;
			}
		}
	}

}

function selectRowsBetweenIndexes(indexes) 
{
    	indexes.sort(function(a, b) 
	{
        	return a - b;
    	});

    	for (var i = indexes[0]; i <= indexes[1]; i++) 
	{
        	trs[i-1].className = 'selected';
		AddToFileArray(trs[i-1].id);
    	}
}

function clearAll() 
{
	//alert('ClearAll');

	FileArray  = new Array();
	FileArray = [];

    	for (var i = 0; i < trs.length; i++) 
	{
        	trs[i].className = '';
    	}	
}

function selectAll() 
{
	//alert('SelectAll');

	x = 0;

    	for (var i = 0; i < trs.length; i++) 
	{
        	trs[i].className = 'selected';
		
		AddToFileArray(trs[i].id);		
    	}	
}


</script>








</body></html>
