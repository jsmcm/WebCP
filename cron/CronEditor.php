<?php
session_start();

include_once($_SERVER["DOCUMENT_ROOT"]."/vendor/autoload.php");
$oUser = new User();
$oSimpleNonce = new SimpleNonce();
$oSettings = new Settings();
$oReseller = new Reseller();

require($_SERVER["DOCUMENT_ROOT"]."/includes/License.inc.php");

$oDomain = new Domain();

$ClientID = $oUser->getClientId();
if($ClientID < 1) {
	header("Location: /domains/");
	exit();
}



$URL = "";
if(isset($_REQUEST["URL"])) {
	$URL = $_REQUEST["URL"];
} else {
	header("location: index.php?Notes=URL not set");
	exit();
}


$nonceArray = [
	$oUser->Role,
	$oUser->ClientID,
	$URL
];

$oSimpleNonce = new SimpleNonce();
$nonce = $oSimpleNonce->GenerateNonce("getDomainOwnerFromDomainName", $nonceArray);
$DomainOwnerID = $oDomain->GetDomainOwnerFromDomainName($URL, $nonce);  
  
if($oUser->Role == "client") {
	if($DomainOwnerID != $ClientID) {
		header("location: index.php?Notes=permission not set");
		exit();
	}
} else if($oUser->Role == "reseller") {
	$ResellerID = $oReseller->GetDomainResellerID($URL);

	if( ($DomainOwnerID != $ClientID) && ($ResellerID != $ClientID) ) {
		header("location: index.php?Notes=permission not set");
		exit();
	}
}

$MaxJobs = 10;
if(file_exists($_SERVER["DOCUMENT_ROOT"]."/cron/max_jobs.dat")) {
	$MaxJobs = (int)file_get_contents($_SERVER["DOCUMENT_ROOT"]."/cron/max_jobs.dat");
}

$Action = "getUserCron";
$Meta = array();
array_push($Meta, $URL);

$NonceValues = $oSimpleNonce->GenerateNonce($Action, $Meta);

$c = curl_init();
curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
if ( file_exists("/etc/letsencrypt/renewal/".$URL.".conf") ) {
	curl_setopt($c, CURLOPT_URL, "https://".$URL.":2083/read.php?Nonce=".$NonceValues["Nonce"]."&TimeStamp=".$NonceValues["TimeStamp"]);
	curl_setopt($c, CURLOPT_SSL_VERIFYPEER, false);
} else {
	curl_setopt($c, CURLOPT_URL, "http://".$URL.":2082/read.php?Nonce=".$NonceValues["Nonce"]."&TimeStamp=".$NonceValues["TimeStamp"]);
}




$ResultString = trim(curl_exec($c));
curl_close($c);


if(trim($ResultString) != "") {
	$CronArray = explode("\n", $ResultString);

	if (!empty($CronArray)) {


		$domainId = $oDomain->GetDomainIDFromDomainName($URL);
	
		$random = random_int(1, 1000000);
		
		$nonceArray = [	
			$oUser->Role,
			$oUser->ClientID,
			$domainId,
			$random
		];
		$nonce = $oSimpleNonce->GenerateNonce("getDomainInfo", $nonceArray);
		
		$DomainInfoArray = array();
		$oDomain->GetDomainInfo($domainId, $random, $DomainInfoArray, $nonce);

		// rename user directories to user friendly version (to "overcome" jailed dirs)
		for($x = 0; $x < count($CronArray); $x++) {

			$CronArray[$x] = str_replace("/home/".$DomainInfoArray["UserName"]."/home/".$DomainInfoArray["UserName"], "/home/".$DomainInfoArray["UserName"], $CronArray[$x]);
		
		}
		
	}	

} else {
	$CronArray = array();
}

?>

<!DOCTYPE html>
<!--[if IE 8]><html class="ie8 no-js" lang="en"><![endif]-->
<!--[if IE 9]><html class="ie9 no-js" lang="en"><![endif]-->
<!--[if !IE]><!-->
<html lang="en" class="no-js">
	<!--<![endif]-->
	<!-- start: HEAD -->
	<head>
		<title>Cron Editor | <?php print $oSettings->GetWebCPTitle(); ?> </title>
		<!-- start: META -->
		<meta charset="utf-8" />
		<!--[if IE]><meta http-equiv='X-UA-Compatible' content="IE=edge,IE=9,IE=8,chrome=1" /><![endif]-->
		<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimum-scale=1.0, maximum-scale=1.0">
		<meta name="apple-mobile-web-app-capable" content="yes">
		<meta name="apple-mobile-web-app-status-bar-style" content="black">
		<meta content="" name="description" />
		<meta content="" name="author" />
		<!-- end: META -->
		<!-- start: MAIN CSS -->
		<link href="/assets/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet" media="screen">
		<link rel="stylesheet" href="/assets/plugins/font-awesome/css/font-awesome.min.css">
		<link rel="stylesheet" href="/assets/fonts/style.css">
		<link rel="stylesheet" href="/assets/css/main.css">
		<link rel="stylesheet" href="/assets/css/main-responsive.css">
		<link rel="stylesheet" href="/assets/plugins/iCheck/skins/all.css">
		<link rel="stylesheet" href="/assets/plugins/bootstrap-colorpalette/css/bootstrap-colorpalette.css">
		<link rel="stylesheet" href="/assets/plugins/perfect-scrollbar/src/perfect-scrollbar.css">
		<link rel="stylesheet" href="/assets/css/theme_light.css" id="skin_color">
		<!--[if IE 7]>
		<link rel="stylesheet" href="/assets/plugins/font-awesome/css/font-awesome-ie7.min.css">
		<![endif]-->
		<!-- end: MAIN CSS -->
		<!-- start: CSS REQUIRED FOR THIS PAGE ONLY -->
		<link rel="stylesheet" type="text/css" href="/assets/plugins/select2/select2.css" />
		<link rel="stylesheet" href="/assets/plugins/DataTables/media/css/DT_bootstrap.css" />
		<!-- end: CSS REQUIRED FOR THIS PAGE ONLY -->
		<link rel="shortcut icon" href="/favicon.ico" />
	

<script language="javascript">

function htmlEntities(str) {
    return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

function EscapeCommands()
{
	document.SaveCronTab.Command_new.value = document.SaveCronTab.Command_new.value;
	//document.SaveCronTab.Command_new.value = htmlEntities(document.SaveCronTab.Command_new.value);
}

function ValidateDelete(RowIndex)
{

	if(confirm("Really delete row " + (RowIndex + 1) + "?\r\n\r\nNOTE: This action will delete this row AND save any changes you've made to other rows"))
	{
		elem = document.getElementById("CronRow_" + RowIndex);
		elem.style.visiblity = "hidden";
		elem.style.display = "none";

		eval("document.SaveCronTab.Minute_" + RowIndex + ".value = \"\"");
		eval("document.SaveCronTab.Hour_" + RowIndex + ".value = \"\"");
		eval("document.SaveCronTab.Day_" + RowIndex + ".value = \"\"");
		eval("document.SaveCronTab.Month_" + RowIndex + ".value = \"\"");
		eval("document.SaveCronTab.Weekday_" + RowIndex + ".value = \"\"");
		eval("document.SaveCronTab.Command_" + RowIndex + ".value = \"\"");
		return true;
	}

	return false;
}

function AddNewCronEntry(Value)
{
		
	Minute = Value.substr(0, Value.indexOf(" "));
	Value = Value.substr(Value.indexOf(" ") + 1);
	document.SaveCronTab.Minute_new.value = Minute;

	Hour = Value.substr(0, Value.indexOf(" "));
	Value = Value.substr(Value.indexOf(" ") + 1);
	document.SaveCronTab.Hour_new.value = Hour;
	
	Day = Value.substr(0, Value.indexOf(" "));
	Value = Value.substr(Value.indexOf(" ") + 1);
	document.SaveCronTab.Day_new.value = Day;
	
	Month = Value.substr(0, Value.indexOf(" "));
	Value = Value.substr(Value.indexOf(" ") + 1);
	document.SaveCronTab.Month_new.value = Month;
	
	Weekday = Value;
	document.SaveCronTab.Weekday_new.value = Weekday;

		
}

function ValidateSingleForwarder()
{

	if(document.SingleForwarder.LocalPart.value == "")
	{
		alert("Please select a Local Part for the email address!");
		document.SingleForwarder.LocalPart.focus();
		return false;
	}


	if(document.SingleForwarder.ForwardTo.value == "")
	{
		alert("Please select a forwarding email address!");
		document.SingleForwarder.ForwardTo.focus();
		return false;
	}
	
	return true;
}


function ConfirmDelete()
{
	if(confirm("Are you sure you want to delete this forwarder?"))
	{
		return true;
	}
	return false;
}
</script>





<style type="text/css">

.InputClass
{
	border: solid 1px; black;
	height: 30px;
	width: 250px;
}

.CronHeader
{
	font-weight: bold;
	font-sie:12px;
	color: #000099;
}


.CronBody
{
	height:30px;
}

.CronBody input
{
	border: 1px solid grey;
	width: 40px;
	//height: 20px;
}

</style>	
		

	</head>
	<!-- end: HEAD -->
	<!-- start: BODY -->
	<body>
		<!-- start: HEADER -->
		<div class="navbar navbar-inverse navbar-fixed-top">
			<!-- start: TOP NAVIGATION CONTAINER -->
			<div class="container">
				<div class="navbar-header">
					<!-- start: RESPONSIVE MENU TOGGLER -->
					<button data-target=".navbar-collapse" data-toggle="collapse" class="navbar-toggle" type="button">
						<span class="clip-list-2"></span>
					</button>
					<!-- end: RESPONSIVE MENU TOGGLER -->
					<!-- start: LOGO -->
					<?php
					require($_SERVER["DOCUMENT_ROOT"]."/includes/PageParts/Logo.inc.php");
					?>					

					<!-- end: LOGO -->
				</div>
				<div class="navbar-tools">
					<!-- start: TOP NAVIGATION MENU -->
					<ul class="nav navbar-right">
						<!-- start: USER DROPDOWN -->
						<?php
						require($_SERVER["DOCUMENT_ROOT"]."/includes/PageParts/UserSection.inc.php");
						?>
					</ul>
					<!-- end: TOP NAVIGATION MENU -->
				</div>
			</div>
			<!-- end: TOP NAVIGATION CONTAINER -->
		</div>
		<!-- end: HEADER -->
		<!-- start: MAIN CONTAINER -->
		<div class="main-container">
			<div class="navbar-content">
				<!-- start: SIDEBAR -->
				<div class="main-navigation navbar-collapse collapse">
					<!-- start: MAIN MENU TOGGLER BUTTON -->
					<div class="navigation-toggler">
						<i class="clip-chevron-left"></i>
						<i class="clip-chevron-right"></i>
					</div>
					<!-- end: MAIN MENU TOGGLER BUTTON -->

					<?php
					require($_SERVER["DOCUMENT_ROOT"]."/includes/PageParts/SideNav.inc.php");
					?>
					
				</div>
				<!-- end: SIDEBAR -->
			</div>
			<!-- start: PAGE -->
			<div class="main-content">

				<div class="container">
					<!-- start: PAGE HEADER -->
					<div class="row">
						<div class="col-sm-12">
							<!-- start: PAGE TITLE & BREADCRUMB -->
							<ol class="breadcrumb">
								<li>
									<i class="active"></i>
									<a href="/cron/">
										Cron
									</a>
								</li>
					
							</ol>
							<div class="page-header">
								<h1>Cron Editor <small> Manage scheduled tasks</small></h1>
							</div>
							<!-- end: PAGE TITLE & BREADCRUMB -->
						</div>
					</div>
					<!-- end: PAGE HEADER -->
					<!-- start: PAGE CONTENT -->
					<div class="row">
					
			
					<?php
					if(isset($_REQUEST["Notes"]))
					{
						$NoteType = "Message";
						
						if(isset($_REQUEST["NoteType"]))
						{
							$NoteType = $_REQUEST["NoteType"];
						}
						
						if($NoteType == "Error")
						{
							print "<div class=\"alert alert-danger\">";
								print "<button data-dismiss=\"alert\" class=\"close\">";
									print "&times;";
								print "</button>";
								print "<i class=\"fa fa-times-circle\"></i>";
						
						}
						else
						{
							print "<div class=\"alert alert-success\">";
								print "<button data-dismiss=\"alert\" class=\"close\">";
									print "&times;";
								print "</button>";
								print "<i class=\"fa fa-check-circle\"></i>";
						}
					
							print $_REQUEST["Notes"];
						print "</div>";
					
					}
					?>
					



				<div class="col-md-12">
				<div class="panel panel-default">
				<div class="panel-body">

<h1>New Cron Entry</h1>


<?php

if(count($CronArray) < $MaxJobs)
{

	print "You've used ".count($CronArray)." of ".$MaxJobs." cron entries<p>";
?>

<form name="NewCronEntry">
<table border="0" cellpadding="0" cellspacing="0" width="90%">

<tr>
	<td width="15%">Quick Select</td>
	<td width="85%" class="CronBody">
	
	<select name="QuickSelect" id="QuickSelect" style="height:30px; border: 1px solid black; background-color:white;" onclick="AddNewCronEntry(this.value);">

	<option value="* * * * *">Every Minute (* * * * *)</option>
	<option value="*/2 * * * *">Every Other Minute (*/2 * * * *)</option>
	<option value="*/5 * * * *">Every 5 Minutes (*/5 * * * *)</option>
	<option value="0,30 * * * *">Twice an hour (0,30 * * * *)</option>
	<option value="0,5,36,55 * * * *">Certain minutes(0,5,36,55 * * * *)</option>

	<option value="0 * * * *">Every Hour (0 * * * *)</option>
	<option value="0 */6 * * *">Every 6 Hours (0 */6 * * *)</option>
	<option value="0 0 * * *">Midnight (0 0 * * *)</option>
	<option value="0 6 * * *">6 a.m (0 6 * * *)</option>
	<option value="0 8,17 * * *">8 a.m and 17 p.m (0 8,17 * * *)</option>
	
	<option value="0 0 * * 1">Every Monday (0 0 * * 1)</option>
	<option value="0 0 * * 2">Every Tuesday (0 0 * * 2)</option>
	<option value="0 0 * * 3">Every Wednesday (0 0 * * 3)</option>
	<option value="0 0 * * 4">Every Thursday (0 0 * * 4)</option>
	<option value="0 0 * * 5">Every Friday (0 0 * * 5)</option>
	<option value="0 0 * * 6">Every Saturday (0 0 * * 6)</option>
	<option value="0 0 * * 0">Every Sunday (0 0 * * 0)</option>
	<option value="0 0 * * 1,3">Every Monday and Wednesday (0 0 * * 1,3)</option>

	
	<option value="0 0 1 * *">Once a Month (0 0 1 * *)</option>
	<option value="0 0 28 * *">Month End (0 0 28 * *)</option>
	<option value="0 0 1,15 * *">Twice a Month (0 0 1,15 * *)</option>
	

	<option value="0 0 1 1 *">Once a Year (0 0 1 1 *)</option>
	<option value="0 0 1 1,6 *">Twice a Year (0 0 1 1,6 *)</option>
	<option value="5 17 26 9 *">September 26, 17H05 (5 17 26 9 *)</option>

	</select>
	</td>
</tr>

</table>
</form>

<?php
}
else
{
	print "<font color=\"red\">You've used ".count($CronArray)." of ".$MaxJobs." cron entries. You have no more available</font><p>";
}
?>





<form action="SaveCronTab.php" name="SaveCronTab" method="post">
	<?php 
	if(count($CronArray) < $MaxJobs)
	{
	?>
        <table border="0" cellpadding="0" cellspacing="0" width="90%">
	<tr>
		<td class="CronHeader">Minute</td>
		<td class="CronHeader">Hour</td>
		<td class="CronHeader">Day</td>
		<td class="CronHeader">Month</td>
		<td class="CronHeader">Weekday</td>
		<td class="CronHeader">Command</td>
		<td class="CronHeader">&nbsp;</td>
	</tr>
	
      	<tbody id="CronRow_new">

                <tr>
                <td class="CronBody"><input type="text" name="Minute_new"></td>
                <td class="CronBody"><input type="text" name="Hour_new"></td>
                <td class="CronBody"><input type="text" name="Day_new"></td>
                <td class="CronBody"><input type="text" name="Month_new"></td>
                <td class="CronBody"><input type="text" name="Weekday_new"></td>
                <td class="CronBody"><input type="text" style="width:400px;" name="Command_new"></td>
      	</tr>   
        </tbody>
	
        </table>
	<?php
	}
	?>



						
				</div>
				</div>
				</div>


				<div class="col-md-12">
				<div class="panel panel-default">
				<div class="panel-body">


<h1>Existing Cron Jobs</h1>

<table border="0" cellpadding="0" cellspacing="0" width=90%">
<tr>
	<td class="CronHeader">Minute</td>
	<td class="CronHeader">Hour</td>
	<td class="CronHeader">Day</td>
	<td class="CronHeader">Month</td>
	<td class="CronHeader">Weekday</td>
	<td class="CronHeader">Command</td>
	<td class="CronHeader">&nbsp;</td>
</tr>


<?php

print "<input type=\"hidden\" name=\"URL\" value=\"".$URL."\">";

if(trim($ResultString) != "")
{
	for($x = 0; $x < count($CronArray); $x++)
	{

		//print $x.") ".$CronArray[$x]."<br>";

		print "<tbody id=\"CronRow_".$x."\">";
 		print "<tr>";
	
		$Line = $CronArray[$x];
		$NextValue = substr($Line, 0, strpos($Line, " "));
		$Line = substr($Line, strpos($Line, " ") + 1);
		print "<td class=\"CronBody\"><input type=\"text\" name=\"Minute_".$x."\" value=\"".trim($NextValue)."\"></td>";

		$NextValue = substr($Line, 0, strpos($Line, " "));
		$Line = substr($Line, strpos($Line, " ") + 1);
		print "<td class=\"CronBody\"><input type=\"text\" name=\"Hour_".$x."\" value=\"".trim($NextValue)."\"></td>";

		$NextValue = substr($Line, 0, strpos($Line, " "));
		$Line = substr($Line, strpos($Line, " ") + 1);
		print "<td class=\"CronBody\"><input type=\"text\" name=\"Day_".$x."\" value=\"".trim($NextValue)."\"></td>";
	
		$NextValue = substr($Line, 0, strpos($Line, " "));
		$Line = substr($Line, strpos($Line, " ") + 1);
		print "<td class=\"CronBody\"><input type=\"text\" name=\"Month_".$x."\" value=\"".trim($NextValue)."\"></td>";

		$NextValue = substr($Line, 0, strpos($Line, " "));
		//print "Line a) ".$Line."<br>";
		$Line = substr($Line, strpos($Line, " ") + 1);
		print "<td class=\"CronBody\"><input type=\"text\" name=\"Weekday_".$x."\" value=\"".trim($NextValue)."\"></td>";
	
		//print "Line b) ".$Line."<br>";
		$NextValue = $Line;
		print "<td class=\"CronBody\"><input type=\"text\" style=\"width:400px;\" name=\"Command_".$x."\" value=\"".htmlentities(trim($NextValue))."\"></td>";
	

		print "<td><button type=\"submit\" class=\"btn btn-bricky btn-xs\" onclick=\" return ValidateDelete(".$x."); return false;\"> delete </button></td>";
		print "</tr>";  
		print "</tbody>";
	}
}


	


print "<tr><td colspan=\"7\" align=\"center\">&nbsp;</td></tr>";
print "<tr><td colspan=\"7\" align=\"center\">

<input type=\"submit\" value=\"Save Cron Entries\" data-style=\"zoom-in\" onclick=\"EscapeCommands();\" class=\"btn btn-info ladda-button\">
													<span class=\"ladda-spinner\"></span>
													<span class=\"ladda-progress\" style=\"width: 0px;\"></span>
												</input>";


print "</form>";
?>
</table>


				</div>
				</div>
				</div>

					</div>
					<!-- end: PAGE CONTENT-->
				</div>
			</div>
			<!-- end: PAGE -->
		</div>
		<!-- end: MAIN CONTAINER -->
		<!-- start: FOOTER -->
		<div class="footer clearfix">
			<?php
			require($_SERVER["DOCUMENT_ROOT"]."/includes/PageParts/footer.inc.php");
			?>
			<div class="footer-items">
				<span class="go-top"><i class="clip-chevron-up"></i></span>
			</div>
		</div>
		<!-- end: FOOTER -->
		<!-- start: MAIN JAVASCRIPTS -->
		<!--[if lt IE 9]>
		<script src="/assets/plugins/respond.min.js"></script>
		<script src="/assets/plugins/excanvas.min.js"></script>
		<![endif]-->
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
		<script src="/assets/plugins/jquery-ui/jquery-ui-1.10.2.custom.min.js"></script>
		<script src="/assets/plugins/bootstrap/js/bootstrap.min.js"></script>
		<script src="/assets/plugins/blockUI/jquery.blockUI.js"></script>
		<script src="/assets/plugins/iCheck/jquery.icheck.min.js"></script>
		<script src="/assets/plugins/perfect-scrollbar/src/jquery.mousewheel.js"></script>
		<script src="/assets/plugins/perfect-scrollbar/src/perfect-scrollbar.js"></script>
		<script src="/assets/plugins/less/less-1.5.0.min.js"></script>
		<script src="/assets/plugins/jquery-cookie/jquery.cookie.js"></script>
		<script src="/assets/plugins/bootstrap-colorpalette/js/bootstrap-colorpalette.js"></script>
		<script src="/assets/js/main.js"></script>
		<!-- end: MAIN JAVASCRIPTS -->
		<!-- start: JAVASCRIPTS REQUIRED FOR THIS PAGE ONLY -->
		<script type="text/javascript" src="/assets/plugins/select2/select2.min.js"></script>
		<script type="text/javascript" src="/assets/plugins/DataTables/media/js/jquery.dataTables.min.js"></script>
		<script type="text/javascript" src="/assets/plugins/DataTables/media/js/DT_bootstrap.js"></script>
		<script src="/assets/js/table-data.js"></script>
		<!-- end: JAVASCRIPTS REQUIRED FOR THIS PAGE ONLY -->
		<script>
			jQuery(document).ready(function() {
				Main.init();
				TableData.init();
			});
		</script>
	</body>
	<!-- end: BODY -->
</html>
