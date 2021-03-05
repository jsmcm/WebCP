<?php
session_start();

include_once($_SERVER["DOCUMENT_ROOT"]."/vendor/autoload.php");

$oUser = new User();
$oSettings = new Settings();
$oUtils = new Utils();
$oSimpleNonce = new SimpleNonce();
$oDomain = new Domain();

require($_SERVER["DOCUMENT_ROOT"]."/includes/License.inc.php");

$ClientID = $oUser->getClientId();
if($ClientID < 1) {
	header("Location: /index.php");
	exit();
}




$domainName = "";
$domainId = 0;
$domainSettings = [];
$pmSettings = [];

if (isset($_GET["domain"])) {
	
	$domainName = filter_var($_GET["domain"], FILTER_SANITIZE_STRING);
    $domainId = $oDomain->DomainExists($domainName);
	$domainSettings = $oDomain->getDomainSettings($domainId); 
			
	$pmSettings["max_children"] = "25";
	$pmSettings["start_servers"] = "3";
	$pmSettings["min_spare_servers"] = "2";
	$pmSettings["max_spare_servers"] = "5";
	$pmSettings["max_requests"] = "1000";
	
}


$domainOwner = 0;
if ($domainName != "") {

    $nonceMeta = [
		$oUser->Role,
		$ClientID,
		$domainName
	];

    $nonce = $oSimpleNonce->GenerateNonce("getDomainOwnerFromDomainName", $nonceMeta);

    $domainOwner = $oDomain->GetDomainOwnerFromDomainName($domainName, $nonce);

}

if( ($domainOwner != $ClientID) && ($oUser->Role != "admin") ) {

	header("location: /domains/index.php?Notes=You don't have permission to be there&NoteType=error");
	exit();

}


$version = filter_var($_GET["version"], FILTER_SANITIZE_STRING);
$nonce = filter_var($_GET["nonce"], FILTER_SANITIZE_STRING);
$timeStamp = filter_var($_GET["timeStamp"], FILTER_SANITIZE_STRING);

    
/*
$nonceArray = [	
    $oUser->Role,
    $oUser->ClientID,
    $version
];

$nonceResult = $oSimpleNonce->VerifyNonce(
    $nonce, 
    "editPHPConfig", 
    $timeStamp, 
    $nonceArray
);


if ($nonceResult === false) {
    
    //header("location: index.php?Notes=Something went wrong, please try again.&noteType=error");
    //exit();
    
}
*/

//print "<p>&nbsp;<p>&nbsp;<p>&nbsp;<p>&nbsp;<p>&nbsp;<p>&nbsp;<p>&nbsp;<p>".print_r($domainSettings, true)."</p>";

$settings = file_get_contents("/etc/php/".$version."/fpm/php.ini");
$settingsArray = explode("\n", $settings);

$phpSettings = [];

// get the settings from the current php.ini file
foreach ($settingsArray as $setting) {

    if (strstr($setting, "=")) {
    
        $key = trim(substr($setting, 0, strpos($setting, "=")));
        $value = trim(substr($setting, strpos($setting, "=") + 1));

        $phpSettings[$key] = $value;

		// override custom values from the domain settings
        if (isset($domainSettings["php_".$version."_".$key])) {
            $phpSettings[$key] = $domainSettings["php_".$version."_".$key]["value"];
        }

    }

}


if (!empty($domainSettings)) {

	foreach ($domainSettings as $key => $values) {

		if (substr($key, 0, strlen("php_".$version."_")) == "php_".$version."_") {

            if (! isset($phpSettings[substr($key, strlen("php_".$version."_"))])) {

				$phpSettings[substr($key, strlen("php_".$version."_"))] = $values["value"];    

			} 

		} else if (substr($key, 0, strlen("php_pm_".$version."_")) == "php_pm_".$version."_") {
			
            if (! isset($phpSettings[substr($key, strlen("php_pm_".$version."_"))])) {

				$pmSettings[substr($key, strlen("php_pm_".$version."_"))] = $values["value"];    

			} 

		}

	}

}


// some basic pm validation
if (!empty($pmSettings)) {
    $validation = true;

	if (intVal($pmSettings["start_servers"]) < intVal($pmSettings["min_spare_servers"])) {
        $validation = false;
	}
	
	if (intVal($pmSettings["max_spare_servers"]) < intVal($pmSettings["min_spare_servers"])) {
        $validation = false;
    }


	if ($validation === false) {
		
		$pmSettings["max_children"] = "25";
		$pmSettings["start_servers"] = "3";
		$pmSettings["min_spare_servers"] = "2";
		$pmSettings["max_spare_servers"] = "5";
		$pmSettings["max_requests"] = "1000";

	}

}


$nonceArray = [	
    $oUser->Role,
    $oUser->ClientID,
    $version
];
$nonce = $oSimpleNonce->GenerateNonce("savePHPConfig", $nonceArray);


?>

<!DOCTYPE html>
<!--[if IE 8]><html class="ie8 no-js" lang="en"><![endif]-->
<!--[if IE 9]><html class="ie9 no-js" lang="en"><![endif]-->
<!--[if !IE]><!-->
<html lang="en" class="no-js">
	<!--<![endif]-->
	<!-- start: HEAD -->
	<head>
		<title>PHP Configuration | <?php print $oSettings->GetWebCPTitle(); ?></title>
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
									<a href="/server/">
										Server
									</a>
								</li>
								<li>
									<a href="/server/php/">
										PHP Settings
									</a>
								</li>
								<li>
									<i class="active"></i>
									<a href="/server/php/settings.php?version=<?php print $version; ?>">
										<?php print $version; ?>
									</a>
								</li>
					
					
							</ol>
							<div class="page-header">
								<h1>PHP <?php print $version; ?> Config
                                
                                <?php 
                                if ($domainName != "") {
                                    print " - ".$domainName;
                                }
                                ?>
                                </h1>
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
					


					<form name="SystemSettings" action="SavePHPSettings.php" method="post">
					
                    <input type="hidden" name="version" value="<?php print $version; ?>">
                    
                    <input type="hidden" name="nonce" value="<?php print $nonce["Nonce"]; ?>">
                    <input type="hidden" name="timeStamp" value="<?php print $nonce["TimeStamp"]; ?>">
                                   
                    <input type="hidden" name="domainId" value="<?php print $domainId; ?>">
                    <input type="hidden" name="domainName" value="<?php print $domainName; ?>">
                    
					<div class="col-md-12">

						<div class="panel panel-default">

							

                                <?php 
                                foreach ($phpSettings as $key => $value) {

                                
                                    if ($key == "zend_extension" || $key == "engine") {
                                       
                                        print "<input type=\"hidden\" name=\"".$key."\" value=\"".$value."\">";
                                        
                                        continue;

                                    }

                                ?>
                                <div class="panel-body" style="border-bottom: 1px solid lightgrey;">

                                <div class="form-group">
                                    <label class="col-sm-4 control-label" style="margin-top: 10px;">
                                    <b><?php print $key; ?></b>:
                                    </label>
                                    <div class="col-sm-8">
                                        
                                        <?php
                                        $key = str_replace(".", "-", $key);
                                        ?>

                                        <input name="<?php print $key; ?>" value="<?php print $value; ?>" type="text" class="form-control">
                                  
                                    </div>
                                </div>

                            
                                </div>
                                <?php
                                }
                                ?>
                            
						</div>
					</div>


					<?php
                    if ($domainId > 0 && $oUser->Role == "admin") {	
					?>

				
					<div class="col-sm-12" style="margin-top:3em;">
						<div class="page-header">
							<h1>PHP <?php print $version; ?> Process Manager</h1>
						</div>
					</div>
			
				
					<div class="col-md-12">

						<div class="panel panel-default">

		
							<div class="panel-body" style="border-bottom: 1px solid lightgrey;">

								<div class="form-group">
                                    <label class="col-sm-4 control-label" style="margin-top: 10px;">
                                    <b>pm.max_children</b>:
                                    </label>
                                    <div class="col-sm-8">
                                    
                                        <input name="pm-max_children" value="<?php print $pmSettings["max_children"]; ?>" type="text" class="form-control">
                                  
                                    </div>
                                </div>

                            </div>

							<div class="panel-body" style="border-bottom: 1px solid lightgrey;">

                                <div class="form-group">
                                    <label class="col-sm-4 control-label" style="margin-top: 10px;">
                                    <b>pm.start_servers</b>:
                                    </label>
                                    <div class="col-sm-8">
                                    
                                        <input name="pm-start_servers" value="<?php print $pmSettings["start_servers"]; ?>" type="text" class="form-control">
                                  
                                    </div>
                                </div>



							</div>

							<div class="panel-body" style="border-bottom: 1px solid lightgrey;">


                                <div class="form-group">
                                    <label class="col-sm-4 control-label" style="margin-top: 10px;">
                                    <b>pm.min_spare_servers</b>:
                                    </label>
                                    <div class="col-sm-8">
                                    
                                        <input name="pm-min_spare_servers" value="<?php print $pmSettings["min_spare_servers"]; ?>" type="text" class="form-control">
                                  
                                    </div>
                                </div>


							</div>

							<div class="panel-body" style="border-bottom: 1px solid lightgrey;">



                                <div class="form-group">
                                    <label class="col-sm-4 control-label" style="margin-top: 10px;">
                                    <b>pm.max_spare_servers</b>:
                                    </label>
                                    <div class="col-sm-8">
                                    
                                        <input name="pm-max_spare_servers" value="<?php print $pmSettings["max_spare_servers"]; ?>" type="text" class="form-control">
                                  
                                    </div>
                                </div>


							</div>

							<div class="panel-body" style="border-bottom: 1px solid lightgrey;">





                                <div class="form-group">
                                    <label class="col-sm-4 control-label" style="margin-top: 10px;">
                                    <b>pm.max_requests</b>:
                                    </label>
                                    <div class="col-sm-8">
                                    
                                        <input name="pm-max_requests" value="<?php print $pmSettings["max_requests"]; ?>" type="text" class="form-control">
                                  
                                    </div>
                                </div>

                            
							</div>
                            
						</div>
					</div>
					<?php
					}
					?>

				    <?php
					$colWidth = 12;

					if ($domainId > 0) {
						$colWidth = 8;
					}
					?>


					<?php
                    if ($domainId > 0) {
					?>
					
					<div class="col-md-4">

							<div class="form-group" style="padding-bottom: 50px;">

								<input type="submit" name="submitButton" value="Restore Default Configuration File" data-style="zoom-in" class="btn btn-warning ladda-button">

								<span class="ladda-spinner"></span>
								<span class="ladda-progress" style="width: 0px;"></span>
								</input>
							</div>

					</div>

					<?php
                    }
					?>

					<div class="col-md-<?php print $colWidth; ?>">

							<div class="form-group" style="padding-bottom: 50px;">

								<input type="submit" value="Save Configuration File" data-style="zoom-in" class="btn btn-info ladda-button" onclick="return ValidateForm(); return false;">

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
