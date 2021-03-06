<?php
session_start();

include_once($_SERVER["DOCUMENT_ROOT"]."/vendor/autoload.php");

$oUser = new User();
$oSettings = new Settings();
$oDomain = new Domain();


$ClientID = $oUser->getClientId();
if($ClientID < 1)
{
	header("Location: /index.php");
	exit();
}

$Role = $oUser->Role;



function DomainInArray($LookInArray, $Domain)
{
	for($x = 0; $x < count($LookInArray); $x++)
	{
		if($LookInArray[$x]["Domain"] == $Domain)
		{
			return true;
		}
	}

	return false;

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
		<title>Email DKIM | <?php print $oSettings->GetWebCPTitle(); ?></title>
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
                                                                        <a href="/emails/">
                                                                                Emails
                                                                        </a>
                                                                </li>
                                                                <li>
                                                                        <i class="active"></i>
                                                                        <a href="/emails/dkim">
                                                                                DKIM
                                                                        </a>
                                                                </li>

					
							</ol>
							<div class="page-header">
								<h1>DKIM <small>Manage email domain's DKIM</small></h1>
							</div>
							<!-- end: PAGE TITLE & BREADCRUMB -->
						</div>
					</div>
					<!-- end: PAGE HEADER -->
					<!-- start: PAGE CONTENT -->

<?php
$PublicKey = "";
if( file_exists("/etc/exim4/dkim.public.key") ) {
	$PublicKey = file_get_contents("/etc/exim4/dkim.public.key");

	$x = strpos($PublicKey, "-----BEGIN PUBLIC KEY-----");

	if( $x !== false) {
		$PublicKey = trim(substr($PublicKey, $x + strlen("-----BEGIN PUBLIC KEY-----")));
	}

	$x = strpos($PublicKey, "--");
		
	if( $x !== false) {
		$PublicKey = trim(substr($PublicKey, 0, $x));
	}

	$PublicKey = str_replace("\r\n", "", $PublicKey);
	$PublicKey = str_replace("\n", "", $PublicKey);

} else {
	print "<h2>Dkim keys not yet set up. This could take up to 24 hours. If you've waited more than 24 hours, please contact WebCP.pw support</h2>";
	exit();
}
?>
					<div class="row">
					
						<div class="col-md-12">
							<b>Note: </b> You cannot simply turn DKIM on. You MUST create the DNS record with your DNS provider (if you're using WebCP's built in DNS its automatic and then you can just turn it on here!)

<p>&nbsp;<br>
Once you've turned DKIM on here you must create a <b>txt</b> record for: 
<p>&nbsp;<p><b>x._domainkey.<i>YOUR_DOMAIN</i></b> with a value of:<p>&nbsp;
<p style="-ms-word-break: break-all; word-break: break-all; word-break: break-word;">v=DKIM1; k=rsa; p=<?php print $PublicKey; ?></p>

<p>&nbsp;<br>
<b>NOTE:</b> Switch <i>YOUR_DOMAIN</i> for your actual domain, eg, x._domainkey.example.com

<p>&nbsp;<br>
See our post about <a href="https://webcp.io/spf-dkim-dmarc" target="_blank">SPF, DKIM and DMARC</a> for more info.
</p>
						</div>
					</div>
			
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

	Turn DKIM on or off for a domain<p>
							<!-- start: DYNAMIC TABLE PANEL -->
							<div class="panel panel-default">
									
								<div class="panel-body">
									<table class="table table-bordered table-full-width table-hover table-striped" id="sample_1">
										<thead>
											<tr>
												<th>Domain</th>
												<th>DKIM</th>
												
											</tr>
										</thead>
										
										
										<tbody>

										<?php
										$oEmail = new Email();

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

										$oDomain->GetDomainList($DkimArray, $DkimArrayCount, $ClientID, $oUser->Role);

										for($x = 0; $x < $DkimArrayCount; $x++)
										{
											print "<tr>";
											print "<td>".$DkimArray[$x]["domain_name"]."</td>\r\n";	
											print "<td><a href=\"#\" id=\"dkim_".$x."\" data-type=\"select\" data-pk=\"".$DkimArray[$x]["id"]."\" data-value=\"".((file_exists("/etc/exim4/dkim/".$DkimArray[$x]["domain_name"]))?"enabled":"not_enabled")."\" data-original-title=\"Select Dkim\"></a></td>\r\n";	
												

												print "</tr>";

										}
										?>
	
									</tbody>
									
									</table>
							




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

		<script src="/assets/plugins/jquery-mockjax/jquery.mockjax.js"></script>

		<script src="/assets/plugins/x-editable/js/bootstrap-editable.min.js"></script>

		<script src="/assets/plugins/x-editable/dkim-save.js"></script>

		<script src="/assets/plugins/x-editable/demo.js"></script>

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
