<?php
session_start();

include_once($_SERVER["DOCUMENT_ROOT"]."/vendor/autoload.php");

$oUser = new User();
$oPackage = new Package();
$oDomains = new Domain();
$oSettings = new Settings();
$oReseller = new Reseller();

require($_SERVER["DOCUMENT_ROOT"]."/includes/License.inc.php");

$ClientID = $oUser->getClientId();
if($ClientID < 1)
{
	header("Location: /index.php");
	exit();
}


if($oUser->Role != "admin")
{
	$FirewallControl = "";
	if($oUser->Role == "reseller")
       	{
        	$FirewallControl = $oReseller->GetResellerSetting($oUser->ClientID, "FirewallControl");
        }
      	if($FirewallControl != "on")
       	{
		header("Location: /index.php");
		exit();
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
		<title>Firewall Management | <?php print $oSettings->GetWebCPTitle(); ?></title>
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


                function MakeSettingsDivVisible(ID, Heading, IPAddress, Country, Date, Timeout, Message, Logs)
                {

			HostName = "";
			URI = "";
			ModsecID = "";

			if(Heading == "LF_MODSEC")
			{
				StartPos = Message.indexOf("(id:") + 4;
				EndPos = Message.indexOf(")", StartPos);

				ModsecID = Message.substr(StartPos, (EndPos - StartPos));	

				StartPos = Logs.indexOf("[hostname ") + 10;
				EndPos = Logs.indexOf("]", StartPos);
					
				HostName = Logs.substr(StartPos, (EndPos - StartPos));	
			
				StartPos = Logs.indexOf("[uri ") + 5;
				EndPos = Logs.indexOf("]", StartPos);
					
				URI = Logs.substr(StartPos, (EndPos - StartPos));	
			}

			Message = Message.replace(/CRLF/g, "<br>");
			Logs = Logs.replace(/CRLF/g, "<br>");

                        SettingsHeading = Heading + " block";
                        
			Settings = "<table width=\"90%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\">";
			Settings = Settings + "<tr><td width=\"20%\"><b>IP Address:</b></td><td width=\"*\">" + IPAddress + "</td></tr>";
			Settings = Settings + "<tr><td><b>Country:</b></td><td>" + Country + "</td></tr>";
			Settings = Settings + "<tr><td><b>Date: </b></td><td>" + Date + "</td></tr>";
			Settings = Settings + "<tr><td><b>Block Time:</b></td><td>" + Timeout + "</td></tr></table><p>&nbsp;<br>";

			Settings = Settings + "<b>Message</b><br>" + Message + "<p>";

			Settings = Settings + "<b>Log Lines</b><br>" + Logs;

			Settings = Settings + "<p>&nbsp;<br><table border=\"0\" width=\"95%\" cellspacing=\"0\" callpadding=\"0\">";
			Settings = Settings + "<tr>";
			Settings = Settings + "<td width=\"30%\"><button type=\"button\" onclick=\"window.location='RemoveBan.php?ID=" + ID + "&IP=" + IPAddress + "&Service=" + Heading + "';\" class=\"btn btn-danger\">Unblock this IP address</button></td>";
	
			Settings = Settings + "<td width=\"*\">&nbsp;</td><td width=\"30%\">";

			if(ModsecID == "")
			{
				Settings = Settings + "&nbsp;";
			}
			else
			{
				Settings = Settings + "<button type=\"button\" onclick=\"window.location='ModsecWhitelist.php?ID=" + ID + "&IP=" + IPAddress + "&Service=" + Heading + "&ModsecID=" + ModsecID + "&URI=" + URI + "&HostName=" + HostName + "';\" class=\"btn btn-info\">White list this error code (" + ModsecID + ")</button>";
			}

			Settings = Settings + "</td>";
			Settings = Settings + "</tr></table>";
			
			//for(x = 0; x < Logs.length; x++)
			//{
				//alert(Logs + "\r\n" + Logs[x]);
			//}

			//alert(Settings);
                        document.getElementById("ModalSettings").innerHTML = Settings;
                        document.getElementById("ModalSettingsHeading").innerHTML = SettingsHeading;
                }

                function ConfirmDelete()
                {
                        if(confirm("Are you sure you want to delete this email address?"))
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
									<a href="/fail2ban/">
										Firewall
									</a>
								</li>
					
							</ol>
							<div class="page-header">
								<h1>Firewall <small>Add / remove IP blocks</small></h1>
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
									
								<div class="panel-body">
									<table class="table table-bordered table-full-width table-hover table-striped" id="sample_1">
										<thead>
											<tr>
												<th>IP</th>
												<th class="hidden-xs">Service</th>
												<th class="hidden-xs">Time</th>
												<th class="hidden-xs">Info</th>
												<th>Country</th>
												<th>&nbsp;</th>	
											</tr>
										</thead>
										
										
										<tbody>

										<?php

										$oFirewall = new Firewall();
										$oFirewall->GetBanList($Array, $ArrayCount, $oUser->Role, $oUser->ClientID);


										for($x = 0; $x < $ArrayCount; $x++)
										{

											$seconds = $Array[$x]["TimeLeft"];
											$d = $seconds / 86400 % 7;
											$h = $seconds / 3600 % 24;
											$m = $seconds / 60 % 60;
											$s = $seconds % 60;

											$Timeout = "";

											if($d > 0)
											{
												$Timeout = $d." d, ";
											}

											if($h > 0)
											{
												$Timeout = $Timeout.$h." h, ";
											}

											if($m > 0)
											{
												$Timeout = $Timeout.$m." m";
											}

											if($Timeout == "")
											{
												$Timeout = $s." s";
											}

											print "<tr>";
											print "<td><a href=\"#full-width\" onclick=\"MakeSettingsDivVisible('".$Array[$x]["ID"]."', '".$Array[$x]["Service"]."', '".$Array[$x]["IP"]."', '".$Array[$x]["Country"]."', '".$Array[$x]["BanTime"]."', '".$Array[$x]["Timeout"]."', '".$Array[$x]["Message"]."', '".str_replace("\n", "CRLF", str_replace("\"", "", $Array[$x]["Logs"]))."');\" data-toggle=\"modal\">".$Array[$x]["IP"]."</a></td>\r\n";	
											print "<td class=\"hidden-xs\">".$Array[$x]["Service"]."</td>\r\n";	
											print "<td class=\"hidden-xs\">".$Timeout."</td>\r\n";
											print "<td class=\"hidden-xs\">".$Array[$x]["Reverse"]."</td>\r\n";
											print "<td>".$Array[$x]["Country"]."</td>\r\n";
												

													print "<td class=\"center\">";
													print "<div class=\"visible-md visible-lg hidden-sm hidden-xs\">";
														
													print "<a href=\"RemoveBan.php?ID=".$Array[$x]["ID"]."&IP=".$Array[$x]["IP"]."&Service=".$Array[$x]["Service"]."\" class=\"btn btn-bricky tooltips\" data-placement=\"top\" data-original-title=\"Unblock\"><i class=\"fa fa-times fa fa-white\" style=\"color:white;\"></i></a>\n";
													print "</div>";
													print "<div class=\"visible-xs visible-sm hidden-md hidden-lg\">";
														print "<div class=\"btn-group\">";
															print "<a class=\"btn btn-primary dropdown-toggle btn-sm\" data-toggle=\"dropdown\" href=\"#\">";
																print "<i class=\"fa fa-cog\"></i> <span class=\"caret\"></span>";
															print "</a>";
															print "<ul role=\"menu\" class=\"dropdown-menu pull-right\">";
															
															print "<li role=\"presentation\">";
															print "<a role=\"menuitem\" tabindex=\"-1\" href=\"RemoveBan.php?ID=".$Array[$x]["ID"]."&IP=".$Array[$x]["IP"]."&Service=".$Array[$x]["Service"]."\">";
															print "<i class=\"fa fa-times\"></i> Unblock";
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
							
									



                                                                                <div class="col-sm-4" style="align:center;">

                                                                                        <form name="ManualBan" action="ManualBan.php" method="post">
											<h3>Manually Ban an IP</h3>

											<input class="form-control" type="text" name="IP">


                                                                                        <p>&nbsp;<p>

                                                                                                <input type="submit" value="Ban IP Address" data-style="zoom-in" class="btn btn-info ladda-button">
                                                                                                        <span class="ladda-spinner"></span>
                                                                                                        <span class="ladda-progress" style="width: 0px;"></span>
                                                                                                </input>

                                                                                        </form>
                                                                                </div>
                                                                        </div>
	
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
