<?php
session_start();

include_once($_SERVER["DOCUMENT_ROOT"]."/vendor/autoload.php");
$oUser = new User();
$oReseller = new Reseller();
$oSettings = new Settings();
$oUtils = new Utils();
$oPackage = new Package();
$oDomain = new Domain();


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

if(!isset($_REQUEST["ResellerID"]))
{
	header("Location: ./reseller.php?Notes=Invalid reseller selected&NoteType=error");
	exit();
}


$ResellerID = intVal($_REQUEST["ResellerID"]);

$Array = array();
$ArrayCount = 0;

	$oReseller->GetResellerDetails($ResellerID, $Array, $ArrayCount, $oUser->Role, $oUser->ClientID);

	$Accounts = ((isset($Array["Accounts"]))? $Array["Accounts"]: 0);
	$DiskSpaceBytes = ((isset($Array["DiskSpace"]))? $Array["DiskSpace"]: 0);
	$Traffic = ((isset($Array["Traffic"]))? $Array["Traffic"]: 0);
	$UserName = $Array["UserName"];
	$FirstName = $Array["FirstName"];
	$Surname = $Array["Surname"];
	$FirewallControl = "";
	if( (isset($Array["FirewallControl"])) && ($Array["FirewallControl"] == "on"))
	{
		$FirewallControl = " checked ";
	}

	$DiskSpace = $DiskSpaceBytes;
	$DiskSpaceScale = "b";
	$oUtils->ConvertFromBytes($DiskSpace, $DiskSpaceScale, "Mb");

	$TrafficScale = "b";
	$oUtils->ConvertFromBytes($Traffic, $TrafficScale, "Mb");

	if($Accounts == -1)
	{
		$Accounts = "unlimited";
	}

        $TotalDiskSpace = $oPackage->GetTotalDiskSpace();
        $NonResellerUsage = $oDomain->GetPackageDiskSpaceUsage(0); // non reseller usage
        $ResellerUsage = $oDomain->GetPackageDiskSpaceUsage(-1); // all reseller usage

        $ResellerAllocations = $oReseller->GetDiskSpaceAllocation(-1); // all reseller allocations

        $LeftOverBytes = $TotalDiskSpace + $DiskSpaceBytes - ($NonResellerUsage + $ResellerAllocations);

	$LeftOver = $LeftOverBytes;
        $Scale = "b";
        $Available = $oUtils->ConvertFromBytes($LeftOver, $Scale);
								
	$LeftOver = $LeftOverBytes;
        $Scale = "b";
        $AvailableMb = $oUtils->ConvertFromBytes($LeftOver, $Scale, "Mb");

?>

<!DOCTYPE html>
<!--[if IE 8]><html class="ie8 no-js" lang="en"><![endif]-->
<!--[if IE 9]><html class="ie9 no-js" lang="en"><![endif]-->
<!--[if !IE]><!-->
<html lang="en" class="no-js">
	<!--<![endif]-->
	<!-- start: HEAD -->
	<head>
		<title>Reseller Management| <?php print $oSettings->GetWebCPTitle(); ?></title>
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
                <link rel="stylesheet" href="/assets/plugins/bootstrap-switch/static/stylesheets/bootstrap-switch.css">
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
			if(document.EditReseller.Accounts.value == "")
			{
				alert("Please enter the number of allowed accounts for this reseller (or unlimited)");
				document.EditReseller.Accounts.focus();
				return false;
			}

			if(isNaN(document.EditReseller.Accounts.value))
			{
				if(document.EditReseller.Accounts.value.toLowerCase() != "unlimited")
				{
					alert("Please enter the number of allowed accounts for this reseller (or unlimited)");
					document.EditReseller.Accounts.value = "";
					document.EditReseller.Accounts.focus();
					return false;
				}
			}
			
			if(document.EditReseller.DiskSpace.value == "")
			{
				alert("Please enter the disk space allowance for this reseller");
				document.EditReseller.DiskSpace.focus();
				return false;
			}

			if(isNaN(document.EditReseller.DiskSpace.value))
			{

				alert("Please enter the disk space allowance for this reseller");
				document.EditReseller.DiskSpace.value = "";
				document.EditReseller.DiskSpace.focus();
				return false;
			}

			if(document.EditReseller.DiskSpace.value < 5)
			{
				alert("Please allocate at least 5 Mb disk space or else the account creation will fail");
				document.EditReseller.DiskSpace.focus();
				return false;
			}
			
			if(document.EditReseller.DiskSpace.value > <?php print $LeftOver; ?>)
			{
				alert("There is only <?php print $AvailableMb; ?> Mb disk space available. I've adjusted the value automatically. If you're happy with that press the \"Edit Reseller\" button again");
				document.EditReseller.DiskSpace.value = "<?php print $LeftOver; ?>";
				document.EditReseller.DiskSpace.focus();
				return false;
			}

			if(document.EditReseller.Traffic.value == "")
			{
				alert("Please enter the traffic allowance for this reseller");
				document.EditReseller.Traffic.focus();
				return false;
			}

			if(isNaN(document.EditReseller.Traffic.value))
			{
				alert("Please enter the traffic allowance for this reseller");
				document.EditReseller.Traffic.value = "";
				document.EditReseller.Traffic.focus();
				return false;
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
									<a href="/users/">
										Users
									</a>
								</li>
								<li>
									<i class="active"></i>
									<a href="/users/reseller.php">
										Resellers
									</a>
								</li>
					
							</ol>
							<div class="page-header">
								<h1>Reseller Management <small><?php print $FirstName." ".$Surname; ?></small> </h1>
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
					
                                                            	<?php
								
                                                                print "<b>Disk space available:</b> ".$Available." / ".$AvailableMb."<p>";
								?>

								<form name="EditReseller" method="post" action="DoEditReseller.php" class="form-horizontal">
									
										<input type="hidden" name="ResellerID" value="<?php print $ResellerID; ?>">

                                                                                <div class="form-group">
                                                                                        <label class="col-sm-2 control-label">
                                                                                               Number of Accounts<br><i>(or unlimited)</i>:
                                                                                        </label>
                                                                                        <div class="col-sm-4">
                                                                                                <span class="input-icon">
                                                                                                <input name="Accounts" value="<?php print $Accounts; ?>" type="text" id="form-field-11" class="form-control">
                                                                                                </span>  
                                                                                        </div>
                                                                                </div>

												
                                                                                <div class="form-group">
                                                                                        <label class="col-sm-2 control-label">
                                                                                                Disk Space<br><i><b>Mb</b></i>:
                                                                                        </label>
                                                                                        <div class="col-sm-4">
                                                                                                <span class="input-icon">
                                                                                                <input name="DiskSpace" value="<?php print $DiskSpace; ?>" type="text" id="form-field-11" class="form-control">
                                                                                                </span>  
                                                                                        </div>
                                                                                </div>											
												
												
													
                                                                                <div class="form-group">
                                                                                        <label class="col-sm-2 control-label">
                                                                                                Traffic<br><i><b>Mb</b></i>:
                                                                                        </label>
                                                                                        <div class="col-sm-4">
                                                                                                <span class="input-icon">
                                                                                                <input name="Traffic" value="<?php print $Traffic; ?>" type="text" id="form-field-11" class="form-control">
                                                                                                </span>  
                                                                                        </div>
                                                                                </div>											

                                                                                <div class="form-group">
                                                                                        <label class="col-sm-2 control-label">
                                                                                                Firewall Control:
                                                                                        </label>
                                                                                        <div class="col-sm-4">
                                                                       				 <div class="make-switch" data-on="success" data-off="danger">
                                                                                			<input type="checkbox" <?php print $FirewallControl; ?> name="FirewallControl">
                                                                				</div>
                                                                                        </div>
                                                                                </div>											


				
												
										<div class="form-group">

											<div class="col-sm-4">
												<input type="submit" value="Edit Reseller" data-style="zoom-in" class="btn btn-info ladda-button" onclick="return DoSubmit(); return false;">
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
		<script src="/assets/plugins/bootstrap-switch/static/js/bootstrap-switch.min.js"></script>

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
