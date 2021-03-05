<?php
session_start();

include_once($_SERVER["DOCUMENT_ROOT"]."/vendor/autoload.php");

$oUser = new User();
$oPackage = new Package();
$oSettings = new Settings();
$oDatabase = new Database();
$oLog = new Log();
$oSimpleNonce = new SimpleNonce();


require($_SERVER["DOCUMENT_ROOT"]."/includes/License.inc.php");

$ClientID = $oUser->getClientId();
if($ClientID < 1) {
	$oLog->WriteLog("DEBUG", "/ssh/index.php -> client_id not set, redirecting to /index.php");
	header("Location: /index.php");
	exit();
}

$nonceArray = [
	$oUser->Role,
	$ClientID,
	"ssh"
];

$nonce = $oSimpleNonce->GenerateNonce("tableExists", $nonceArray);

if($oDatabase->TableExists("ssh", $nonce) == false) {

	$TableName = "ssh";

	$TableInfoArray[0]["name"] = "id";
	$TableInfoArray[0]["type"] = "int";
	$TableInfoArray[0]["key"] = "primary key auto_increment";
	$TableInfoArray[0]["default"] = "";

	$TableInfoArray[1]["name"] = "domain_id";
	$TableInfoArray[1]["type"] = "int";
	$TableInfoArray[1]["key"] = "";
	$TableInfoArray[1]["default"] = "";

	$TableInfoArray[2]["name"] = "public_key_name";
	$TableInfoArray[2]["type"] = "text";
	$TableInfoArray[2]["key"] = "";
	$TableInfoArray[2]["default"] = "";

	$TableInfoArray[3]["name"] = "file_name";
	$TableInfoArray[3]["type"] = "text";
	$TableInfoArray[3]["key"] = "";
	$TableInfoArray[3]["default"] = "";

	$TableInfoArray[4]["name"] = "authorised";
	$TableInfoArray[4]["type"] = "int";
	$TableInfoArray[4]["key"] = "";
	$TableInfoArray[4]["default"] = "0";

	$TableInfoArray[5]["name"] = "date";
	$TableInfoArray[5]["type"] = "datetime";
	$TableInfoArray[5]["key"] = "";
	$TableInfoArray[5]["default"] = "";

	$TableInfoArray[6]["name"] = "deleted";
	$TableInfoArray[6]["type"] = "int";
	$TableInfoArray[6]["key"] = "";
	$TableInfoArray[6]["default"] = "0";

	$nonceArray = [
		$oUser->Role,
		$ClientID,
		$TableName
	];
	
	$nonce = $oSimpleNonce->GenerateNonce("createTableFromArray", $nonceArray);
	
	$oDatabase->CreateTableFromArray($TableName, $TableInfoArray, $nonce);
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
									<i class="active"></i>
									<a href="/ssh/">
										SSH Manager
									</a>
								</li>
					
							</ol>
							<div class="page-header">
								<h1>SSH Manager <small>manage ssh keys</small></h1>
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

									<p>Click on a domain in the list to manage SSH access for that domain</p>

									<table class="table table-bordered table-full-width table-hover table-striped">
										<thead>
											<tr>
												<th>Domain</th>
												<th>User Name</th>
											</tr>
										</thead>
										
										
										<tbody>

										<?php
										$oDomain = new Domain();

										$ClientID = $oUser->ClientID;

										if(isset($_REQUEST["ClientID"])) {
											if($oUser->Role == "admin") {
												//yes, permission..
												$ClientID = $_REQUEST["ClientID"];
											}
										}
										//print "ClientID: ".$ClientID."<p>";
										//print "Role: ".$oUser->Role."<p>";

										$oDomain->GetDomainList($Array, $ArrayCount, $ClientID, $oUser->Role);

										for($x = 0; $x < $ArrayCount; $x++) {

											$Action = "logInEditor";
											$Meta = array();
											array_push($Meta, $_SERVER["SERVER_ADDR"]);
											array_push($Meta, $Array[$x]["domain_name"]);
		
											$NonceValues = $oSimpleNonce->GenerateNonce($Action, $Meta);


											if($Array[$x]["type"] == 'primary') {
												print "<tr>";

												print "<td><a href=\"/ssh/keys.php?domainId=".$Array[$x]["id"]."\" style=\"background:transparent; color:#4D81CC; border: 0;\">".$Array[$x]["domain_name"]."</a></td>\r\n";
												print "<td>".$Array[$x]["username"]."</td>\r\n";
												
												print "</tr>";
											}
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