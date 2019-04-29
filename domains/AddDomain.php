<?php
session_start();

include_once($_SERVER["DOCUMENT_ROOT"]."/vendor/autoload.php");
$oUser = new User();
$oPackage = new Package();
$oLog = new Log();
$oUtils = new Utils();
$oDNS = new DNS();
$oDomain = new Domain();
$oReseller = new Reseller();
$oSettings = new Settings();

require($_SERVER["DOCUMENT_ROOT"]."/includes/License.inc.php");

$serverAccountsCreated = $oDomain->GetAccountsCreatedCount();
$serverAccountsAllowed = $validationArray["allowed"];
$serverLicenseType = $validationArray["type"];


if ( $serverLicenseType == "free" && ($serverAccountsCreated >= $serverAccountsAllowed) ) {
	header("Location: index.php?Notes=".htmlentities("You are on a free license. Please upgrade to add more accounts")."&NoteType=error");
	exit();
}


$oDNS->ManageIPAddresses();
$oLog->WriteLog("DEBUG", "/domains/index.php...");

$ClientID = $oUser->getClientId();
if($ClientID < 1)
{
	$oLog->WriteLog("DEBUG", "/domains/index.php -> client_id not set, redirecting to /index.php");
	header("Location: /index.php");
	exit();
}
	
if( ! (($oUser->Role == "admin") || ($oUser->Role == "reseller")) )
{
	header("Location: ./index.php?NoteType=Error&Notes=No Permissions");
	exit();
}

$oLog->WriteLog("DEBUG", "/domains/index.php -> client_id set, continuing");

$TotalDiskSpace = 0;
$TotalDiskSpaceUsed = 0;
$TotalDiskSpaceAvailable = 0;
$PercentageDiskSpaceUsed = 0;

$TotalTraffic = 0;
$TotalTrafficUsed = 0;
$TotalTrafficAvailable = 0;
$PercentageTrafficUsed = 0;

//$oUtils->GetTrafficStats($TotalTraffic, $TotalTrafficUsed, $TotalTrafficAvailable, $PercentageTrafficUsed);
//$oUtils->GetDiskSpaceStats($TotalDiskSpace, $TotalDiskSpaceUsed, $TotalDiskSpaceAvailable, $PercentageDiskSpaceUsed);



$Accounts = -1;
if($oUser->Role == "admin")
{
	$TotalDiskSpace = $oPackage->GetTotalDiskSpace();
	$TotalDiskSpaceUsed = $oDomain->GetPackageDiskSpaceUsage();

        $TotalTraffic = $oSettings->GetServerTrafficAllowance();
        $TotalTrafficUsed = $oDomain->GetPackageTrafficUsage();
}
else
{
	$TotalDiskSpace = $oReseller->GetDiskSpaceAllocation($oUser->ClientID);
	$TotalDiskSpaceUsed = $oDomain->GetPackageDiskSpaceUsage($oUser->ClientID);
	
	$Accounts = $oReseller->GetAccountsLimit($oUser->ClientID);
	$AccountsCreated = $oReseller->GetAccountsCreatedCount($oUser->ClientID);	
        
	$TotalTraffic = $oReseller->GetTrafficAllocation($oUser->ClientID);
        $TotalTrafficUsed = $oDomain->GetPackageDiskSpaceUsage($oUser->ClientID);	

	if( ($Accounts > -1) && ($AccountsCreated >= $Accounts) )
	{
		header("Location: ./index.php?Notes=You've created the max number of accounts allocated&NoteType=error");
		exit();
	}

	if($TotalDiskSpaceUsed >= $TotalDiskSpace)
	{
		header("Location: ./index.php?Notes=You've used all available disk space&NoteType=error");
		exit();
	}

	if($TotalTrafficUsed >= $TotalTraffic)
	{
		header("Location: ./index.php?Notes=You've used all available traffic&NoteType=error");
		exit();
	}

}

$PercentageDiskSpaceUsed = 0;
if($TotalDiskSpace > 0)
{
	$PercentageDiskSpaceUsed = $TotalDiskSpaceUsed / $TotalDiskSpace * 100;
}
$TotalDiskSpaceAvailable = $TotalDiskSpace - $TotalDiskSpaceUsed;


$TotalTrafficAvailable = 0;
$PercentageTrafficUsed = 0;
if($TotalTraffic > 0)
{
	$PercentageTrafficUsed = $TotalTrafficUsed / $TotalTraffic * 100;
}
$TotalTrafficAvailable = $TotalTraffic - $TotalTrafficUsed;

/*
print "<p>&nbsp;<p>&nbsp;<p>";
print "TotalTraffic: ".$TotalTraffic." - ".gettype($TotalTraffic)."<br>";
print "TotalTrafficUsed: ".$TotalTrafficUsed."- ".gettype($TotalTrafficUsed)."<br>";
print "TotalTrafficAvailable: ".$TotalTrafficAvailable." - ".gettype($TotalTrafficAvailable)."<br>";
print "PercentageTrafficUsed: ".$PercentageTrafficUsed."- ".gettype($PercentageTrafficUsed)."<p>";

print "TotalDiskSpace: ".$TotalDiskSpace." - ".gettype($TotalDiskSpace)."<br>";
print "TotalDiskSpaceUsed: ".$TotalDiskSpaceUsed."- ".gettype($TotalDiskSpaceUsed)."<br>";
print "TotalDiskSpaceAvailable: ".$TotalDiskSpaceAvailable." - ".gettype($TotalDiskSpaceAvailable)."<br>";
print "PercentageDiskSpaceUsed: ".$PercentageDiskSpaceUsed."- ".gettype($PercentageDiskSpaceUsed)."<br>";
*/

function ConvertToDouble($Text)
{
	$Text = (double)trim(substr($Text, 0, strpos($Text, " ")));

	return $Text;
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
		<title>Add Domain | <?php print $oSettings->GetWebCPTitle(); ?></title>
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


		function ValidateForm()
		{

			DomainName = document.AddDomain.DomainName.value;

			if(DomainName.indexOf("#") > -1)
			{
				alert("ERROR!!!\r\nIllegal character, domain names can contain -, but not _ or $%#@!, etc....");
				document.AddDomain.DomainName.focus();
				return false;
				
			}
			
			if(DomainName.indexOf("%") > -1)
			{
				alert("ERROR!!!\r\nIllegal character, domain names can contain -, but not _ or $%#@!, etc....");
				document.AddDomain.DomainName.focus();
				return false;
				
			}


			if(DomainName.indexOf("_") > -1)
			{
				alert("ERROR!!!\r\nIllegal character, domain names can contain -, but not _ or $%#@!, etc....");
				document.AddDomain.DomainName.focus();
				return false;
				
			}

			if(document.AddDomain.DomainName.value == "")
			{
				alert("ERROR!!!\r\nPlease enter a domain name....");
				document.AddDomain.DomainName.focus();
				return false;
			}

			
			if(document.AddDomain.PackageID.value == "")
			{
				alert("ERROR!!!\r\nPlease select the package....");
				document.AddDomain.PackageID.focus();
				return false;
			}
			
			if(document.AddDomain.PackageID.value == "-2")
			{
				alert("ERROR!!!\r\nYou cannot select that package as it will put you over quota....");
				document.AddDomain.PackageID.focus();
				return false;
			}
			
			if(document.AddDomain.ClientID.value == "")
			{
				alert("ERROR!!!\r\nPlease select the user who this domain will belong to....");
				document.AddDomain.ClientID.focus();
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
							
									<a href="/domains/index.php">
										Domains
									</a>
								</li>
					
								<li>
									<i class="active"></i>
									<a href="/domains/AddDomain.php">
										Add
									</a>
								</li>
					
							</ol>
							<div class="page-header">
								<h1>Add Domains </h1>
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
					

								<form name="AddDomain" method="post" action="DoAddDomain.php" class="form-horizontal">
									
										<input type="hidden" name="DomainType" value="primary">

										<div class="form-group">
											<label class="col-sm-2 control-label">
												Domain Name:
											</label>										
											<div class="col-sm-4">
												<span class="input-icon">
												<input name="DomainName" type="text" placeholder="domain name" id="form-field-11" class="form-control">
												<i class="fa clip-globe"></i>
												</span>
											</div>
										</div>
										
								
										<div class="form-group">
											<label class="col-sm-2 control-label">
												Domain Package:
											</label>
											<div class="col-sm-4">
												<span class="input-icon">
												<select id="form-field-select-1" class="form-control" name="PackageID" style="padding-left:20px;">
													<option value="">select...</option>
													<?php
													$oPackage->GetPackageList($Array, $ArrayCount, $oUser->Role, $oUser->ClientID);

													$LimitReached = 0;
													for($x = 0; $x < $ArrayCount; $x++)
		    											{
													       $TrafficAllowance = $oPackage->GetPackageAllowance("traffic", $Array[$x]["package_id"]);
													       $DiskSpaceAllowance = $oPackage->GetPackageAllowance("diskspace", $Array[$x]["package_id"]);

//print "\nDiskSpace Allowance: ".$DiskSpaceAllowance." - ".gettype($DiskSpaceAllowance)."\n";
//print "\nTraffic Allowance: ".$TrafficAllowance." - ".gettype($TrafficAllowance)."\n";

														if( ($TotalTrafficAvailable >= $TrafficAllowance) && ($TotalDiskSpaceAvailable >= $DiskSpaceAllowance) )
														{
															print "<option value=\"".$Array[$x]["package_id"]."\"";
														}
														else
														{
															print "<option style=\"color:red;\" value=\"-2\"";
															$LimitReached = 1;
														}
						


														print ">".$Array[$x]["package_name"]."</option>";
													}

													?>
												</select>

												<?php
												if($LimitReached == 1)
												{
													print "<font color=\"red\">NOTE: packages in red cannot be used as the server quota has been reached or exceeded for those values</font><p>";
												}
												else
												{
													print "<i class=\"fa clip-list-4\"></i>";
												}
												?>
												</span>										
											</div>
										</div>
									



										<div class="form-group">
											<label class="col-sm-2 control-label">
												Domain Owner:
											</label>
											<div class="col-sm-4">
												<span class="input-icon">
												<select id="form-field-select-1" class="form-control" name="ClientID" style="padding-left:20px;">
													<option value="">select...</option>
													<?php
													$oUser->GetUserList($Array, $ArrayCount, $oUser->ClientID, $oUser->Role);

													for($x = 0; $x < $ArrayCount; $x++)
													{
														print "<option value=\"".$Array[$x]["id"]."\">".$Array[$x]["first_name"]." ".$Array[$x]["surname"]." (".$Array[$x]["username"].")</option>";
													}


													?>
												</select>
												<i class="fa clip-user-2"></i>
												</span>										
											</div>
										</div>

							
										<div class="form-group">										
											<div class="col-sm-4">
												<input type="submit" value="Add Domain" data-style="zoom-in" class="btn btn-info ladda-button" onclick="return ValidateForm(); return false;">
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
