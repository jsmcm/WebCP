<?php
session_start();

function __autoload($classname)
{
        require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.".$classname.".php");
}

$oUser = new User();
$oDomain = new Domain();


$Title = $_POST["Title"];

if($Title == "")
{
	// htaccess causes 500 error if this is blank
	$Title = " ";
}

$HTAccessPath = $_POST["HTAccessPath"];
$PasswordPath = $_POST["PasswordPath"];
$ActualPasswordPath = $_POST["ActualPasswordPath"];
$URL = $_POST["URL"];
$Path = $_POST["Path"];

$Status = "off";

//print "Role: ".$oUser->Role."<br>";
//print "ClientID: ".$oUser->ClientID."<br>";
//print "DomainOwnerFromDomainName: ".$oDomain->GetDomainOwnerFromDomainName($URL)."<br>";
//exit();

if( ($oDomain->GetDomainOwnerFromDomainName($URL) != $oUser->ClientID) && ($oUser->Role != "admin") )
{
        header("location: index.php?Notes=You do not have permission to access this sites detail");
        exit();
}

if(isset($_POST["Status"]))
{
	$Status = $_POST["Status"];
}

//print "Title: ".$Title."<br>";
//print "URL: ".$URL."<br>";
//print "Status: ".$Status."<br>";
//print "HTAccessPath: ".$HTAccessPath."<br>";
//print "PasswordPath: ".$PasswordPath."<br>";

$AuthOutput = "";

$AuthOutput = "\nAuthType Basic\n";
$AuthOutput = $AuthOutput."AuthName \"".$Title."\"\n";
$AuthOutput = $AuthOutput."AuthUserFile \"".$PasswordPath."\"\n";
$AuthOutput = $AuthOutput."require valid-user\n";

if(file_exists($HTAccessPath))
{

	//print "FILE DOES EXISTS<p>";

	$PostData = "Path=".$HTAccessPath;
	
	//print "PostData: ".$PostData."<p>";

	$c = curl_init();
	curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($c, CURLOPT_POSTFIELDS,  $PostData);
	curl_setopt($c, CURLOPT_POST, 1);
	curl_setopt($c, CURLOPT_URL, "http://".$URL.":20001/read.php");

	$ResultString = trim(curl_exec($c));
	curl_close($c);

	$DirectoryProtected = false;
	$UserArray = array();
	$Title = "";
	$PasswordFileFromHTAccess = "";
		
	//print "ResultString: '".$ResultString."'<p>";

	if($ResultString != "")
	{
		$x = 0;

	       	//print "<p>HTACCESS<p>";
		//print "<p>";

	       	// Header
        	// PasswordFileFromHTAccess

		$x = strpos($ResultString, "AuthType Basic");
		$n = strpos($ResultString, "\n", $x);

		if($x > -1)
	        {
	                if($n > $x)
			{
				$ResultString = substr($ResultString, 0, $x).substr($ResultString, $n + 1);
			}
			else
			{
				$ResultString = substr($ResultString, 0, $x);
			}
	        }
		

	        $x = strpos($ResultString, "AuthName");
		$n = strpos($ResultString, "\n", $x);

		if($x > -1)
	        {
	                if($n > $x)
			{
				$ResultString = substr($ResultString, 0, $x).substr($ResultString, $n + 1);
			}
			else
			{
				$ResultString = substr($ResultString, 0, $x);
			}
	        }



	        $x = strpos($ResultString, "AuthUserFile");
		$n = strpos($ResultString, "\n", $x);

		if($x > -1)
	        {
	                if($n > $x)
			{
				$ResultString = substr($ResultString, 0, $x).substr($ResultString, $n + 1);
			}
			else
			{
				$ResultString = substr($ResultString, 0, $x);
			}
	        }

	        $x = strpos($ResultString, "require valid-user");
		$n = strpos($ResultString, "\n", $x);

		if($x > -1)
	        {
	                if($n > $x)
			{
				$ResultString = substr($ResultString, 0, $x).substr($ResultString, $n + 1);
			}
			else
			{
				$ResultString = substr($ResultString, 0, $x);
			}
	        }
	
	}


}
else
{
	// Easy, no .htaccess, just add one

}

	$Message = "Protection Added";
	if($Status == "off")
	{
		$Message = "Protection Removed";
		$AuthOutput = "";
	}

	$PostData="FileContent=".$AuthOutput.$ResultString."&HTAccessPath=".$HTAccessPath."&PasswordPath=".$PasswordPath;

	//print "<p>".$PostData."<p>";

	$c = curl_init();
	curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($c, CURLOPT_POSTFIELDS,  $PostData);
	curl_setopt($c, CURLOPT_POST, 1);
	curl_setopt($c, CURLOPT_URL, "http://".$URL.":20001/write.php");
	
	$ResultString = trim(curl_exec($c));
	curl_close($c);

	//print "Creating: ".substr($PasswordPath, 0, strlen($PasswordPath) - 6)."<br>";

	if(!is_dir(substr($PasswordPath, 0, strlen($PasswordPath) - 6)))
	{
		mkdir(substr($PasswordPath, 0, strlen($PasswordPath) - 6), 0755, true);
	}
	
	if($PasswordPath != $ActualPasswordPath)
	{
		if(file_exists($PasswordPath))
		{
			unlink($PasswordPath);
		}

		copy($ActualPasswordPath, $PasswordPath);
	
	}

	touch($PasswordPath);

	header("location: manage.php?Notes=".$Message."!&URL=".$URL."&Path=".$Path);
?>
