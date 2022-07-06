<?php
session_start();

include_once($_SERVER["DOCUMENT_ROOT"]."/vendor/autoload.php");

$oUser = new User();
$oPackage = new Package();
$oDomain = new Domain();
$oSettings = new Settings();

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


if($DomainID > -1)
{

		$random = random_int(1, 1000000);
		$nonceArray = [	
			$oUser->Role,
			$oUser->ClientID,
			$DomainID,
			$random
		];
		$oSimpleNonce = new SimpleNonce();
		
		$nonce = $oSimpleNonce->GenerateNonce("getDomainInfo", $nonceArray);
		
		$DomainInfoArray = array();
		$oDomain->GetDomainInfo($DomainID, $random, $DomainInfoArray, $nonce);
	
        $DomainUserName = $DomainInfoArray["UserName"];
        $FTPAllowance = $oPackage->GetPackageAllowance("FTP", $DomainInfoArray["PackageID"]);
        $DomainOwnerClientID = $DomainInfoArray["ClientID"];
        $Role = 'client';
        $ClientID = $DomainOwnerClientID;

        $FTPUsage = $oPackage->GetFTPUsage($DomainID);

	/*  
	print "<p>&nbsp;<p>";     
	print "<p>&nbsp;<p>";     
	print "<p>&nbsp;<p>";     
	print "<p>&nbsp;<p>";     
	print "FTPAllowance: ".$FTPAllowance."<br>";
        print "FTPUsage: ".$FTPUsage."<br>";
        print "DomainID: ".$DomainID."<br>";
        print "ClientID: ".$ClientID."<br>";
        print "DomainUserName: ".$DomainUserName."<br>";
        print "DomainOwnerClientID: ".$DomainOwnerClientID."<br>";
	*/

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
		<title>Add FTP | <?php print $oSettings->GetWebCPTitle(); ?></title>
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
	//alert(DomainID);

        if(DomainID > -1)
        {
                document.AddFTP.action="AddFTP.php";
                document.AddFTP.submit();
        }
}




function ChangeDomainUserName(Value)
{
	elem = document.getElementById("DomainUserName");

	elem.innerHTML = Value + "_";
}

function DoSubmit()
{
	if(document.AddFTP.DomainUserName.value == "")
	{
		alert("Please select a domain name");
		document.AddFTP.DomainUserName.focus();
		return false;
	}

	if(document.AddFTP.UserName.value == "")
	{
		alert("Please enter an FTP user name");
		document.AddFTP.UserName.focus();
		return false;
	}


			if(document.AddFTP.Password.value == "")
			{
				alert("Please enter a password");
				document.AddFTP.Password.focus();
				return false;
			}
			
			if(document.AddFTP.Password.value != document.AddFTP.PasswordAgain.value)
			{
				alert("You passwords don't match");
				document.AddFTP.Password.value = "";
				document.AddFTP.PasswordAgain.value = "";
				document.AddFTP.Password.focus();
				return false;
			}
			
			
			if(CheckPassword(document.AddFTP.Password.value) < 3)
			{
				alert("Please enter a stronger password - it should contain at least 1 special character, 1 number, 1 lower case letter and 1 upper case letter");
				document.AddFTP.Password.value = "";
				document.AddFTP.PasswordAgain.value = "";
				document.AddFTP.Password.focus();
				return false;
			}
				
	UserName = document.AddFTP.UserName.value;

	for(x = 0; x < UserName.length; x++)
	{
		cc = UserName.substr(x, 1);	
		//alert(cc);
		if(!( (cc >= '0' && cc <= '9')  || (cc >= 'a' && cc <= 'z') || cc == '_' || cc == '-' ) )
	        {
			alert("FTP user names an only use letters (a - z), numbers (0 - 9) and the special characters  - _ 	");
			document.AddFTP.UserName.focus();
			return false;
	        }	
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
									<a href="/ftp/">
										FTP
									</a>
								</li>
								<li>
									<i class="active"></i>
									<a href="/ftp/AddFTP.php">
										Add
									</a>
								</li>
					
							</ol>
							<div class="page-header">
								<h1>Add FTP </h1>
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
										if( ($FTPAllowance == -1) || ( $FTPUsage < $FTPAllowance) )
										{
										?>
											<div class="alert alert-block alert-success fade in">
											<button data-dismiss="alert" class="close" type="button">
											&times;
											</button>
											<h4 class="alert-heading"><i class="fa fa-check-circle"></i> FTP Available!</h4>
											<p>
												You have used <?php print $FTPUsage; ?> of <?php print ($FTPAllowance==-1)?"unlimited":$FTPAllowance; ?> FTP accounts
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
											<h4 class="alert-heading"><i class="fa fa-times-circle"></i> No FTP Available!</h4>
											<p>
												You have used <?php print $FTPUsage." of ".$FTPAllowance; ?> FTP accounts
											</p>
											</div>
										<?php
										}
									}
									?>
								
							<!-- start: DYNAMIC TABLE PANEL -->
							<div class="panel panel-default">
									
								<div class="panel-body">
					

								<form name="AddFTP" method="post" action="DoAddFTP.php" class="form-horizontal">
									

										<div class="form-group">
											<label class="col-sm-2 control-label">
												Domain:
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
												User Name:
											</label>
											<div class="col-sm-4">
												<span class="input-icon">


                                   	<table border="0" wifth="100%" cellpadding="0" cellspacing="0">
					<tr>
						<td width="1">
							<b id="DomainUserName">
							<?php 
							if($DomainUserName != "")
							{
								print $DomainUserName."_"; 
							}
							?>
							</b>
						</td>
						<td width="*">
							<input name="UserName" type="text" id="form-field-11" class="form-control">
						</td>
					</tr>
					</table>


												</span>										
											</div>
										</div>



										
								
										<div class="form-group">
											<label class="col-sm-2 control-label">
												Password:
											</label>
											<div class="col-sm-4">
												<span class="input-icon">
												<input name="Password" type="password" id="form-field-11" class="form-control">
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
												<input name="PasswordAgain" type="password" id="form-field-11" class="form-control">
												<i class="fa clip-key-3"></i>
												</span>										
											</div>
										</div>

							
                        							<?php
							                        if( ($DomainID > -1) && (($FTPUsage < $FTPAllowance) || ($FTPAllowance == -1) || $oUser->Role == "admin") )
							                        {
							                        ?>
										<div class="form-group">

											<div class="col-sm-4">
<input type="hidden" name="DomainUserName" value="<?php print $DomainUserName; ?>">	
											<input type="submit" value="Add FTP Account" data-style="zoom-in" class="btn btn-info ladda-button" onclick="return DoSubmit(); return false;">
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
