<?php
//$Debug = true;
$Debug = false;

if(!file_exists($_SERVER["DOCUMENT_ROOT"]."/includes/cron/tmp/"))
{
        mkdir($_SERVER["DOCUMENT_ROOT"]."/includes/cron/tmp/", 0755);
}

include_once($_SERVER["DOCUMENT_ROOT"]."/vendor/autoload.php");
$oSettings = new Settings();
$oDNS = new DNS();

if($oDNS->IPExists($_SERVER["REMOTE_ADDR"]) == false)
{
	if( ($_SERVER["REMOTE_ADDR"] != "127.0.0.1") && ($_SERVER["REMOTE_ADDR"] != "::1") )
	{
		//file_put_contents("./tmp/log.txt", $_SERVER["REMOTE_ADDR"]." not allowed!\n", FILE_APPEND);

	        if($Debug == false)
	        {
			print "No access from remote IP!";
	                exit();
	        }
	        print "Remote address not allowed, BUT in debug so continuing<p>";
	}
}


function LoopDirectory($DirBase, $InSub = 0, $Debug=false)
{

	$Base = substr(dirname(__FILE__), strlen($_SERVER["DOCUMENT_ROOT"]));
	$Base = substr($Base, 0, strpos($Base, "/includes/cron"));

	$ServerName = filter_var($_SERVER["SERVER_NAME"], FILTER_SANITIZE_STRING);

	$Port = "8880";
	$HTTP = "http";
	if( (isset($_SERVER["HTTPS"])) && ($_SERVER["HTTPS"] == "on"))
	{
		$HTTP = "https";
		$Port = "8443";
	}

	if($Debug == true)
	{
		print "Http: ".$HTTP."<p>";
		print "ServerName: ".$ServerName."<p>";
		print "Base: ".$Base."<p>";
	}

	$liPart = "";
	$LinkPart = "";
	
	if ($handle = opendir($DirBase)) 
	{

		/* This is the correct way to loop over the directory. */
		while (false !== ($file = readdir($handle))) 
		{
			if(is_file($DirBase."/".$file))
			{
				
				
				if($file != "." && $file != ".." && $file != "index.php" && ! strstr($file, ".txt") )
				{

					if($Debug == true)
					{
						print "<p>".$file."<p>";
					}

					if(substr($file, strlen($file) - 4, 4) == ".php")
					{					
						$c = curl_init();
						curl_setopt($c, CURLOPT_SSL_VERIFYPEER, false);
						curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);

	
						curl_setopt($c, CURLOPT_URL, $HTTP."://".$ServerName.$Base.":".$Port."/includes/cron/".$file);
		
						$ResultString = curl_exec($c);
						curl_close($c);
						
						if($Debug == true)
						{
							print $ResultString."<br>";
						}
				
					}
				}
			}
		}

		closedir($handle);
	}
}

LoopDirectory("./", $InSub = 0, $Debug);
print "cron_ok";
