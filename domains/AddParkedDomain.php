<?php
session_start();


require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.User.php");
$oUser = new User();

require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.Package.php");
$oPackage = new Package();

require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.Domain.php");
$oDomain = new Domain();

require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.Settings.php");
$oSettings = new Settings();

require($_SERVER["DOCUMENT_ROOT"]."/includes/License.inc.php");

$ClientID = $oUser->getClientId();
if($ClientID < 1)
{
	header("Location: /index.php");
	exit();
}
$Role = $oUser->Role;
	


$DomainID = -1;

if(isset($_POST["DomainID"]))
{
	$DomainID = $_POST["DomainID"];
}

$DomainUserName = "";
$ParkedDomainAllowance = -1;
$ParkedDomainUsage = -1;


$ParkedDomain = "";
if(isset($_POST["ParkedDomain"]))
{
	$ParkedDomain = $_POST["ParkedDomain"];
}



$DomainOwnerClientID = -1;

if($DomainID > -1)
{
	$DomainInfoArray = array();
	$oDomain->GetDomainInfo($DomainID, $DomainInfoArray);

	$DomainUserName = $DomainInfoArray["UserName"];
	$ParkedDomainAllowance = $oPackage->GetPackageAllowance("ParkedDomains", $DomainInfoArray["PackageID"]);
	$ParkedDomainUsage = $oPackage->GetParkedDomainUsage($DomainUserName);
	$DomainOwnerClientID = $DomainInfoArray["ClientID"];

	/*
	print "ParkedDomainAllowance: ".$ParkedDomainAllowance."<br>";
	print "ParkedDomainUsage: ".$ParkedDomainUsage."<br>";
	print "DomainID: ".$DomainID."<br>";
	print "ClientID: ".$ClientID."<br>";
	print "DomainUserName: ".$DomainUserName."<br>";
	print "DomainOwnerClientID: ".$DomainOwnerClientID."<br>";
	*/

}

//print "ClientID: ".$ClientID."<br>";
//print "Role: ".$Role."<br>";

if($DomainOwnerClientID > 0)
{
	$ClientID = $DomainOwnerClientID;
	$Role = "client";
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
		<title>Add Parked Domain | <?php print $oSettings->GetWebCPTitle(); ?></title>
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

				function Redirect(DomainID)
				{
					if(DomainID > -1)
					{
						document.AddParkedDomain.action="AddParkedDomain.php";
						document.AddParkedDomain.submit();	
					}	
				}

				function DoSubmit()
				{

					if(document.AddParkedDomain.DomainID.value == "-1")
					{
						alert("please select the domain on which to park");
						document.AddParkedDomain.DomainID.focus();
						return false;
					}

					if(document.AddParkedDomain.ParkedDomain.value == "")
					{
						alert("Please enter a parked domain");
						document.AddParkedDomain.ParkedDomain.focus();
						return false;
					}



					DomainName = document.AddParkedDomain.ParkedDomain.value;

					if(DomainName.indexOf("#") > -1)
					{
						alert("ERROR!!!\r\nIllegal character, domain names can contain -, but not _ or $%#@!, etc....");
						document.AddParkedDomain.ParkedDomain.focus();
						return false;

					}

					if(DomainName.indexOf("%") > -1)
					{
						alert("ERROR!!!\r\nIllegal character, domain names can contain -, but not _ or $%#@!, etc....");
						document.AddParkedDomain.ParkedDomain.focus();
						return false;

					}


					if(DomainName.indexOf("_") > -1)
					{
						alert("ERROR!!!\r\nIllegal character, domain names can contain -, but not _ or $%#@!, etc....");
						document.AddParkedDomain.ParkedDomain.focus();
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
									<a href="/domains/">
										Domains
									</a>
								</li>
								<li>
									<i class="active"></i>
									<a href="/domains/AddParkedDomain.php">
										Add
									</a>
								</li>
					
							</ol>
							<div class="page-header">
								<h1>Add Parked Domain</h1>
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
						
	



									<?php
									if($DomainID != -1)
									{
										if( ($ParkedDomainUsage < $ParkedDomainAllowance) || ($ParkedDomainAllowance == -1) )
										{
										?>
											<div class="alert alert-block alert-success fade in">
											<button data-dismiss="alert" class="close" type="button">
											&times;
											</button>
											<h4 class="alert-heading"><i class="fa fa-check-circle"></i> Parked Domains Available!</h4>
											<p>
												You have used <?php print $ParkedDomainUsage." of ".(($ParkedDomainAllowance == -1)? "unlimited":$ParkedDomainAllowance); ?> parked domains
											</p>
											</div>
										<?php
										}
										else
										{
										?>
											<div class="alert alert-block alert-danger fade in">
											<button data-dismiss="alert" class="close" type="button">
											&times;
											</button>
											<h4 class="alert-heading"><i class="fa fa-times-circle"></i> No Parked Domains Available!</h4>
											<p>
												You have used <?php print $ParkedDomainUsage." of ".$ParkedDomainAllowance; ?> parked domains
											</p>
											</div>
										<?php
										}
									}
									?>
								
							<!-- start: DYNAMIC TABLE PANEL -->
							<div class="panel panel-default">
									
								<div class="panel-body">
					

								<form name="AddParkedDomain" method="post" action="DoAddParkedDomain.php" class="form-horizontal">
									

										<div class="form-group">
											<label class="col-sm-2 control-label">
											Existing Domain:
											</label>
											<div class="col-sm-4">
												<span class="input-icon">								
											<select name="DomainID" class="form-control" id="email" onchange="Redirect(this.value);">
										
												<option value="-1">Select Domain</option>				
								
												<?php
												$oDomain = new Domain();
												$oDomain->GetDomainList($DomainArray, $ArrayCount, $ClientID, $Role);
												for($x = 0; $x < $ArrayCount; $x++)
												{
													print "<option value=\"".$DomainArray[$x]["id"]."\"";

													if($DomainID == $DomainArray[$x]["id"])
													{
														print " selected ";
													}

													print ">".$DomainArray[$x]["domain_name"]."</option>";
												}
												?>
											</select>	
												</span>
											</div>
										</div>
										
								



										<div class="form-group">
											<label class="col-sm-2 control-label">
											Parked Domain:
											</label>

											<div class="col-sm-4">
												<span class="input-icon">								
												<input type="text" name="ParkedDomain" value="<?php print $ParkedDomain; ?>" placeholder="Parked Domain" class="form-control">
												</span>
											</div>
										</div>










							
                        							<?php
							                        if( ($DomainID > -1) && (($ParkedDomainUsage < $ParkedDomainAllowance) || ($ParkedDomainAllowance == -1) || $oUser->Role == "admin") )
							                        {
							                        ?>
										<div class="form-group">

											<div class="col-sm-4">
												<input type="submit" value="Add Parked Domain" data-style="zoom-in" class="btn btn-info ladda-button" onclick="return DoSubmit(); return false;">
													<span class="ladda-spinner"></span>
													<span class="ladda-progress" style="width: 0px;"></span>
												</input>
											</div>
										</div>
                        							<?php
							                        }
							                        ?>							



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
