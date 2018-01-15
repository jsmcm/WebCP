<?php
session_start();


$URL = $_SERVER["SERVER_NAME"];

if(isset($_POST["Path"]))
{
        $Path = $_POST["Path"];
}
else
{
        $Path = $_SERVER["DOCUMENT_ROOT"]."/skel/public_html";
}
		
	move_uploaded_file($_FILES['UploadedFile']['tmp_name'], $UploadedFile=$Path.$_FILES['UploadedFile']['name']);
	chmod($UploadedFile, 0755);
	
	//print $Reply;
	header("location: index.php?Path=".$Path."&Notes=File Uploaded");
?>
