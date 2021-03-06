<?php

session_start();

include_once($_SERVER["DOCUMENT_ROOT"]."/vendor/autoload.php");

$oUser = new User();
$oSettings = new Settings();
$oUtils = new Utils();
$oDomain = new Domain();
$oSimpleNonce = new SimpleNonce();
$oReseller = new Reseller();
$oSSH = new SSH();

$ClientID = $oUser->getClientId();
if($ClientID < 1) {
	header("Location: /index.php");
	exit();
}

// can this client edit this domain?
$domainId = intVal( $_REQUEST["domainId"] );
$clientId = $ClientID;
$clientRole = $oUser->Role;

$random = random_int(1, 100000);
$nonceArray = [
    $oUser->Role,
    $oUser->ClientID,
	$domainId,
	$random
];

$nonce = $oSimpleNonce->GenerateNonce("getDomainOwner", $nonceArray);
$domainOwnerId = $oDomain->GetDomainOwner($domainId, $random, $nonce);

$random = random_int(1, 100000);
$nonceArray = [
    $oUser->Role,
    $oUser->ClientID,
    $domainOwnerId,
	$random
];

$oReseller = new Reseller();
$nonce = $oSimpleNonce->GenerateNonce("getClientResellerID", $nonceArray);
$resellerId = $oReseller->GetClientResellerID($domainOwnerId, $random, $nonce);

if ( $clientId != $domainOwnerId ) {
	if ( $resellerId != $clientId ) {
		if ($oUser->Role != "admin") {
			header("Location: index.php?Notes=You don't have permission to edit that domain&NoteType=error");
			exit();
		}
	}
}

$nonceArray = [
	$clientRole,
	$clientId,
	$domainId
];

$nonce = $oSimpleNonce->GenerateNonce("getDomainPublicKeyList", $nonceArray);

$publicKeyList = $oSSH->getDomainPublicKeyList($domainId, $nonce);


$random = random_int(1,100000);
$nonceArray = [
	$clientRole,
	$clientId,
	$domainId,
	$random
];

$nonce = $oSimpleNonce->GenerateNonce("getDomainNameFromDomainID", $nonceArray);
$domainName = $oDomain->GetDomainNameFromDomainID( $domainId, $random, $nonce);

?>

<!DOCTYPE html>
<!--[if IE 8]><html class="ie8 no-js" lang="en"><![endif]-->
<!--[if IE 9]><html class="ie9 no-js" lang="en"><![endif]-->
<!--[if !IE]><!-->
<html lang="en" class="no-js">
	<!--<![endif]-->
	<!-- start: HEAD -->
	<head>
		<title>Manage SSH | <?php print $oSettings->GetWebCPTitle(); ?></title>
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


                <link rel="stylesheet" href="/assets/plugins/x-editable/css/bootstrap-editable.css">




		<!-- end: CSS REQUIRED FOR THIS PAGE ONLY -->
		<link rel="shortcut icon" href="/favicon.ico" />
		
		<script language="javascript">
		function ConfirmDelete(keyName)
		{
			if(confirm("Are you sure you want to delete " + keyName + "?")) {
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
									<a href="/ssh/">
										SSH
									</a>
								</li>
								<li>
									<i class="active"></i>
									
										Keys
							
								</li>
					
							</ol>
							<div class="page-header">
								<h1>Keys for <?php print $domainName; ?></h1>
							</div>
							<!-- end: PAGE TITLE & BREADCRUMB -->
						</div>
					</div>
					<!-- end: PAGE HEADER -->
					<!-- start: PAGE CONTENT -->

					<div class="row">
					<?php
					if(isset($_REQUEST["Notes"])) {
						$NoteType = "Message";
						
						if(isset($_REQUEST["NoteType"])) {
							$NoteType = $_REQUEST["NoteType"];
						}
						
						if($NoteType == "Error") {
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

									<p>Manage <?php print $domainName; ?>'s SSH keys here<br>
									<a href="https://docs.webcp.io/docs/ssh/" target="_new">View Documentation</a>
									</p>

									<table class="table table-bordered table-full-width table-hover table-striped" id="sample_1">
										<thead>
											<tr>
												<th>Key Name</th>
												<th>Created</th>
												<th>Authorised <a href="https://docs.webcp.io/docs/ssh/authorising-ssh-keys/" target="_new"><img src="/img/help.png" width="20px"></a></th>
												<th>&nbsp;</th>
											</tr>
										</thead>
										
										
										<tbody>

										<?php
										$oEmail = new Email();

										$ClientID = $oUser->ClientID;

										if(isset($_REQUEST["ClientID"])) {
											if($oUser->Role == "admin") {
												//yes, permission..
												$ClientID = $_REQUEST["ClientID"];
											}
										}
										
										for($x = 0; $x < count($publicKeyList); $x++) {
											print "<tr>";
											print "<td>".$publicKeyList[$x]["publicKeyName"]."</td>\r\n";
										
											print "<td>".date("F jS, Y", strtotime($publicKeyList[$x]["date"]))."</td>\r\n";

											$nonceArray = [	
												$oUser->Role,
												$oUser->ClientID,
												$publicKeyList[$x]["id"],
												$domainId
											];
											$nonce = $oSimpleNonce->GenerateNonce("pubKeyAuthorisation", $nonceArray);
											

											print "<td><a href=\"#\" data-nonce-timestamp=\"".$nonce["TimeStamp"]."\" data-domain-id=\"".$domainId."\" data-nonce-value=\"".$nonce["Nonce"]."\" id=\"ssh_key_authorise_".$publicKeyList[$x]["id"]."\" data-type=\"select\" data-pk=\"".$publicKeyList[$x]["id"]."\" data-value=\"".$publicKeyList[$x]["authorised"]."\" data-original-title=\"Select Authorisation\"></a></td>\r\n";	


											print "<td class=\"center\">";
												print "<div class=\"visible-md visible-lg hidden-sm hidden-xs\">";

													$nonceArray = [	
														$oUser->Role,
														$oUser->ClientID,
														$publicKeyList[$x]["id"],
														$domainId
													];
													$nonce = $oSimpleNonce->GenerateNonce("deleteSSHKey", $nonceArray);
													

												
													print "<a href=\"deleteKey.php?nonce=".$nonce["Nonce"]."&timeStamp=".$nonce["TimeStamp"]."&domainId=".$domainId."&keyId=".$publicKeyList[$x]["id"]."\" onclick=\"return ConfirmDelete('".$publicKeyList[$x]["publicKeyName"]."'); return false;\" class=\"btn btn-bricky tooltips\" data-placement=\"top\" data-original-title=\"Delete Public Key\"><i class=\"fa fa-times fa fa-white\" style=\"color:white;\"></i></a>\n";

												
													print "</div>";
													
													print "<div class=\"visible-xs visible-sm hidden-md hidden-lg\">";
													
													print "<div class=\"btn-group\">";
													print "<a class=\"btn btn-primary dropdown-toggle btn-sm\" data-toggle=\"dropdown\" href=\"#\">";
														print "<i class=\"fa fa-cog\"></i> <span class=\"caret\"></span>";
													print "</a>";

													print "<ul role=\"menu\" class=\"dropdown-menu pull-right\">";


														print "<li role=\"presentation\">";
															print "<a role=\"menuitem\" tabindex=\"-1\" href=\"deleteKey.php?nonce=".$nonce["Nonce"]."&timeStamp=".$nonce["TimeStamp"]."&domainId=".$domainId."&keyId=".$publicKeyList[$x]["id"]."\" onclick=\"return ConfirmDelete('".$publicKeyList[$x]["publicKeyName"]."'); return false;\">";
																print "<i class=\"fa fa-times\"></i> Delete Public Key";
															print "</a>";
														print "</li>";															

													print "</ul>";
												print "</div>";
												print "</div>";
											print "</td>";	


											print "</tr>";
										}
										?>

									</tbody>
									
									</table>
							

									<a class="btn btn-primary" href="addKey.php?domainId=<?php print $domainId; ?>"><i class="fa fa-plus"></i>
										Add New Public Key
									</a>


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




		<script src="/assets/plugins/bootstrap-modal/js/bootstrap-modal.js"></script>

		<script src="/assets/plugins/bootstrap-modal/js/bootstrap-modalmanager.js"></script>

		<script src="/assets/js/ui-modals.js"></script>

		<script src="/assets/plugins/jquery-mockjax/jquery.mockjax.js"></script>

		<script src="/assets/plugins/x-editable/js/bootstrap-editable.min.js"></script>

		<script src="/assets/plugins/x-editable/ssh-key-authorise.js"></script>

		<script src="/assets/plugins/x-editable/demo.js"></script>


		<script>
			jQuery(document).ready(function() {
				Main.init();
				TableData.init();
				//UIModals();
			});
		</script>
	</body>
	<!-- end: BODY -->
</html>
