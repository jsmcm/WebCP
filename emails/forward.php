<?php
session_start();

include_once($_SERVER["DOCUMENT_ROOT"]."/vendor/autoload.php");
$oSimpleNonce = new SimpleNonce();
$oUser = new User();
$oDomains = new Domain();
$oSettings = new Settings();
$oEmail = new Email();


$ClientID = $oUser->GetClientID();
$loggedInId = $ClientID;

$email_ClientID = $oEmail->getLoggedInEmailId();

if($ClientID < 1)
{
        if( $email_ClientID < 1 )
        {
                header("Location: /index.php");
                exit();
        }
        $loggedInId = $email_ClientID;
}


$NonceArrayMeta = array("id"=>$loggedInId);
$AddForwarderNonceArray = $oSimpleNonce->GenerateNonce("addSingleForwarder", $NonceArrayMeta);

?>

<!DOCTYPE html>
<!--[if IE 8]><html class="ie8 no-js" lang="en"><![endif]-->
<!--[if IE 9]><html class="ie9 no-js" lang="en"><![endif]-->
<!--[if !IE]><!-->
<html lang="en" class="no-js">
	<!--<![endif]-->
	<!-- start: HEAD -->
	<head>
		<title>Email Forwarding | <?php print $oSettings->GetWebCPTitle(); ?></title>
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

			function ValidateSingleForwarder()
			{

				if(document.SingleForwarder.localPart.value == "")
				{
					alert("Please select a Local Part for the email address!");
					document.SingleForwarder.localPart.focus();
					return false;
				}


				if(document.SingleForwarder.ForwardTo.value == "")
				{
					alert("Please select a forwarding email address!");
					document.SingleForwarder.ForwardTo.focus();
					return false;
				}
				
				return true;
			}


			function ConfirmDelete()
			{
				if(confirm("Are you sure you want to delete this forwarder?"))
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
									<a href="/emails/forward.php">
										Forwarding
									</a>
								</li>
					
					
							</ol>
							<div class="page-header">
								<h1>Email Forwarding  <small>Add / remove</small></h1>
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
												<th>Email Address</th>
<th>Forward to</th>

												<th>&nbsp;</th>
												
											</tr>
										</thead>
										
										
										<tbody>

										<?php
										$oEmail = new Email();


										if(isset($_REQUEST["ClientID"]))
										{
											if($oUser->Role == "admin")
											{
												//yes, permission..
												$ClientID = $_REQUEST["ClientID"];
											}
										}
										
										//print "ClientID: ".$ClientID."<p>";
										//print "Role: ".$oUser->Role."<p>";

										if ( $ClientID > 0 )
										{
											$oEmail->GetSingleForwardList($Array, $ArrayCount, $ClientID, $oUser->Role);
										}
										else if ( $email_ClientID > 0 )
										{
											$oEmail->GetSingleForwardList($Array, $ArrayCount, $email_ClientID, "email");
										}

										for($x = 0; $x < $ArrayCount; $x++)
										{
											$localPart = substr($Array[$x]["EmailAddress"], 0, strpos($Array[$x]["EmailAddress"], "@"));

											$NonceArrayMeta = array();
											$NonceArrayMeta = array("loggedInId"=>$loggedInId, "forwarderId"=>$Array[$x]["ID"], "localPart" => $localPart);

											$DeleteForwarderNonceArray = $oSimpleNonce->GenerateNonce("deleteSingleForwarder", $NonceArrayMeta);
											print "<tr>";
											print "<td>".$Array[$x]["EmailAddress"]."</td>\r\n";	
											print "<td>".$Array[$x]["ForwardTo"]."</td>\r\n";	
												

													print "<td class=\"center\">";
													print "<div class=\"visible-md visible-lg hidden-sm hidden-xs\">";
														
													print "<a href=\"DeleteSingleForwarder.php?Nonce=".$DeleteForwarderNonceArray["Nonce"]."&TimeStamp=".$DeleteForwarderNonceArray["TimeStamp"]."&id=".$Array[$x]["ID"]."&localPart=".$localPart."\" onclick=\"return ConfirmDelete(); return false;\" class=\"btn btn-bricky tooltips\" data-placement=\"top\" data-original-title=\"Delete Forwarder\"><i class=\"fa fa-times fa fa-white\" style=\"color:white;\"></i></a>\n";
													print "</div>";
													print "<div class=\"visible-xs visible-sm hidden-md hidden-lg\">";
														print "<div class=\"btn-group\">";
															print "<a class=\"btn btn-primary dropdown-toggle btn-sm\" data-toggle=\"dropdown\" href=\"#\">";
																print "<i class=\"fa fa-cog\"></i> <span class=\"caret\"></span>";
															print "</a>";
															print "<ul role=\"menu\" class=\"dropdown-menu pull-right\">";
															print "<li role=\"presentation\">";
															print "<a role=\"menuitem\" tabindex=\"-1\" href=\"DeleteSingleForwarder.php?Nonce=".$DeleteForwarderNonceArray["Nonce"]."&TimeStamp=".$DeleteForwarderNonceArray["TimeStamp"]."&id=".$Array[$x]["ID"]."&localPart=".$localPart."\" onclick=\"return ConfirmDelete(); return false;\">";
															print "<i class=\"fa fa-times\"></i> Delete Forwarder";
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

<h1>Add new forwarder</h1>
<form name="SingleForwarder" method="post" action="DoAddSingleForwarder.php" class="form-horizontal">

<?php
if( $ClientID > 0 )
{


?>
	<input type="hidden" name="Nonce" value="<?php print $AddForwarderNonceArray["Nonce"]; ?>">
	<input type="hidden" name="TimeStamp" value="<?php print $AddForwarderNonceArray["TimeStamp"]; ?>">

	<div class="form-group">
	<label class="col-sm-2 control-label">
	Email Address:
	</label>       
	<div class="col-sm-4">
	<span class="input-icon">
	<table border="0">
	<tr>
	<td>
	<input name="localPart" type="text" placeholder="Local Part" id="form-field-11" class="form-control">
	</td>
	<td>
	<b>@</b>
	</td>
	<td>
	<select name="domainId" class="form-control" id="email">
	
	<option value="-1">Select Domain</option>
	<?php
	$oDomain = new Domain();
	
	
	$oDomain->GetDomainList($DomainArray, $ArrayCount, $ClientID, $oUser->Role);
	
	for($x = 0; $x < $ArrayCount; $x++)
	{
		print "<option value=\"".$DomainArray[$x]["id"]."\"";
		print ">".$DomainArray[$x]["domain_name"]."</option>";
	}
	
	?>
	</select>      
	</td>
	</tr>
	</table>
	</span>
	</div>
	</div>
<?php
}
else
{
	
	$userName = "";
	$localPart = "";
	$domainName = "";
	$domainId = 0;
	$oEmail->GetEmailInfo($email_ClientID, $userName, $localPart, $domainName, $domainId);


	
?>
	<input type="hidden" name="Nonce" value="<?php print $AddForwarderNonceArray["Nonce"]; ?>">
	<input type="hidden" name="TimeStamp" value="<?php print $AddForwarderNonceArray["TimeStamp"]; ?>">
	<input type="hidden" name="localPart" value="<?php print $localPart; ?>">
	<input type="hidden" name="domainId" value="<?php print $domainId; ?>">
<?php
}
?>


<div class="form-group">
<label class="col-sm-2 control-label">
Forward To:
</label>
<div class="col-sm-4">
<span class="input-icon">
<input name="ForwardTo" type="text" id="form-field-11" class="form-control">
<i class="fa fa-envelope-o"></i>
</span>
</div>
</div>

<div class="form-group">

<div class="col-sm-4">
<input type="submit" value="Add new single forwarder" data-style="zoom-in" class="btn btn-info ladda-button" onclick="return ValidateSingleForwarder(); return false;">

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
