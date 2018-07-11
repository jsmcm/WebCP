<?php
session_start();

require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.User.php");
$oUser = new User();
       
require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.Settings.php");
$oSettings = new Settings();

$ClientID = $oUser->getClientId();
if ($ClientID < 1) {
    header("Location: /index.php");
    exit();
}

$UserID = "";
$Action = "add";
$FirstName = "";
$Surname = "";
$EmailAddress = "";
$Username = "";
$UserRole = "";

if(isset($_REQUEST["UserID"]))
{
        $UserID = $_REQUEST["UserID"];
        $Action = "update";

        $oUser->GetUserDetails($UserID, $FirstName, $Surname, $EmailAddress, $Username, $UserRole);

}

if( ($UserID == "") && ($oUser->Role == "client") )
{
	header("Location: ./index.php?Notes=You don't have permission to be there&NoteType=error");
	exit();
}


if ($oUser->EmailAddress == "admin@admin.admin") {
    $Action = "update";
    $UserID = $ClientID;
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
		<title><?php print ucfirst($Action); ?> User | <?php print $oSettings->GetWebCPTitle(); ?></title>
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

xmlhttp = null;
count = 0;
var d = new Date();

function GetNewUserName()
{
        if(document.AddUser.Username.value != "")
        {
                return;
        }

        xmlhttp = null;

        if (window.XMLHttpRequest)
        {
                // IE7+, Firefox, Chrome, Opera, Safari
                xmlhttp=new XMLHttpRequest();
        }
        else
        {
                // code for IE6, IE5
                xmlhttp=new ActiveXObject("Msxml2.XMLHTTP");
        }


        RndString = d.getFullYear() + "" + d.getMonth() + "" + d.getDate() + ""  + d.getHours() + "" + d.getMinutes() + "" + d.getSeconds() + "" + count++;


        xmlhttp.open("GET", "/users/AjaxCreateUserName.php?InputName=" + document.AddUser.FirstName.value + document.AddUser.Surname.value + "&C=" + RndString, false);
        xmlhttp.send(null);
        if(xmlhttp.responseText != "")
        {
                document.AddUser.Username.value = xmlhttp.responseText;
        }
}





function DoSubmit()
{

        SkipPassword = 0;
        <?php
        if($Action == "update")
        {
                print "SkipPassword = 1;";
        }
        ?>

        if(document.AddUser.FirstName.value == "")
        {
                alert("Please enter a first name");
                document.AddUser.FirstName.focus();
                return false;
        }


        if(document.AddUser.Username.value == "")
        {
                alert("Please enter a username, this is needed to create FTP accounts and databases");
                document.AddUser.Username.focus();
                return false;
        }


        email = document.AddUser.EmailAddress.value;

        if(email.length > 0)
        {
                filter = /^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9\.]{2,8})+$/;
                if (! filter.test(email))
                {
                        alert("Invalid email, please correct it, eg, user@email.co.za");
                        document.AddUser.EmailAddress.focus();
                        return false;
                }
        }

        if(document.AddUser.EmailAddress.value == "admin@admin.admin")
        {
                alert("Please enter an email address, its needed to log in.");
                document.AddUser.EmailAddress.focus();
                return false;
        }

        if(document.AddUser.EmailAddress.value == "")
        {
                alert("Please enter an email address, its needed to log in.");
                document.AddUser.EmailAddress.focus();
                return false;
        }

        if(SkipPassword == 0)
        {
                if(document.AddUser.Password.value.length < 6)
                {
                        alert("Password must be at least 6 characters long");
                        document.AddUser.Password.focus();
                        return false;
                }
        }

        if(document.AddUser.Password.value != document.AddUser.PasswordConfirm.value)
        {
                alert("Your passwords don't match, please retry")
                document.AddUser.Password.value = "";
                document.AddUser.PasswordConfirm.value = "";
                document.AddUser.Password.focus();
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
						if( ! (($Action == "update") && ($oUser->EmailAddress == "admin@admin.admin")) )
						{
							require($_SERVER["DOCUMENT_ROOT"]."/includes/PageParts/UserSection.inc.php");
						}
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

					if( ! (($Action == "update") && ($oUser->EmailAddress == "admin@admin.admin")) )
					{
						require($_SERVER["DOCUMENT_ROOT"]."/includes/PageParts/SideNav.inc.php"); 
					}
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
									<?php
									if( ! (($Action == "update") && ($oUser->EmailAddress == "admin@admin.admin")) )
									{
									?>
										<a href="/users/">
											Users
										</a>

									<?php
									}
									?>
								</li>
					
							</ol>
							<div class="page-header">
								<h1><?php print ucfirst($Action); ?> User </h1>
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
					

								<form name="AddUser" method="post" action="DoAddUser.php" class="form-horizontal">
									
						                <input type="hidden" name="Action" value="<?php print $Action; ?>">
						                <input type="hidden" name="UserID" value="<?php print $UserID; ?>">

								
										<div class="form-group">
											<label class="col-sm-2 control-label">
												First Name:
											</label>
											<div class="col-sm-4">
												<span class="input-icon">
												<input name="FirstName" value="<?php print $FirstName; ?>" type="text" id="form-field-11" class="form-control">
												<i class="fa clip-key-3"></i>
												</span>										
											</div>
										</div>
									
										<div class="form-group">
											<label class="col-sm-2 control-label">
												Surname:
											</label>
											<div class="col-sm-4">
												<span class="input-icon">
												<input name="Surname" value="<?php print $Surname; ?>" type="text" id="form-field-11" class="form-control">
												<i class="fa clip-key-3"></i>
												</span>										
											</div>
										</div>
									

										<?php

										if($oUser->Role == "admin")
										{
										?>

										<div class="form-group">
											<label class="col-sm-2 control-label">
												Role:
											</label>
											<div class="col-sm-4">
												<span class="input-icon">
												<select style="padding-left: 25px;" name="UserRole" id="form-field-11" class="form-control">

												<?php
												if($UserRole == "")
												{
													$UserRole = "client";
													if( $oUser->EmailAddress == "admin@admin.admin" && $oUser->Role == "admin")
													{
														$UserRole = "admin";
													}
															
												}
												?>

												<option value="admin" <?php (($UserRole == "admin")? print " selected": " "); ?>>Admin</option>
												<option value="reseller" <?php (($UserRole == "reseller")? print " selected": " "); ?>>Reseller</option>
												<option value="client" <?php (($UserRole == "client")? print " selected": " "); ?>>Client</option>
												</select>
												<i class="fa clip-key-3"></i>
												</span>										
											</div>
										</div>
									
										<p>&nbsp;<p>
										<?php
										}
										?>
	
										<div class="form-group">
											<label class="col-sm-2 control-label">
												User Name:
											</label>
											<div class="col-sm-4">
												<span class="input-icon">
												<input name="Username" value="<?php print $Username; ?>" type="text" id="form-field-11" class="form-control" readonly>
												<i class="fa clip-key-3"></i>
												</span>										
											</div>
										</div>
									
										<p>&nbsp;<p>
	
										<div class="form-group">
											<label class="col-sm-2 control-label">
												Email Address:
											</label>
											<div class="col-sm-4">
												<span class="input-icon">
												<input name="EmailAddress" value="<?php print $EmailAddress; ?>" type="email" id="form-field-11" class="form-control">
												<i class="fa fa-envelope-o"></i>
												</span>										
											</div>
										</div>
									
	

								

								

								

										<div class="form-group">
											<label class="col-sm-2 control-label">
												Password:
											</label>
											<div class="col-sm-4">
												<span class="input-icon">
												<input name="Password" value="" type="password" id="form-field-11" class="form-control">
												<i class="fa clip-key-3"></i>
												</span>										
											</div>
										</div>
									



										<div class="form-group">
											<label class="col-sm-2 control-label">
												Password Again:
											</label>
											<div class="col-sm-4">
												<span class="input-icon">
												<input name="PasswordConfirm" value="" type="password" id="form-field-11" class="form-control">
												<i class="fa clip-key-3"></i>
												</span>										
											</div>
										</div>

							
										<div class="form-group">

											<div class="col-sm-4">
												<input type="submit" value="<?php print ucfirst($Action);?> User" data-style="zoom-in" class="btn btn-info ladda-button" onclick="GetNewUserName(); return DoSubmit(); return false;">
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
