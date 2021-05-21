<?php
session_start();

include_once($_SERVER["DOCUMENT_ROOT"]."/vendor/autoload.php");
$oUser = new User();
$oDNS = new DNS();
$oSettings = new Settings();
$oUtils = new Utils();
$oDatabase = new Database();
$oSimpleNonce = new SimpleNonce();

require($_SERVER["DOCUMENT_ROOT"]."/includes/License.inc.php");

$ClientID = $oUser->getClientId();
if($ClientID < 1) {
	header("Location: /index.php");
	exit();
}

if($oUser->Role != "admin") {
	header("location: /domains/index.php?Notes=You don't have permission to be there&NoteType=error");
	exit();
}


$oDNS->GenerateKeyFiles();

$nonceArray = [
	$oUser->Role,
	$ClientID,
	"dns_slaves"
];

$nonce = $oSimpleNonce->GenerateNonce("tableExists", $nonceArray);

if($oDatabase->TableExists("dns_slaves", $nonce) == false) {
	$TableName = "dns_slaves";

	$TableInfoArray[0]["name"] = "id";
	$TableInfoArray[0]["type"] = "int";
	$TableInfoArray[0]["key"] = "primary key auto_increment";
	$TableInfoArray[0]["default"] = "";

	$TableInfoArray[1]["name"] = "host_name";
	$TableInfoArray[1]["type"] = "tinytext";
	$TableInfoArray[1]["key"] = "";
	$TableInfoArray[1]["default"] = "";

	$TableInfoArray[2]["name"] = "ip_address";
	$TableInfoArray[2]["type"] = "tinytext";
	$TableInfoArray[2]["key"] = "";
	$TableInfoArray[2]["default"] = "";

	$TableInfoArray[3]["name"] = "public_key";
	$TableInfoArray[3]["type"] = "text";
	$TableInfoArray[3]["key"] = "";
	$TableInfoArray[3]["default"] = "";

	$TableInfoArray[4]["name"] = "password";
	$TableInfoArray[4]["type"] = "tinytext";
	$TableInfoArray[4]["key"] = "";
	$TableInfoArray[4]["default"] = "";

	$TableInfoArray[5]["name"] = "status";
	$TableInfoArray[5]["type"] = "tinytext";
	$TableInfoArray[5]["key"] = "";
	$TableInfoArray[5]["default"] = "";

	$TableInfoArray[6]["name"] = "status_date";
	$TableInfoArray[6]["type"] = "datetime";
	$TableInfoArray[6]["key"] = "";
	$TableInfoArray[6]["default"] = "";

	$TableInfoArray[7]["name"] = "deleted";
	$TableInfoArray[7]["type"] = "int";
	$TableInfoArray[7]["key"] = "";
	$TableInfoArray[7]["default"] = "0";


	$nonceArray = [
		$oUser->Role,
		$ClientID,
		$TableName
	];
	$nonce = $oSimpleNonce->GenerateNonce("createTableFromArray", $nonceArray);

	$oDatabase->CreateTableFromArray($TableName, $TableInfoArray, $nonce);
}

$ServerType = $oDNS->GetSetting("server_type");

if($ServerType != "master") {
	header("location: index.php?Notes=Incorrect server type&NoteType=Error");
	exit();
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
		<title>DNS Slaves | <?php print $oSettings->GetWebCPTitle(); ?></title>
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

		<style>
		.StatusError
		{
			color: #b94a48;
			background-color: #f2dede;
			border-color: #ebccd1;
			padding: 15px;
			width: 100%;
			min-height: 50px;
			min-width: 150px;
		}

		.StatusSuccess
		{
			color: #468847;
			background-color: #dff0d8;
			border-color: #d6e9c6;
			padding: 15px;
			width: 100%;
			min-height: 50px;
			min-width: 150px;
		}

		</style>

		<!-- end: CSS REQUIRED FOR THIS PAGE ONLY -->
		<link rel="shortcut icon" href="/favicon.ico" />
		
		
		<script language="javascript">
		xmlhttp = null;
		count = 0;
		var d = new Date();

		function GetIP(HostName, ControlSuffix)
		{
			elem = document.getElementById("IPAddress_" + ControlSuffix);		

			if(elem.value != "")
			{
				return;
			}

    			xmlhttp = null;
    
    			if (window.XMLHttpRequest)
			    {
		            // code for IE7+, Firefox, Chrome, Opera, Safari
			            xmlhttp=new XMLHttpRequest();
			    }
			    else
			    {
			            // IE6, IE5
			            xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
			    }

			    RndString = d.getFullYear() + "" + d.getMonth() + "" + d.getDate() + ""  + d.getHours() + "" + d.getMinutes() + "" + d.getSeconds() + "" + count++;
    
			    xmlhttp.open("GET",'ajax/GetIP.php?HostName=' + HostName + '&C=' + RndString,false);
    
    
			    xmlhttp.send(null);
			    if(xmlhttp.responseText != "")
			    {
				elem.value = xmlhttp.responseText;
    			    }
		
		}

		function TestConnection(ID)
		{
    			xmlhttp = null;
    
    			if (window.XMLHttpRequest)
			    {
			            // code for IE7+, Firefox, Chrome, Opera, Safari
			            xmlhttp=new XMLHttpRequest();
			    }
			    else
			    {
			            // IE6, IE5
			            xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
			    }

			    RndString = d.getFullYear() + "" + d.getMonth() + "" + d.getDate() + ""  + d.getHours() + "" + d.getMinutes() + "" + d.getSeconds() + "" + count++;
    
			    xmlhttp.open("GET",'ajax/TestConnection.php?ID=' + ID + '&C=' + RndString,false);
    
			    xmlhttp.send(null);
			    if(xmlhttp.responseText != "")
			    {
					alert(xmlhttp.responseText);

					e = document.getElementById("Status_" + ID);

					if(xmlhttp.responseText.substr(0, 6) == "error:")
					{
						e.innerHTML = "Result: Error";
						e.className = "StatusError";
					}
					else if(xmlhttp.responseText.substr(0, 8) == "success:")
					{
						e.innerHTML = "Result: Success";
						e.className = "StatusSuccess";
					}
    			    }
		}

		function ValidateForm(ID)
		{
		        return true;
			alert("Saving ID: " + ID);
			HostName = document.getElementById("HostName_" + ID).value;
			IPAddress = document.getElementById("IPAddress_" + ID).value;
			PublicKey = document.getElementById("PublicKey_" + ID).value;
			Password = document.getElementById("Password_" + ID).value;

			alert("HostName: " + HostName + "\r\nIPAddress: " + IPAddress + "\r\nPublicKey: " + PublicKey + "\r\nPassword: " + Password);
		}


		
		function ValidateDelete(SlaveName)
		{
			if(confirm("Really delete slave: " + SlaveName + "?"))
			{
		        	return true;
			}
			return false
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
									<a href="/dns/">
										DNS
									</a>
								</li>
								<li>
									<i class="active"></i>
									<a href="/dns/slaves.php">
										DNS Slaves
									</a>
								</li>
					
					
							</ol>
							<div class="page-header">
								<h1>DNS Slaves</h1>
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
					


					
					<?php
					$oDNS->GetSlaveList($Array, $ArrayCount);

					for($x = 0; $x < $ArrayCount; $x++)
					{
					?>
					<div class="col-md-12">

						<div class="panel panel-default">

							<div class="panel-body">
	
							<form action="EditSlave.php" method="post" name="DNSSlaves_<?php print $Array[$x]["ID"]; ?>" id="DNSSlaves_<?php print $Array[$x]["ID"]; ?>" >
							<input type="hidden" id="SlaveID_<?php print $Array[$x]["ID"]; ?>" name="ID" value="<?php print $Array[$x]["ID"]; ?>">
							<h1><?php print $Array[$x]["SlaveName"]; ?></h1>
							Enter the <b>SLAVE's</b> password, public key, and IP address info here. 	
							<p>&nbsp;<p>
							<?php
							$Class = "";
							if($Array[$x]["Status"] == "error")
							{			
								$Class = "StatusError";											}
							else if($Array[$x]["Status"] == "success")
							{
								$Class = "StatusSuccess";
							}
							?>
							<span class="<?php print $Class; ?>" id="Status_<?php print $Array[$x]["ID"]; ?>">Status: <?php print $Array[$x]["Status"]; ?> - Last Checked: <?php print $Array[$x]["StatusDate"]; ?></span>
							<p>&nbsp;<p>

							<div class="form-group" style="padding-bottom: 20px;">
								<label class="col-sm-2 control-label">
								<b>Host Name</b>:
								</label>
								<div class="col-sm-8">
									<span class="input-icon">
									<input type="text" value="<?php print $Array[$x]["HostName"]; ?>" name="HostName" id="HostName_<?php print $Array[$x]["ID"]; ?>" rows="10" class="form-control" onblur="GetIP(this.value, '<?php print $Array[$x]["ID"]; ?>');">
									</span>
								</div>
							</div>
							<br>
							<div class="form-group" style="padding-bottom: 20px;">
								<label class="col-sm-2 control-label">
								<b>IP Address</b>:
								</label>
								<div class="col-sm-8">
									<span class="input-icon">
									<input type="text" id="IPAddress_<?php print $Array[$x]["ID"]; ?>" name="IPAddress" value="<?php print $Array[$x]["IPAddress"]; ?>" class="form-control">
									</span>
								</div>
							</div>
							<br>
							<div class="form-group" style="padding-bottom: 20px;">
								<label class="col-sm-2 control-label">
								<b>Password</b>:
								</label>
								<div class="col-sm-8">
									<span class="input-icon">
									<input type="text" name="Password" id="Password_<?php print $Array[$x]["ID"]; ?>" rows="10" class="form-control" Placeholder="*******">
									</span>
								</div>
							</div>
							<br>
							<div class="form-group" style="padding-bottom: 20px;">
								<label class="col-sm-2 control-label">
								<b>Public Key</b>:
								</label>
								<div class="col-sm-8">
									<span class="input-icon">
									<textarea id="PublicKey_<?php print $Array[$x]["ID"]; ?>" rows="15" name="PublicKey" class="form-control"><?php print $Array[$x]["PublicKey"]; ?></textarea>
									</span>
								</div>
							</div>
		
							<br>
							<div class="form-group" style="padding-bottom:20px; padding-top:20px;">

								<div class="col-sm-4">
								<input type="submit" value="Save Settings" data-style="zoom-in" class="btn btn-info ladda-button" onclick="return ValidateForm(<?php print $Array[$x]["ID"]; ?>); return false;">

								<span class="ladda-spinner"></span>
								<span class="ladda-progress" style="width: 0px;"></span>
								</input>
								</form>
								</div>

								<div class="col-sm-4">
								<form name="DeleteSlave_<?php print $Array[$x]["ID"]; ?>" action="DeleteSlave.php" method="post">
								<input type="hidden" name="ID" value="<?php print $Array[$x]["ID"]; ?>">
								<input type="submit" value="Delete Slave" data-style="zoom-in" class="btn btn-bricky ladda-button" onclick="return ValidateDelete('<?php print $Array[$x]["SlaveName"]; ?>'); return false;">
								</form>

								<span class="ladda-spinner"></span>
								<span class="ladda-progress" style="width: 0px;"></span>
								</input>
								</div>
								
								<div class="col-sm-4">
								<input type="button" value="Test Connection" data-style="zoom-in" class="btn btn-green ladda-button" onclick="return TestConnection(<?php print $Array[$x]["ID"]; ?>);">

								<span class="ladda-spinner"></span>
								<span class="ladda-progress" style="width: 0px;"></span>
								</input>
								</form>
								</div>
							</div>

							</div>
						</div>
					</div>
					<?php
					}
					?>

					<div class="col-md-12">

						<div class="panel panel-default">

							<div class="panel-body">
	
							<form name="DNSSlaves" action="AddSlave.php" method="post">
							<h1>New Slave</h1>
							Enter the <b>SLAVE's</b> password, public key, and IP address info here. 	
							<p>
							<div class="form-group" style="padding-bottom: 20px;">
								<label class="col-sm-2 control-label">
								<b>Host Name</b>:
								</label>
								<div class="col-sm-8">
									<span class="input-icon">
									<input type="text" name="HostName_New" id="form-field-11" rows="10" class="form-control" onblur="GetIP(this.value, 'New');">
									</span>
								</div>
							</div>
							<br>
							<div class="form-group" style="padding-bottom: 20px;">
								<label class="col-sm-2 control-label">
								<b>IP Address</b>:
								</label>
								<div class="col-sm-8">
									<span class="input-icon">
									<input type="text" id="IPAddress_New" name="IPAddress_New" class="form-control">
									</span>
								</div>
							</div>
							<br>
							<div class="form-group" style="padding-bottom: 20px;">
								<label class="col-sm-2 control-label">
								<b>Password</b>:
								</label>
								<div class="col-sm-8">
									<span class="input-icon">
									<input type="text" name="Password_New" id="form-field-11" rows="10" class="form-control">
									</span>
								</div>
							</div>
							<br>
							<div class="form-group" style="padding-bottom: 20px;">
								<label class="col-sm-2 control-label">
								<b>Public Key</b>:
								</label>
								<div class="col-sm-8">
									<span class="input-icon">
									<textarea id="form-field-11" rows="15" name="PublicKey_New" class="form-control"></textarea>
									</span>
								</div>
							</div>
							<br>
							<div class="form-group" style="padding-bottom: 50px;">

								<div class="col-sm-8">
								<input type="submit" value="Save Settings" data-style="zoom-in" class="btn btn-info ladda-button" onclick="return ValidateForm(); return false;">

								<span class="ladda-spinner"></span>
								<span class="ladda-progress" style="width: 0px;"></span>
								</input>
								</div>
							</div>

						</form>
		
							</div>
						</div>
					</div>



















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
