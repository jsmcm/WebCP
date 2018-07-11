<?php
session_start();

require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.User.php");
$oUser = new User();

require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.Email.php");
$oEmail = new Email();

require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.Domain.php");
$oDomain = new Domain();

require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.Settings.php");
$oSettings = new Settings();



$ClientID = $oUser->GetClientID();
$loggedInId = $ClientID;

$email_ClientID = $oEmail->getLoggedInEmailId();

$Role = $oUser->Role;

if($ClientID < 1)
{
        if( $email_ClientID < 1 )
        {
                header("Location: /index.php");
                exit();
        }
        $loggedInId = $email_ClientID;

        $Role = "email";
}


$EmailAddress = "";

if(isset($_REQUEST["EmailAddress"]))
{
	$EmailAddress = $_REQUEST["EmailAddress"];
}
else
{
	header("location: index.php?NoteType=Error&Notes=No email address specified");
	exit();
}

$SpamSubjectModifier = ""; 
$SpamBlockLevel = $oEmail->GetSpamBlockLevel($EmailAddress);
$SpamWarnLevel = $oEmail->GetSpamWarnLevel($EmailAddress);
$SpamSubjectModifier = $oEmail->GetSpamSubjectModifier($EmailAddress);

?>

<!DOCTYPE html>
<!--[if IE 8]><html class="ie8 no-js" lang="en"><![endif]-->
<!--[if IE 9]><html class="ie9 no-js" lang="en"><![endif]-->
<!--[if !IE]><!-->
<html lang="en" class="no-js">
	<!--<![endif]-->
	<!-- start: HEAD -->
	<head>
		<title><?php ($EmailAddress == "")? print "Add": print "Edit"; ?> Spam Assassin | <?php print $oSettings->GetWebCPTitle(); ?></title>
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
		<!--link rel="stylesheet" type="text/css" href="/assets/plugins/select2/select2.css" />
		<link rel="stylesheet" href="/assets/plugins/DataTables/media/css/DT_bootstrap.css" /-->

		<link href="/assets/plugins/jquery-ui/jquery-ui-1.10.1.custom.min.css" rel="stylesheet"/>
		<link rel="stylesheet" href="/assets/plugins/jQRangeSlider/css/classic-min.css">

		<!-- end: CSS REQUIRED FOR THIS PAGE ONLY -->
		<link rel="shortcut icon" href="/favicon.ico" />
		
		
		<script language="javascript">

		function DoSubmit()
		{
			if(document.AddSpamAssassin.MailBoxID.value < 1)
			{
				alert("Please select the email address");
				document.AddSpamAssassin.MailBoxID.focus();
				return false;
			}

			if(document.AddSpamAssassin.Subject.value == "")
			{
				alert("Please enter a subject");
				document.AddSpamAssassin.Subject.focus();
				return false;
			}

			if(document.AddSpamAssassin.MessageBody.value == "")
			{
				alert("Please enter a message body");
				document.AddSpamAssassin.MessageBody.focus();
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
									<a href="/emails/spamassassin/index.php">
										<?php ($EmailAddress == "")? print "Add": print "Edit"; ?> Spam Assassin
									</a>
								</li>
					
							</ol>
							<div class="page-header">
								<h1><?php ($EmailAddress == "")? print "Add": print "Edit"; ?> Spam Assassin - <?php print $EmailAddress; ?></h1>
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
							<form name="AddSpamAssassin" method="post" action="DoAddSpamAssassin.php" class="form-horizontal">
							<input type="hidden" value="<?php print $EmailAddress; ?>" name="EmailAddress">
	
							<!-- start: DYNAMIC TABLE PANEL -->
							<div class="panel panel-default">
									
								<div class="panel-body">
					

									<h1>Spam Block Level</h1>		
									The default spam block level is <?php print $oEmail->GetGlobalSpamBlockLevel(); ?>. Setting the value lower will block more spam, but will probably also block legitimate mail. Please use this setting only if you know what you're doing.
									<p>&nbsp;<p>
									You should probably first test by setting a warn level and subject modifier below (eg, add ***spam*** to subject lines). If your testing after a few weeks proves that you're marking spam, but not legitimate email, then you can use that level here.

									<p>&nbsp;<p>
									<div class="row">
										<div class="col-md-12">		
											<h4>Block mail with a spam score higher than:</h4>
											<input class="knob" name="SpamBlockLevel" data-width=150 data-angleOffset=-125 data-angleArc=250 data-fgColor="#BC1818" value="<?php print $SpamBlockLevel; ?>">
										</div>
									</div>	
								</div>
							</div>


							<div class="panel panel-default">
									
								<div class="panel-body">
					



									<h1>Spam Warn Level</h1>		
									The default spam warn level is <?php print $oEmail->GetGlobalSpamWarnLevel(); ?>. 
									<p>&nbsp;<p>
									When an email comes in which has a spam score equal to or higher than this spam warn level, then that mail will have its subject line modified with the text you enter below. 
									<p>&nbsp;<p>
									For instance, if we set a subject modifier of <b>***spam***</b> and a mail with subject <b>Make Money Online</b> triggers the spam warning level, then the mail will be delivered with modified subject <b>***spam*** Make Money Online</b>. This will allow you to test settings before adjusting the block level, or you can have your mail program filter out mail based on your subject modifier text
									<p>&nbsp;<p>
									<div class="row">
										<div class="col-md-12">		
											<h4>Warn about mail with a spam score higher than:</h4>
											<input class="knob" name="SpamWarnLevel" data-width=150 data-angleOffset=-125 data-angleArc=250 data-fgColor="#EEC400" value="<?php print $SpamWarnLevel; ?>">
										</div>
									</div>	
							
									<div class="row">
										<div class="col-sm-4">		
											<h4>Spam Subject Modifier</h4>
											<input name="SpamSubjectModifier" value="<?php print $SpamSubjectModifier; ?>" class="form-control">
										</div>
									</div>	
							
								

										
								</div>
							</div>
							<div class="form-group">
								<div class="col-sm-4">
									<input type="submit" value="Save Spam Assassin" data-style="zoom-in" class="btn btn-info ladda-button">
									<span class="ladda-spinner"></span>
									<span class="ladda-progress" style="width: 0px;"></span>
									</input>
									</div>
								</div>
							</form>




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
		<!--script type="text/javascript" src="/assets/plugins/select2/select2.min.js"></script>
		<script type="text/javascript" src="/assets/plugins/DataTables/media/js/jquery.dataTables.min.js"></script>
		<script type="text/javascript" src="/assets/plugins/DataTables/media/js/DT_bootstrap.js"></script>
		<script src="/assets/js/table-data.js"></script-->

		<script src="/assets/plugins/jquery-ui/jquery-ui-1.10.1.custom.min.js" type="text/javascript"></script>
		<script src="/assets/plugins/jQRangeSlider/jQAllRangeSliders-min.js"></script>
		<script src="/assets/plugins/jQuery-Knob/js/jquery.knob.js"></script>
		<script src="/assets/js/ui-sliders.js"></script>

		<!-- end: JAVASCRIPTS REQUIRED FOR THIS PAGE ONLY -->
		<script>
			jQuery(document).ready(function() {
				Main.init();
				UISliders.init();
			});
		</script>
	</body>
	<!-- end: BODY -->
</html>
