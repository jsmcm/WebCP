<?php

session_start();

require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.User.php");
$oUser = new User();

require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.Settings.php");
$oSettings = new Settings();

require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.Domain.php");
$oDomain = new Domain();

$ClientID = $oUser->getClientId();
if($ClientID < 1)
{
        header("Location: /index.php");
        exit();
}

if($oUser->Role != "admin")
{
	header("location: /domains/index.php?Notes=No permissions");
	exit();
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head profile="http://gmpg.org/xfn/11">
	

<title>Skel Editor | <?php print $oSettings->GetWebCPTitle(); ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />


<style type="text/css">
<!--
@import url("/includes/styles/tablestyle.css");
-->

.InputClass
{
	border: solid 1px; black;
	height: 20px;
	width: 250px;
}

.CronHeader
{
	font-sie:12px;
	color: #000099;
}


.CronBody
{
	height:35px;
}

.CronBody input
{
	border: 1px solid grey;
	width: 40px;
	height: 20px;
}

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


<h1>Skel folder setup</h1>

<center>
<table border="1" cellpadding="0" cellspacing="0" width=90%">
<tr>
	<td class="CronHeader">Use the skel editor to create a basic page which will be used every time you setup a new hosting account.<br>The skep directory can contain any valid web content, including links, PHP pages, dynamic content and downloadable files.

<p>

Having said that, you'll probably only want to create a bare bones single page saying something like:<p>"This is the future home of www.somedomain.com, hosted by www.hosting-company.com"

<p>&nbsp;<p>
<a target="_new" href="./editor/">Continue to the editor by clicking here!</a>

<p>&nbsp;<p>
<font color="#cc0000">Note: the files you create here will only "work" after a few hours. We recommend creating your skel files at least one day before setting up new accounts</font>

</td>
</tr>


</table>
</center>

</div>


</div>
</body>
</html>

