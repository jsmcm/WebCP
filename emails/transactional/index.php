<?php
session_start();

include_once($_SERVER["DOCUMENT_ROOT"]."/vendor/autoload.php");

$oUser = new User();
$oEmail = new Email();
$oSettings = new Settings();

require($_SERVER["DOCUMENT_ROOT"]."/includes/License.inc.php");

$ClientID = $oUser->getClientId();
if ($ClientID < 1) {
	header("Location: /index.php");
	exit();
}

if ($oUser->Role != "admin") {
	header("Location: /index.php");
	exit();
}

$settings = $oEmail->getTransactionalEmailSettings();

$serviceName = "";
$hostName = "";
$userName = "";
$password = "";
$default = "";

if (isset($settings["servicename"])) {
	$serviceName = $settings["servicename"];
}

if (isset($settings["hostname"])) {
	$hostName = $settings["hostname"];
}

if (isset($settings["username"])) {
	$userName = $settings["username"];
}

if (isset($settings["password"])) {
	$password = $settings["password"];
}

if (isset($settings["default"])) {
	$default = $settings["default"];
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
		<title>Transactional Email | <?php print $oSettings->GetWebCPTitle(); ?></title>
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
		<link rel="stylesheet" href="assets/plugins/ladda-bootstrap/dist/ladda-themeless.min.css">

		<link rel="stylesheet" href="/assets/plugins/bootstrap-switch/static/stylesheets/bootstrap-switch.css">

		<link rel="stylesheet" href="/assets/plugins/bootstrap-social-buttons/social-buttons-3.css">

		<link href="/assets/plugins/bootstrap-modal/css/bootstrap-modal-bs3patch.css" rel="stylesheet" type="text/css"/>

		<link href="/assets/plugins/bootstrap-modal/css/bootstrap-modal.css" rel="stylesheet" type="text/css"/>

		<!-- end: CSS REQUIRED FOR THIS PAGE ONLY -->
		<link rel="shortcut icon" href="/favicon.ico" />
		
		
		<script language="javascript">

		function ValidateForm()
		{
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
									<a href="/emails/">
										Emails
									</a>
								</li>
								<li>
									<i class="active"></i>
									<a href="/emails/transactional/index.php">
										Transactional
									</a>
								</li>
					
					
							</ol>
							<div class="page-header">
								<h1>Transactional Emails</h1>
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
					


					<form name="TransactionalEmailSettings" action="saveTransactionalEmailSettings.php" method="post">
					
					<div class="col-md-12">

						<div class="panel panel-default">

							<div class="panel-body">
	
							<h1>Transactional Email</h1>
							Enter your transactional email credentials here:
							<p>
	
							<div class="form-group" style="padding-bottom: 50px;">
								<label class="col-sm-2 control-label">
								<b>Service Name:</b>
								<br><i>eg, Transactional</i>
								</label>
								<div class="col-sm-10">
									<span class="input-icon">
									<input name="serviceName" value="<?php print $serviceName; ?>" type="text" id="form-field-11" class="form-control">
									</span>
								</div>
							</div>

							<div class="form-group" style="padding-bottom: 50px;">
								<label class="col-sm-2 control-label">
								<b>Host Name:</b>
								</label>
								<div class="col-sm-10">
									<span class="input-icon">
									<input name="hostName" value="<?php print $hostName; ?>" type="text" id="form-field-11" class="form-control">
									</span>
								</div>
							</div>

							<div class="form-group" style="padding-bottom: 50px;">
								<label class="col-sm-2 control-label">
								<b>User Name:</b>
								</label>
								<div class="col-sm-10">
									<span class="input-icon">
									<input name="userName" value="<?php print $userName; ?>" type="text" id="form-field-11" class="form-control">
									</span>
								</div>
							</div>


							<div class="form-group" style="padding-bottom: 50px;">
								<label class="col-sm-2 control-label">
								<b>Password:</b>
								</label>
								<div class="col-sm-10">
									<span class="input-icon">
									<input name="password" value="<?php print $password; ?>" type="password" id="form-field-11" class="form-control">
									</span>
								</div>
							</div>
					
							<div class="form-group" style="padding-bottom: 100px;">
		                                        	<label class="col-sm-2 control-label">
									<b>Apply to new domains</b>:
		                                                </label>
							
								<div class="col-sm-2">
		                                                 	<div class="make-switch" data-on="success" data-off="danger">
		                                                        	<input type="checkbox" <?php print $default; ?> value="checked" name="default">
		                                                        </div>
								 </div>

								<div class="col-sm-8">
									<i>If set to yes then all new domains will use this. If set to no then new domains will not be created using this setting. In either case you can manually edit <a href="/emails/transactional/transactional.php">domains using transactional emails</a>
								</div>
		                                     	</div>


						</div>
					</div>
	










					<div class="form-group" style="padding-bottom: 50px;">

						<input type="submit" value="Save Settings" data-style="zoom-in" class="btn btn-info ladda-button" onclick="return ValidateForm(); return false;">

						<span class="ladda-spinner"></span>
						<span class="ladda-progress" style="width: 0px;"></span>
						</input>
					</div>

					</form>


















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
		<script src="/assets/plugins/bootstrap-modal/js/bootstrap-modal.js"></script>

		<script src="/assets/plugins/bootstrap-modal/js/bootstrap-modalmanager.js"></script>

		<script src="/assets/js/ui-modals.js"></script>

		<script src="/assets/plugins/ladda-bootstrap/dist/spin.min.js"></script>

		<script src="/assets/plugins/ladda-bootstrap/dist/ladda.min.js"></script>

		<script src="/assets/plugins/bootstrap-switch/static/js/bootstrap-switch.min.js"></script>

		<script src="/assets/js/ui-buttons.js"></script>


		<script>
			jQuery(document).ready(function() {
				Main.init();
				UIButtons.init();
			});
		</script>
	</body>
	<!-- end: BODY -->
</html>
