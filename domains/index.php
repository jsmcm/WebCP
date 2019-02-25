<?php
session_start();

include_once($_SERVER["DOCUMENT_ROOT"]."/vendor/autoload.php");

$oDomain = new Domain();
$oUtils = new Utils();
$oUser = new User();
$oPackage = new Package();
$oLog = new Log();
$oSettings = new Settings();
$oReseller = new Reseller();
$oDatabase = new Database();

require($_SERVER["DOCUMENT_ROOT"]."/includes/License.inc.php");

if($oDatabase->FieldExists("packages", "user_id", array("int")) == false)
{

	if($oDatabase->FieldExists("packages", "username", array("int")) == true)
	{
		$oDatabase->DoSQL("ALTER TABLE packages ADD user_id int AFTER username;");
		$oDatabase->DoSQL("ALTER TABLE packages DROP username;");
	}
}

if($oDatabase->TableExists("reseller_relationships") == false)
{
	$TableName = "reseller_relationships";

	$TableInfoArray[0]["name"] = "id";
	$TableInfoArray[0]["type"] = "int";
	$TableInfoArray[0]["key"] = "primary key auto_increment";
	$TableInfoArray[0]["default"] = "";

	$TableInfoArray[1]["name"] = "reseller_id";
	$TableInfoArray[1]["type"] = "int";
	$TableInfoArray[1]["key"] = "";
	$TableInfoArray[1]["default"] = "";

	$TableInfoArray[2]["name"] = "client_id";
	$TableInfoArray[2]["type"] = "int";
	$TableInfoArray[2]["key"] = "";
	$TableInfoArray[2]["default"] = "";

	$TableInfoArray[3]["name"] = "deleted";
	$TableInfoArray[3]["type"] = "int";
	$TableInfoArray[3]["key"] = "";
	$TableInfoArray[3]["default"] = "0";

	$oDatabase->CreateTableFromArray($TableName, $TableInfoArray);
}

if($oDatabase->TableExists("reseller_settings") == false)
{
	$TableName = "reseller_settings";

	$TableInfoArray[0]["name"] = "id";
	$TableInfoArray[0]["type"] = "int";
	$TableInfoArray[0]["key"] = "primary key auto_increment";
	$TableInfoArray[0]["default"] = "";

	$TableInfoArray[1]["name"] = "reseller_id";
	$TableInfoArray[1]["type"] = "int";
	$TableInfoArray[1]["key"] = "";
	$TableInfoArray[1]["default"] = "";

	$TableInfoArray[2]["name"] = "setting";
	$TableInfoArray[2]["type"] = "tinytext";
	$TableInfoArray[2]["key"] = "";
	$TableInfoArray[2]["default"] = "";

	$TableInfoArray[3]["name"] = "value";
	$TableInfoArray[3]["type"] = "tinytext";
	$TableInfoArray[3]["key"] = "";
	$TableInfoArray[3]["default"] = "";

	$TableInfoArray[4]["name"] = "extra1";
	$TableInfoArray[4]["type"] = "tinytext";
	$TableInfoArray[4]["key"] = "";
	$TableInfoArray[4]["default"] = "";

	$TableInfoArray[5]["name"] = "extra2";
	$TableInfoArray[5]["type"] = "tinytext";
	$TableInfoArray[5]["key"] = "";
	$TableInfoArray[5]["default"] = "";

	$TableInfoArray[6]["name"] = "deleted";
	$TableInfoArray[6]["type"] = "int";
	$TableInfoArray[6]["key"] = "";
	$TableInfoArray[6]["default"] = "0";

	$oDatabase->CreateTableFromArray($TableName, $TableInfoArray);
}

$oLog->WriteLog("DEBUG", "/domains/index.php...");

$ClientID = $oUser->getClientId();
if($ClientID < 1)
{
	$oLog->WriteLog("DEBUG", "/domains/index.php -> client_id not set, redirecting to /index.php");
	header("Location: /index.php");
	exit();
}
	
$oLog->WriteLog("DEBUG", "/domains/index.php -> client_id set, continuing");

$Accounts = 0;
if($oUser->Role == "admin")
{
	$TotalDiskSpace = $oPackage->GetTotalDiskSpace();
	$Usage = $oDomain->GetPackageDiskSpaceUsage();

	$Traffic = $oSettings->GetServerTrafficAllowance();
	$TrafficUsage = $oDomain->GetPackageTrafficUsage();
	
}
else
{
	$TotalDiskSpace = $oReseller->GetDiskSpaceAllocation($oUser->ClientID);
	$Usage = $oDomain->GetPackageDiskSpaceUsage($oUser->ClientID);
	$Accounts = $oReseller->GetAccountsLimit($oUser->ClientID);
	$AccountsCreated = $oReseller->GetAccountsCreatedCount($oUser->ClientID);


	$Traffic = $oReseller->GetTrafficAllocation($oUser->ClientID);
	$TrafficUsage = $oDomain->GetPackageDiskSpaceUsage($oUser->ClientID);

}

$AccountsPercent = 0;
if(($Accounts > 0) && ($AccountsCreated > 0) )
{
	$AccountsPercent = $AccountsCreated / $Accounts * 100;
}

$DiskSpaceUsageBuffer = $Usage;
$DiskSpaceBuffer = $TotalDiskSpace;

$DiskSpacePercent = 0;
if($TotalDiskSpace > 0)
{
	$DiskSpacePercent = $Usage / $TotalDiskSpace * 100;
}
$Scale = "b";								
$DisplayUsage = $oUtils->ConvertFromBytes($Usage, $Scale);
$Scale = "b";								
$DisplayTotalDiskSpace = $oUtils->ConvertFromBytes($TotalDiskSpace, $Scale);

$TrafficUsageBuffer = $TrafficUsage;
$TrafficBuffer = $Traffic;

$TrafficPercent = 0;
if($Traffic > 0)
{
	$TrafficPercent = $TrafficUsage / $Traffic * 100;
}
$Scale = "b";								
$DisplayTrafficUsage = $oUtils->ConvertFromBytes($TrafficUsage, $Scale);
$Scale = "b";								
$DisplayTraffic = $oUtils->ConvertFromBytes($Traffic, $Scale);

?>

<!DOCTYPE html>
<!--[if IE 8]><html class="ie8 no-js" lang="en"><![endif]-->
<!--[if IE 9]><html class="ie9 no-js" lang="en"><![endif]-->
<!--[if !IE]><!-->
<html lang="en" class="no-js">
	<!--<![endif]-->
	<!-- start: HEAD -->
	<head>
		<title>Domain Management | <?php print $oSettings->GetWebCPTitle(); ?></title>
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


                <link rel="stylesheet" href="/assets/plugins/x-editable/css/bootstrap-editable.css">





		<!-- end: CSS REQUIRED FOR THIS PAGE ONLY -->
		<link rel="shortcut icon" href="/favicon.ico" />
		
		
		<script language="javascript">
		function ConfirmDelete(DomainName)
		{
			if(confirm("Are you sure you want to delete " + DomainName + "?\r\nWARNING: This will delete all files, emails, FTP and database accounts and cannot be reversed"))
			{
				return true;
			}
			return false;
		}
		
		function ConfirmChange(ChangeToState, DomainName)
		{

			ChangeToMessage = "unsuspend";

			if(ChangeToState == 1)
			{
				ChangeToMessage = "suspend";
			}

			if(confirm("Are you sure you want to " + ChangeToMessage + " " + DomainName + "?\r\n"))
			{
				return true;
			}
			return false;
		}

		</script>
		
		
		<style>
		tr.ActiveRow td
		{
			padding: 8px;
			background: #e8edff;
			border-top: 1px solid #fff;
			color: #669;
		}

		tr.ActiveRow:hover td
		{
			background: #d0dafd;
		}
		
		tr.SuspendedRow td a
		{
			color: #9a2727;
		}
		
		tr.SuspendedRow td
		{
			padding: 8px;
			background: #fae9e7;
			border-top: 1px solid #fff;
			color: #9a2727;
		}

		tr.SuspendedRow:hover td
		{
			background: #f7cecb;
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
									<a href="/domains/">
										Domains
									</a>
								</li>
					
							</ol>
							<div class="page-header">
								<h1>Domains <small>Add / remove, suspend / unsuspend domains, parked and sub-domains</small></h1>
							</div>
							<!-- end: PAGE TITLE & BREADCRUMB -->
						</div>
					</div>
					<!-- end: PAGE HEADER -->
					<!-- start: PAGE CONTENT -->

					<?php
					if( ($oUser->Role == "admin") || ($oUser->Role == "reseller") )
					{
					?>
					<div class="row">
						<div class="col-md-12">
							<div class="panel panel-default">	
								<div class="panel-body">
						<div class="col-sm-12">

							<div class="row space12">
									<h2>Server allocation</h2>
								<div class="col-sm-<?php (($oUser->Role == "admin")? print "6": print "4"); ?>">
									<div class="easy-pie-chart">

										<div class="label-chart">
									
										<span class="bounce number" data-percent="<?php print $DiskSpacePercent; ?>"> <span class="percent"><?php print number_format($DiskSpacePercent, 0); ?></span> </span>
											Disk Space
											<br>
											<small><b><?php print $DisplayUsage; ?> of <?php print $DisplayTotalDiskSpace; ?></b></small>
										</div>
									</div>
								</div>

								<div class="col-sm-<?php (($oUser->Role == "admin")? print "6": print "4"); ?>">
									<div class="easy-pie-chart">

<?php
$CountryCode = "";
if(file_exists($_SERVER["DOCUMENT_ROOT"]."/includes/country.inc"))
{
        $CountryCode = file_get_contents($_SERVER["DOCUMENT_ROOT"]."/includes/country.inc");
}





if($Traffic == 0)
{
	$TrafficPercent = 0;

	if($oUser->Role == "admin")
	{
		print "<a href=\"/server/settings.php\"><font color=\"red\"><b>Click to set Traffic Allowance</b></font></a>";
	}
	else
	{
		print "<font color=\"red\"><b>Your account resources have not been setup, please contact the server administrator</b></font><p>";
	}
}
?>


										<span class="cpu number" data-percent="<?php print $TrafficPercent; ?>"> <span class="percent"><?php print number_format($TrafficPercent, 0); ?></span> </span>
										<div class="label-chart">
											Traffic	
											<br>
											<small><b><?php print $DisplayTrafficUsage; ?> of <?php print $DisplayTraffic; ?></b></small>
										</div>
									</div>
								</div>

								<?php
								if($oUser->Role == "reseller")
								{
								?>
								<div class="col-sm-4">
									<div class="easy-pie-chart">

										<div class="label-chart">
									
										<span class="bounce number" data-percent="<?php print $AccountsPercent; ?>"> <span class="percent"><?php print number_format($AccountsPercent, 0); ?></span> </span>
											Accounts Allowed
											<br>
											<small><b><?php print $AccountsCreated; ?> of <?php print $Accounts; ?></b></small>
										</div>
									</div>
								</div>
								<?php
								}
								?>
	
								</div>
							</div>
								</div>
							</div>
						</div>
					</div>
					<?php
					}
					?>

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
									<table class="table table-bordered table-full-width" id="sample_1">
										<thead>
											<tr>
												<th>Domain</th>
												<th class="hidden-xs">Sub</th>
												<th class="hidden-xs">Parked</th>

								
												<?php
												if(($oUser->Role == "admin") || ($oUser->Role == "reseller"))
												{
													print "<th class=\"hidden-xs\">Belongs to</th>";
												}
												?>

												<th>Package</th>
		<th>Redirect</th>											
												<?php
												if(($oUser->Role == "admin") || ($oUser->Role == "reseller"))
												{
													print "<th>&nbsp;</th>";
												}
												?>
												
											</tr>
										</thead>
										
										
										<tbody>

										<?php
										$oDomain = new Domain();

										$ClientID = $oUser->ClientID;

										if(isset($_REQUEST["ClientID"]))
										{
											if($oUser->Role == "admin")
											{
												//yes, permission..
												$ClientID = $_REQUEST["ClientID"];
											}
										}
									//print "ClientID: ".$ClientID."<p>";
									//print "Role: ".$oUser->Role."<p>";

										$oDomain->GetDomainList($Array, $ArrayCount, $ClientID, $oUser->Role);

										$DomainCount = 0;

										for($x = 0; $x < $ArrayCount; $x++)
										{
											if($Array[$x]["type"] == 'primary')
											{
											
												$domainSettings = $oDomain->getDomainSettings($Array[$x]["id"]);
	                                                                                        $domainRedirect = "none";
												if (isset($domainSettings["domain_redirect"]["value"])) {
	     												$domainRedirect = $domainSettings["domain_redirect"]["value"];
	                                                                                        }

												$ClientInfoArray = array();
												$oUser->GetUserInfoArray($Array[$x]["client_id"], $ClientInfoArray);
												$DomainCount++;
											
												if($Array[$x]["Suspended"] == 1)
												{
													print "<tr class=\"SuspendedRow\">";
												}
												else
												{
													print "<tr class=\"ActiveRow\">";
												}

												
												print "<td><a href=\"http://".$Array[$x]["domain_name"]."\" target=\"_BLANK\">".$Array[$x]["domain_name"]."</a></td>\r\n";
												print "<td class=\"hidden-xs\"><a href=\"ListSubDomains.php?DomainID=".$Array[$x]["id"]."\">[ ".$oDomain->GetSubDomainCount($Array[$x]["id"])." ]</a></td>\r\n";
												print "<td class=\"hidden-xs\"><a href=\"ListParkedDomains.php?DomainID=".$Array[$x]["id"]."\">[ ".$oDomain->GetParkedDomainCount($Array[$x]["id"])." ]</a></td>\r\n";
												if(($oUser->Role == "admin") || ($oUser->Role == "reseller"))
												{
													print "<td class=\"hidden-xs\"><a href=\"./EditUser.php?DomainID=".$Array[$x]["id"]."\">[ ".$ClientInfoArray["FirstName"]." ".$ClientInfoArray["Surname"]." ]</a></td>\r\n";
												}
										
												if(($oUser->Role == "admin") || ($oUser->Role == "reseller"))
												{
													print "<td><a href=\"./EditPackage.php?DomainID=".$Array[$x]["id"]."\">[ ".$oPackage->GetPackageName($Array[$x]["PackageID"])." ]</a></td>\r\n";
												}
												else
												{
													print "<td>".$oPackage->GetPackageName($Array[$x]["PackageID"])."</td>\r\n";
												}	

                print "<td><a href=\"#\" id=\"wwwredirect_".$Array[$x]["id"]."\" data-type=\"select\" data-pk=\"".$Array[$x]["id"]."\" data-value=\"".$domainRedirect."\" data-original-title=\"Select Redirect\"></a></td>\r\n";

												
												if(($oUser->Role == "admin") || ($oUser->Role == "reseller"))
												{

													print "<td class=\"center\">";
													print "<div class=\"visible-md visible-lg hidden-sm hidden-xs\">";
														
														if($Array[$x]["Suspended"] == 1)
														{
															print "<a href=\"ManageSuspension.php?ChangeTo=0&DomainID=".$Array[$x]["id"]."\" onclick=\"return ConfirmChange(0, '".$Array[$x]["domain_name"]."'); return false;\" class=\"btn btn-green tooltips\" data-placement=\"top\" data-original-title=\"Unsuspend Domain\"><i class=\"fa clip-spinner-4 fa fa-white\" style=\"color:white;\"></i></a>\n";
														}
														else
														{
															print "<a href=\"ManageSuspension.php?ChangeTo=1&DomainID=".$Array[$x]["id"]."\" onclick=\"return ConfirmChange(1, '".$Array[$x]["domain_name"]."'); return false;\" class=\"btn btn-green tooltips\" data-placement=\"top\" data-original-title=\"Suspend Domain\"><i class=\"fa fa-ban fa fa-white\" style=\"color:white;\"></i></a>\n";
														}
														
														print "<a href=\"DeleteDomain.php?DomainID=".$Array[$x]["id"]."\" onclick=\"return ConfirmDelete('".$Array[$x]["domain_name"]."'); return false;\" class=\"btn btn-bricky tooltips\" data-placement=\"top\" data-original-title=\"Delete Domain\"><i class=\"fa fa-times fa fa-white\" style=\"color:white;\"></i></a>\n";
													print "</div>";
													print "<div class=\"visible-xs visible-sm hidden-md hidden-lg\">";
														print "<div class=\"btn-group\">";
															print "<a class=\"btn btn-primary dropdown-toggle btn-sm\" data-toggle=\"dropdown\" href=\"#\">";
																print "<i class=\"fa fa-cog\"></i> <span class=\"caret\"></span>";
															print "</a>";
															print "<ul role=\"menu\" class=\"dropdown-menu pull-right\">";
															
																if($Array[$x]["Suspended"] == 1)
																{
																	print "<li role=\"presentation\">";
																		print "<a role=\"menuitem\" tabindex=\"-1\" href=\"ManageSuspension.php?ChangeTo=0&DomainID=".$Array[$x]["id"]."\" onclick=\"return ConfirmChange(0, '".$Array[$x]["domain_name"]."'); return false;\">";
																		print "<i class=\"fa clip-spinner-4\"></i> Unsuspend Domain";
																		print "</a>";
																	print "</li>";
																}
																else
																{
																	print "<li role=\"presentation\">";
																		print "<a role=\"menuitem\" tabindex=\"-1\" href=\"ManageSuspension.php?ChangeTo=1&DomainID=".$Array[$x]["id"]."\" onclick=\"return ConfirmChange(1, '".$Array[$x]["domain_name"]."'); return false;\">";
																		print "<i class=\"fa fa-ban\"></i> Suspend Domain";
																		print "</a>";
																	print "</li>";
																}
																
																print "<li role=\"presentation\">";
																	print "<a role=\"menuitem\" tabindex=\"-1\" href=\"DeleteDomain.php?DomainID=".$Array[$x]["id"]."\" onclick=\"return ConfirmDelete('".$Array[$x]["domain_name"]."'); return false;\">";
																		print "<i class=\"fa fa-times\"></i> Delete Domain";
																	print "</a>";
																print "</li>";																
															print "</ul>";
														print "</div>";
													print "</div></td>";				
												}
											
										
												print "</tr>";

											}
										}
										?>
	
									</tbody>
									
									</table>
							
									<?php
									
									if( ($oUser->Role == "admin") || ($oUser->Role == "reseller") )
									{
										$BlockReason = "";
										if($DiskSpaceUsageBuffer >= $DiskSpaceBuffer)
										{
											$BlockReason = $BlockReason."No more disk space<br>";
										}

										if($TrafficUsageBuffer >= $TrafficBuffer)
										{
											$BlockReason = $BlockReason."No more traffic<br>";
										}

										
										if( ($oUser->Role == "reseller") && ($AccountsCreated >= $Accounts) )
										{
											$BlockReason = $BlockReason."Max number of accounts created<br>";
										}
									
										if($BlockReason == "")
										{		
										?>
										<a class="btn btn-primary" href="AddDomain.php"><i class="fa fa-plus"></i>
										Add new Domain</a>
										<?php
										}
										else
										{
											print $BlockReason;
										}
									}
									?>
										
								</div>
							</div>

							<?php
							if($oUser->Role == "admin")
							{
							?>
							<b>
							<a href="http://api.webcp.pw/com.php" target="_new">Click here to order .com, .net. .org, etc domain names</a>
							<br>
							<a href="http://api.webcp.pw/coza.php" target="_new">Click here to order co.za domain names</a>
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






                <script src="/assets/plugins/bootstrap-modal/js/bootstrap-modal.js"></script>

                <script src="/assets/plugins/bootstrap-modal/js/bootstrap-modalmanager.js"></script>

                <script src="/assets/js/ui-modals.js"></script>

                <script src="/assets/plugins/jquery-mockjax/jquery.mockjax.js"></script>

                <script src="/assets/plugins/x-editable/js/bootstrap-editable.min.js"></script>

                <script src="/assets/plugins/x-editable/domain-redirect.js"></script>

                <script src="/assets/plugins/x-editable/demo.js"></script>


		<!-- end: JAVASCRIPTS REQUIRED FOR THIS PAGE ONLY -->
		<script>
			jQuery(document).ready(function() {
				Main.init();
				TableData.init();
				Index.init();
				UIModals();
			});
		</script>
	</body>
	<!-- end: BODY -->
</html>
