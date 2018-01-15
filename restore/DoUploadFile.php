<?php
session_start();

if(! file_exists($_SERVER["DOCUMENT_ROOT"]."/restore/tmp/"))
{
	mkdir($_SERVER["DOCUMENT_ROOT"]."/restore/tmp/", 0755);
}

        $RandomString = date("Y-m-d_H-i-s")."_";
        $RandomString = $RandomString.rand(0,9);
        $RandomString = $RandomString.rand(0,9);
        $RandomString = $RandomString.rand(0,9);
        $RandomString = $RandomString.rand(0,9);
        $RandomString = $RandomString.rand(0,9);
        $RandomString = $RandomString.rand(0,9);

        while(is_dir($_SERVER["DOCUMENT_ROOT"]."/restore/tmp/".$RandomString))
        {
                $RandomString = date("Y-m-d_H-i-s")."_";
                $RandomString = $RandomString.rand(0,9);
                $RandomString = $RandomString.rand(0,9);
                $RandomString = $RandomString.rand(0,9);
                $RandomString = $RandomString.rand(0,9);
                $RandomString = $RandomString.rand(0,9);
                $RandomString = $RandomString.rand(0,9);
        }

	mkdir("./tmp/".$RandomString."/", 0755);
	chmod("./tmp/".$RandomString."/", 0755);
	
	move_uploaded_file($_FILES['UploadedFile']['tmp_name'], $UploadedFile="./tmp/".$RandomString."/".$_FILES['UploadedFile']['name']);
	chmod($UploadedFile, 0755);

	$UserName = trim($UploadedFile);

	//print "RandomString = ".$RandomString."<br>";
	//print "UserName = ".$UserName."<br>";

	$x = strrpos($UserName, "/");
	if($x > -1)
	{
		$UserName = substr($UserName, $x + 1);
	}	
	
	$FileName = $UserName;
	//print "FileName = ".$FileName."<br>";

	if(strtolower(substr($UserName, strlen($UserName) - 7)) != ".tar.gz")
	{
		unlink($UploadedFile);
		rmdir("./tmp/".$RandomString."/");

		header("location: index.php?NoteType=Error&Notes=Error, incorrect file format!");
		exit();
	}
	
	$x = strpos($UserName, "_");
	if($x > -1)
	{
		$UserName = substr($UserName, 0, $x);
	}
	else
	{
		$UserName = substr($UserName, 0, strpos($UserName, "."));
	}

	//print "UserName = ".$UserName."<br>";

	if(strlen($UserName) < 1)
	{
		unlink($UploadedFile);
		rmdir("./tmp/".$RandomString."/");
		
		header("location: index.php?NoteType=Error&Notes=Error, incorrect file uploaded!");
		exit();
	}	

	header("location: DoUnzip.php?RandomString=".$RandomString."&UserName=".$UserName."&FileName=".$FileName);
?>
