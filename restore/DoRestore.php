<?php
session_start();


if(!file_exists($_SERVER["DOCUMENT_ROOT"]."/restore/tmp")) {
	mkdir($_SERVER["DOCUMENT_ROOT"]."/restore/tmp", 0755);
}

$FileName = "";
if(isset($_REQUEST["FileName"])) {
	$UploadedFile = $_REQUEST["FileName"];
} else {
	//print "Sorry, no valid file found please, hit the back button to continue..";
	exit();
}


        $RandomString = date("Y-m-d_H-i-s")."_";
        $RandomString = $RandomString.rand(0,9);
        $RandomString = $RandomString.rand(0,9);
        $RandomString = $RandomString.rand(0,9);
        $RandomString = $RandomString.rand(0,9);
        $RandomString = $RandomString.rand(0,9);
        $RandomString = $RandomString.rand(0,9);

        while(is_dir($_SERVER["DOCUMENT_ROOT"]."/restore/tmp/".$RandomString)) {
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

	$UserName = trim($UploadedFile);

	//print "RandomString = ".$RandomString."<br>";
	//print "UserName = ".$UserName."<br>";

	$x = strrpos($UserName, "/");
	if($x > -1) {
		$UserName = substr($UserName, $x + 1);
	}	
	
	$FileName = $UserName;
	//print "FileName = ".$FileName."<br>";

	copy($UploadedFile, $_SERVER["DOCUMENT_ROOT"]."/restore/tmp/".$RandomString."/".$FileName);
	chmod($_SERVER["DOCUMENT_ROOT"]."/restore/tmp/".$RandomString."/".$FileName, 0755);
	
	if(strtolower(substr($UserName, strlen($UserName) - 7)) != ".tar.gz") {
		unlink($UploadedFile);
		rmdir("./tmp/".$RandomString."/");
	
		if(isset($_REQUEST["URL"])) {
			header("location: ".$_REQUEST["URL"]."?NoteType=Error&Notes=Incorrect file format!");
		} else {
			header("location: index.php?NoteType=Error&Notes=Incorrect file format!");
		}
		exit();
	}
	
	$x = strpos($UserName, "_");
	if($x > -1) {
		$UserName = substr($UserName, 0, $x);
	} else {
		$UserName = substr($UserName, 0, strpos($UserName, "."));
	}

	//print "UserName = ".$UserName."<br>";

	if(strlen($UserName) < 1)
	{
		unlink($_SERVER["DOCUMENT_ROOT"]."/restore/tmp/".$RandomString."/".$FileName);
		rmdir("./tmp/".$RandomString."/");
		
		//print "location: index.php?Notes=Error, incorrect file uploaded!<p>Please press the back button to continue";
		exit();
	}	

	$URL = "";
	if(isset($_REQUEST["URL"]))
	{
		$URL = "&URL=".$_REQUEST["URL"];
	}

	
	//print "location: DoUnzip.php?RandomString=".$RandomString."&UserName=".$UserName."&FileName=".$FileName.$URL;
	header("location: DoUnzip.php?RandomString=".$RandomString."&UserName=".$UserName."&FileName=".$FileName.$URL);
