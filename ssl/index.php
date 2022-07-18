<?php
session_start();

include_once($_SERVER["DOCUMENT_ROOT"]."/vendor/autoload.php");
$oUser = new User();
$oDomain = new Domain();
$oSettings = new Settings();


if($oUser->Role != "admin")
{
	header("Location: /index.php");
	exit();
}

$ClientID = $oUser->getClientId();
if($ClientID < 1)
{
	header("Location: /index.php");
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
		<title>SSL Management | <?php print $oSettings->GetWebCPTitle(); ?></title>
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


		<link href="/assets/plugins/bootstrap-modal/css/bootstrap-modal-bs3patch.css" rel="stylesheet" type="text/css"/>

		<link href="/assets/plugins/bootstrap-modal/css/bootstrap-modal.css" rel="stylesheet" type="text/css"/>

		<!-- end: CSS REQUIRED FOR THIS PAGE ONLY -->
		<link rel="shortcut icon" href="/favicon.ico" />
		
		<script language="javascript">		
		function ConfirmDelete(DomainName)
		{
			if(confirm("Really delete SSL for " + DomainName + "?\nThis will permanently delete the domain's certificate")) {
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
									<a href="/ssl/">
										SSL
									</a>
								</li>
					
							</ol>
							<div class="page-header">
								<h1>SSL <small>Add / remove SSL</small></h1>
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
						
						if(strtolower($NoteType) == "error") {
							print "<div class=\"alert alert-danger\">";
								print "<button data-dismiss=\"alert\" class=\"close\">";
									print "&times;";
								print "</button>";
								print "<i class=\"fa fa-times-circle\"></i>";
						
						} else {
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
									<table class="table table-bordered table-full-width table-hover table-striped" id="sample_1">
										<thead>
											<tr>
												<th>Domain</th>
												<th>CSR</th>
												<th>CRT</th>
												<th>&nbsp;</th>
												
											</tr>
										</thead>
										
										
										<tbody>

										<?php
										$SSLTempArray = array();
										$SSLJson = `/usr/webcp/utils/get_ssl_list.sh`;
                								if($SSLJson != "")
							                        {
								                        $SSLTempArray = json_decode($SSLJson, true);
								                }


										$CRTCount = count($SSLTempArray["crt"]);
										foreach($SSLTempArray["csr"] as $CSR)
										{	
											$SSLArray[$CSR]["csr"] = 1;
											$SSLArray[$CSR]["crt"] = 0;

											for($x = 0; $x < $CRTCount; $x++)
											if($SSLTempArray["crt"][$x] == $CSR)
											{
												$SSLArray[$CSR]["crt"] = 1;
												$SSLTempArray["crt"][$x] = "";
												break;
											}
										}
										
										foreach($SSLTempArray["crt"] as $CRT)
										{	
											if($CRT != "")
											{
												$SSLArray[$CRT]["csr"] = 0;
												$SSLArray[$CRT]["crt"] = 1;
											}
										}
				
										$KeyArray = array();
										
										//print "<p>";
										//print_r($SSLArray);
										//print "<p>";
										if(isset($SSLArray))
										{
											$KeyArray = array_keys($SSLArray);
											//print "<p>".print_r($KeyArray, true)."<p>";
										}

										foreach($KeyArray as $Domain)
										{
											$DomainID = $oDomain->DomainExists($Domain);

											if($DomainID < 1)
											{				
											//	continue;
											}

											print "<tr>";
											print "<td>".$Domain."</td>\r\n";	
											print "<td>";
												if($SSLArray[$Domain]["csr"] == 1)
												{
													print "<span class=\"badge badge-success tooltips\" data-original-title=\"This domain has a CSR (certificate sigining request)\"> <i class=\"fa fa-check-circle\"></i></span>";
												}
												else
												{
													print "<span class=\"badge badge-danger tooltips\" data-original-title=\"This domain does not have a CSR (certificate sigining request). This is probably an error and needs to be resolved\"> <i class=\"fa fa-times-circle\"></i></span>";
												}
											print "</td>\r\n";	
											
											print "<td>";
												if($SSLArray[$Domain]["crt"] == 1)
												{
													print "<span class=\"badge badge-success tooltips\" data-original-title=\"This domain has a certificate\"> <i class=\"fa fa-check-circle\"></i></span>";
												}
												else
												{
													print "<span class=\"badge badge-warning tooltips\" data-original-title=\"There is not a certificate installed for this domain. If you have one, click on the green edit button to add it\"> <i class=\"fa fa-times-circle\"></i></span>";
												}
											print "</td>\r\n";	

												

													print "<td class=\"center\">";
													print "<div class=\"visible-md visible-lg hidden-sm hidden-xs\">";
														

													print "<a href=\"CRT.php?DomainID=".$DomainID."&Domain=".$Domain."\" class=\"btn btn-green tooltips\" data-placement=\"top\" data-original-title=\"Configure SSL for ".$Domain."\"><i class=\"fa fa-edit fa fa-white\" style=\"color:white;\"></i></a>\n";
														
													print "<a href=\"DeleteSSL.php?DomainID=".$DomainID."&Domain=".$Domain."\" onclick=\"return ConfirmDelete('".$Domain."'); return false;\" class=\"btn btn-bricky tooltips\" data-placement=\"top\" data-original-title=\"Delete SSL for ".$Domain."\"><i class=\"fa fa-times fa fa-white\" style=\"color:white;\"></i></a>\n";
													print "</div>";
													print "<div class=\"visible-xs visible-sm hidden-md hidden-lg\">";
														print "<div class=\"btn-group\">";
															print "<a class=\"btn btn-primary dropdown-toggle btn-sm\" data-toggle=\"dropdown\" href=\"#\">";
																print "<i class=\"fa fa-cog\"></i> <span class=\"caret\"></span>";
															print "</a>";
															print "<ul role=\"menu\" class=\"dropdown-menu pull-right\">";
															
															print "<li role=\"presentation\">";
															print "<a role=\"menuitem\" tabindex=\"-1\" href=\"CRT.php?DomainID=".$DomainID."&Domain=".$Domain."\">";
															print "<i class=\"fa fa-edit\"></i> Configure SSL";
															print "</a>";
															print "</li>";
															
															print "<li role=\"presentation\">";
															print "<a role=\"menuitem\" tabindex=\"-1\" href=\"DeleteSSL.php?DomainID=".$DomainID."&Domain=".$Domain."\" onclick=\"return ConfirmDelete('".$Domain."'); return false;\">";
															print "<i class=\"fa fa-times\"></i> Delete SSL";
															print "</a>";
															print "</li>";																
															print "</ul>";
														print "</div>";
													print "</div></td>";				
											
										
												print "</tr>";

										}
										?>
	
									</tbody>
									
									</table>
							
									<a class="btn btn-primary" href="AddSSL.php"><i class="fa fa-plus"></i>
									Add new SSL</a>
										
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

		<div id="full-width" class="modal container fade" tabindex="-1" style="display: none;">

			<div class="modal-header">

				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">

					&times;

				</button>

				<h4 class="modal-title"><span id="ModalSettingsHeading"></span></h4>

			</div>

			<div class="modal-body">
				<span id="ModalSettings"></span>
				</p>

			</div>

			<div class="modal-footer">

				<button type="button" data-dismiss="modal" class="btn btn-default">

					Close

				</button>


			</div>

		</div>

<div id="ajax-modal" class="modal fade" tabindex="-1" style="display: none;"></div>	



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
		<script src="/assets/plugins/bootstrap-modal/js/bootstrap-modal.js"></script>

		<script src="/assets/plugins/bootstrap-modal/js/bootstrap-modalmanager.js"></script>

		<script src="/assets/js/ui-modals.js"></script>



		<script>
			jQuery(document).ready(function() {
				Main.init();
				TableData.init();
				UIModals.init();
			});
		</script>
	</body>
	<!-- end: BODY -->
</html>
