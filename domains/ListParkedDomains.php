<?php
session_start();

include_once($_SERVER["DOCUMENT_ROOT"]."/vendor/autoload.php");

$oUser = new User();
$oPackage = new Package();
$oSettings = new Settings();
$oSimpleNonce = new SimpleNonce();

$ClientID = $oUser->getClientId();
if($ClientID < 1) {
	header("Location: /index.php");
	exit();
}
	
$DomainID = 0;
if(isset($_REQUEST["DomainID"])) {
	$DomainID = intVal($_REQUEST["DomainID"]);
}

if( (! is_numeric($DomainID)) || ($DomainID < 1) ) {
	header("Location: /index.php");
	exit();
}

	$oDomain = new Domain();
	$ClientID = $oUser->ClientID;

	$random = random_int(1, 100000);
	$nonceArray = [
		$oUser->Role,
		$oUser->ClientID,
		$DomainID,
		$random
	];
	
	$nonce = $oSimpleNonce->GenerateNonce("getDomainOwner", $nonceArray);
	$DomainOwnerClientID = $oDomain->GetDomainOwner($DomainID, $random, $nonce);

	$random = random_int(1,100000);
	$nonceArray = [
		$oUser->Role,
		$oUser->getClientId(),
		$DomainID,
		$random
	];
	
	$nonce = $oSimpleNonce->GenerateNonce("getDomainNameFromDomainID", $nonceArray);
	$PrimaryDomainName = $oDomain->GetDomainNameFromDomainID($DomainID, $random, $nonce);

	//print "ClientID: ".$ClientID."<p>";
	//print "DomainOwnerClientID: ".$DomainOwnerClientID."<p>";
	//print "Role: ".$oUser->Role."<p>";

	if( ($DomainOwnerClientID != $ClientID) && ($oUser->Role != 'admin') ) {
		throw new Exception("You do not have permission to be here...");
	}


        $DomainUserName = "";
        $ParkedDomainAllowance = -1;
        $ParkedDomainUsage = -1;

        if($DomainID > -1)
        {
				$DomainInfoArray = array();
				

				$random = random_int(1, 1000000);
				$nonceArray = [	
					$oUser->Role,
					$oUser->ClientID,
					$DomainID,
					$random
				];
				$nonce = $oSimpleNonce->GenerateNonce("getDomainInfo", $nonceArray);		
                $oDomain->GetDomainInfo($DomainID, $random, $DomainInfoArray, $nonce);

                $DomainUserName = $DomainInfoArray["UserName"];
                $ParkedDomainAllowance = $oPackage->GetPackageAllowance("ParkedDomains", $DomainInfoArray["PackageID"]);
                $ParkedDomainUsage = $oPackage->GetParkedDomainUsage($DomainUserName);

                //print "ParkedDomainAllowance: ".$ParkedDomainAllowance."<br>";
                //print "ParkedDomainUsage: ".$ParkedDomainUsage."<br>";
                //print "DomainID: ".$DomainID."<br>";
                //print "DomainUserName: ".$DomainUserName."<br>";

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
		<title>Parked domain Management | <?php print $oSettings->GetWebCPTitle(); ?></title>
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

                <link rel="stylesheet" href="/assets/plugins/x-editable/css/bootstrap-editable.css">

		
		
		<script language="javascript">
		function ConfirmDelete(DomainName)
		{

			if(confirm("Are you sure you want to delete the parked domain " + DomainName + "?\r\nWARNING: This will delete all files, emails, FTP and database accounts and cannot be reversed"))
			{
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
									<a href="/domains/">
										Domains
									</a>
								</li>
								<li>
									<i class="active"></i>
									<a href="/domains/ListParkedDomains.php?DomainID=<?php print $_REQUEST["DomainID"]; ?>">
										Parked Domains
									</a>
								</li>
					
							</ol>
							<div class="page-header">
								<h1>Parked Domains for <?php print $PrimaryDomainName; ?><small>Add / remove</small></h1>
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
										if( ($ParkedDomainUsage < $ParkedDomainAllowance) || ($ParkedDomainAllowance == -1))
										{
										?>
											<div class="alert alert-block alert-success fade in">
											<button data-dismiss="alert" class="close" type="button">
											&times;
											</button>
											<h4 class="alert-heading"><i class="fa fa-check-circle"></i>Parked Domains Available!</h4>
											<p>
												You have used <?php print $ParkedDomainUsage." of ".(($ParkedDomainAllowance == -1)? "unlimited": $ParkedDomainAllowance); ?> Parked Domains
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
												You have used <?php print $ParkedDomainUsage." of ".(($ParkedDomainAllowance == -1)? "unlimited": $ParkedDomainAllowance); ?> Parked Domains
											</p>
											</div>
										<?php
										}
									}
									?>


							<!-- start: DYNAMIC TABLE PANEL -->
							<div class="panel panel-default">
									
								<div class="panel-body">
									<table class="table table-bordered table-full-width table-striped table-hover" id="sample_1">
										<thead>
											<tr>
												<th>Parked Domain</th>
												<th>Redirect <a href="https://webcp.io/parked-redirect/" target="_new"><img src="/img/help.png" width="20px"></a></th>
												<th>Parked On</th>
												<th>&nbsp;</th>
											</tr>
										</thead>
										
										<tbody>

										<?php
										$oDomain = new Domain();

										$oDomain->GetParkedDomainList($Array, $ArrayCount, $DomainID, $DomainOwnerClientID, $oUser->Role);

										for($x = 0; $x < $ArrayCount; $x++)
										{
											print "<tr>";
											print "<td><a href=\"http://".$Array[$x]["ParkedDomain"]."\" target=\"_new\">".$Array[$x]["ParkedDomain"]."</a></td>\r\n";

                                                                                        $parkedRedirect = "";
                                                                                        $domainSettings = $oDomain->getDomainSettings($Array[$x]["ID"]);

                                                                                        if (isset($domainSettings["parked_redirect"]["value"])) {
                                                                                                $parkedRedirect = $domainSettings["parked_redirect"]["value"];
                                                                                        }

											print "<td><a href=\"#\" id=\"parked_redirect_".$Array[$x]["ID"]."\" data-type=\"select\" data-pk=\"".$Array[$x]["ID"]."\" data-value=\"".$parkedRedirect."\" data-original-title=\"Select Redirect\"></a></td>\r\n";

											print "<td>".$Array[$x]["ParkedOn"]."</td>\r\n";

											print "<td class=\"center\">";
											print "<div class=\"visible-md visible-lg hidden-sm hidden-xs\">";
											print "<a href=\"DeleteParkedDomain.php?parentDomainId=".$DomainID."&ParkedDomainID=".$Array[$x]["ID"]."\" onclick=\"return ConfirmDelete('".$Array[$x]["ParkedDomain"]."'); return false;\" class=\"btn btn-bricky tooltips\" data-placement=\"top\" data-original-title=\"Delete Parked Domain\"><i class=\"fa fa-times fa fa-white\" style=\"color:white;\"></i></a>\n";
											print "</div>";
											print "<div class=\"visible-xs visible-sm hidden-md hidden-lg\">";
											print "<div class=\"btn-group\">";
											print "<a class=\"btn btn-primary dropdown-toggle btn-sm\" data-toggle=\"dropdown\" href=\"#\">";
											print "<i class=\"fa fa-cog\"></i> <span class=\"caret\"></span>";
											print "</a>";
											print "<ul role=\"menu\" class=\"dropdown-menu pull-right\">";
															
											print "<li role=\"presentation\">";
											print "<a role=\"menuitem\" tabindex=\"-1\" href=\"DeleteParkedDomain.php?parentDomainId=".$DomainID."&ParkedDomainID=".$Array[$x]["ID"]."\" onclick=\"return ConfirmDelete('".$Array[$x]["ParkedDomain"]."'); return false;\">";
											print "<i class=\"fa fa-times\"></i> Delete Parked Domain";
											print "</a>";
											print "</li>";																
											print "</ul>";
											print "</div>";
											print "</div></td>";				
											print "</tr>";
										}
											
										?>
	
									</tbody>
									
									</table>


                                                                        <?php
                                                                        if( (($ParkedDomainUsage >= $ParkedDomainAllowance) && ($ParkedDomainAllowance != -1) ) && ($oUser->Role != 'admin') )
                                                                        {       
                                                                                print "<span class=\"label label-danger\">You have used all of your parked domains!</span>";
                                                                        }
                                                                        else    
                                                                        {
                                                                        ?>
										<div class="form-group">										
										<div class="col-sm-4">
                                                                                <form action="AddParkedDomain.php" method="post">
                                                                                <input type="hidden" value="<?php print $DomainID; ?>" name="DomainID">
										<input type="submit" value="Add new parked domain" data-style="zoom-in" class="btn btn-info ladda-button">
										<span class="ladda-spinner"></span>
										<span class="ladda-progress" style="width: 0px;"></span>
										</input>
                                                                                </form>
										</div>
										</div>

                                                                        <?php   
                                                                        }
                                                                        ?>
							
										
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





                <script src="/assets/plugins/bootstrap-modal/js/bootstrap-modal.js"></script>

                <script src="/assets/plugins/bootstrap-modal/js/bootstrap-modalmanager.js"></script>

                <script src="/assets/js/ui-modals.js"></script>

                <script src="/assets/plugins/jquery-mockjax/jquery.mockjax.js"></script>

                <script src="/assets/plugins/x-editable/js/bootstrap-editable.min.js"></script>

                <script src="/assets/plugins/x-editable/parked-redirect.js"></script>

                <script src="/assets/plugins/x-editable/demo.js"></script>




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
