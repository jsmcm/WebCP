<?php
session_start();

include_once($_SERVER["DOCUMENT_ROOT"]."/vendor/autoload.php");
$oEmail = new Email();
$oUser = new User();
$oSettings = new Settings();


$ClientID = $oUser->GetClientID();
$loggedInId = $ClientID;

$email_ClientID = $oEmail->getLoggedInEmailId();

if($ClientID < 1) {
	if( $email_ClientID < 1 ) {
		header("Location: /index.php");
		exit();
	}
	$loggedInId = $email_ClientID;
}




$SearchFor = "both";
if(isset($_POST["EmailTraceRadio"]))
{
	$SearchFor = $_POST["EmailTraceRadio"];
}

if(isset($_REQUEST["SearchFor"]))
{
	$SearchFor = $_REQUEST["SearchFor"];
}


$SearchTerm = "";
if(isset($_POST["SearchTerm"]))
{
	$SearchTerm = $_POST["SearchTerm"];
}


if(isset($_REQUEST["SearchTerm"]))
{
	$SearchTerm = $_REQUEST["SearchTerm"];
}


$Offset = 0;
if(isset($_REQUEST["Offset"]))
{
	$Offset = $_REQUEST["Offset"];
}

if(! is_numeric($Offset))
{
	$Offset = 0;
}

if($Offset < 0)
{
	$Offset = 0;

}


$QuerySize = 50; 
if(isset($_REQUEST["QuerySize"]))
{
	if(is_numeric($_REQUEST["QuerySize"]))
	{
		$QuerySize = $_REQUEST["QuerySize"];
	}
}


$SortBy = 1;
if(isset($_REQUEST["SortBy"]))
{
	if(is_numeric($_REQUEST["SortBy"]))
	{
		$SortBy = $_REQUEST["SortBy"];
	}

	if( ($SortBy < 1) || ($SortBy > 6) )
	{
		$SortBy = 0;
	}
}
	
$SortOrder = "DESC";
if(isset($_REQUEST["SortOrder"]))
{
	$SortOrder = strtoupper($_REQUEST["SortOrder"]);

	if( ($SortOrder != "DESC") && ($SortOrder != "ASC") )
	{
		$SortOrder = "DESC";
	}
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
		<title>Email Trace | <?php print $oSettings->GetWebCPTitle(); ?></title>
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

		<!-- end: CSS REQUIRED FOR THIS PAGE ONLY -->
		<link rel="shortcut icon" href="/favicon.ico" />


		<script language="javascript">

		function closewin()
		{
			if(typeof myRef != "undefined")
			{
				myRef.close();
			}
		}

		function focuswin()
		{
			if(typeof myRef != "undefined")
			{
				myRef.focus();
			}
		}

		function FillModal(id)
		{

			if(typeof myRef != "undefined")
			{
				myRef.close();
			}
			
			myRef = window.open("GetEmailTraceInfo.php?EmailTraceID=" + id,'mywin', 'width=750,height=600,toolbar=1,resizable=0');
			myRef.moveTo(200,200);
			myRef.focus();
		}
		</script>

		
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
									<a href="/emails/">
										Emails
									</a>
								</li>
								<li>
									<i class="active"></i>
									<a href="/emails/EmailTrace.php">
										Email Trace
									</a>
								</li>
					
					
					
							</ol>
							<div class="page-header">
								<h1>Email Trace <small>detail about email delivery</small></h1>
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
							<!-- start: DYNAMIC TABLE PANEL -->
							<div class="panel panel-default">


<?php
	$oEmail = new Email();
										$ClientID = $oUser->ClientID;

										if(isset($_REQUEST["ClientID"]))
										{		
											if($oUser->Role == "admin")
											{
												//yes, permission..
												$ClientID = $_REQUEST["ClientID"];
											}
										}
	$oEmail->GetEmailTraceList($Array, $ArrayCount, $SearchFor, $SearchTerm, $oUser->GetUserName($ClientID), $oUser->Role, $Offset, $QuerySize, $TotalResultCount, $SortBy, $SortOrder);

?>
								<div class="panel-body">

									<?php
									$ToExtent =  ( $QuerySize * (($Offset / $QuerySize) + 1) );

									if($ToExtent > $TotalResultCount)
									{	
										$ToExtent = $TotalResultCount;
									}

									?>

									<b><?php print  ($Offset + 1); ?> to <?php print $ToExtent; ?> of <?php print $TotalResultCount;?></b><br>

<form name="QuerySize" action="DoEmailTrace.php" method="get">
<input type="hidden" name="SortBy" value="<?php print $SortBy; ?>">
<input type="hidden" name="SortOrder" value="<?php print $SortOrder; ?>">
<input type="hidden" name="Offset" value="0">
<input type="hidden" name="SearchFor" value="<?php print $SearchFor; ?>">
<input type="hidden" name="SearchTerm" value="<?php print $SearchTerm;?>">
Show <select name="QuerySize" onchange="document.QuerySize.submit();">
<option value="20" <?php if($QuerySize == 20) print " selected "; ?>>20</option>
<option value="50" <?php if($QuerySize == 50) print " selected "; ?>>50</option>
<option value="100" <?php if($QuerySize == 100) print " selected "; ?>>100</option>
<option value="250" <?php if($QuerySize == 250) print " selected "; ?>>250</option>
<option value="500" <?php if($QuerySize == 500) print " selected "; ?>>500</option>
<option value="1000" <?php if($QuerySize == 1000) print " selected "; ?>>1000</option>
</select> records:<p>							
</form>
										<table class="table table-bordered table-full-width table-hover table-striped" id="sample_1">

										<thead>
											<tr>
												<th><a href="DoEmailTrace.php?Offset=<?php print $Offset; ?>&SearchFor=<?php print $SearchFor; ?>&SearchTerm=<?php print $SearchTerm; ?>&QuerySize=<?php print $QuerySize; ?>&SortBy=1&SortOrder=<?php if($SortBy == 1){ if(strtoupper($SortOrder) == "ASC") print "DESC"; else print "ASC"; }else {print $SortOrder; } ?>">Date</a></th>
												<th><a href="DoEmailTrace.php?Offset=<?php print $Offset; ?>&SearchFor=<?php print $SearchFor; ?>&SearchTerm=<?php print $SearchTerm; ?>&QuerySize=<?php print $QuerySize; ?>&SortBy=2&SortOrder=<?php if($SortBy == 2){ if(strtoupper($SortOrder) == "ASC") print "DESC"; else print "ASC"; }else {print $SortOrder; } ?>">From Address</a></th>
												<th><a href="DoEmailTrace.php?Offset=<?php print $Offset; ?>&SearchFor=<?php print $SearchFor; ?>&SearchTerm=<?php print $SearchTerm; ?>&QuerySize=<?php print $QuerySize; ?>&SortBy=3&SortOrder=<?php if($SortBy == 3){ if(strtoupper($SortOrder) == "ASC") print "DESC"; else print "ASC"; }else {print $SortOrder; } ?>">To Address</a></th>
												<th class="hidden-xs"><a href="DoEmailTrace.php?Offset=<?php print $Offset; ?>&SearchFor=<?php print $SearchFor; ?>&SearchTerm=<?php print $SearchTerm; ?>&QuerySize=<?php print $QuerySize; ?>&SortBy=4&SortOrder=<?php if($SortBy == 4){ if(strtoupper($SortOrder) == "ASC") print "DESC"; else print "ASC"; }else {print $SortOrder; } ?>">Subject</a></th>
												<th class="hidden-xs"><a href="DoEmailTrace.php?Offset=<?php print $Offset; ?>&SearchFor=<?php print $SearchFor; ?>&SearchTerm=<?php print $SearchTerm; ?>&QuerySize=<?php print $QuerySize; ?>&SortBy=5&SortOrder=<?php if($SortBy == 5){ if(strtoupper($SortOrder) == "ASC") print "DESC"; else print "ASC"; }else {print $SortOrder; } ?>">Confirmation</a></th>
												<th><a href="DoEmailTrace.php?Offset=<?php print $Offset; ?>&SearchFor=<?php print $SearchFor; ?>&SearchTerm=<?php print $SearchTerm; ?>&QuerySize=<?php print $QuerySize; ?>&SortBy=6&SortOrder=<?php if($SortBy == 6){ if(strtoupper($SortOrder) == "ASC") print "DESC"; else print "ASC"; }else {print $SortOrder; } ?>">Status</a></th>

												
											</tr>
										</thead>
										
										
										<tbody>
										<?php



										for($x = 0; $x < $ArrayCount; $x++)
										{
											print "<tr>";
											//print "<td>".$Array[$x]["StartDate"]."</td>\r\n";	
											print "<td class=\"ajax\"><a onclick=\"FillModal(".$Array[$x]["ID"].");\">".$Array[$x]["StartDate"]."</a></td>\r\n";	
											print "<td>".$Array[$x]["FromAddress"]."</td>\r\n";	
											print "<td>".$Array[$x]["ToAddress"]."</td>\r\n";
											print "<td class=\"hidden-xs\">".$Array[$x]["Subject"]."</td>\r\n";


$Confirmation = $Array[$x]["Confirmation"];	

if($Array[$x]["Status"] == "SPAM")
{
	$Link = substr($Confirmation, strpos($Confirmation, "http:"));
	$Confirmation = substr($Confirmation, 0, strpos($Confirmation, "http:") - 1);
	$Confirmation = "<a href=\"".$Link."\" target=\"_new\">".$Link."</a>";
}
else if($Array[$x]["Status"] == "spam assassin")
{
	$Confirmation = "<a href=\"spamassassin/AddSpamAssassin.php?EmailAddress=".$Array[$x]["ToAddress"]."\" target=\"_new\">".$Array[$x]["Confirmation"]."</a>";
}

											$Button = "btn-green";
											if($Array[$x]["Status"] == "SPAM" || $Array[$x]["Status"] == "spam assassin")
											{
												$Button = "btn-bricky";
											}
											else if( ($Array[$x]["Confirmation"] == "Discarded (black list?)") )
											{
												$Button = "btn-bricky";
											}
											else if( (substr($Array[$x]["Confirmation"], 0, 1) == "5") )
											{
												$Button = "btn-bricky";
												$Array[$x]["Status"] = "Error";
											}
											else if($Array[$x]["Status"] == "relay not permitted")
											{
												$Button = "btn-bricky";
												$Confirmation = "Auth failed";
											}
											else if($Array[$x]["Status"] == "Potential Virus")
											{
												$Button = "btn-bricky";
											}
											else if( (substr($Array[$x]["Confirmation"], 0, 1) == "4") || ($Array[$x]["Status"] == "Disk quota exceeded: mailbox is full") )
											{
												$Button = "btn-yellow";
											}
											else if($Array[$x]["Status"] == "started")
											{
												$Button = "btn-blue";
											}
											else if($Array[$x]["Status"] == "in progress")
											{
												$Button = "btn-info";
											}
											
											print "<td class=\"hidden-xs\">".$Confirmation."</a></td>\r\n";

											print "<td class=\"center ajax\"><button type=\"button\" class=\"btn ".$Button." btn-sm\" onclick=\"FillModal(".$Array[$x]["ID"].");\">".$Array[$x]["Status"]."</button></td>";	
											
										
												print "</tr>";

										}
										?>
	

									<tr>
									<td colspan="3" width="50%">

									<?php
									if($Offset > 0)
									{
									?>
									 <button class="btn btn-purple" onclick="window.location='DoEmailTrace.php?Offset=<?php print ($Offset - $QuerySize); ?>&SearchFor=<?php print $SearchFor; ?>&SearchTerm=<?php print $SearchTerm; ?>&QuerySize=<?php print $QuerySize; ?>&SortBy=<?php print $SortBy; ?>&SortOrder=<?php print $SortOrder; ?>';"><i class="fa fa-arrow-circle-left"></i>i Previous</button>
									<?php
									}
									else
									{
										print "&nbsp;";
									}
									?>

									</td>
									<td colspan="3" align="right" width="50%">

									<?php
									if($ToExtent < $TotalResultCount)
									{
									?>
									 <button class="btn btn-purple" onclick="window.location='DoEmailTrace.php?Offset=<?php print ($Offset + $QuerySize); ?>&SearchFor=<?php print $SearchFor; ?>&SearchTerm=<?php print $SearchTerm; ?>&QuerySize=<?php print $QuerySize; ?>&SortBy=<?php print $SortBy; ?>&SortOrder=<?php print $SortOrder; ?>';">Next <i class="fa fa-arrow-circle-right"></i></button>
									<?php
									}
									else
									{
										print "&nbsp;";
									}
									?>

									</td>
									</tr>

									</tbody>
									
									</table>
							
									
								</div>
							</div>
							<!-- end: DYNAMIC TABLE PANEL -->
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

<div id="ajax-modal" class="modal fade" data-width="760" tabindex="-1" style="display: none;"></div>



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
		<!-- end: JAVASCRIPTS REQUIRED FOR THIS PAGE ONLY -->
		<script src="/assets/plugins/bootstrap-modal/js/bootstrap-modal.js"></script>

		<script src="/assets/plugins/bootstrap-modal/js/bootstrap-modalmanager.js"></script>

		<script src="/assets/js/ui-modals.js"></script>



		<script>
			jQuery(document).ready(function() {
				Main.init();
			});
		</script>
	</body>
	<!-- end: BODY -->
</html>
