<?php
	
$LockFile = "/var/www/html/webcp/ftp/trace/bw_trace_lock";
if(file_exists($LockFile))
{
	// if its older than 5 minutes something's gone wrong, delete it.
	$datetime1 = new DateTime(date("Y-m-d H:i:s", filemtime($LockFile)));
        $datetime2 = new DateTime(date("Y-m-d H:i:s"));
        $interval = $datetime1->diff($datetime2);

	if( (int)$interval->format('%i') > 5)
	{
		$f = fopen("/var/www/html/webcp/ftp/trace/bw_log.txt", "a");
		fwrite($f, "deleting stale lock file\r\n");
		fclose($f);

		// The previous instance stalled...
		unlink($LockFile);
	}

	exit();

}

touch($LockFile);

function WriteBandWidthLog($EmailAddress, $DomainUserName, $Size)
{
	$f = fopen("/var/www/html/bandwidth/dovecot/".date("H")."_".$DomainUserName, "a");
	fwrite($f, $Size."\n");
	fclose($f);
}

$x = 0;
while (false !== ($line = fgets(STDIN))) 
{
	

	touch($LockFile);
	
		if( (strstr($line, "\"GET")) || (strstr($line, "\"PUT")) )
                {
			print "Line: ".$line."\r\n";

			$DomainUserName = substr($line, strpos($line, " - ") + 3);
			$DomainUserName = substr($DomainUserName, 0, strpos($DomainUserName, "_")); 

			$Size = substr($line, strrpos($line, " ") + 1); 
			
			while( (substr($Size, strlen($Size) - 1) == "\r") || (substr($Size, strlen($Size) - 1) == "\n") )
                        {
                                $Size = substr($Size, 0, strlen($Size) - 1);
                        }

			print "DomainUserName: '".$DomainUserName."'\r\n";
			print "Size: '".$Size."'\r\n";
                        

			if($Size > 0)
			{
				//WriteBandWidthLog($EmailAddress, $DomainUserName, $Size);
			}

		}

}

unlink($LockFile);

?>
