<?php
	if(!isset($SiteName))
	{
		$SiteName = $_SERVER["SERVER_NAME"];
	}
	
	if(!isset($FirewallAddress))
	{
		$FirewallAddress = "http://".$_SERVER["SERVER_NAME"];
	}
	
     $URL = $_SERVER["REQUEST_URI"];

     $options = array(
     'uri' => 'https://api.webcp.io',
     'location' => 'https://api.webcp.io/Country.php',
     'trace' => 1);

     $client = new SoapClient(NULL, $options);
	
	$CountryCode = "";
     
	try
	{
		$CountryCode = $client->GetCountryCode($_SERVER["REMOTE_ADDR"]);
		print "CountryCode: ".$CountryCode."<p>";

     		$BlockIP = false;
     		$Blocking = array();


		array_push($Blocking, "onmousedown=%22return%20rwt");
		array_push($Blocking, "../../../configuration.php%00");
		array_push($Blocking, "/rfid_news/comment.php");
		array_push($Blocking, "connector.asp");
		array_push($Blocking, "name=magic.php");
		array_push($Blocking, "ofc_upload_image.php");   
		array_push($Blocking, "muieblackcat");
		array_push($Blocking, "login.action");     
		array_push($Blocking, "php-ofc-library");  
		array_push($Blocking, "administrator/components");
		array_push($Blocking, "javascript:location.reload()");
		array_push($Blocking, "/wp-content/security.php");
		array_push($Blocking, "/wp-content/uploads/comments.php");
		array_push($Blocking, "/wp-content/uploads/thread.php");    
		array_push($Blocking, "/wp-content/uploads/4O4.php");
		array_push($Blocking, "/wp-content/uploads/help.php");
		array_push($Blocking, "/wp-content/uploads/plagmain.php");               
		array_push($Blocking, "/wp-content/uploads/mute.php");
		array_push($Blocking, "/wp-content/218.php");
		array_push($Blocking, "editor/editor.html");
		array_push($Blocking, "editor/filemanager");
		array_push($Blocking, "force-download.php");
		array_push($Blocking, "file=wp-config.php");    
		array_push($Blocking, "/author-panel");
		array_push($Blocking, "/submit-articles");
		array_push($Blocking, "FCKeditor");
		array_push($Blocking, "fckeditor");
		array_push($Blocking, "/js/testurl");     
		array_push($Blocking, "wp-admin");
		array_push($Blocking, "wp-login");
		array_push($Blocking, "wp-update.php");
		array_push($Blocking, "/wp-content/plugins/hello.php");
		array_push($Blocking, "/mw/");     
		array_push($Blocking, ".sql");
		array_push($Blocking, "wso2.php");
		array_push($Blocking, "wso.php");
		array_push($Blocking, "wp_config.php");
		array_push($Blocking, "wp-cache.php");	
		array_push($Blocking, "wp-cmd.php");
		array_push($Blocking, "wp-admin-cache.php");
		array_push($Blocking, "src.php");
		array_push($Blocking, "setup.php");
		array_push($Blocking, "license.php");	
		array_push($Blocking, "lib.php");
		array_push($Blocking, "shell.php");
		array_push($Blocking, "class.salt.php");
		array_push($Blocking, "wp-log.php");
		array_push($Blocking, "hh.php");	
		array_push($Blocking, "wp-content/uploads/");
		array_push($Blocking, "wp-xml.php");
		array_push($Blocking, "wp-css.php");
		array_push($Blocking, "ms-default-base.php");
		array_push($Blocking, "_tmp_cache.php");
		array_push($Blocking, "install");
		array_push($Blocking, "images.php");
		array_push($Blocking, "crms2.php");
		array_push($Blocking, "shtml.exe");
                array_push($Blocking, "pomo.php");
		array_push($Blocking, "config_options.php");
		array_push($Blocking, "cache_checkexpress.php");
		array_push($Blocking, "common_configuration.php");
		array_push($Blocking, "licenseerror.php");
		array_push($Blocking, "admin/login.php");
		array_push($Blocking, "configuration.php");
		array_push($Blocking, "configuration.php");

		array_push($Blocking, "sqlite");
                array_push($Blocking, "sqlitemanager");
                array_push($Blocking, "phpmyadmin");
                array_push($Blocking, "myadmin");
                array_push($Blocking, "mysqldumper");
                array_push($Blocking, "manager");
                array_push($Blocking, "login");


     		//array_push($Blocking, "");
   
     		for($BlockingArrayCount = 0; $BlockingArrayCount < count($Blocking); $BlockingArrayCount++)
     		{
          		//print "Checking: ".$Blocking[$BlockingArrayCount]." - ".$BlockingArrayCount."<br>";
          
          		if(strstr(strtolower($URL), $Blocking[$BlockingArrayCount]))
          		{
               			$BlockIP = true;
               			break;
          		}
     		}
     
    	 	if($BlockIP == true)
     		{  	 
          		mail("john@softsmart.co.za", $SiteName."404 - Firewalled!", "URL: ".$URL."\r\nIP: ".$_SERVER["REMOTE_ADDR"]."\r\nCountryCode: ".$CountryCode);
 
          		$ch = curl_init();
          		curl_setopt($ch, CURLOPT_URL, $FirewallAddress.":10025/fail2ban/BanFromEmail.php?Silence=1&IP=".$_SERVER["REMOTE_ADDR"]);
          		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
          		$result=curl_exec($ch);
          		curl_close($ch);
          		print $result;
	
       			exit();
		}


		$POSTS = print_r($_POST, true);
		$SERVERS = print_r($_SERVER, true);

		$Message = "There was a 404\r\n\r\nURL: ".$URL."\r\n";
		$Message = $Message."IP: ".$FirewallAddress.":10025/fail2ban/BanFromEmail.php?IP=".$_SERVER["REMOTE_ADDR"]."\r\nCountryCode: ".$CountryCode."\r\nPOSTS: ".$POSTS."\r\nSERVERS: ".$SERVERS;

		$URL = date("Ymd").str_replace("/", "_", $URL);
		if( ! file_exists($_SERVER["DOCUMENT_ROOT"]."/tmp"))
		{
	        	mkdir($_SERVER["DOCUMENT_ROOT"]."/tmp", 0755);
		}

		if( ! file_exists($_SERVER["DOCUMENT_ROOT"]."/tmp/".$URL))
		{
	        	touch($_SERVER["DOCUMENT_ROOT"]."/tmp/".$URL);
	        	mail("john@softsmart.co.za", $SiteName." 404", $Message);
		}
	}
	catch(Execption $e)
	{
		print "Error!";
	}

?>
