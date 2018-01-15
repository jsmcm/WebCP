<?php

include("/var/www/html/webcp/includes/classes/class.Database.php");
include("/var/www/html/webcp/includes/classes/class.Log.php");
	
$LockFile = "/var/www/html/webcp/emails/trace/bw_trace_lock";
if(file_exists($LockFile))
{
	// if its older than 5 minutes something's gone wrong, delete it.
	$datetime1 = new DateTime(date("Y-m-d H:i:s", filemtime($LockFile)));
        $datetime2 = new DateTime(date("Y-m-d H:i:s"));
        $interval = $datetime1->diff($datetime2);

	if( (int)$interval->format('%i') > 5)
	{
		$f = fopen("/var/www/html/webcp/emails/trace/bw_log.txt", "a");
		fwrite($f, "deleting stale lock file\r\n");
		fclose($f);

		// The previous instance stalled...
		unlink($LockFile);
	}

	exit();

}

touch($LockFile);

$UserNameArray = array();
$UserNameArrayIndex = 0;

$DomainUserNameArray = array();
$DomainUserNameArrayIndex = 0;

function WriteBandWidthLog($EmailAddress, $DomainUserName, $Size)
{
	$f = fopen("/var/www/html/bandwidth/dovecot/".date("H")."_".$DomainUserName, "a");
	fwrite($f, $Size."\n");
	fclose($f);
}



function GetDomainUserNameFromArray($DomainName)
{
	global $DomainUserNameArrayIndex;
	global $DomainUserNameArray;

	for($x = 0; $x < $DomainUserNameArrayIndex; $x++)
	{
		if($DomainUserNameArray[$x]["DomainName"] == $DomainName)
		{
			return $DomainUserNameArray[$x]["DomainUserName"];
		}
	}

	return "";
}

function GetDomainUserName($DomainName)
{
	global $DomainUserNameArrayIndex;
	global $DomainUserNameArray;

	$DomainUserName = GetDomainUserNameFromArray($DomainName);

 	if($DomainUserName != "") 
	{
		if($DomainUserName == "!")
		{
			return "";
		}

		return $DomainUserName;
	}

        $oDatabase = new Database();
        $DatabaseConnection = $oDatabase->GetConnection();



	try
	{
		$query = $DatabaseConnection->prepare("SELECT UserName FROM domains WHERE fqdn = :domain_name AND deleted = 0");
		$query->bindParam(":domain_name", $DomainName);
		$query->execute();

		if($result = $query->fetch(PDO::FETCH_ASSOC))
		{
			$UserNameArray[$UserNameArrayIndex]["DomainName"] = $DomainName;
			$UserNameArray[$UserNameArrayIndex++]["DomainUserName"] = $result["UserName"];

			return $result["UserName"];

		}

	}
	catch(PDOException $e)
	{
		$oLog = new Log();
		$oLog->WriteLog("error", "/class.Reseller.php -> GetDomainUserName(); Error = ".$e);
	}


	$DomainUserNameArray[$DomainUserNameArrayIndex]["DomainName"] = $DomainName;
	$DomainUserNameArray[$DomainUserNameArrayIndex++]["DomainUserName"] = "!";
	//print "Returning ''\r\n";
	return "";
}




function GetUserNameFromArray($DomainName)
{
	global $UserNameArrayIndex;
	global $UserNameArray;

	for($x = 0; $x < $UserNameArrayIndex; $x++)
	{
		if($UserNameArray[$x]["DomainName"] == $DomainName)
		{
			return $UserNameArray[$x]["UserName"];
		}
	}

	return "";
}

function GetEmailUser($DomainName)
{
	global $UserNameArrayIndex;
	global $UserNameArray;


	$UserName = GetUserNameFromArray($DomainName);

 	if($UserName != "") 
	{
		//print "From array!\r\n";

		if($UserName == "!")
		{
			return "";
		}

		return $UserName;
	}


        $oDatabase = new Database();
        $DatabaseConnection = $oDatabase->GetConnection();


	try
	{
		$query = $DatabaseConnection->prepare("SELECT admin_username FROM domains WHERE fqdn = :domain_name AND deleted = 0");
		$query->bindParam(":domain_name", $DomainName);
		$query->execute();
	
		if($result = $query->fetch(PDO::FETCH_ASSOC))
		{
	                $UserNameArray[$UserNameArrayIndex]["DomainName"] = $DomainName;
	                $UserNameArray[$UserNameArrayIndex++]["UserName"] = $result["admin_username"];
	
	                return $result["admin_username"];
	
		}
	
	}
	catch(PDOException $e)
	{
		$oLog = new Log();
		$oLog->WriteLog("error", "/class.Reseller.php -> GetEmailUser(); Error = ".$e);
	}

	$UserNameArray[$UserNameArrayIndex]["DomainName"] = $DomainName;
	$UserNameArray[$UserNameArrayIndex++]["UserName"] = "!";
	//print "Returning ''\r\n";
	return "";
}

$x = 0;
while (false !== ($line = fgets(STDIN))) 
{
	
	touch($LockFile);

		if( (strstr($line, "Info: Disconnected: Logged out")) && (strstr($line, "pop3")) )
                {
			//May 21 20:11:26 pop3(john@nodecp.rsa.pw): Info: Disconnected: Logged out top=0/0, retr=2/853274, del=0/3, size=1187756

			$EmailAddress = substr($line, strpos($line, "pop3(") + 5);
			$EmailAddress = substr($EmailAddress, 0, strpos($EmailAddress, ")"));

			$DomainName = substr($EmailAddress, strpos($EmailAddress, "@") + 1);
			$DomainUserName = GetDomainUserName($DomainName);

			$BytesIn = substr($line, strpos($line, "retr=") + 5);

                        $Size = substr($BytesIn, strpos($BytesIn, "/") + 1);

			$Size = substr($Size, 0, strpos($Size, ","));
			
			while( (substr($Size, strlen($Size) - 1) == "\r") || (substr($Size, strlen($Size) - 1) == "\n") )
                        {
                                $Size = substr($Size, 0, strlen($Size) - 1);
                        }


			if($Size > 0)
			{
				/*
				print "Line: ".$line."\r\n";
				print "EmailAddress: '".$EmailAddress."'\r\n";
				print "DomainName: '".$DomainName."'\r\n";
				print "DomainUserName: '".$DomainUserName."'\r\n";
				print "Size: '".$Size."'\r\n";
	                        */

				WriteBandWidthLog($EmailAddress, $DomainUserName, $Size);
			}

		}
		
		else if( (strstr($line, "Info: Disconnected: Logged out")) && (strstr($line, "imap")) )
		{
			//print "Line: ".$line."\r\n";
			//May 21 20:35:38 imap(john@nodecp.rsa.pw): Info: Disconnected: Logged out bytes=1454/34121

			$EmailAddress = substr($line, strpos($line, "imap(") + 5);
			$EmailAddress = substr($EmailAddress, 0, strpos($EmailAddress, ")"));
			$DomainName = substr($EmailAddress, strpos($EmailAddress, "@") + 1);
			$DomainUserName = GetDomainUserName($DomainName);

			$BytesIn = substr($line, strpos($line, "bytes=") + 6);
			$BytesOut = substr($BytesIn, strpos($BytesIn, "/") + 1);

			while( (substr($BytesOut, strlen($BytesOut) - 1) == "\r") || (substr($BytesOut, strlen($BytesOut) - 1) == "\n") )
			{
				$BytesOut = substr($BytesOut, 0, strlen($BytesOut) - 1);
			}

		
			$BytesIn = substr($BytesIn, 0, strpos($BytesIn, "/"));
			
			$Size = $BytesIn + $BytesOut;

			/*
			print "EmailAddress: '".$EmailAddress."'\r\n";
			print "DomainName: '".$DomainName."'\r\n";
			print "DomainUserName: '".$DomainUserName."'\r\n";
			print "BytesIn: '".$BytesIn."'\r\n";
			print "BytesOut: '".$BytesOut."'\r\n";
			print "Size: '".$Size."'\r\n";
			*/



			if($Size > 0)
			{
				WriteBandWidthLog($EmailAddress, $DomainUserName, $Size);
			}
		}

}

unlink($LockFile);

?>
