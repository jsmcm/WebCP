<?php
session_start();

include_once($_SERVER["DOCUMENT_ROOT"]."/vendor/autoload.php");
$oDNS = new DNS();
$oDatabase = new Database();
$oUtils = new Utils();
$oUser = new User();
$oLog = new Log();
$oSettings = new Settings();
$oReseller = new Reseller();
$oSimpleNonce = new SimpleNonce();

require($_SERVER["DOCUMENT_ROOT"]."/includes/License.inc.php");

$oDNS->GenerateKeyFiles();

$ClientID = $oUser->getClientId();

include dirname(__FILE__)."/dns_db.php";



if($ClientID < 1) {
	$oLog->WriteLog("DEBUG", "/dns/index.php -> client_id not set, redirecting to /index.php");
	header("Location: /index.php");
	exit();
}
	
$oLog->WriteLog("DEBUG", "/dns/index.php -> client_id set, continuing");

if($oUser->Role != "admin") {
	header("location: /dns/index.php?Notes=You don't have permission to be there&NoteType=Error");
	exit();
}

$ServerType = $oDNS->GetSetting("server_type");

if( $ServerType == "") {
	header("Location: settings.php?Notes=The DNS settings for this server are not set&NoteType=Error");
	exit();
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
		<title>DNS Management | <?php print $oSettings->GetWebCPTitle(); ?></title>
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
		function ConfirmDelete(DNSName)
		{
			if(confirm("Are you sure you want to delete " + DNSName + "?\r\nWARNING: This will stop the website / emails working if this is the authoritive name server for the domain"))
			{
				return true;
			}
			return false;
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
									<a href="/dns/">
										DNS
									</a>
								</li>
					
							</ol>
							<div class="page-header">
								<h1>DNS <small>Add / edit DNS</small></h1>
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
						
						if(strtolower($NoteType) == "error")
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
									
								<div class="panel-body">

									<?php
									if($ServerType == "slave")
									{
										print "<b>This server is a DNS slave. You can add / edit zones on the master server</b><p>";
									}
									?>
									<table class="table table-bordered table-full-width" id="sample_1">
										<thead>
											<tr>
												<th>DNS</th>
												<th>&nbsp;</th>
											</tr>
										</thead>
										
										
										<tbody>

										<?php
										$Array = array();
										$ArrayCount = 0;
										$oDNS->GetSOAList($Array, $ArrayCount);

										for($x = 0; $x < $ArrayCount; $x++)
										{
											print "<tr>";
												$Domain = $oDNS->RemoveLastPeriod($Array[$x]["Domain"]);
												print "<td><a href=\"http://".$Domain."\" target=\"_BLANK\">".$Domain."</a></td>\r\n";
												if(($oUser->Role == "admin") || ($oUser->Role == "reseller"))
												{

													print "<td class=\"center\">";
													
													if($ServerType == "master")
													{
													print "<div class=\"visible-md visible-lg hidden-sm hidden-xs\">";
														
														print "<a href=\"EditZone.php?ID=".$Array[$x]["ID"]."\" class=\"btn btn-green tooltips\" data-placement=\"top\" data-original-title=\"Edit Zone\"><i class=\"fa fa-edit fa fa-white\" style=\"color:white;\"></i></a>\n";
														print "<a href=\"DeleteZone.php?ZoneName=".$Domain."\" onclick=\"return ConfirmDelete('".$Domain."'); return false;\" class=\"btn btn-bricky tooltips\" data-placement=\"top\" data-original-title=\"Delete Zone\"><i class=\"fa fa-times fa fa-white\" style=\"color:white;\"></i></a>\n";
													print "</div>";
													print "<div class=\"visible-xs visible-sm hidden-md hidden-lg\">";
														print "<div class=\"btn-group\">";
															print "<a class=\"btn btn-primary dropdown-toggle btn-sm\" data-toggle=\"dropdown\" href=\"#\">";
																print "<i class=\"fa fa-cog\"></i> <span class=\"caret\"></span>";
															print "</a>";
															print "<ul role=\"menu\" class=\"dropdown-menu pull-right\">";
															
																print "<li role=\"presentation\">";
																	print "<a role=\"menuitem\" tabindex=\"-1\" href=\"EditZone.php?ID=".$Array[$x]["ID"]."\">";
																	print "<i class=\"fa fa-ban\"></i> Edit Zone";
																	print "</a>";
																print "</li>";
																
																print "<li role=\"presentation\">";
																	print "<a role=\"menuitem\" tabindex=\"-1\" href=\"DeleteZone.php?ZoneName=".$Domain."\" onclick=\"return ConfirmDelete('".$Domain."'); return false;\">";
																		print "<i class=\"fa fa-times\"></i> Delete Zone";
																	print "</a>";
																print "</li>";																
															print "</ul>";
														print "</div>";
													print "</div>";

													}
												
													print "</td>";				
												}
											
										
												print "</tr>";
										}
										?>
	
									</tbody>
									
									</table>
						
									<?php
									if($ServerType == "master")
									{	
									?>
										<a class="btn btn-primary" href="AddZone.php"><i class="fa fa-plus"></i>
										Add new Zone</a>
									<?php
									}
									?>
								</div>
							</div>

							<?php
							if ( $ServerType == "master" ) {
							?>
							[ <a href="RecreateAllZones.php">Recreate all zones</a> ]
							<p>
							<?php
							}
							?>

							<?php
							if($oUser->Role == "admin")
							{
							?>
							<b>
							<a href="https://api.webcp.io/com.php" target="_new">Click here to order .com, .net. .org, etc domain names</a>
							<br>
							<a href="https://api.webcp.io/coza.php" target="_new">Click here to order co.za domain names</a>
							</b>
							<?php
							}
							?>

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

		<script src="/assets/plugins/flot/jquery.flot.js"></script>
		<script src="/assets/plugins/jquery.sparkline/jquery.sparkline.js"></script>
		<script src="/assets/plugins/jquery-easy-pie-chart/jquery.easy-pie-chart.js"></script>
		<script src="/assets/js/index.js"></script>



		<!-- end: JAVASCRIPTS REQUIRED FOR THIS PAGE ONLY -->
		<script>
			jQuery(document).ready(function() {
				Main.init();
				TableData.init();
				Index.init();
			});
		</script>
	</body>
	<!-- end: BODY -->
</html>
