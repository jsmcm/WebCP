<?php
session_start();

include_once($_SERVER["DOCUMENT_ROOT"]."/vendor/autoload.php");
$oUser = new User();
$oDNS = new DNS();
$oSettings = new Settings();
$oUtils = new Utils();
$oDatabase = new Database();

require($_SERVER["DOCUMENT_ROOT"]."/includes/License.inc.php");

$ClientID = $oUser->getClientId();
if($ClientID < 1)
{
	header("Location: /index.php");
	exit();
}

if($oUser->Role != "admin")
{
	header("location: /domains/index.php?Notes=You don't have permission to be there&NoteType=error");
	exit();
}


$oDNS->GenerateKeyFiles();

if($oDatabase->TableExists("soa") == false)
{
        $TableName = "soa";

        $TableInfoArray[0]["name"] = "id";
        $TableInfoArray[0]["type"] = "int";
        $TableInfoArray[0]["key"] = "primary key auto_increment";
        $TableInfoArray[0]["default"] = "";

        $TableInfoArray[1]["name"] = "client_id";
        $TableInfoArray[1]["type"] = "int";
        $TableInfoArray[1]["key"] = "";
        $TableInfoArray[1]["default"] = "";

        $TableInfoArray[2]["name"] = "domain";
        $TableInfoArray[2]["type"] = "tinytext";
        $TableInfoArray[2]["key"] = "";
        $TableInfoArray[2]["default"] = "";

        $TableInfoArray[3]["name"] = "ttl";
        $TableInfoArray[3]["type"] = "int";
        $TableInfoArray[3]["key"] = "";
        $TableInfoArray[3]["default"] = "";

        $TableInfoArray[4]["name"] = "name_server";
        $TableInfoArray[4]["type"] = "tinytext";
        $TableInfoArray[4]["key"] = "";
        $TableInfoArray[4]["default"] = "";

        $TableInfoArray[5]["name"] = "email_address";
        $TableInfoArray[5]["type"] = "tinytext";
        $TableInfoArray[5]["key"] = "";
        $TableInfoArray[5]["default"] = "";

        $TableInfoArray[6]["name"] = "serial_number";
        $TableInfoArray[6]["type"] = "int";
        $TableInfoArray[6]["key"] = "";
        $TableInfoArray[6]["default"] = "";

        $TableInfoArray[7]["name"] = "refresh";
        $TableInfoArray[7]["type"] = "int";
        $TableInfoArray[7]["key"] = "";
        $TableInfoArray[7]["default"] = "";
        $TableInfoArray[8]["name"] = "retry";
        $TableInfoArray[8]["type"] = "int";
        $TableInfoArray[8]["key"] = "";
        $TableInfoArray[8]["default"] = "";

        $TableInfoArray[9]["name"] = "expire";
        $TableInfoArray[9]["type"] = "int";
        $TableInfoArray[9]["key"] = "";
        $TableInfoArray[9]["default"] = "";

        $TableInfoArray[10]["name"] = "negative_ttl";
        $TableInfoArray[10]["type"] = "int";
        $TableInfoArray[10]["key"] = "";
        $TableInfoArray[10]["default"] = "";

        $TableInfoArray[11]["name"] = "status";
        $TableInfoArray[11]["type"] = "tinytext";
        $TableInfoArray[11]["key"] = "";
        $TableInfoArray[11]["default"] = "";

        $TableInfoArray[12]["name"] = "deleted";
        $TableInfoArray[12]["type"] = "int";
        $TableInfoArray[12]["key"] = "";
        $TableInfoArray[12]["default"] = "0";

        $oDatabase->CreateTableFromArray($TableName, $TableInfoArray);
}

if($oDatabase->TableExists("rrs") == false)
{
        $TableName = "rrs";

        $TableInfoArray[0]["name"] = "id";
        $TableInfoArray[0]["type"] = "int";
        $TableInfoArray[0]["key"] = "primary key auto_increment";
        $TableInfoArray[0]["default"] = "";

        $TableInfoArray[1]["name"] = "soa_id";
        $TableInfoArray[1]["type"] = "int";
        $TableInfoArray[1]["key"] = "";
        $TableInfoArray[1]["default"] = "";

        $TableInfoArray[2]["name"] = "domain";
        $TableInfoArray[2]["type"] = "tinytext";
        $TableInfoArray[2]["key"] = "";
        $TableInfoArray[2]["default"] = "";
        $TableInfoArray[3]["name"] = "ttl";
        $TableInfoArray[3]["type"] = "int";
        $TableInfoArray[3]["key"] = "";
        $TableInfoArray[3]["default"] = "";

        $TableInfoArray[4]["name"] = "class";
        $TableInfoArray[4]["type"] = "tinytext";
        $TableInfoArray[4]["key"] = "";
        $TableInfoArray[4]["default"] = "";

        $TableInfoArray[5]["name"] = "type";
        $TableInfoArray[5]["type"] = "tinytext";
        $TableInfoArray[5]["key"] = "";
        $TableInfoArray[5]["default"] = "";

        $TableInfoArray[6]["name"] = "value1";
        $TableInfoArray[6]["type"] = "tinytext";
        $TableInfoArray[6]["key"] = "";
        $TableInfoArray[6]["default"] = "";

        $TableInfoArray[7]["name"] = "value2";
        $TableInfoArray[7]["type"] = "tinytext";
        $TableInfoArray[7]["key"] = "";
        $TableInfoArray[7]["default"] = "";

        $TableInfoArray[8]["name"] = "value3";
        $TableInfoArray[8]["type"] = "tinytext";
        $TableInfoArray[8]["key"] = "";
        $TableInfoArray[8]["default"] = "";

        $TableInfoArray[9]["name"] = "value4";
        $TableInfoArray[9]["type"] = "tinytext";
        $TableInfoArray[9]["key"] = "";
        $TableInfoArray[9]["default"] = "";

        $TableInfoArray[10]["name"] = "value5";
        $TableInfoArray[10]["type"] = "tinytext";
        $TableInfoArray[10]["key"] = "";
        $TableInfoArray[10]["default"] = "";

        $TableInfoArray[11]["name"] = "value6";
        $TableInfoArray[11]["type"] = "tinytext";
        $TableInfoArray[11]["key"] = "";
        $TableInfoArray[11]["default"] = "";
        $TableInfoArray[12]["name"] = "value7";
        $TableInfoArray[12]["type"] = "tinytext";
        $TableInfoArray[12]["key"] = "";
        $TableInfoArray[12]["default"] = "";

        $TableInfoArray[13]["name"] = "value8";
        $TableInfoArray[13]["type"] = "tinytext";
        $TableInfoArray[13]["key"] = "";
        $TableInfoArray[13]["default"] = "";

        $TableInfoArray[14]["name"] = "value9";
        $TableInfoArray[14]["type"] = "tinytext";
        $TableInfoArray[14]["key"] = "";
        $TableInfoArray[14]["default"] = "";

        $TableInfoArray[15]["name"] = "value10";
        $TableInfoArray[15]["type"] = "tinytext";
        $TableInfoArray[15]["key"] = "";
        $TableInfoArray[15]["default"] = "";

        $TableInfoArray[16]["name"] = "deleted";
        $TableInfoArray[16]["type"] = "int";
        $TableInfoArray[16]["key"] = "";
        $TableInfoArray[16]["default"] = "0";

        $oDatabase->CreateTableFromArray($TableName, $TableInfoArray);
}







$TTL = 7200;
$NegativeTTL = 7200;
$Refresh = 1800;
$Retry = 7200;
$Expire = 1209600;
$EmailAddress = "";

$ServerType = $oDNS->GetSetting("server_type");
if( $ServerType == "master")
{
	$TTL = $oDNS->GetSetting("ttl");
	if( (is_numeric($TTL) == false) || ($TTL < 0) )
	{
		$TTL = 7200;
	}

	$NegativeTTL = $oDNS->GetSetting("negative_ttl");
	if( (is_numeric($NegativeTTL) == false) || ($NegativeTTL < 0) )
	{
		$NegativeTTL = 7200;
	}

	$Refresh = $oDNS->GetSetting("refresh");
	if( (is_numeric($Refresh) == false) || ($Refresh < 0) )
	{
		$Refresh = 1800;
	}

	$Retry = $oDNS->GetSetting("retry");
	if( (is_numeric($Retry) == false) || ($Retry < 0) )
	{
		$Retry = 7200;
	}

	$Expire = $oDNS->GetSetting("expire");
	if( (is_numeric($Expire) == false) || ($Expire < 0) )
	{
		$Expire = 1209600;
	}

	$EmailAddress = $oDNS->GetSetting("email_address");


	$PrimaryNameServer = $oDNS->GetSetting("primary_name_server");
        if($PrimaryNameServer == "")
        {
        	$PrimaryNameServer = $_SERVER["SERVER_NAME"];
        }


}
else if($ServerType == "slave")
{
	$MasterHostName = $oDNS->GetSetting("master_host_name");
	$MasterIPAddress = $oDNS->GetSetting("master_ip_address");
	$MasterPassword = $oDNS->GetSetting("master_password");
	$MasterPublicKey = $oDNS->GetSetting("master_public_key");
}
else
{
	$ServerType == "no_dns";
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
		<title>DNS Settings | <?php print $oSettings->GetWebCPTitle(); ?></title>
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
                xmlhttp = null;
                count = 0;
                var d = new Date();

                function GetIP(HostName)
                {
                        elem = document.getElementById("MasterIPAddress");           

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



                function TestConnection()
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
    
                            xmlhttp.open("GET",'ajax/TestMasterConnection.php?C=' + RndString,false);
    
                            xmlhttp.send(null);
                            if(xmlhttp.responseText != "")
                            {
                                        alert(xmlhttp.responseText);
                            }
                }



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
									<a href="/dns/">
										DNS
									</a>
								</li>
								<li>
									<i class="active"></i>
									<a href="/dns/settings.php">
										DNS Settings
									</a>
								</li>
					
					
							</ol>
							<div class="page-header">
								<h1>DNS Settings</h1>
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
					


					<form name="DNSSettings" action="SaveDNSSettings.php" method="post">
					
					<div class="col-md-12">

						<div class="panel panel-default">

							<div class="panel-body">
	
							<h1>This Server</h1>
							This setting control whether this server is a master, slave, or not involved in your DNS setup!
							<p>
	
							<div class="form-group">
								<label class="col-sm-2 control-label">
								<b>This server is</b>:
								</label>
								<div class="col-sm-4">
									<span class="input-icon">
									<select name="ServerType" id="form-field-11" class="form-control">
									<option value="no_dns" <?php if($ServerType == "no_dns") print " selected "; ?>>Not involved with DNS</option>
									<option value="master" <?php if($ServerType == "master") print " selected "; ?>>A master</option>
									<option value="slave" <?php if($ServerType == "slave") print " selected "; ?>>A slave</option>
									</select>

									</span>
								</div>
							</div>
							</div>
						</div>
					</div>
	
					<div class="col-md-12">

						<div class="panel panel-default">

							<div class="panel-body">
	
							<h1>Authentication</h1>
							<?php
							if($ServerType == "slave")
							{
							?>
								Enter a password here. Then copy this public key as well as this password to the master DNS server.	
							<?php
							}
							else
							{
							?>
								If your slave DNS server is also being used to host web / email (ie, it needs to create DNS zones), then enter a password here and copy this password / public key into the <b>"Master Settings"</b> on each slave.<p>&nbsp;<p>This will allow the slave(s) to send their zone requests to this master to publish.

							<?php
							}
							?>
							<p>
							<div class="form-group" style="padding-bottom: 20px;">
								<label class="col-sm-2 control-label">
								<b>Password</b>:
								</label>
								<div class="col-sm-8">
									<span class="input-icon">
									<input type="text" name="Password" id="form-field-11" rows="10" class="form-control">
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
									<textarea readonly id="form-field-11" rows="15" class="form-control"><?php print file_get_contents($_SERVER["DOCUMENT_ROOT"]."/includes/protected/key.public"); ?></textarea>
									</span>
								</div>
							</div>
		
							</div>
						</div>
					</div>
					

					<?php

					if($ServerType == "slave")
					{
					?>
					<div class="col-md-12">

						<div class="panel panel-default">

							<div class="panel-body">
	
							<h1>Master Settings</h1>
								<p>&nbsp;<p>
								<input type="button" value="Test Connection" data-style="zoom-in" class="btn btn-green ladda-button" onclick="return TestConnection();">
								<p>&nbsp;<p>
							Enter the host name of the master here. Copy the password and public key from the master into here.	

                                                        <div class="form-group" style="padding-bottom: 20px;">
                                                                <label class="col-sm-2 control-label">
                                                                <b>Master's Host Name</b>:
                                                                </label>
                                                                <div class="col-sm-8">
                                                                        <span class="input-icon">
                                                                        <input type="text" value="<?php print $MasterHostName; ?>" name="MasterHostName" id="MasterHostName" rows="10" class="form-control" onblur="GetIP(this.value);">
                                                                        </span>
                                                                </div>
                                                        </div>
                                                        <br>
                                                        <div class="form-group" style="padding-bottom: 20px;">
                                                                <label class="col-sm-2 control-label">
                                                                <b>Master's IP Address</b>:
                                                                </label>
                                                                <div class="col-sm-8">
                                                                        <span class="input-icon">
                                                                        <input type="text" readonly id="MasterIPAddress" name="MasterIPAddress" value="<?php print $MasterIPAddress; ?>" class="form-control">
                                                                        </span>
                                                                </div>
                                                        </div>
                                                        <br>


							<div class="form-group" style="padding-bottom: 20px;">
								<label class="col-sm-2 control-label">
								<b>Master's Password</b>:
								</label>
								<div class="col-sm-8">
									<span class="input-icon">
									<input type="text" name="MasterPassword" id="form-field-11" rows="10" class="form-control">
									</span>
								</div>
							</div>
							<br>
							<div class="form-group" style="padding-bottom: 20px;">
								<label class="col-sm-2 control-label">
								<b>Master's Public Key</b>:
								</label>
								<div class="col-sm-8">
									<span class="input-icon">
									<textarea id="form-field-11" rows="15" class="form-control" name="MasterPublicKey"><?php print $MasterPublicKey; ?></textarea>
									</span>
								</div>
							</div>
		
							</div>
						</div>
					</div>
					<?php
					}
					else if($ServerType == "master")
					{
					?>
					<div class="col-md-12">

						<div class="panel panel-default">

							<div class="panel-body">
	
							<h1>SOA Settings</h1>
							<p>
	
							<div class="form-group" style="padding-bottom: 20px;">
								<label class="col-sm-2 control-label">
								<b>TTL</b>:
								</label>
								<div class="col-sm-8">
									<input name="TTL" value="<?php print $TTL; ?>" type="number" id="form-field-11" class="form-control">
								</div>
							</div>
							<br>
	
							<div class="form-group" style="padding-bottom: 20px;">
								<label class="col-sm-2 control-label">
								<b>Negative TTL</b>:
								</label>
								<div class="col-sm-8">
									<input name="NegativeTTL" value="<?php print $NegativeTTL; ?>" type="number" id="form-field-11" class="form-control">
								</div>
							</div>
							<br>
							
							<div class="form-group" style="padding-bottom: 20px;">
								<label class="col-sm-2 control-label">
								<b>Refresh</b>:
								</label>
								<div class="col-sm-8">
									<input name="Refresh" value="<?php print $Refresh; ?>" type="number" id="form-field-11" class="form-control">
								</div>
							</div>
							<br>


							<div class="form-group" style="padding-bottom: 20px;">
								<label class="col-sm-2 control-label">
								<b>Retry</b>:
								</label>
								<div class="col-sm-8">
									<input name="Retry" value="<?php print $Retry; ?>" type="number" id="form-field-11" class="form-control">
								</div>
							</div>
							<br>

							<div class="form-group" style="padding-bottom: 20px;">
								<label class="col-sm-2 control-label">
								<b>Expire</b>:
								</label>
								<div class="col-sm-8">
									<input name="Expire" value="<?php print $Expire; ?>" type="number" id="form-field-11" class="form-control">
								</div>
							</div>
							<br>

							<div class="form-group" style="padding-bottom: 20px;">
								<label class="col-sm-2 control-label">
								<b>Master Name Server</b>:
								</label>
								<div class="col-sm-8">
									<input name="PrimaryNameServer" value="<?php print $PrimaryNameServer; ?>" type="text" id="form-field-11" class="form-control">
								</div>
							</div>
							<br>


							<div class="form-group" style="padding-bottom: 20px;">
								<label class="col-sm-2 control-label">
								<b>Email Address</b>:
								</label>
								<div class="col-sm-8">
									<input name="EmailAddress" value="<?php print $EmailAddress; ?>" type="email" id="form-field-11" class="form-control">
								</div>
							</div>
							<br>


							</div>
						</div>
					</div>
	
					<?php					
					}
					?>












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
