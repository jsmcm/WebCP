<?php
if(! isset($_POST["FilesAndFolders"]))
{
	print "No file supplied!";
	exit();
}
$file = $_POST["FilesAndFolders"];
header("Pragma: public");
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
header("Content-Type: application/force-download");
header("Content-Type: application/octet-stream");
header("Content-Type: application/download");
header("Content-Disposition: attachment;filename=".basename($file));
header("Content-Transfer-Encoding: binary");
readfile($file);
?>
