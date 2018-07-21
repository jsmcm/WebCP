<?php
session_start();

require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.User.php");
$oUser = new User();

require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.Domain.php");
$oDomains = new Domain();

require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.Settings.php");
$oSettings = new Settings();

require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.Utils.php");
$oUtils = new Utils();

require($_SERVER["DOCUMENT_ROOT"]."/includes/License.inc.php");

$ClientID = $oUser->getClientId();
if($ClientID < 1)
{
	header("Location: /index.php");
	exit();
}


$FTPSettingsArray = array();

$oSettings->GetFTPBackupSettings($FTPSettingsArray);

$FTPHost = "";
if(isset($FTPSettingsArray["FTPHost"]))
{
	$FTPHost = $FTPSettingsArray["FTPHost"];
}

$FTPRemotePath = "";
if(isset($FTPSettingsArray["FTPRemotePath"]))
{
	$FTPRemotePath = $FTPSettingsArray["FTPRemotePath"];
}

$FTPUserName = "";
if(isset($FTPSettingsArray["FTPUserName"]))
{
	$FTPUserName = $FTPSettingsArray["FTPUserName"];
}

$FTPPassword = "";
if(isset($FTPSettingsArray["FTPPassword"]))
{
	$FTPPassword = $FTPSettingsArray["FTPPassword"];
}





     
$DailyBackupSettingsArray = array();

$oSettings->GetBackupSettings('daily', $DailyBackupSettingsArray);
     
$DailyBackupStatus = "checked";
if(isset($DailyBackupSettingsArray["BackupStatus"]))
{
        if($DailyBackupSettingsArray["BackupStatus"] != "on")
	{
		$DailyBackupStatus = "";
	}
}

$DailyBackupWhat = "all";
if(isset($DailyBackupSettingsArray["BackupWhat"]))
{
        $DailyBackupWhat = $DailyBackupSettingsArray["BackupWhat"];
}

	
$DailyBackupWebandMail = "";
$DailyBackupWebOnly = "";
$DailyBackupMailOnly = "";

if($DailyBackupWhat == "all")
{
	$DailyBackupWebandMail = "checked";
}
else if($DailyBackupWhat == "web")
{
	$DailyBackupWebOnly = "checked";
}
else if($DailyBackupWhat == "mail")
{
	$DailyBackupMailOnly = "checked";
}

$DailyBackupUseFTP = "";
if(isset($DailyBackupSettingsArray["BackupUseFTP"]))
{
	if(trim(strtolower($DailyBackupSettingsArray["BackupUseFTP"])) == "true")
	{
		$DailyBackupUseFTP = "checked";
	}
}

$DailyBackupFTPCount = 0;
if(isset($DailyBackupSettingsArray["BackupFTPCount"]))
{
        $DailyBackupFTPCount = intVal($DailyBackupSettingsArray["BackupFTPCount"]);
}



     
$WeeklyBackupSettingsArray = array();

$oSettings->GetBackupSettings('weekly', $WeeklyBackupSettingsArray);
     
$WeeklyBackupStatus = "";
if(isset($WeeklyBackupSettingsArray["BackupStatus"]))
{
        if($WeeklyBackupSettingsArray["BackupStatus"] == "on")
	{
		$WeeklyBackupStatus = "checked";
	}
}

$WeeklyBackupWhat = "web";
if(isset($WeeklyBackupSettingsArray["BackupWhat"]))
{
        $WeeklyBackupWhat = $WeeklyBackupSettingsArray["BackupWhat"];
}
$WeeklyBackupWebandMail = "";
$WeeklyBackupWebOnly = "";
$WeeklyBackupMailOnly = "";

if($WeeklyBackupWhat == "all")
{
        $WeeklyBackupWebandMail = "checked";
}
else if($WeeklyBackupWhat == "web")
{
        $WeeklyBackupWebOnly = "checked";
}
else if($WeeklyBackupWhat == "mail")
{
        $WeeklyBackupMailOnly = "checked";
}


$WeeklyBackupUseFTP = "";
if(isset($WeeklyBackupSettingsArray["BackupUseFTP"]))
{
	if(trim(strtolower($WeeklyBackupSettingsArray["BackupUseFTP"])) == "true")
	{
		$WeeklyBackupUseFTP = "checked";
	}
}

$WeeklyBackupFTPCount = 0;
if(isset($WeeklyBackupSettingsArray["BackupFTPCount"]))
{
        $WeeklyBackupFTPCount = intVal($WeeklyBackupSettingsArray["BackupFTPCount"]);
}








     
$MonthlyBackupSettingsArray = array();

$oSettings->GetBackupSettings('monthly', $MonthlyBackupSettingsArray);

$MonthlyBackupStatus = "";
if(isset($MonthlyBackupSettingsArray["BackupStatus"]))
{
        if($MonthlyBackupSettingsArray["BackupStatus"] == "on")
	{
		$MonthlyBackupStatus = "checked";
	}
}

$MonthlyBackupWhat = "web";
if(isset($MonthlyBackupSettingsArray["BackupWhat"]))
{
        $MonthlyBackupWhat = $MonthlyBackupSettingsArray["BackupWhat"];
}

$MonthlyBackupWebandMail = "";
$MonthlyBackupWebOnly = "";
$MonthlyBackupMailOnly = "";

if($MonthlyBackupWhat == "all")
{
        $MonthlyBackupWebandMail = "checked";
}
else if($MonthlyBackupWhat == "web")
{
        $MonthlyBackupWebOnly = "checked";
}
else if($MonthlyBackupWhat == "mail")
{
        $MonthlyBackupMailOnly = "checked";
}


$MonthlyBackupUseFTP = "";
if(isset($MonthlyBackupSettingsArray["BackupUseFTP"]))
{
	if(trim(strtolower($MonthlyBackupSettingsArray["BackupUseFTP"])) == "true")
	{
		$MonthlyBackupUseFTP = "checked";
	}
}

$MonthlyBackupFTPCount = 0;
if(isset($MonthlyBackupSettingsArray["BackupFTPCount"]))
{
        $MonthlyBackupFTPCount = intVal($MonthlyBackupSettingsArray["BackupFTPCount"]);
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
		<title>Backup Settings | <?php print $oSettings->GetWebCPTitle(); ?></title>
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
			FTPHost = document.BackupSettings.FTPHost.value;
			
			DailyBackupFTPCount = document.BackupSettings.DailyBackupFTPCount.value;
			DailyBackupUseFTP = document.BackupSettings.DailyBackupUseFTP.checked;

			if( (DailyBackupUseFTP == true) && ( (isNaN(DailyBackupFTPCount)) || (DailyBackupFTPCount < 0) ) )
			{
				alert("Please enter a valid number of backups to keep on the FTP server");
				document.BackupSettings.DailyBackupFTPCount.focus();
				return false;				
			}

			if( (DailyBackupUseFTP == true) && (FTPHost == "") )
			{
				alert("Please enter a FTP Host or turn FTP off for the backups");
				document.BackupSettings.FTPHost.focus();
				return false;				
			}

                        WeeklyBackupFTPCount = document.BackupSettings.WeeklyBackupFTPCount.value;
                        WeeklyBackupUseFTP = document.BackupSettings.WeeklyBackupUseFTP.checked;
                 
                        if( (WeeklyBackupUseFTP == true) && ( (isNaN(WeeklyBackupFTPCount)) || (WeeklyBackupFTPCount < 0) ) )
                        {
                                alert("Please enter a valid number of backups to keep on the FTP server");
                                document.BackupSettings.WeeklyBackupFTPCount.focus();
                                return false;
                        }

                        if( (WeeklyBackupUseFTP == true) && (FTPHost == "") )
                        {
                                alert("Please enter a FTP Host or turn FTP off for the backups");
                                document.BackupSettings.FTPHost.focus();
                                return false;
                        }

			
			
                        MonthlyBackupFTPCount = document.BackupSettings.MonthlyBackupFTPCount.value;
                        MonthlyBackupUseFTP = document.BackupSettings.MonthlyBackupUseFTP.checked;
                 
                        if( (MonthlyBackupUseFTP == true) && ( (isNaN(MonthlyBackupFTPCount)) || (MonthlyBackupFTPCount < 0) ) )
                        {
                                alert("Please enter a valid number of backups to keep on the FTP server");
                                document.BackupSettings.MonthlyBackupFTPCount.focus();
                                return false;
                        }

                        if( (MonthlyBackupUseFTP == true) && (FTPHost == "") )
                        {
                                alert("Please enter a FTP Host or turn FTP off for the backups");
                                document.BackupSettings.FTPHost.focus();
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
									<a href="/backups/">
										Backups
									</a>
								</li>
								<li>
									<i class="active"></i>
									<a href="/backups/settings.php">
										Backup Settings
									</a>
								</li>
					
					
							</ol>
							<div class="page-header">
								<h1>Backup Settings</h1>
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
					


					<form name="BackupSettings" action="SaveBackupSettings.php" method="post">
					
					<div class="col-md-12">

						<div class="panel panel-default">

							<div class="panel-body">
	
							<h1>FTP Settings</h1>
							If you want to make use of an external FTP server to move backups to enter the FTP server settings here.
							<p>
	
							<div class="form-group" style="padding-bottom: 50px;">
								<label class="col-sm-2 control-label">
								<b>FTP Host:</b>
								</label>
								<div class="col-sm-10">
									<span class="input-icon">
									<input name="FTPHost" value="<?php print $FTPHost; ?>" type="text" id="form-field-11" class="form-control">
									</span>
								</div>
							</div>


							<div class="form-group" style="padding-bottom: 50px;">
								<label class="col-sm-2 control-label">
								<b>FTP Remote Path:</b>
								</label>
								<div class="col-sm-10">
									<span class="input-icon">
									<input name="FTPRemotePath" value="<?php print $FTPRemotePath; ?>" type="text" id="form-field-11" class="form-control">
									<br>
 									<i><b>Note: </b>This path must already exist on the FTP server, we will not create it!!!</i><br>&nbsp;<br>
									</span>
								</div>
							</div>

							<div class="form-group" style="padding-bottom: 50px;">
								<label class="col-sm-2 control-label">
								<b>FTP Username:</b>
								</label>
								<div class="col-sm-10">
									<span class="input-icon">
									<input name="FTPUserName" value="<?php print $FTPUserName; ?>" type="text" id="form-field-11" class="form-control">
									</span>
								</div>
							</div>

							<div class="form-group" style="padding-bottom: 50px;">
								<label class="col-sm-2 control-label">
								<b>FTP Password:</b>
								</label>
								<div class="col-sm-10">
									<span class="input-icon">
									<input name="FTPPassword" value="<?php print $FTPPassword; ?>" type="text" id="form-field-11" class="form-control">
									</span>
								</div>
							</div>

							</div>
						</div>
					</div>
	

					<div class="col-md-12">

						<div class="panel panel-default">

							<div class="panel-body">
	
							<h1>Daily backups</h1>
							Configure automatic daily backups
							<p>
	
							<div class="form-group" style="padding-bottom: 50px;">
								<label class="col-sm-2 control-label">
								<b>Daily backups are</b>:
								</label>
								<div class="col-sm-10">
									<div class="make-switch" data-on="success" data-off="danger">
										<input type="checkbox" <?php print $DailyBackupStatus; ?> value="on" name="DailyBackupStatus">
									</div>  
								</div>
							</div>

							<div class="form-group" style="padding-bottom: 50px;">
								<label class="col-sm-2 control-label">
								<b>What to backup</b>:
								</label>
								<div class="col-sm-10" style="margin-bottom:50px;">
									Website content and Mail: <input type="radio" <?php print $DailyBackupWebandMail; ?> value="all" name="DailyBackupWhat"><br>
									Website content only: <input type="radio" <?php print $DailyBackupWebOnly; ?> value="web" name="DailyBackupWhat"><br>
									Mail only: <input type="radio" <?php print $DailyBackupMailOnly; ?> value="mail" name="DailyBackupWhat">
								</div>
							</div>	

							<div class="form-group" style="padding-bottom: 100px;">
								<label class="col-sm-2 control-label">
								<b>Send to FTP</b>:
								</label>
								<div class="col-sm-10">
									<div class="make-switch" data-on="success" data-off="danger">
										<input type="checkbox" <?php print $DailyBackupUseFTP; ?>  value="true" name="DailyBackupUseFTP">
									</div>  
								</div>
							</div>
	
							<div class="form-group" style="padding-bottom: 50px;">
								<label class="col-sm-2 control-label">
								<b>Number of backups on FTP Server</b>:
								</label>
								<div class="col-sm-10">
									<span class="input-icon">
									<input name="DailyBackupFTPCount" value="<?php print $DailyBackupFTPCount; ?>" type="number" id="form-field-11" class="form-control">
									</span>
								</div>
							</div>
							</div>
						</div>
					</div>
	

                                        <div class="col-md-12">

                                                <div class="panel panel-default">

                                                        <div class="panel-body">

                                                        <h1>Weekly backups</h1>
                                                        Configure automatic weekly backups
                                                        <p>

                                                        <div class="form-group" style="padding-bottom: 50px;">
                                                                <label class="col-sm-2 control-label">
                                                                <b>Weekly backups are</b>:
                                                                </label>
                                                                <div class="col-sm-10">
                                                                        <div class="make-switch" data-on="success" data-off="danger">
                                                                                <input type="checkbox" <?php print $WeeklyBackupStatus; ?> value="on"  name="WeeklyBackupStatus">
                                                                        </div>
                                                                </div>
                                                        </div>


							<div class="form-group" style="padding-bottom: 50px;">
                                                                <label class="col-sm-2 control-label">
                                                                <b>What to backup</b>:
                                                                </label>
                                                                <div class="col-sm-10" style="margin-bottom:50px;">
                                                                        Website content and Mail: <input type="radio" <?php print $WeeklyBackupWebandMail; ?> value="all" name="WeeklyBackupWhat"><br>
                                                                        Website content only: <input type="radio" <?php print $WeeklyBackupWebOnly; ?> value="web" name="WeeklyBackupWhat"><br>
                                                                        Mail only: <input type="radio" <?php print $WeeklyBackupMailOnly; ?> value="mail" name="WeeklyBackupWhat">  
                                                                </div>   
                                                        </div>

                                                        <div class="form-group" style="padding-bottom: 100px;">
                                                                <label class="col-sm-2 control-label">
                                                                <b>Send to FTP</b>:
                                                                </label>
                                                                <div class="col-sm-10">
                                                                        <div class="make-switch" data-on="success" data-off="danger">
                                                                                <input type="checkbox" <?php print $WeeklyBackupUseFTP; ?>  value="true"  name="WeeklyBackupUseFTP">
                                                                        </div>
                                                                </div>
                                                        </div>

                                                        <div class="form-group" style="padding-bottom: 50px;">
                                                                <label class="col-sm-2 control-label">
                                                                <b>Number of backups on FTP Server</b>:
                                                                </label>
                                                                <div class="col-sm-10">
                                                                        <span class="input-icon">
                                                                        <input name="WeeklyBackupFTPCount" value="<?php print $WeeklyBackupFTPCount; ?>" type="number" id="form-field-11" class="form-control">
                                                                        </span>
                                                                </div>
                                                        </div>
                                                        </div>
                                                </div>
                                        </div>






                                        <div class="col-md-12">

                                                <div class="panel panel-default">

                                                        <div class="panel-body">

                                                        <h1>Monthly backups</h1>
                                                        Configure automatic monthly backups
                                                        <p>

                                                        <div class="form-group" style="padding-bottom: 50px;">
                                                                <label class="col-sm-2 control-label">
                                                                <b>Monthly backups are</b>:
                                                                </label>
                                                                <div class="col-sm-10">
                                                                        <div class="make-switch" data-on="success" data-off="danger">
                                                                                <input type="checkbox" <?php print $MonthlyBackupStatus; ?> value="on"  name="MonthlyBackupStatus">
                                                                        </div>
                                                                </div>
                                                        </div>


							<div class="form-group" style="padding-bottom: 50px;">
                                                                <label class="col-sm-2 control-label">
                                                                <b>What to backup</b>:
                                                                </label>
                                                                <div class="col-sm-10" style="margin-bottom:50px;">
                                                                        Website content and Mail: <input type="radio" <?php print $MonthlyBackupWebandMail; ?> value="all" name="MonthlyBackupWhat"><br>
                                                                        Website content only: <input type="radio" <?php print $MonthlyBackupWebOnly; ?> value="web" name="MonthlyBackupWhat"><br>
                                                                        Mail only: <input type="radio" <?php print $MonthlyBackupMailOnly; ?> value="mail" name="MonthlyBackupWhat">  
                                                                </div>   
                                                        </div>

                                                        <div class="form-group" style="padding-bottom: 100px;">
                                                                <label class="col-sm-2 control-label">
                                                                <b>Send to FTP</b>:
                                                                </label>
                                                                <div class="col-sm-10">
                                                                        <div class="make-switch" data-on="success" data-off="danger">
                                                                                <input type="checkbox" <?php print $MonthlyBackupUseFTP; ?> value="true" name="MonthlyBackupUseFTP">
                                                                        </div>
                                                                </div>
                                                        </div>

                                                        <div class="form-group" style="padding-bottom: 50px;">
                                                                <label class="col-sm-2 control-label">
                                                                <b>Number of backups on FTP Server</b>:
                                                                </label>
                                                                <div class="col-sm-10">
                                                                        <span class="input-icon">
                                                                        <input name="MonthlyBackupFTPCount" value="<?php print $MonthlyBackupFTPCount; ?>" type="number" id="form-field-11" class="form-control">
                                                                        </span>
                                                                </div>
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
