<?php

include_once("/var/www/html/webcp/includes/classes/class.Database.php");
include_once("/var/www/html/webcp/includes/classes/class.Log.php");

$serverName = gethostname();

$LockFile = "/var/www/html/webcp/emails/trace/run_trace_lock";
if(file_exists($LockFile))
{
	// if its older than 60 minutes something's gone wrong, delete it.
	$datetime1 = new DateTime(date("Y-m-d H:i:s", filemtime($LockFile)));
        $datetime2 = new DateTime(date("Y-m-d H:i:s"));
        $interval = $datetime1->diff($datetime2);

	if( (int)$interval->format('%i') > 60)
	{
		$f = fopen("/var/www/html/webcp/emails/trace/log.txt", "a");
		fwrite($f, "deleting stale lock file\r\n");
		fclose($f);

		// The previous instance stalled...
		unlink($LockFile);
	}

	exit();

}

touch($LockFile);

$MySQLMailSent = 0;
$UserNameArray = array();
$UserNameArrayIndex = 0;

$DomainUserNameArray = array();
$DomainUserNameArrayIndex = 0;

function WriteBandWidthLog($EmailAddress, $DomainUserName, $Size)
{
	$f = fopen("/var/www/html/bandwidth/exim/".date("H")."_".$DomainUserName, "a");
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
			$DomainUserNameArray[$DomainUserNameArrayIndex]["DomainName"] = $DomainName;
			$DomainUserNameArray[$DomainUserNameArrayIndex++]["DomainUserName"] = $result["UserName"];

			return $result["UserName"];

		}

	}
	catch(PDOException $e)
	{
		$oLog = new Log();
		$oLog->WriteLog("error", "/emails/trace/run_trace.php -> GetDomainUserName(); Error = ".$e);
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
		$oLog->WriteLog("error", "/emails/trace/run_trace.php -> GetEmailUser(); Error = ".$e);
	}
	

	$UserNameArray[$UserNameArrayIndex]["DomainName"] = $DomainName;
	$UserNameArray[$UserNameArrayIndex++]["UserName"] = "!";
	//print "Returning ''\r\n";
	return "";
}


function InsertEmailTrace($MailQueueID, $ToUser, $FromUser, $Date, $Time, $SenderHost, $Protocol, $Auth, $Size, $Subject, $From, $For, $Status = "")
{

	if($Status == "")
	{
		$Status == "started";
	}
	else if(strlen($Status) > 7)
	{
		if(substr($Status, 0, 8) == "DNSBL - ")
		{
			$f = fopen("/var/www/html/webcp/emails/trace/spam_log.txt", "a");
			fwrite($f, "Spam logger at 1\r\n");
			fwrite($f, $Status."\r\n");
			
			
			$Number = 0;
			if(strpos($Status, "dnsbl.phpwebhost.co.za"))
			{
				$Number = -1;
			}
			else if(strpos($Status, "cbl.abuseat.org"))
			{
				$Number = 3;
			}

			$IP = "";
			if( ($Number > -1) && (strstr($Status, "?")) )
			{
				$IP = substr($Status, strpos($Status, "?") + 1 + $Number);
			}

			fwrite($f, "IP: ".$IP."\r\n");


			if(strlen($IP) > 0)
			{
	         		$SendData = "IP=".$IP;
	
				fwrite($f, "curling\r\n");

	               	 	$c = curl_init();
	                	curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
	                	curl_setopt($c, CURLOPT_POSTFIELDS,  $SendData);
	                	curl_setopt($c, CURLOPT_POST, 1);
	               	 	curl_setopt($c, CURLOPT_URL, "http://dnsbl.phpwebhost.co.za/insert_mail.php");
        	        	$ResultString = curl_exec($c);
	                	curl_close($c);

				fwrite($f, "Result String: ".$ResultString."\r\n");
			}			
			else
			{
				fwrite($f, "strlen(IP): ".strlen($IP)."\r\n");
			}

			fclose($f);

		}
	}
	
        $oDatabase = new Database();
        $DatabaseConnection = $oDatabase->GetConnection();

	try
	{
		$Date = $Date." ".$Time;
		$query = $DatabaseConnection->prepare("INSERT INTO email_trace VALUES (0, :mail_queue_id, :to_user, :from_user, :date, NULL, :sender_host, '', :protocol, :auth, :size, 0, :subject, :from, :for, '', '', '', '0', '', :status)");
		

		$query->bindParam(":mail_queue_id", $MailQueueID);
		$query->bindParam(":to_user", $ToUser);
		$query->bindParam(":from_user", $FromUser);
		$query->bindParam(":date", $Date);
		$query->bindParam(":sender_host", $SenderHost);
		$query->bindParam(":protocol", $Protocol);
		$query->bindParam(":auth", $Auth);
		$query->bindParam(":size", $Size);
		$query->bindParam(":subject", $Subject);
		$query->bindParam(":from", $From);
		$query->bindParam(":for", $For);
		$query->bindParam(":status", $Status);
		
		$query->execute();

	}
	catch(PDOException $e)
	{
		$oLog = new Log();
		$oLog->WriteLog("error", "/emails/trace/run_trace.php -> InsertEmailTrace(); Error = ".$e);
	}
	

}




function UpdateEmailTrace($MailQueueID, $Date, $Time, $ToAddress, $From, $ReturnPath, $Transport, $Router, $ReceiverHost, $Size, $Confirmation, $QueueTime, $Progress)
{

	if( ($ToAddress == "root@".gethostname() ) || ($ToAddress == "" && $From == "") )
	{
		return;
	}


        $oDatabase = new Database();
        $DatabaseConnection = $oDatabase->GetConnection();

	$QueueTimePart = "";
        if(trim($QueueTime) != "")
        {
                $QueueTimePart = ", queue_time = :queue_time ";
        }

        $ReturnPathPart = "";
        if(trim($ReturnPath) != "")
        {
                $ReturnPathPart = ", return_path = :return_path ";
        }

	$SQL = "UPDATE email_trace SET update_date = :date, transport = :transport, router = :router, receiver_host = :receiver_host, receiver_size = :size, confirmation = :confirmation, status = :progress ".$QueueTimePart." ".$ReturnPathPart." WHERE mail_queue_id = :mail_queue_id AND for_address = :to_address";

		

	try
	{
		$query = $DatabaseConnection->prepare($SQL);
		
	
		$Date = $Date." ".$Time;
	
		$query->bindParam(":date", $Date);
		$query->bindParam(":mail_queue_id", $MailQueueID);
		
		$query->bindParam(":transport", $Transport);
		$query->bindParam(":router", $Router);

		$query->bindParam(":receiver_host", $ReceiverHost);
		$query->bindParam(":size", $Size);
		$query->bindParam(":confirmation", $Confirmation);
		$query->bindParam(":progress", $Progress);
		$query->bindParam(":to_address", $ToAddress);
		
		
		if(trim($QueueTime) != "")
		{
			$query->bindParam(":queue_time", $QueueTime);
		}

		if(trim($ReturnPath) != "")
		{
			$query->bindParam(":return_path", $ReturnPath);
		}
		
		$query->execute();

	}
	catch(PDOException $e)
	{
		$oLog = new Log();
		$oLog->WriteLog("error", "/emails/trace/run_trace.php -> UpdateEmailTrace(); Error = ".$e);
	}
	
		
		
	if( (strlen($Progress) > 7) && (substr($Progress, 0, 8) == "DNSBL - ") )
	{
		$f = fopen("/var/www/html/webcp/emails/trace/spam_log.txt", "a");
		fwrite($f, "Spam logger at 2\r\n");
		fwrite($f, $Progress."\r\n");
			
			$Number = 0;
			if(strpos($Progress, "dnsbl.phpwebhost.co.za"))
			{
				$Number = -1;
			}
			else if(strpos($Progress, "cbl.abuseat.org"))
			{
				$Number = 3;
			}
	
			$IP = "";
			if( ($Number > -1) && (strpos($Progress, "?")) )
			{
				$IP = substr($Progress, strpos($Progress, "?") + 1 + $Number);
			}
			fwrite($f, "IP = ".$IP."\r\n");

			if(strlen($IP) > 0)
			{
	         		$SendData = "IP=".$IP;
	
				fwrite($f, "Curling\r\n");

	                	$c = curl_init();
	               	 	curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
	               	 	curl_setopt($c, CURLOPT_POSTFIELDS,  $SendData);
	                	curl_setopt($c, CURLOPT_POST, 1);
	                	curl_setopt($c, CURLOPT_URL, "http://dnsbl.phpwebhost.co.za/insert_mail.php");
        	        	$ResultString = curl_exec($c);
	                	curl_close($c);
				
				fwrite($f, "Result String = ".$ResultString."\r\n");
			}
			else
			{
				fwrite($f, "strlen(IP): ".strlen($IP)."\r\n");
			}

			fclose($f);
	}
		
	

}

function UpdateStatus($MailQueueID, $Status, $Debug=0)
{

	if( (strlen($Status) > 7) && (substr($Status, 0, 8) == "DNSBL - ") )
	{
		$f = fopen("/var/www/html/webcp/emails/trace/spam_log.txt", "a");
			fwrite($f, "Spam logger at 3\r\n");
		fwrite($f, $Status."\r\n");
		
			$Number = 0;
			if(strpos($Status, "dnsbl.phpwebhost.co.za"))
			{
				$Number = -1;
			}
			else if(strpos($Status, "cbl.abuseat.org"))
			{
				$Number = 3;
			}


			$IP = "";
			if( ($Number > -1) && (strpos($Status, "?")) )
			{
				$IP = substr($Status, strpos($Status, "?") + 1 + $Number);
			}

			fwrite($f, $IP."\r\n");

			if(strlen($IP) > 0)
			{
	         		$SendData = "IP=".$IP;
				
				fwrite($f, "curling\r\n");

	                	$c = curl_init();
	                	curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
	               	 	curl_setopt($c, CURLOPT_POSTFIELDS,  $SendData);
	                	curl_setopt($c, CURLOPT_POST, 1);
	                	curl_setopt($c, CURLOPT_URL, "http://dnsbl.phpwebhost.co.za/insert_mail.php");
        	        	$ResultString = curl_exec($c);
	                	curl_close($c);

				fwrite($f, "Result String: ".$ResultString."\r\n");

			}
			else
			{
				fwrite($f, "strlen(IP): ".strlen($IP)."\r\n");
			}

			fclose($f);

	}
		

        $oDatabase = new Database();
        $DatabaseConnection = $oDatabase->GetConnection();


	try
	{
		$query = $DatabaseConnection->prepare("UPDATE email_trace SET status = :status WHERE mail_queue_id = :mail_queue_id");
		

	
		$query->bindParam(":status", $Status);
		$query->bindParam(":mail_queue_id", $MailQueueID);
		
		
		$query->execute();

	}
	catch(PDOException $e)
	{
		$oLog = new Log();
		$oLog->WriteLog("error", "/emails/trace/run_trace.php -> UpdateStatus(); Error = ".$e);
	}

}

function UpdateConfirmation($MailQueueID, $Confirmation)
{


        $oDatabase = new Database();
        $DatabaseConnection = $oDatabase->GetConnection();


	try
	{
		$query = $DatabaseConnection->prepare("UPDATE email_trace SET confirmation = :confirmation WHERE mail_queue_id = :mail_queue_id");
		
		$query->bindParam(":mail_queue_id", $MailQueueID);
		$query->bindParam(":confirmation", $Confirmation);
		
		
		$query->execute();

	}
	catch(PDOException $e)
	{
		$oLog = new Log();
		$oLog->WriteLog("error", "/emails/trace/run_trace.php -> UpdateConfirmation(); Error = ".$e);
	}
	

}

function GetValue($MessageType, $LineIn)
{

	//print "\r\ngetting value for '".$MessageType."' from '".$LineIn."'\r\n";
	if( ! strstr($LineIn, " ".$MessageType."="))
	{

		// make sure its not the 1st character...

		if(substr($LineIn, 0, strlen($MessageType) + 1) != $MessageType."=")
		{
			//print "returning . ''\r\n";
			return "";
		}
		else
		{
			// Insert a blank space so the below will work...
			$LineIn = " ".$LineIn;
		}
	}

	$MessageTypeLength = strlen($MessageType) + 2;
	$ReturnMessage = substr($LineIn, strpos($LineIn, " ".$MessageType."="));
	
	if(strstr($ReturnMessage, $MessageType."=\""))
	{
		$MessageTypeLength++;
		// has a double quote
		$End = strpos($ReturnMessage, "\"", $MessageTypeLength);
		$ReturnMessage = substr($ReturnMessage, 0, $End);
		$ReturnMessage = substr($ReturnMessage, $MessageTypeLength);
	}
	else
	{
		// no double quote
		$End = strpos($ReturnMessage, " ", 1);

		//print "End: ".$End."\n";
		if($End === false)
		{
			//print "No space..., take end as the last pos\n";
		
			$ReturnMessage = substr($ReturnMessage, 0);
		}
		else
		{
			$ReturnMessage = substr($ReturnMessage, 0, $End);
		}

		$ReturnMessage = substr($ReturnMessage, $MessageTypeLength);		
	
	}
	
	return trim($ReturnMessage);
}









$x = 0;
while (false !== ($line = fgets(STDIN))) 
{
	$Ignore = 0;
	

	touch($LockFile);

	if( strstr($line, "for devnull") )
	{
		$Ignore = 1;
	}	
	else if( (strstr($line, "login authenticator failed for")) || (strstr($line, "=> webcp@softsmart.co.za P=<root@")) ||  (strstr($line, "=> /dev/null ")) || (strstr($line, "Start queue run")) || (strstr($line, "End queue run:")) || (strstr($line, "unexpected disconnection while reading SMTP command from")) )
	{
		$Ignore = 1;
	}
	else if( (strstr($line, "Retry time not yet reached")) || ( strstr($line, "retry timeout exceeded") ) || ( strstr($line, "retry time not reached") ) )
	{
		$Ignore = 1;
	}
	else if( (strstr($line, "SMTP protocol synchronization error")) || (strstr($line, "Failed to find group \"\" from expanded string")) || (strstr($line, "SMTP command timeout on connection from")) || (strstr($line, "no host name found for IP address")) || (strstr($line, "no IP address found for host"))  )
	{
		$Ignore = 1;
	}
	else if( (strstr($line, "cwd=")) || (strstr($line, "SMTP connection from")) || (strstr($line, "daemon started:")) )
	{
		$Ignore = 1;
	}
        else if( (strstr($line, "Warning: remote host presented unverifiable HELO/EHLO greeting")) || (strstr($line, "incomplete transaction (QUIT) from")) )
        {
                $Ignore = 1;
        }
	else if( (strstr($line, "<= root@".gethostname()." U=root"))  )
        {
                $Ignore = 1;
        }
	else if ( strstr($line, " Failed to find user \"\" from expanded string") )
        {
                $Ignore = 1;
        }


	if($Ignore == 0)
	{
		//print "\r\n=============================\r\nLine: ".$line."\r\n";

		if(strstr($line, ": MYSQL connection failed: Too many connections"))
		{
			// problem!

			if($MySQLMailSent == 0)
			{
				mail("admin@softsmart.co.za", "Too many mysql connections!", $line);
			}

			$MySQLMailSent = 1;
		}
		else if(strstr($line, "Completed"))
                {
                        $Date = trim(substr($line, 0, strpos($line, " ")));
                        $line = substr($line, strpos($line, " ") + 1);

                        $Time = trim(substr($line, 0, strpos($line, " ")));
                        $line = substr($line, strpos($line, " ") + 1);

                        // remove next id
                        $line = substr($line, strpos($line, " ") + 1);

                        $MailQueueID = trim(substr($line, 0, strpos($line, " ")));

                        $QueueTime = GetValue("QT", $line);

                        UpdateStatus($MailQueueID, "Completed");

                }
                else if( (strstr($line, " => ")) || (strstr($line, " -> ")) )
                {
                        $Date = trim(substr($line, 0, strpos($line, " ")));
                        $line = substr($line, strpos($line, " ") + 1);

                        $Time = trim(substr($line, 0, strpos($line, " ")));
                        $line = substr($line, strpos($line, " ") + 1);

                        // remove next id
                        $line = substr($line, strpos($line, " ") + 1);

                        $MailQueueID = trim(substr($line, 0, strpos($line, " ")));

                        // remove prompt
			if(strstr($line, " => "))
			{
                       		$line = trim(substr($line, strpos($line, " => ") + 4));
			}
			else
			{
                       		$line = trim(substr($line, strpos($line, " -> ") + 4));
			}
		
                        $ToAddress = trim(substr($line, 0));
			$ToAddress = trim(substr($ToAddress, 0, strpos($ToAddress, "=") - 2));

                        $IntermediateToAddress = trim($ToAddress);
			$IntermediateToAddress = trim(substr($IntermediateToAddress, 0, strpos($IntermediateToAddress, " <")));

			$Pos = strpos($ToAddress, "<");
			if($Pos !== false)
			{
                        	$ToAddress = trim(substr($ToAddress, strpos($ToAddress, "<") + 1));
                               	$ToAddress = trim(substr($ToAddress, 0, strlen($ToAddress) - 1));
			}

                        $From = GetValue("F", $line);
                        if(strpos($From, "<") >= 0)
                        {
                                $From = trim(substr($From, strpos($From, "<") + 1));
                                $From = trim(substr($From, 0, strlen($From) - 1));
                        }

                        $ReturnPath = GetValue("P", $line);
                        if(strpos($ReturnPath, "<") >= 0)
                        {
                                $ReturnPath = trim(substr($ReturnPath, strpos($ReturnPath, "<") + 1));
                                $ReturnPath = trim(substr($ReturnPath, 0, strlen($ReturnPath) - 1));
                        }

                        $Transport = GetValue("T", $line);
			$Router = GetValue("R", $line);

			$Size = GetValue("S", $line);
			$ReceiverHost = GetValue("H", $line);
			
			$Confirmation = GetValue("C", $line);
			$QueueTime = GetValue("QT", $line);
		

			if( ($IntermediateToAddress == "discarded") && ($Router == "userforward") && ($Confirmation == "") )
			{
				$Confirmation = "Discarded (black list?)";
			}
				
			if(trim($Size) == "")
			{
				$Size = 0;
			}

			/*
			print "MailQueueID: ".$MailQueueID."\n";
			print "Date: ".$Date."\n";
			print "Time: ".$Time."\n";
			print "ToAddress: ".$ToAddress."\n";
			print "IntermediateToAddress: '".$IntermediateToAddress."'\n";
			print "From: ".$From."\n";
			print "ReturnPath: ".$ReturnPath."\n";
			print "Transport: ".$Transport."\n";
			print "Router: ".$Router."\n";
			print "ReceiverHost: ".$ReceiverHost."\n";
			print "Size: ".$Size."\n";
			print "Confirmation: ".$Confirmation."\n";
			print "QueueTime: ".$QueueTime."\n";
			*/

			if( ($Transport == 'remote_smtp') && ($Router == 'dnslookup') )
			{
				$FromDomainUser = GetDomainUserName(substr($From, strpos($From, "@") + 1));
				WriteBandWidthLog($From, $FromDomainUser, $Size);
			}

			UpdateEmailTrace($MailQueueID, $Date, $Time, $ToAddress, $From, $ReturnPath, $Transport, $Router, $ReceiverHost, $Size, $Confirmation, $QueueTime, "in progress");

		}
		
		else if(strstr($line, "<= <>"))
		{

                        $Date = trim(substr($line, 0, strpos($line, " ")));
                        $line = substr($line, strpos($line, " ") + 1);

                        $Time = trim(substr($line, 0, strpos($line, " ")));
                        $line = substr($line, strpos($line, " ") + 1);

                        // remove next id
                        $line = substr($line, strpos($line, " ") + 1);



			$MailQueueID = "";
			$MailQueueID = GetValue("R", $line);

                        // remove prompt
                        $line = trim(substr($line, strpos($line, " <= ") + 4));
			



                        $For = trim(substr($line, strrpos($line, " for ") + 5));

			if ( $For != "root@".gethostname() )
			{	
                        	if( $MailQueueID != "") 
				{
					UpdateStatus($MailQueueID, "Bounce");
				}
			}
		}

		else if(strstr($line, "<="))
		{


			//print "line: ".$line."\r\n";

                        $Date = trim(substr($line, 0, strpos($line, " ")));
                        $line = substr($line, strpos($line, " ") + 1);

                        $Time = trim(substr($line, 0, strpos($line, " ")));
                        $line = substr($line, strpos($line, " ") + 1);

                        // remove next id
                        $line = substr($line, strpos($line, " ") + 1);

                        $MailQueueID = trim(substr($line, 0, strpos($line, " ")));

                        // remove prompt
                        $line = trim(substr($line, strpos($line, " <= ") + 4));
			
                        $SenderHost = GetValue("H", $line);

                        $Protocol = GetValue("P", $line);
	
                        $Auth = GetValue("A", $line);

                        $Size = GetValue("S", $line);
			
                        $Subject = GetValue("T", $line);

			$FromUser = "";
                        $From = trim(substr($line, strrpos($line, "from") + 4));
                        $From = trim(substr($From, 0, strrpos($From, "for") ));

                        if(substr($From, strlen($From) - 1) == ">")
                        {
                                $From = substr($From, 0, strlen($From) - 1);
                        }

                        if(substr($From, 0, 1) == "<")
                        {
                                $From = substr($From, 1);
			}                

			
			$FromUser = GetEmailUser(substr($From, strpos($From, "@") + 1));
			$FromDomainUser = GetDomainUserName(substr($From, strpos($From, "@") + 1));

			$BandWidthWritten = false;
			if($FromDomainUser != "")
			{
				WriteBandWidthLog($From, $FromDomainUser, $Size);
				$BandWidthWritten = true;
			}

		
                        $For = trim(substr($line, strrpos($line, " for ") + 5));
				
			$EmailArray = array();
			$EmailArray = explode(" ", $For);

			$LocalUserCount = 0;  


			foreach($EmailArray as $ForAddress)
			{	
				$ToUser = GetEmailUser(substr($ForAddress, strpos($ForAddress, "@") + 1));
				InsertEmailTrace($MailQueueID, $ToUser, $FromUser, $Date, $Time, $SenderHost, $Protocol, $Auth, $Size, $Subject, $From, $ForAddress, "started");

				if($ToUser != "")
				{
					$LocalUserCount++;
				}
			}
		
			if($BandWidthWritten == false)
			{

				$Size = $Size / $LocalUserCount;


				foreach($EmailArray as $ForAddress)
				{	
					$ToDomainUser = GetDomainUserName(substr($ForAddress, strpos($ForAddress, "@") + 1));
	
					if($ToDomainUser != "")
					{
						WriteBandWidthLog($ForAddress, $ToDomainUser, $Size);
					}
				}
			}

		}

		else if(strstr($line, " ** "))
		{


			if( (strpos($line, ": SMTP") === false) && ( strpos($line, "retry timeout exceeded") !== false) )
			{
				continue;
			}
				

                        $Date = trim(substr($line, 0, strpos($line, " ")));
                        $line = substr($line, strpos($line, " ") + 1);

                        $Time = trim(substr($line, 0, strpos($line, " ")));
                        $line = substr($line, strpos($line, " ") + 1);

                        // remove next id
                        $line = substr($line, strpos($line, " ") + 1);

                        $MailQueueID = trim(substr($line, 0, strpos($line, " ")));
                       
                       	$line = trim(substr($line, strpos($line, " ** ") + 4));

			$ErrorMessage = trim(substr($line, strrpos($line, ": SMTP") + 6));
			$ErrorMessage = trim(substr($ErrorMessage, 0, strpos($ErrorMessage, "RCPT TO:")));

			$For = trim(substr($line, strpos($line, "RCPT TO:") + 8));
			$For = trim(substr($For, 0, strpos($For, ":") - 1));

                        if(substr($For, strlen($For) - 1) == ">")
                        {
                                $For = substr($For, 0, strlen($For) - 1);
                        }

                        if(substr($For, 0, 1) == "<")
                        {
                                $For = substr($For, 1);
                        }

			$RemoteHost = "";
			$Confirmation = "550 error (invalid TO address)";
			
						
			$x = strpos($line, ": host ");
			if($x === false)
			{
				;
			}
			else
			{
				$RemoteHost = trim(substr($line, $x + 7));


				$x = strpos($line, ": 550");

				if($x  === false)
				{
					;
				}

				else
				{
					$Confirmation = trim(substr($line, $x + 1));
					$RemoteHost = trim(substr($RemoteHost, 0, $x - 1));
				}
		
			}
                        
			$ReturnPath = GetValue("P", $line);
                        if(strpos($ReturnPath, "<") >= 0)
                        {
                                $ReturnPath = trim(substr($ReturnPath, strpos($ReturnPath, "<") + 1));
                                $ReturnPath = trim(substr($ReturnPath, 0, strlen($ReturnPath) - 1));
                        }

			$Router = GetValue("R", $line);
                        if(strpos($Router, "<") >= 0)
                        {
                                $Router = trim(substr($Router, strpos($Router, "<") + 1));
                                $Router = trim(substr($Router, 0, strlen($Router) - 1));
                        }

			UpdateEmailTrace($MailQueueID, $Date, $Time, $For, "", $ReturnPath, $ErrorMessage, $Router, $RemoteHost, 0, $Confirmation, 0, "error");
			
		}

		else if(strstr($line, " == "))
		{
                        

			$Date = trim(substr($line, 0, strpos($line, " ")));
                        $line = substr($line, strpos($line, " ") + 1);

                        $Time = trim(substr($line, 0, strpos($line, " ")));
                        $line = substr($line, strpos($line, " ") + 1);

                        // remove next id
                        $line = substr($line, strpos($line, " ") + 1);
		
                        $MailQueueID = trim(substr($line, 0, strpos($line, " ")));
                       
                       	$line = trim(substr($line, strpos($line, " == ") + 4));


			$RemoteHost = "";
			$Confirmation = "";

			$For = "";
			$ErrorMessage = "error";

			if(strpos($line, "Disk quota exceeded") !== false)
			{
			       
				$ErrorMessage = substr($line, strpos($line, "Disk quota exceeded"));

			}
			else if(strpos($line, ": SMTP") !== false)
			{
				$ErrorMessage = trim(substr($line, strrpos($line, ": SMTP") + 6));
				$ErrorMessage = trim(substr($ErrorMessage, 0, strpos($ErrorMessage, "RCPT TO:")));
	
				$For = trim(substr($line, strpos($line, "RCPT TO:") + 8));
				$For = trim(substr($For, 0, strpos($For, ":") - 1));
	
	                        if(substr($For, strlen($For) - 1) == ">")
	                        {
	                                $For = substr($For, 0, strlen($For) - 1);
	                        }
	
	                        if(substr($For, 0, 1) == "<")
	                        {
	                                $For = substr($For, 1);
	                        }
	
				$RemoteHost = "";
				$Confirmation = "451 Temporarily delayed";
							
				$x = strpos($line, ": host ");
				if($x === false)
				{
					;
				}
				else
				{
					$RemoteHost = trim(substr($line, $x + 7));
	
	
					$x = strpos($line, ": 451");
	
					if($x  === false)
					{
						;
					}
	
					else
					{
						$Confirmation = trim(substr($line, $x + 1));
						$RemoteHost = trim(substr($RemoteHost, 0, $x - 1));
					}
			
				}
        		}



                
			$Router = GetValue("R", $line);
                        if(strpos($Router, "<") >= 0)
                        {
                                $Router = trim(substr($Router, strpos($Router, "<") + 1));
                                $Router = trim(substr($Router, 0, strlen($Router) - 1));
                        }

			/*		
			print "Date: ".$Date." ".$Time."\r\n";
			print "MailQueueID: ".$MailQueueID."\r\n";
			print "ErrorMessage: ".$ErrorMessage."\r\n";
			print "For: ".$For."\r\n";
			print "RemoteHost: ".$RemoteHost."\r\n";
			print "Router: ".$Router."\r\n";
			print "Confirmation: ".$Confirmation."\r\n";
			*/

                        UpdateEmailTrace($MailQueueID, $Date, $Time, $For, "", "", $ErrorMessage, $Router, $RemoteHost, 0, $Confirmation, 0, $ErrorMessage);
			
		}


		else if(strstr($line, ": DNSBL -"))
		{
                        $Date = trim(substr($line, 0, strpos($line, " ")));
                        $line = substr($line, strpos($line, " ") + 1);

                        $Time = trim(substr($line, 0, strpos($line, " ")));
                        $line = substr($line, strpos($line, " ") + 1);

                        // remove next id
                        $line = substr($line, strpos($line, " ") + 1);

			//print "Line now: ".$line."\r\n";
			$RemoteHost = GetValue("H", $line);


                        $IPAddress = "";
                        $IPStart = strpos($line, "[");

                        if($IPStart > 0)
                        {
                                $IPAddress = substr($line, $IPStart + 1);
                                $IPAddress = substr($IPAddress, 0, strpos($IPAddress, "]"));
                        }

			$From = GetValue("F", $line);
                        if(strpos($From, "<") >= 0)
                        {
                                $From = trim(substr($From, strpos($From, "<") + 1));
                                $From = trim(substr($From, 0, strlen($From) - 1));
                        }

			$For = trim(substr($line, strpos($line, "rejected RCPT ") + 14));
			$For = trim(substr($For, 0, strpos($For, ":") - 1));

                        if(substr($For, strlen($For) - 1) == ">")
                        {
                                $For = substr($For, 0, strlen($For) - 1);
                        }

                        if(substr($For, 0, 1) == "<")
                        {
                                $For = substr($For, 1);
                        }
		
			$Confirmation = trim(substr($line, strpos($line, ": DNSBL - ") + 2));

			//print "Date: ".$Date." ".$Time."\r\n";
			//print "For: ".$For."\r\n";
			//print "RemoteHost: ".$RemoteHost."\r\n";
			//print "From: ".$From."\r\n";
			//print "Confirmation: ".$Confirmation."\r\n";
			
			$ToUser = GetEmailUser(substr($For, strpos($For, "@") + 1));
			$FromUser = GetEmailUser(substr($From, strpos($From, "@") + 1));

			InsertEmailTrace("", $ToUser, $FromUser, $Date, $Time, $RemoteHost, "", "", 0, "", $From, $For, $Confirmation);



			$ToDomain = substr($For, strpos($For, "@") + 1);
			$FromDomain = substr($From, strpos($From, "@") + 1);


                        file_put_contents("/tmp/mailbandnsbl", "ToDomain: ".$ToDomain."; FromDomain: ".$FromDomain.";  IPAddress: ".$IPAddress."\r\n", FILE_APPEND);
                        if($ToDomain = $FromDomain)
                        {
                                if($IPAddress != "")
                                {
                                        $FileName = "/var/www/html/webcp/fail2ban/tmp/add.ban";

                                        $fh = fopen($FileName, 'a') or die("can't open file");
                                        fwrite($fh, $IPAddress.",604800\n");
                                        fclose($fh);

                                }
                        } 
						 
		}











		else if(strstr($line, "forbidden binary in attachment:"))
		{
                        $Date = trim(substr($line, 0, strpos($line, " ")));
                        $line = substr($line, strpos($line, " ") + 1);

                        $Time = trim(substr($line, 0, strpos($line, " ")));
                        $line = substr($line, strpos($line, " ") + 1);

                        // remove next id
			$MailQueueID = substr($line, strpos($line, "[") + 1);
			$MailQueueID = substr($MailQueueID, 0, strpos($MailQueueID, "]"));
                        $line = substr($line, strpos($line, " ") + 1);

			//print "Line now: ".$line."\r\n";
			$RemoteHost = GetValue("H", $line);

			$From = GetValue("F", $line);
                        if(strpos($From, "<") >= 0)
                        {
                                $From = trim(substr($From, strpos($From, "<") + 1));
                                $From = trim(substr($From, 0, strlen($From) - 1));
                        }

			$For = trim(substr($line, strpos($line, "recipients=") + 11));

                        if(substr($For, strlen($For) - 1) == ">")
                        {
                                $For = substr($For, 0, strlen($For) - 1);
                        }

                        if(substr($For, 0, 1) == "<")
                        {
                                $For = substr($For, 1);
                        }
		
			$Confirmation = trim(substr($line, strpos($line, "filename=") + 9));
			$Confirmation = trim(substr($Confirmation, 0, strpos($Confirmation, ",")));

			$Status = "Potential Virus";
			//print "Date: ".$Date." ".$Time."\r\n";
			//print "For: ".$For."\r\n";
			//print "RemoteHost: ".$RemoteHost."\r\n";
			//print "From: ".$From."\r\n";
			//print "Confirmation: ".$Confirmation."\r\n";
			
			$ToUser = GetEmailUser(substr($For, strpos($For, "@") + 1));
			$FromUser = GetEmailUser(substr($From, strpos($From, "@") + 1));

			InsertEmailTrace($MailQueueID, $ToUser, $FromUser, $Date, $Time, $RemoteHost, "", "", 0, "", $From, $For, $Status);
			UpdateConfirmation($MailQueueID, $Confirmation);

			 
		}


















		else if(strstr($line, "SpamAssassin point."))
		{
                        $Date = trim(substr($line, 0, strpos($line, " ")));
                        $line = substr($line, strpos($line, " ") + 1);

                        $Time = trim(substr($line, 0, strpos($line, " ")));
                        $line = trim(substr($line, strpos($line, " ") + 1));

                        // remove next id
                        $line = trim(substr($line, strpos($line, " ") + 1));

                        $MailQueueID = trim(substr($line, 0, strpos($line, " ")));

			//print "Line now: ".$line."\r\n";
			$RemoteHost = GetValue("H", $line);

			$From = GetValue("F", $line);
                        if(strpos($From, "<") >= 0)
                        {
                                $From = trim(substr($From, strpos($From, "<") + 1));
                                $From = trim(substr($From, 0, strlen($From) - 1));
                        }

			$For = trim(substr($line, strpos($line, "Your message to ") + 16));
			$For = trim(substr($For, 0, strpos($For, " scored")));

                        if(substr($For, strlen($For) - 1) == ">")
                        {
                                $For = substr($For, 0, strlen($For) - 1);
                        }

                        if(substr($For, 0, 1) == "<")
                        {
                                $For = substr($For, 1);
                        }
		
			$SpamScore = trim(substr($line, strpos($line, " scored ") + 8));
			$SpamScore = trim(substr($SpamScore, 0, strpos($SpamScore, "SpamAssassin point") - 1));

			$ToUser = GetEmailUser(substr($For, strpos($For, "@") + 1));
			$FromUser = GetEmailUser(substr($From, strpos($From, "@") + 1));

			
			/*
			print "SpamScore: ".$SpamScore."\r\n";
			print "ToUser: ".$ToUser."\r\n";
			print "FromUser: ".$FromUser."\r\n";
			print "Date: ".$Date." ".$Time."\r\n";
			print "For: ".$For."\r\n";
			print "RemoteHost: ".$RemoteHost."\r\n";
			print "From: ".$From."\r\n";
			print "MailQueueID: ".$MailQueueID."\r\n";
			*/

			InsertEmailTrace($MailQueueID, $ToUser, $FromUser, $Date, $Time, $RemoteHost, "", "", 0, "", $From, $For, "spam assassin");
			UpdateEmailTrace($MailQueueID, $Date, $Time, $For, $From, '','','', '',0, "Spam score: ".$SpamScore, 0, "spam assassin");
		}





		else if(strstr($line, ": relay not permitted"))
		{
                        $Date = trim(substr($line, 0, strpos($line, " ")));
                        $line = substr($line, strpos($line, " ") + 1);

                        $Time = trim(substr($line, 0, strpos($line, " ")));
                        $line = substr($line, strpos($line, " ") + 1);

                        // remove next id
                        $line = substr($line, strpos($line, " ") + 1);

			//print "Line now: ".$line."\r\n";
			$RemoteHost = GetValue("H", $line);
			
			$IPAddress = "";
			$IPStart = strpos($line, "[");
			
			if($IPStart > 0)
			{
				$IPAddress = substr($line, $IPStart + 1);
				$IPAddress = substr($IPAddress, 0, strpos($IPAddress, "]"));
			}

			$From = GetValue("F", $line);
                        if(strpos($From, "<") >= 0)
                        {
                                $From = trim(substr($From, strpos($From, "<") + 1));
                                $From = trim(substr($From, 0, strlen($From) - 1));
                        }

			$For = trim(substr($line, strpos($line, "rejected RCPT ") + 14));
			$For = trim(substr($For, 0, strpos($For, ":") - 1));

                        if(substr($For, strlen($For) - 1) == ">")
                        {
                                $For = substr($For, 0, strlen($For) - 1);
                        }

                        if(substr($For, 0, 1) == "<")
                        {
                                $For = substr($For, 1);
                        }
		
			$Confirmation = "relay not permitted";
			
			$ToUser = GetEmailUser(substr($For, strpos($For, "@") + 1));
			$FromUser = GetEmailUser(substr($From, strpos($From, "@") + 1));

			$ToDomain = substr($For, strpos($For, "@") + 1);
			$FromDomain = substr($From, strpos($From, "@") + 1);

			//file_put_contents("/tmp/mailban", "ToDomain: ".$ToDomain."; FromDomain: ".$FromDomain.";  IPAddress: ".$IPAddress."\r\n", FILE_APPEND);
			if($ToDomain = $FromDomain)
			{
				if($IPAddress != "")
				{
			                $FileName = "/var/www/html/webcp/fail2ban/tmp/add.ban";

			                $fh = fopen($FileName, 'a') or die("can't open file");
			                fwrite($fh, $IPAddress.",604800\n");
			                fclose($fh);

				}
			}	
				
			InsertEmailTrace("", $ToUser, $FromUser, $Date, $Time, $RemoteHost, "", "", 0, "", $From, $For, $Confirmation);
			 
		}


	}



}

unlink($LockFile);

//fwrite($f, "\r\n=================================================\r\n");

//print "\r\nArray: \r\n";
//print_r($UserNameArray);

?>
