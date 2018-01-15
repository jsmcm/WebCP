<?php
session_start();

function __autoload($classname)
{
	require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.".$classname.".php");
}

$oUser = new User();
$oSettings = new Settings();
$oUtils = new Utils();

require($_SERVER["DOCUMENT_ROOT"]."/includes/License.inc.php");

$ClientID = $oUser->getClientId();
if($ClientID < 1)
{
	header("Location: /index.php");
	exit();
}





$PackageID = "";
$Action = "add";

$PackageName = "";
$Emails = 0;
$SubDomains = 0;
$ParkedDomains = 0;
$Domains = 0;
$DiskSpace = 0;
$DiskSpaceScale = "Mb";
$Traffic = 0;
$TrafficScale = "Mb";
$FTP = 0;
$MySQL = 0;
$PostgreSQL = 0;

if(isset($_REQUEST["PackageID"]))
{
	$PackageID = $_REQUEST["PackageID"];
	$Action = "update";

	$oPackage = new Package();

	$oPackage->GetPackageDetails($PackageID, $Array, $ArrayCount, $oUser->Role, $oUser->ClientID);

	if($ArrayCount < 0)
	{
		header("Location: /domains/index.php?Notes=You don't have permission to be there&NoteType=error");
		exit();
	}

	$PackageName = $Array["PackageName"];
	$Emails = (($Array["Emails"] == "-1")?"unlimited":$Array["Emails"]);
	$SubDomains = (($Array["SubDomains"] == "-1")?"unlimited":$Array["SubDomains"]);
	$ParkedDomains = (($Array["ParkedDomains"] == "-1")?"unlimited":$Array["ParkedDomains"]);
	$Domains = $Array["Domains"];
	$DiskSpace = $Array["DiskSpace"];
	$Traffic = $Array["Traffic"];
	$FTP = (($Array["FTP"] == "-1")?"unlimited":$Array["FTP"]);
	$MySQL = (($Array["MySQL"] == "-1")?"unlimited":$Array["MySQL"]);
	//$PostgreSQL = $Array["PostgreSQL"];

	$DiskSpaceScale = "b";
	$oUtils->ConvertFromBytes($DiskSpace, $DiskSpaceScale, "Mb");

	//print "<p>&nbsp;<p>&nbsp;<p>";
	
	$TrafficScale = "b";
	$oUtils->ConvertFromBytes($Traffic, $TrafficScale, "Mb");
	//print "<p>&nbsp;<p>&nbsp;<p>DiskSpace: ".$DiskSpace." - ".gettype($DiskSpace)."<p>";
	//print "Traffic: ".$Traffic." - ".gettype($Traffic)."<p>";
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
		<title>Package Manager| <?php print $oSettings->GetWebCPTitle(); ?></title>
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
		
		<script language="javascript" src="/includes/javascript/password.js"></script>
		

		<script language="javascript">


		function DoSubmit()
		{
			

			if(document.AddPackage.PackageName.value == "")
			{
				alert("Please enter a name for this package");
				document.AddPackage.PackageName.focus();
				return false;
			}

			if(document.AddPackage.Emails.value == "")
			{
				alert("Please enter the number of allowed email addresses in this package (or unlimited)");
				document.AddPackage.Emails.focus();
				return false;
			}

			if(isNaN(document.AddPackage.Emails.value))
			{
				if(document.AddPackage.Emails.value.toLowerCase() != "unlimited")
				{
					alert("Please enter the number of allowed email addresses in this package (or unlimited)");
					document.AddPackage.Emails.value = "";
					document.AddPackage.Emails.focus();
					return false;
				}
			}
			
			if(document.AddPackage.SubDomains.value == "")
			{
				alert("Please enter the number of allowed sub domains in this package (or unlimited)");
				document.AddPackage.SubDomains.focus();
				return false;
			}

			if(isNaN(document.AddPackage.SubDomains.value))
			{
				if(document.AddPackge.SubDomains.value.toLowerCase() != "unlimited")
				{
					alert("Please enter the number of allowed sub domains in this package (or unlimited)");
					document.AddPackage.SubDomains.value = "";
					document.AddPackage.SubDomains.focus();
					return false;
				}
			}


			if(document.AddPackage.ParkedDomains.value == "")
			{
				alert("Please enter the number of allowed parked domains in this package (or unlimited)");
				document.AddPackage.ParkedDomains.focus();
				return false;
			}

			if(isNaN(document.AddPackage.ParkedDomains.value))
			{
				if(document.AddPackage.ParkedDomains.value.toLowerCase() != "unlimited")
				{ 
					alert("Please enter the number of allowed parked domains in this package (or unlimited)");
					document.AddPackage.ParkedDomains.value = "";
					document.AddPackage.ParkedDomains.focus();
					return false;
				}
			}


			if(document.AddPackage.Domains.value == "")
			{
				alert("Please enter the number of allowed domains in this package (or unlimited)");
				document.AddPackage.Domains.focus();
				return false;
			}

			if(isNaN(document.AddPackage.Domains.value))
			{
				if(document.AddPackage.Domains.value.toLowerCase() != "unlimited")
				{
					alert("Please enter the number of allowed domains in this package (or unlimited)");
					document.AddPackage.Domains.value = "";
					document.AddPackage.Domains.focus();
					return false;
				}
			}



			if(document.AddPackage.DiskSpace.value == "")
			{
				alert("Please enter the disk space allowance for this package (or unlimited)");
				document.AddPackage.DiskSpace.focus();
				return false;
			}

			if(isNaN(document.AddPackage.DiskSpace.value))
			{

				alert("Please enter the disk space allowance for this package (or unlimited)");
				document.AddPackage.DiskSpace.value = "";
				document.AddPackage.DiskSpace.focus();
				return false;
			}


			if(document.AddPackage.DiskSpace.value < 5)
			{
				alert("Please allocate at least 5 Mb disk space or else the account creation will fail");
				document.AddPackage.DiskSpace.focus();
				return false;
			}

			if(document.AddPackage.Traffic.value == "")
			{
				alert("Please enter the traffic allowance for this package (or unlimited)");
				document.AddPackage.Traffic.focus();
				return false;
			}

			if(isNaN(document.AddPackage.Traffic.value))
			{
				alert("Please enter the traffic allowance for this package (or unlimited)");
				document.AddPackage.Traffic.value = "";
				document.AddPackage.Traffic.focus();
				return false;
			}




			if(document.AddPackage.FTP.value == "")
			{
				alert("Please enter the number of allowed FTP accounts in this package (or unlimited)");
				document.AddPackage.FTP.focus();
				return false;
			}

			if(isNaN(document.AddPackage.FTP.value))
			{
				if(document.AddPackage.FTP.value.toLowerCase() != "unlimited")
				{
					alert("Please enter the number of allowed FTP accounts in this package (or unlimited)");
					document.AddPackage.FTP.value = "";
					document.AddPackage.FTP.focus();
					return false;
				}
			}


			if(document.AddPackage.MySQL.value == "")
			{
				alert("Please enter the number of allowed mysql databases in this package (or unlimited)");
				document.AddPackage.MySQL.focus();
				return false;
			}

			if(isNaN(document.AddPackage.MySQL.value))
			{
				if(document.AddPackage.MySQL.value.toLowerCase() != "unlimited")
				{
					alert("Please enter the number of allowed mysql databases in this package (or unlimited)");
					document.AddPackage.MySQL.value = "";
					document.AddPackage.MySQL.focus();
					return false;
				}
			}



			if(document.AddPackage.PostgreSQL.value == "")
			{
				alert("Please enter the number of allowed postgresql databases in this package (or unlimited)");
				document.AddPackage.PostgreSQL.focus();
				return false;
			}

			if(isNaN(document.AddPackage.PostgreSQL.value))
			{
				if(document.AddPackage.PostgreSQL.value.toLowerCase() != "unlimited")
				{
					alert("Please enter the number of allowed postgresql databases in this package (or unlimited)");
					document.AddPackage.PostgreSQL.value = "";
					document.AddPackage.PostgreSQL.focus();
					return false;
				}
			}
			return true;
			
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
									<a href="/packages/">
										Packages
									</a>
								</li>
								<li>
									<i class="active"></i>
									<a href="/packages/AddPackage.php">
										Package Editor
									</a>
								</li>
					
							</ol>
							<div class="page-header">
								<h1>Package Manager <small><?php print $PackageName; ?></small> </h1>
							</div>
							<!-- end: PAGE TITLE & BREADCRUMB -->
						</div>
					</idiv>
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
					

								<form name="AddPackage" method="post" action="DoAddPackage.php" class="form-horizontal">
									
										<input type="hidden" name="Action" value="<?php print $Action; ?>">
										<input type="hidden" name="PackageID" value="<?php print $PackageID; ?>">
								
										<div class="form-group">
											<label class="col-sm-2 control-label">
												Package Name:
											</label>
											<div class="col-sm-4">
												<span class="input-icon">
												<input name="PackageName" type="text" value="<?php print $PackageName; ?>" id="form-field-11" class="form-control">
												</span>										
											</div>
										</div>
									



												<?php
												$Domains = 1;
												?>
												<input name="Domains" value="<?php print $Domains; ?>" type="hidden">





                                                                                <div class="form-group">
                                                                                        <label class="col-sm-2 control-label">
                                                                                                Number of Sub Domains<br><i>(or unlimited)</i>:
                                                                                        </label>
                                                                                        <div class="col-sm-4">
                                                                                                <span class="input-icon">
                                                                                                <input name="SubDomains" value="<?php print $SubDomains; ?>" type="text" id="form-field-11" class="form-control">
                                                                                                </span>  
                                                                                        </div>
                                                                                </div>

												

												
												
												
                                                                                <div class="form-group">
                                                                                        <label class="col-sm-2 control-label">
                                                                                                Number of Parked Domains<br><i>(or unlimited)</i>:
                                                                                        </label>
                                                                                        <div class="col-sm-4">
                                                                                                <span class="input-icon">
                                                                                                <input name="ParkedDomains" value="<?php print $ParkedDomains; ?>" type="text" id="form-field-11" class="form-control">
                                                                                                </span>  
                                                                                        </div>
                                                                                </div>											
												
											
										<hr>	
												
                                                                                <div class="form-group">
                                                                                        <label class="col-sm-2 control-label">
                                                                                                Number of Emails<br><i>(or unlimited)</i>:
                                                                                        </label>
                                                                                        <div class="col-sm-4">
                                                                                                <span class="input-icon">
                                                                                                <input name="Emails" value="<?php print $Emails; ?>" type="text" id="form-field-11" class="form-control">
                                                                                                </span>  
                                                                                        </div>
                                                                                </div>											
												
												
									
                                                                                <div class="form-group">
                                                                                        <label class="col-sm-2 control-label">
                                                                                                Disk Space<br><i><b><?php print $DiskSpaceScale; ?></b></i>:
                                                                                        </label>
                                                                                        <div class="col-sm-4">
                                                                                                <span class="input-icon">
                                                                                                <input name="DiskSpace" value="<?php print $DiskSpace; ?>" type="text" id="form-field-11" class="form-control">
                                                                                                </span>  
                                                                                        </div>
                                                                                </div>											
												
												
													
												
										


									
                                                                                <div class="form-group">
                                                                                        <label class="col-sm-2 control-label">
                                                                                                Traffic<br><i><b><?php print $TrafficScale; ?></b></i>:
                                                                                        </label>
                                                                                        <div class="col-sm-4">
                                                                                                <span class="input-icon">
                                                                                                <input name="Traffic" value="<?php print $Traffic; ?>" type="text" id="form-field-11" class="form-control">
                                                                                                </span>  
                                                                                        </div>
                                                                                </div>											
												
												
		


									
                                                                                <div class="form-group">
                                                                                        <label class="col-sm-2 control-label">
                                                                                                Number of FTP Accounts<br><i>(or unlimited)</i>:
                                                                                        </label>
                                                                                        <div class="col-sm-4">
                                                                                                <span class="input-icon">
                                                                                                <input name="FTP" value="<?php print $FTP; ?>" type="text" id="form-field-11" class="form-control">
                                                                                                </span>  
                                                                                        </div>
                                                                                </div>											
												
												
		
												

									
                                                                                <div class="form-group">
                                                                                        <label class="col-sm-2 control-label">
                                                                                                Number of MySQL Databases<br><i>(or unlimited)</i>:
                                                                                        </label>
                                                                                        <div class="col-sm-4">
                                                                                                <span class="input-icon">
                                                                                                <input name="MySQL" value="<?php print $MySQL; ?>" type="text" id="form-field-11" class="form-control">
                                                                                                </span>  
                                                                                        </div>
                                                                                </div>											
												
												
														
												
									



							
										<div class="form-group">

											<div class="col-sm-4">
												<input type="submit" value="<?php ($PackageID != "") ? print "Edit" : print "Add"; ?> Package" data-style="zoom-in" class="btn btn-info ladda-button" onclick="return DoSubmit(); return false;">
													<span class="ladda-spinner"></span>
													<span class="ladda-progress" style="width: 0px;"></span>
												</input>
											</div>
										</div>


								</form>


										
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
