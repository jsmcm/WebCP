<?php
session_start();

require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.User.php");
$oUser = new User();

require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.Settings.php");
$oSettings = new Settings();

if( ! strstr($_SERVER["REMOTE_ADDR"], "127.0.0"))
{
	require($_SERVER["DOCUMENT_ROOT"]."/includes/License.inc.php");

	$ClientID = $oUser->getClientId();
	if($ClientID < 1)
	{
	        header("Location: /index.php");
	        exit();
	}
}


?>

<html>
<head>

<script language="javascript">

function ValidateFile()
{
	FileName = document.UploadForm.NewFile.value;
	
	FileName = FileName.toLowerCase();
	
	if(FileName == "")
	{
		alert("You have not selected an input file");
		return false;
	}
	
	if(FileName.indexOf(".zip") < 0)
	{
		alert("Only .zip files are valid!");
		return false;
	}
	
	return true;
}

</script>

</head>

<body>

<p>

<?php 
if(isset($_GET["Notes"]))
{
	print "<h2 style=\"color:red;\">".$_GET["Notes"]."</h2>";
	print "<p><hr><p>";
}
?>

<form id="UploadForm" name="UploadForm" action="UploadFile.php" method="post" enctype="multipart/form-data">

<input type="file" name="NewFile">
<p>
<input type="submit" value="Upload File" onclick="return ValidateFile(); return false;">
</form>
			
</body>

</html>
