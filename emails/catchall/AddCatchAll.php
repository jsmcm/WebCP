<?php
session_start();

include_once($_SERVER["DOCUMENT_ROOT"]."/vendor/autoload.php");
$oUser = new User();
$oEmail = new Email();
$oDomain = new Domain();
$oSettings = new Settings();

$ClientID = $oUser->getClientId();
if($ClientID < 1)
{
	header("Location: /index.php");
	exit();
}
$Role = $oUser->Role;

$DomainName = "";
$DomainOwnerID = -1;
$DomainID = -1;
$Catcher = "";

if(isset($_REQUEST["DomainID"]))
{
	$DomainID = $_REQUEST["DomainID"];
	

	$random = random_int(1, 1000000);
	$nonceArray = [	
		$oUser->Role,
		$oUser->ClientID,
		$DomainID,
		$random
	];
	$oSimpleNonce = new SimpleNonce();
	$nonce = $oSimpleNonce->GenerateNonce("getDomainInfo", $nonceArray);
	
	$InfoArray = array();
	$oDomain->GetDomainInfo($DomainID, $random, $InfoArray, $nonce);
	

	$DomainName = $InfoArray["DomainName"];
	$DomainOwnerID = $InfoArray["ClientID"];
	$Catcher = $oEmail->GetCatcher($DomainName);
}
else if(isset($_REQUEST["ID"]))
{
	$oEmail->GetCatchAllDetail($_REQUEST["ID"], $ClientID, $Role, $DomainName, $Catcher);

	$DomainOwnerID = $oDomain->GetClientIDFromDomainName($DomainName);
}
else
{
	header("location: index.php?Notes=Please select correct domain&NoteType=Error");
}


/*
print "<p>&nbsp;<p>&nbsp;<p>";
print "DomainID: ".$DomainID."<br>";
print "DomainOwnerID: ".$DomainOwnerID."<br>";
print "DomainName: ".$DomainName."<br>";
print "Catcher: ".$Catcher."<br>";
*/

?>

<!DOCTYPE html>
<!--[if IE 8]><html class="ie8 no-js" lang="en"><![endif]-->
<!--[if IE 9]><html class="ie9 no-js" lang="en"><![endif]-->
<!--[if !IE]><!-->
<html lang="en" class="no-js">
	<!--<![endif]-->
	<!-- start: HEAD -->
	<head>
		<title>Manage Catch All | <?php print $oSettings->GetWebCPTitle(); ?></title>
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

		function DoSubmit()
		{
			if(document.AddCatchAll.EmailAddress.value == "-1")
			{
				alert("Please select the email address");
				document.AddCatchAll.EmailAddress.focus();
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
									<a href="/emails/">
										Emails
									</a>
								</li>
								<li>
									<i class="active"></i>
									<a href="/emails/catchall">
										Catch All
									</a>
								</li>
					
							</ol>
							<div class="page-header">
								<h1>Catch All - <b><?php print $DomainName; ?></b></h1>
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
					

								<form name="AddCatchAll" method="post" action="DoAddCatchAll.php" class="form-horizontal">
									
<input type="hidden" name="DomainName" value="<?php print $DomainName;?>">

										<div class="form-group">
											<label class="col-sm-2 control-label">
												"Catcher" Email Address:
											</label>										
											<div class="col-sm-4">
												<span class="input-icon">								
												<select name="EmailAddress" class="form-control" id="email">
										
												<option value="-1">Select Email Address</option>				
								
												<?php
												$oEmail->GetEmailListForDomain($EmailArray, $ArrayCount, $ClientID, $Role, $DomainName);
												for($x = 0; $x < $ArrayCount; $x++)
												{
													print "<option value=\"".$EmailArray[$x]["local_part"]."@".$EmailArray[$x]["fqdn"]."\"";

														if($Catcher == $EmailArray[$x]["local_part"]."@".$EmailArray[$x]["fqdn"])
														{
															print " selected ";
														}

														print ">".$EmailArray[$x]["local_part"]."@".$EmailArray[$x]["fqdn"]."</option>";
												}
												?>
											</select>											
												</span>
											</div>
										</div>
									
										<p>&nbsp;<p>

											<div class="col-sm-4">
		<input type="submit" value="Save Catch All" data-style="zoom-in" class="btn btn-info ladda-button" onclick="return DoSubmit(); return false;">
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
