<?php
session_start();


$URL = $_SERVER["SERVER_NAME"];

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

$LeftNavPath = substr($Path, 0, strpos($Path, "public_html") + 12);

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"> 
<html xmlns="http://www.w3.org/1999/xhtml"> 
<head> 
 
	<meta http-equiv="content-type" content="text/html; charset=iso-8859-1"/> 
	
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
	

	</head> 
	<body style="margin:0; background: #ededed"> 
		 


	<div align="center" style="width:95%; min-height:100%; background:white; margin: 15px 25px 25px; padding-top:20px; padding-bottom:20px; border-style:dotted; border-width:1px">
	
	<form id="UnzipForm" name="UnzipForm" action="DoUnzipFile.php" method="post">

	<input type="text" value="<?php print $Path; ?>" name="Path">
				
	<input type="file" name="UnzipedFile">
	<p>
	<input type="submit" value="Unzip File">
	</form>	

	</div>




</body></html>
