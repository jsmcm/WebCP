<?php
session_start();

include_once($_SERVER["DOCUMENT_ROOT"]."/vendor/autoload.php");

$oUser = new User();
$oSettings = new Settings();
$oUtils = new Utils();
$oDomain = new Domain();
$oSimpleNonce = new SimpleNonce();
$oReseller = new Reseller();

$ClientID = $oUser->getClientId();
if($ClientID < 1) {
	header("Location: /index.php");
	exit();
}

// can this client edit this domain?

$domainName = filter_var($_REQUEST["domainName"], FILTER_SANITIZE_STRING);
$domainId = intVal( $_REQUEST["DomainID"] );
$clientId = $ClientID;
$clientRole = $oUser->Role;


$random = random_int(1, 100000);
$nonceArray = [
    $oUser->Role,
    $ClientID,
	$domainId,
	$random
];

$nonce = $oSimpleNonce->GenerateNonce("getDomainOwner", $nonceArray);
$domainOwnerId = $oDomain->GetDomainOwner($domainId, $random, $nonce);

$random = random_int(1, 100000);
$nonceArray = [
    $oUser->Role,
    $ClientID,
	$domainOwnerId,
	$random
];

$nonce = $oSimpleNonce->GenerateNonce("getClientResellerID", $nonceArray);
$resellerId = $oReseller->GetClientResellerID($domainOwnerId, $random, $nonce);

if ( $oUser->Role != "admin") {
	if ( $clientId != $domainOwnerId ) {
		if ( $resellerId != $clientId ) {
			header("Location: index.php?Notes=You don't have permission to edit that domain&NoteType=error");
			exit();
		}
	}
}

$timeStamp = filter_var($_REQUEST["timeStamp"], FILTER_SANITIZE_STRING);
$nonce = filter_var($_REQUEST["nonce"], FILTER_SANITIZE_STRING);

$nonceArray = [
        $domainName,
        $domainId,
        $clientRole,
	$clientId,
	"sslRedirect",
	"domainRedirect",
	"phpVersion"
];

$nonce = $oSimpleNonce->GenerateNonce("domainSettings", $nonceArray);


$domainSettings = $oDomain->getDomainSettings($domainId);

$domainRedirect = "none";
if (isset($domainSettings["domain_redirect"]["value"])) {
	$domainRedirect = $domainSettings["domain_redirect"]["value"];
}

$sslRedirect = "none";
if (isset($domainSettings["ssl_redirect"]["value"])) {
	$sslRedirect = $domainSettings["ssl_redirect"]["value"];
}

$autoWebp = "none";
if (isset($domainSettings["auto_webp"]["value"])) {
	$autoWebp = $domainSettings["auto_webp"]["value"];
}

$fastCgiCache = "false";
if (isset($domainSettings["fastcgi_cache"]["value"])) {
	$fastCgiCache = $domainSettings["fastcgi_cache"]["value"];
}


$phpVersion = "8.2";
if (isset($domainSettings["php_version"]["value"])) {
	$phpVersion = $domainSettings["php_version"]["value"];
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
		<title>Domain Settings | <?php print $oSettings->GetWebCPTitle(); ?></title>
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
									<a href="/domains/">
										Domains
									</a>
								</li>
								<li>
									<i class="active"></i>
									<a href="/domains/settings.php">
										Domains Settings
									</a>
								</li>
					
					
							</ol>
							<div class="page-header">
								<h1>Domain Settings</h1>
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
					


					<form name="DomainSettings" action="SaveDomainSettings.php" method="post">
					<input type="hidden" name="domainName" value="<?php print $domainName; ?>">
					<input type="hidden" name="domainId" value="<?php print $domainId; ?>">
					<input type="hidden" name="nonce" value="<?php print $nonce["Nonce"]; ?>">
					<input type="hidden" name="timeStamp" value="<?php print $nonce["TimeStamp"]; ?>">

					<div class="col-md-12">

						<div class="panel panel-default">

							<div class="panel-body">
	
							<h1>Domain Settings</h1>
						
							<p>
	
							<div class="form-group" style="padding-bottom: 50px;">
								<label class="col-sm-2 control-label">
								<b>Domain Redirect</b>:
								</label>
								<div class="col-sm-8">
									<span class="input-icon">
									<select name="domainRedirect" id="form-field-11" class="form-control">
									<option value="none" <?php if($domainRedirect == "none") print " selected "; ?>>No Redirect</option>
									<option value="www" <?php if($domainRedirect == "www") print " selected "; ?>>Redirect to WWW</option>
									<option value="naked" <?php if($domainRedirect == "naked") print " selected "; ?>>Redirect to naked</option>
									</select>
									</span>
								</div>
							</div>
							
							<div class="form-group" style="padding-bottom: 50px;">
								<label class="col-sm-2 control-label">
								<b>SSL Redirect</b>:
								</label>
								<div class="col-sm-8">
									<span class="input-icon">
									<select name="sslRedirect" id="form-field-11" class="form-control">
									<option value="none" <?php if($sslRedirect == "none") print " selected "; ?>>No Redirect</option>
									<option value="enforce" <?php if($sslRedirect == "enforce") print " selected "; ?>>Redirect to HTTPS</option>
									</select>
									</span>
								</div>
							</div>


							<div class="form-group" style="padding-bottom: 50px;">
								<label class="col-sm-2 control-label">
								<b>Use FastCGI Cache</b>:
								</label>
								<div class="col-sm-8">
									<span class="input-icon">
									<select name="fastcgi_cache" id="form-field-11" class="form-control">
									<option value="false" <?php if($fastCgiCache == "false") print " selected "; ?>>Do not use FastCGI cache</option>
									<option value="true" <?php if($fastCgiCache == "true") print " selected "; ?>>Use FastCGI cache</option>
									</select>
									</span>
								</div>
							</div>


							<div class="form-group" style="padding-bottom: 50px;">
								<label class="col-sm-2 control-label">
								<b>Auto Webp</b>:
								</label>
								<div class="col-sm-8">
									<span class="input-icon">
									<select name="auto_webp" id="form-field-11" class="form-control">
									<option value="none" <?php if($autoWebp == "none") print " selected "; ?>>Do not create WebP</option>
									<option value="auto" <?php if($autoWebp == "auto") print " selected "; ?>>Auto create WebP</option>
									</select>
									</span>
								</div>
							</div>



							<div class="form-group" style="padding-bottom: 50px;">
								<label class="col-sm-2 control-label">
								<b>PHP Version</b>:
								</label>
								<div class="col-sm-4">
									<span class="input-icon">
									<select name="phpVersion" id="form-field-11" class="form-control">
									<option value="8.2" <?php if($phpVersion == "8.2") print " selected "; ?>>8.2</option>
									<option value="8.1" <?php if($phpVersion == "8.1") print " selected "; ?>>8.1</option>
									<option value="7.4" <?php if($phpVersion == "7.4") print " selected "; ?>>7.4</option>
									<option value="7.2" <?php if($phpVersion == "7.2") print " selected "; ?>>7.2</option>
									<option value="7.1" <?php if($phpVersion == "7.1") print " selected "; ?>>7.1</option>
									<option value="5.6" <?php if($phpVersion == "5.6") print " selected "; ?>>5.6</option>
									</select>
									</span>
								</div>
								<div class="col-sm-4">
									<a href="/server/php/settings.php?nonce=&timeStamp=&version=<?php print $phpVersion; ?>&domain=<?php print $domainName; ?>" data-style="expand-right" class="btn btn-teal ladda-button">
                                        <span class="ladda-label"> Edit Config </span>
                                        <i class="fa fa-arrow-circle-right"></i>
                                        <span class="ladda-spinner"></span>
                                    <span class="ladda-spinner"></span></a>
								</div>
							</div>

							</div>

						</div>
					</div>
					


					<div class="col-md-12">

	
							<div class="form-group" style="padding-bottom: 50px;">

								<input type="submit" value="Save Settings" data-style="zoom-in" class="btn btn-info ladda-button" onclick="return ValidateForm(); return false;">

								<span class="ladda-spinner"></span>
								<span class="ladda-progress" style="width: 0px;"></span>
								</input>
							</div>
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
