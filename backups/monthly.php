<?php
session_start();

include_once($_SERVER["DOCUMENT_ROOT"]."/vendor/autoload.php");

$oUser = new User();
$oDomain = new Domain();
$oUtils = new Utils();
$oSettings = new Settings();

$LicenseKey = $oSettings->GetLicenseKey();
$Activation = file_get_contents($_SERVER["DOCUMENT_ROOT"]."/includes/activation.dat");

if( md5($LicenseKey.$_SERVER["SERVER_ADDR"].date("Y-m-t 23:59:59")) != $Activation)
{
        header("location: /index.php?Notes=License expired or invalid, please contact support");
        exit();
}

if( ! file_exists("./weekly"))
{
        mkdir("./weekly", 0755);
}
if( ! file_exists("./daily"))
{
        mkdir("./daily", 0755);
}
if( ! file_exists("./adhoc"))
{
        mkdir("./adhoc", 0755);
}
if( ! file_exists("./tmp"))
{
        mkdir("./tmp", 0755);
}


$ClientID = $oUser->getClientId();
if($ClientID < 1)
{
	$oLog->WriteLog("DEBUG", "/backups/index.php -> ClientID not set, redirecting to /index.php");
	header("Location: /index.php");
	exit();
}

$Role = $oUser->Role;
	
if($Role != "admin")
{
	header("location: index.php?Notes=No permission");
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
		<title>Weekly Backups | <?php print $oSettings->GetWebCPTitle(); ?></title>
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
		
		function ValidateDelete(FileName)
		{
			if(confirm("Really delete backup file: " + FileName + "?"))
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
									<a href="/backups/monthly.php">
										backups - monthly
									</a>
								</li>
					
							</ol>
							<div class="page-header">
								<h1>Backups <small>monthly backups</small></h1>
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
									<table class="table table-striped table-hover table-bordered table-full-width" id="sample_1">
										<thead>
											<tr>
												<th>File</th>
												<th class="hidden-xs">Domain</th>
												<th>Date</th>
												<th class="hidden-xs">Size</th>
												<th>&nbsp;</th>
											</tr>
										</thead>
										
										
										<tbody>

	<?php
	
	if ($handle = opendir($_SERVER["DOCUMENT_ROOT"]."/backups/monthly/"))
	{

		/* This is the correct way to loop over the directory. */
		while (false !== ($file = readdir($handle)))
		{
			if($file != "." && $file != "..")
			{

				if($oUser->Role == "admin") 
				{
					print "<tr>";

					print "<td>".$file."</td>";	
					print "<td>".$oDomain->GetDomainName(substr($file, 0, strpos($file, ".")))."</td>";	

                                        

					print "<td>".date ("Y-m-d H:i:s", filemtime($_SERVER["DOCUMENT_ROOT"]."/backups/monthly/".$file))."</td>";
					print "<td>".$oUtils->ConvertFromBytes(filesize($_SERVER["DOCUMENT_ROOT"]."/backups/monthly/".$file))."</td>";	



                        print "<td class=\"center\">";
                        print "<div class=\"visible-md visible-lg hidden-sm hidden-xs\">";

                        print "<a href=\"/restore/DoRestore.php?URL=/backups/monthly.php&FileName=".$_SERVER["DOCUMENT_ROOT"]."/backups/monthly/".$file."\" class=\"btn btn-green tooltips\" data-placement=\"top\" data-original-title=\"Restore File\"><i class=\"fa fa-cloud-upload fa fa-white\" style=\"color:white;\"></i></a>\n";
			print "<a href=\"DeleteBackup.php?Type=monthly&File=".$file."\" onclick=\"return ValidateDelete('".$file."'); return false;\" class=\"btn btn-bricky tooltips\" data-placement=\"top\" data-original-title=\"Delete File\"><i class=\"fa fa-times fa fa-white\" style=\"color:white;\"></i></a>\n";

                        print "</div>";

                        print "<div class=\"visible-xs visible-sm hidden-md hidden-lg\">";
                        print "<div class=\"btn-group\">";
                        print "<a class=\"btn btn-primary dropdown-toggle btn-sm\" data-toggle=\"dropdown\" href=\"#\">";
                        print "<i class=\"fa fa-cog\"></i> <span class=\"caret\"></span>";
                        print "</a>";

                        print "<ul role=\"menu\" class=\"dropdown-menu pull-right\">";
                        print "<li role=\"presentation\">";
                        print "<a role=\"menuitem\" tabindex=\"-1\" href=\"/restore/DoRestore.php?FileName=".$_SERVER["DOCUMENT_ROOT"]."/backups/monthly/".$file."\">";
                        print "<i class=\"fa fa-cloud-upload\"></i> Restore File";
                        print "</a>";
                        print "</li>";

                        print "<li role=\"presentation\">";
                        print "<a role=\"menuitem\" tabindex=\"-1\" href=\"DeleteBackup.php?Type=monthly&File=".$file."\" onclick=\"return ValidateDelete('".$file."'); return false;\">";
                        print "<i class=\"fa fa-times\"></i> Delete File";
                        print "</a>";
                        print "</li>";
                        print "</ul>";
                        print "</div>";
                        print "</div></td>";





















					print "</tr>";
				}


			}

		}

		closedir($handle);
	}

	?>

	
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
