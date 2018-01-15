<?php

	function FetchIconListFromRemoteServer()
	{
		$LocalMD5 = "";
		$RemoteMD5 = md5_file("http://api.webcp.pw/FileEditor/icon_list.txt");

		if( file_exists($_SERVER["DOCUMENT_ROOT"]."/Editor/icon_list.txt"))
		{
			$LocalMD5 = md5_file($_SERVER["DOCUMENT_ROOT"]."/Editor/icon_list.txt");
		}

		if($LocalMD5 == $RemoteMD5)
		{
			// file unchanged...
			print "Icon list unchanged, not downloading<br>";
			touch($_SERVER["DOCUMENT_ROOT"]."/Editor/icon_list.txt");
			return;
		}

		print "Downloading new Icon List<br>";
		
		// got here, download new list!
		if( file_exists($_SERVER["DOCUMENT_ROOT"]."/Editor/icon_list.txt"))
		{
			unlink($_SERVER["DOCUMENT_ROOT"]."/Editor/icon_list.txt");
		}

		file_put_contents($_SERVER["DOCUMENT_ROOT"]."/Editor/icon_list.txt", file_get_contents("http://api.webcp.pw/FileEditor/icon_list.txt"));

		$IconArray = file($_SERVER["DOCUMENT_ROOT"]."/Editor/icon_list.txt");

		for($x = 1; $x < count($IconArray); $x++) // ignore first element as its the date!
		{
			$val = trim($IconArray[$x]);

			if(file_exists($_SERVER["DOCUMENT_ROOT"]."/Editor/icons/".$val))
			{
				unlink($_SERVER["DOCUMENT_ROOT"]."/Editor/icons/".$val);
			}

			file_put_contents($_SERVER["DOCUMENT_ROOT"]."/Editor/icons/".$val, file_get_contents("http://api.webcp.pw/FileEditor/icons/".$val));
		}
	}

	
	function FetchEditableListFromRemoteServer()
	{
		$LocalMD5 = "";
		$RemoteMD5 = md5_file("http://api.webcp.pw/FileEditor/editable_list.txt");

		if( file_exists($_SERVER["DOCUMENT_ROOT"]."/Editor/editable_list.txt"))
		{
			$LocalMD5 = md5_file($_SERVER["DOCUMENT_ROOT"]."/Editor/editable_list.txt");
		}

		if($LocalMD5 == $RemoteMD5)
		{
			// file unchanged...
			print "Editable list unchanged, not downloading<br>";
			touch($_SERVER["DOCUMENT_ROOT"]."/Editor/editable_list.txt");
			return;
		}
		
		print "Downloading new Icon List<br>";

		// got here, download new list!
		if( file_exists($_SERVER["DOCUMENT_ROOT"]."/Editor/editable_list.txt"))
		{
			unlink($_SERVER["DOCUMENT_ROOT"]."/Editor/editable_list.txt");
		}

		file_put_contents($_SERVER["DOCUMENT_ROOT"]."/Editor/editable_list.txt", file_get_contents("http://api.webcp.pw/FileEditor/editable_list.txt"));
		
	}

?>
