<?php
session_start();

function __autoload($classname)
{
	require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.".$classname.".php");
}

$oUser = new User();

if(!file_exists("./tmp"))
{
	mkdir("./tmp", 0755);
}

$ClientID = $oUser->getClientId();
if($ClientID < 1)
{
	header("Location: /index.php");
	exit();
}

if($oUser->Role != "admin")
{
        header("Location: /index.php");
        exit();
}





?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head profile="http://gmpg.org/xfn/11">
	

<title>Dashboard | Web Control Panel</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />

<!--
@import url("/includes/styles/tablestyle.css");
-->
</style>

<?php
include($_SERVER["DOCUMENT_ROOT"]."/includes/styles/Styles.inc");
?>

<script src="/includes/javascript/jquery.js"></script>
<script src="/includes/javascript/main.js"></script>
<script src="/includes/javascript/sorttable.js"></script>

</head>

<body style="margin:0; background: #ededed">

<?php
include($_SERVER["DOCUMENT_ROOT"]."/includes/PageParts/TopBar.inc");
include($_SERVER["DOCUMENT_ROOT"]."/includes/PageParts/TopNav.inc");

?>



<div id="wrap">
<p>

<div align="center" style="width:100%; background:white; padding-top:20px; padding-bottom:20px; border-style:dotted; border-width:1px">

<h2>Uptime / Load Average</h2>
<br>
<pre>
<?php
include("./tmp/load.txt");
?>
</pre>
<hr>
<p>

<h2>Memory (Mb)</h2>
<br>
<pre>
<?php
include("./tmp/memory.txt");
?>
</pre>
<hr>
<p>


<h2>Disk Space (Mb)</h2>
<br>
<pre>
<?php
include("./tmp/diskspace.txt");
?>
</pre>
<hr>
<p>

</div>


</div>
	
</body>
</html>
