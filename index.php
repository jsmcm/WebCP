<?php

if ( ! file_exists($_SERVER["DOCUMENT_ROOT"]."/vendor/autoload.php") ) {
    print "<h1>Missing dependencies</h1><p>I'm going to try and install them. This might take several minutes. Please wait 30 minutes then try again</p><p>If you've already waited 30 minutes and still see this please contact support@webcp.io for support</p>";

    if ( ! file_exists($_SERVER["DOCUMENT_ROOT"]."/nm/") ) {
        mkdir($_SERVER["DOCUMENT_ROOT"]."/nm/", 0755);
    }

    touch($_SERVER["DOCUMENT_ROOT"]."/nm/composer_install");
    exit();
}

require_once($_SERVER["DOCUMENT_ROOT"]."/vendor/autoload.php");
$oSettings = new Settings();

?>
<!DOCTYPE html>

<!--[if IE 8]><html class="ie8 no-js" lang="en"><![endif]-->
<!--[if IE 9]><html class="ie9 no-js" lang="en"><![endif]-->
<!--[if !IE]><!-->

<html lang="en" class="no-js">

	<!--<![endif]-->

	<!-- start: HEAD -->

	<head>

		<title>Log in | <?php print $oSettings->GetWebCPTitle(); ?></title>

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

		<!-- end: CSS REQUIRED FOR THIS PAGE ONLY -->

	</head>

	<!-- end: HEAD -->

	<!-- start: BODY -->

	<body class="login example2">

		<div class="main-login col-sm-4 col-sm-offset-4">

			<div class="logo">
			<?php print $oSettings->GetWebCPName(); ?>
			</div>

			<!-- start: LOGIN BOX -->

			<div class="box-login">
	
				<?php
				if(isset($_REQUEST["Notes"]))
				{
					print "<font color=\"red\">".$_REQUEST["Notes"]."</font><p>";
				}
				?>

				<h3>Sign in to your account</h3>

				<p>

					Please enter your email and password to log in.

				</p>

				<form class="form-login" action="DoLogin.php" method="post">

					<div class="errorHandler alert alert-danger no-display">

						<i class="fa fa-remove-sign"></i> You have some form errors. Please check below.

					</div>

					<fieldset>

						<div class="form-group">

							<span class="input-icon">

								<input type="text" class="form-control" name="EmailAddress" placeholder="Email Address">

								<i class="fa fa-user"></i> </span>

						</div>

						<div class="form-group form-actions">

							<span class="input-icon">

								<input type="password" class="form-control password" name="password" placeholder="Password">

								<i class="fa fa-lock"></i>

								<a class="forgot" href="#">

									I forgot my password

								</a> </span>

						</div>

						<div class="form-actions">

	

							<button type="submit" class="btn btn-bricky pull-right">

								Login <i class="fa fa-arrow-circle-right"></i>

							</button>

						</div>



					</fieldset>

				</form>

			</div>

			<!-- end: LOGIN BOX -->

			<!-- start: FORGOT BOX -->

			<div class="box-forgot">

				<h3>Forget Password?</h3>

				<p>

					Enter your e-mail address below to reset your password.

				</p>

				<form class="form-forgot" action="SendLogin.php" method="post">

					<div class="errorHandler alert alert-danger no-display">

						<i class="fa fa-remove-sign"></i> You have some form errors. Please check below.

					</div>

					<fieldset>

						<div class="form-group">

							<span class="input-icon">

								<input type="email" class="form-control" name="email" placeholder="Email Address">

								<i class="fa fa-envelope"></i> </span>

						</div>

						<div class="form-actions">

							<button class="btn btn-light-grey go-back">

								<i class="fa fa-circle-arrow-left"></i> Back

							</button>

							<button type="submit" class="btn btn-bricky pull-right">

								Reset Password <i class="fa fa-arrow-circle-right"></i>

							</button>

						</div>

					</fieldset>

				</form>

			</div>

			<!-- end: FORGOT BOX -->



			<!-- start: COPYRIGHT -->

			<div class="copyright">

				2018 &copy; <?php print $oSettings->GetWebCPLink(); ?>

			</div>

			<!-- end: COPYRIGHT -->

		</div>

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

		<script src="/assets/plugins/jquery-validation/dist/jquery.validate.min.js"></script>

		<script src="/assets/js/login.js"></script>

		<!-- end: JAVASCRIPTS REQUIRED FOR THIS PAGE ONLY -->
		<script>

			jQuery(document).ready(function() {

				Main.init();

				Login.init();

			});

		</script>

	</body>

	<!-- end: BODY -->
</html>
