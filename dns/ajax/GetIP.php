<?php

$IP = "";

if(isset($_GET["HostName"]))
{
	$IP = gethostbyname($_GET["HostName"]);
}
print $IP;
?>
