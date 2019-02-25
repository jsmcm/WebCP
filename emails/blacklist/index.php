<?php
session_start();

include_once($_SERVER["DOCUMENT_ROOT"]."/vendor/autoload.php");

$oUser = new User();
$oDomains = new Domain();
$oSettings = new Settings();
$oEmail = new Email();

require($_SERVER["DOCUMENT_ROOT"]."/includes/License.inc.php");



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

?>

<!DOCTYPE html>
<!--[if IE 8]><html class="ie8 no-js" lang="en"><![endif]-->
<!--[if IE 9]><html class="ie9 no-js" lang="en"><![endif]-->
<!--[if !IE]><!-->
<html lang="en" class="no-js">
	<!--<![endif]-->
	<!-- start: HEAD -->
	<head>
		<title>Black List | <?php print $oSettings->GetWebCPTitle(); ?></title>
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


		<link href="/assets/plugins/bootstrap-modal/css/bootstrap-modal-bs3patch.css" rel="stylesheet" type="text/css"/>

		<link href="/assets/plugins/bootstrap-modal/css/bootstrap-modal.css" rel="stylesheet" type="text/css"/>

		<!-- end: CSS REQUIRED FOR THIS PAGE ONLY -->
		<link rel="shortcut icon" href="/favicon.ico" />
		
		
		<script language="javascript">

			function ValidateBlackList()
			{
			
				EmailAddress = document.BlackList.BlackListAddress.value;

				if(document.BlackList.DomainNameOrEmailAddress.value == "-1")
				{
					alert("Please select a domain or email address which this black listing applies to!");
					document.BlackList.DomainNameOrEmailAddress.focus();
					return false;
				}

				if(EmailAddress.indexOf(",") > -1)
				{
					alert("Please enter only a SINGLE email address / domain to black list!");
					document.BlackList.BlackListAddress.focus();
					return false;
				}
				
				if(EmailAddress.indexOf(" ") > -1)
				{
					alert("Please enter only a SINGLE email address / domain to black list!");
					document.BlackList.BlackListAddress.focus();
					return false;
				}
				

				if(EmailAddress == "")
				{
					alert("Please enter the email address / domain to black list!");
					document.BlackList.BlackListAddress.focus();
					return false;
				}
				
				return true;
			}


			function ConfirmDelete()
			{
				if(confirm("Are you sure you want to delete this black listing?"))
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
									<a href="/emails/">
										Emails
									</a>
								</li>
								<li>
									<i class="active"></i>
									<a href="/emails/blacklist/index.php">
										Black List
									</a>
								</li>
					
					
							</ol>
							<div class="page-header">
								<h1>Black List  <small>Add / remove</small></h1>
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
									<table class="table table-bordered table-full-width table-hover table-striped" id="sample_1">
										<thead>
											<tr>
												<th>Black Listed Address</th>
<th>You Address</th>

												<th>&nbsp;</th>
												
											</tr>
										</thead>
										
										
										<tbody>

										<?php
										$oEmail = new Email();


										$BlackListArray = array();
										$ArrayCount = 0;

										$oEmail->GetUserBlackWhiteList($BlackListArray, $ArrayCount, $loggedInId, $Role, "black");

										for($x = 0; $x < $ArrayCount; $x++)
										{
											print "<tr>";
											
											print "<td>".$BlackListArray[$x]["ListedEmail"]."</td>\r\n";	

											$ClientInfo = "";
											if($BlackListArray[$x]["ClientEmail"] != "")
											{
												$ClientInfo = $BlackListArray[$x]["ClientEmail"];
											}
											else if($BlackListArray[$x]["ClientDomain"] != "")
											{
												$ClientInfo = $BlackListArray[$x]["ClientDomain"];
											}
												
											print "<td>".$ClientInfo."</td>\r\n";	
												

													print "<td class=\"center\">";
													print "<div class=\"visible-md visible-lg hidden-sm hidden-xs\">";
														
													print "<a href=\"DeleteBlackList.php?id=".$BlackListArray[$x]["ID"]."\" onclick=\"return ConfirmDelete(); return false;\" class=\"btn btn-bricky tooltips\" data-placement=\"top\" data-original-title=\"Delete Black Listing\"><i class=\"fa fa-times fa fa-white\" style=\"color:white;\"></i></a>\n";
													print "</div>";
													print "<div class=\"visible-xs visible-sm hidden-md hidden-lg\">";
														print "<div class=\"btn-group\">";
															print "<a class=\"btn btn-primary dropdown-toggle btn-sm\" data-toggle=\"dropdown\" href=\"#\">";
																print "<i class=\"fa fa-cog\"></i> <span class=\"caret\"></span>";
															print "</a>";
															print "<ul role=\"menu\" class=\"dropdown-menu pull-right\">";
															print "<li role=\"presentation\">";
															print "<a role=\"menuitem\" tabindex=\"-1\" href=\"DeleteBlackList.php?id=".$BlackListArray[$x]["ID"]."\" onclick=\"return ConfirmDelete(); return false;\">";
															print "<i class=\"fa fa-times\"></i> Delete Black Listing";
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
							
								</div>
							</div>
							<!-- end: DYNAMIC TABLE PANEL -->





<div class="panel panel-default">

<div class="panel-body">

<h1>Add new Black List</h1>

<?php
if( $ClientID > 0 )
{
?>
	You can add a black list to either an entire domain, or to a specific email address
	<p>&nbsp<br>
<?php
}
?>


<form name="BlackList" method="post" action="DoAddBlackList.php" class="form-horizontal">

<?php
if( $ClientID > 0 )
{
?>
	<div class="form-group">
	<label class="col-sm-2 control-label">
	Domain / Email Address:
	</label>       
	<div class="col-sm-4">
	<span class="input-icon">
	<select name="DomainNameOrEmailAddress" class="form-control" id="email">
	
	<option value="-1" style="font-weight:bold; color:blue;">Select Domain</option>
	<?php
	$oDomain = new Domain();
	
	$oDomain->GetDomainList($DomainArray, $ArrayCount, $ClientID, $oUser->Role);
	
	for($x = 0; $x < $ArrayCount; $x++)
	{
		print "<option value=\"".$DomainArray[$x]["domain_name"]."\"";
			
		if($EmailAddress == $DomainArray[$x]["domain_name"])
		{
			print " selected ";
		}
	
		print ">".$DomainArray[$x]["domain_name"]."</option>";
	}
	
	
	print "<option value=\"-1\"></option>";
	print "<option value=\"-1\" style=\"font-weight:bold; color:red;\">---------------------</option>";
	print "<option value=\"-1\"></option>";
	
	print "<option value=\"-1\" style=\"color:blue; font-weight:bold;\">Select Email Address</option>";
	
	$oEmail = new Email();
	
	$EmailArray = array();
	$ArrayCount = 0;
	
	$oEmail->GetEmailList($EmailArray, $ArrayCount, $ClientID, $oUser->Role);
	
	for($x = 0; $x < $ArrayCount; $x++)
	{
		print "<option value=\"".$EmailArray[$x]["local_part"]."@".$EmailArray[$x]["fqdn"]."\"";
			
		if($EmailAddress == $EmailArray[$x]["local_part"]."@".$EmailArray[$x]["fqdn"])
		{
			print " selected ";
		}
	
		print ">".$EmailArray[$x]["local_part"]."@".$EmailArray[$x]["fqdn"]."</option>";
	}
	
	?>
	      
	</select>      
	</div>
	</div>
<?php
}
else if ($email_ClientID > 0 )
{
        $userName = "";
        $localPart = "";
        $domainName = "";
        $domainId = 0;


	$oEmail->GetEmailInfo($email_ClientID, $userName, $localPart, $domainName, $domainId);


	print "<input name=\"DomainNameOrEmailAddress\" type=\"hidden\" value=\"".$localPart."@".$domainName."\">";
}
?>

<div class="form-group">
<label class="col-sm-2 control-label">
Black List:
</label>
<div class="col-sm-4">
<span class="input-icon">
<input name="BlackListAddress" type="text" id="form-field-11" class="form-control">
<i class="fa fa-envelope-o"></i>
</span>
You can black list an email address, eg, <b>person@example.com</b>, or a domain, eg, <b>example.com</b>. 
<p>&nbsp;<br>
<b>BE WARNED</b>: black listing is done on a partial match. If you want to black list your "former" friend, "John" and you enter <b><font color="red">jo</font></b> in here, that will black list anything with <b><font color="red">jo</font></b> in, eg, <b>mike@<font color="red">jo</font>hannesburg.com</b>, <b><font color="red">jo</font>anne@iscool.com</b>, etc.

</div>
</div>

<div class="form-group">

<div class="col-sm-4">
<input type="submit" value="Add Black List" data-style="zoom-in" class="btn btn-info ladda-button" onclick="return ValidateBlackList(); return false;">

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

		<div id="full-width" class="modal container fade" tabindex="-1" style="display: none;">

			<div class="modal-header">

				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">

					&times;

				</button>

				<h4 class="modal-title"><span id="ModalSettingsHeading"></span></h4>

			</div>

			<div class="modal-body">
				<span id="ModalSettings"></span>
				</p>

			</div>

			<div class="modal-footer">

				<button type="button" data-dismiss="modal" class="btn btn-default">

					Close

				</button>


			</div>

		</div>

<div id="ajax-modal" class="modal fade" tabindex="-1" style="display: none;"></div>	



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



		<script>
			jQuery(document).ready(function() {
				Main.init();
				TableData.init();
				UIModals.init();
			});
		</script>
	</body>
	<!-- end: BODY -->
</html>
