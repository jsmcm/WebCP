<?php
session_start();

$Notes = "";
if(isset($_REQUEST["Notes"]))
{
        $Notes = $_REQUEST["Notes"];
}

$FileName = "";
if(isset($_GET["FileName"]))
{
	$FileName = $_GET["FileName"];
}

if($FileName == "")
{
	print "No file select";
	exit();
}


$Contents = file_get_contents($FileName);

?>
		


<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"> 
<html xmlns="http://www.w3.org/1999/xhtml"> 
<head> 
 
	<meta http-equiv="content-type" content="text/html; charset=iso-8859-1"/> 
	
	 
	<link rel="stylesheet" href="js/screen.css" />
	

	<style>	 
	td
	{
		border-bottom-color:grey; border-bottom-style:solid; border-bottom-width:1px;
	}
	</style>


	<title>Dashboard | Web Control Panel</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />

	
	<style type="text/css">
	<!--
	@import url("/includes/styles/tablestyle.css");
	-->
	</style>

	<?php
	include($_SERVER["DOCUMENT_ROOT"]."/includes/styles/Styles.inc");
	?>
	
	<script language="javascript">
	
function str_rot13 () 
{
	document.TextEditor.style.visibility = "hidden";

	elem = document.getElementById("PleaseWait");
	elem.style.visibility = "visible";
	elem.style.display = "inline";

	str = document.TextEditor.FileContents.value;
	str = TransformText(str);
	document.TextEditor.FileContents.value = str;
}



function TransformText(str)
{
  // http://kevin.vanzonneveld.net
  // +   original by: Jonas Raoni Soares Silva (http://www.jsfromhell.com)
  // +   improved by: Ates Goral (http://magnetiq.com)
  // +   bugfixed by: Onno Marsman
  // +   improved by: Rafał Kukawski (http://blog.kukawski.pl)
  // *     example 1: str_rot13('Kevin van Zonneveld');
  // *     returns 1: 'Xriva ina Mbaariryq'
  // *     example 2: str_rot13('Xriva ina Mbaariryq');
  // *     returns 2: 'Kevin van Zonneveld'
  // *     example 3: str_rot13(33);
  // *     returns 3: '33'
  return (str + '').replace(/[a-z]/gi, function (s) {
    return String.fromCharCode(s.charCodeAt(0) + (s.toLowerCase() < 'n' ? 13 : -13));
  });
}

	function HideSuccessDivTimer()
	{
		setTimeout("HideSuccessDiv()", 5000);
	}

	function HideSuccessDiv()
	{
		elem = document.getElementById("SuccessDiv");
		elem.style.visibility = "hidden";
		elem.style.display = "none";
	}
	</script>




	</head> 
	<body style="margin:0; background: #ededed" onload="HideSuccessDivTimer();"> 
		 


	<div align="center" style="width:95%; min-height:100%; background:white; margin: 15px 25px 25px; padding-top:20px; padding-bottom:20px; border-style:dotted; border-width:1px">
	

	<?php
	if($Notes != "")
	{
        ?>
		<div id="SuccessDiv" style="background-color:green; width:100%; height:75px; border:1px solid black; font-weight: bold; color:white; font-size:48px;"><?php print $Notes; ?></div><p>
	<?php
	}
	?>

	<div id="PleaseWait" style="color:red; font-size:28px; visibility: hidden; display: none">
	Saving your changes, this won't take long...
	</div>

	<form name="TextEditor" action="save_text_editor.php" method="post">
	<input type="hidden" value="<?php print $FileName; ?>" name="FileName">
	<table border="1" cellpadding="0" cellspacing="0" width="100%">
	<tr>
		<td valign="middle" style="padding:10px;"><input type="submit" value="Save File" onclick="str_rot13();" id="button"></td>
	</tr>

	</table>
		
	<br>


	<table border="10" cellpadding="0" cellspacing="0" width="100%" height="100%">
	<tr>
	<td width="*" valign="top" style="color:#0000cc; font-size:14px;padding:10px 10px 10px; line-height:200%;">

	<textarea name="FileContents" style="width:100%; height:500px; border: 1px solid black;"><?php print htmlentities($Contents); ?></textarea>

	</td>
	</tr>		
	</table>
	</form>	 





<p>

</div> 
</body></html>
