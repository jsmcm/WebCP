<!-- start: MAIN NAVIGATION MENU -->
<ul class="main-navigation-menu">

	<?php
	include_once($_SERVER["DOCUMENT_ROOT"]."/vendor/autoload.php");
	$oReseller = new Reseller();
	
	if( ! isset($oDNS))
	{
		$oDNS = new DNS();
	}

	if( ($oUser->Role == "admin") || ($oUser->Role == "reseller") )
	{

		if(strstr($_SERVER["SCRIPT_FILENAME"], "/users/"))
		{
			print "<li class=\"active open\">";
		}
		else
		{
			print "<li>";
		}

		if($oUser->Role == "admin")
		{
		?>
			<a href="javascript:void(0)"><i class="clip-user-2"></i>
				<span class="title"> Users </span><i class="icon-arrow"></i>
				<span class="selected"></span>
			</a>
	
	                <ul class="sub-menu">
                        <?php
                        if(strstr($_SERVER["SCRIPT_FILENAME"], "/users/index.php"))
                        {
                                print "<li class=\"active open\">";
                        }
                        else
                        {
                                print "<li>";
                        }
                        ?>

                                <a href="/users/index.php">
                                        <span class="title"> Users </span>
                                </a>
                        </li>
                        <?php
                        if(strstr($_SERVER["SCRIPT_FILENAME"], "/users/resellers.php"))
                        {
                                print "<li class=\"active open\">";
                        }
                        else
                        {
                                print "<li>";
                        }
                        ?>

                                <a href="/users/resellers.php">
                                        <span class="title"> Resellers </span>
                                </a>
                        </li>
			</ul>
		<?php
		}
		else
		{
			
			print "<a href=\"/users/index.php\"><i class=\"clip-user-2\"></i>";
				print "<span class=\"title\"> Users </span><span class=\"selected\"></span>";
			print "</a>";
		}
		
		print "</li>";



	}
	else if( $oUser->Role == "client" || $oUser->Role == "reseller" || $oUser->Role == "admin" )
	{
		if( strstr($_SERVER["SCRIPT_FILENAME"], "/users/"))
		{
			print "<li class=\"active\">";
		}
		else
		{
			print "<li>";
		}
		
		print "<a href=\"/users/AddUser.php?UserID=".$oUser->ClientID."\"><i class=\"clip-user-2\"></i>";
			print "<span class=\"title\"> My Profile </span><span class=\"selected\"></span>";
		print "</a>";
	?>
		<ul class="sub-menu">
			<?php
			if(strstr($_SERVER["SCRIPT_FILENAME"], "/users/"))
			{
				print "<li class=\"active open\">";
			}
			else
			{
				print "<li>";
			}
			?>

				<a href="/logout.php">
					<span class="title"> <font color="red">Log out</font> </span>
				</a>
			</li>
		</ul>
		</li>


	
	
		<?php
	}

	if( $oUser->Role == "client" || $oUser->Role == "admin" || $oUser->Role == "reseller" )
	{
		if( (strstr($_SERVER["SCRIPT_FILENAME"], "/domains/")) || (strstr($_SERVER["SCRIPT_FILENAME"], "/ssl/")) || (strstr($_SERVER["SCRIPT_FILENAME"], "/freessl/")) )
		{
			print "<li class=\"active\">";
		}
		else
		{
			print "<li>";
		}
		?>
			<a href="javascript:void(0)"><i class="clip-globe"></i>
				<span class="title"> Domains </span><i class="icon-arrow"></i>
				<span class="selected"></span>
			</a>
			<ul class="sub-menu">
				<?php
				if(strstr($_SERVER["SCRIPT_FILENAME"], "/domains/index.php"))
				{
					print "<li class=\"active open\">";
				}
				else
				{
					print "<li>";
				}
				?>
	
					<a href="/domains/index.php">
						<span class="title"> Domains </span>
					</a>
				</li>
				<li>
					<a href="/domains/AddSubDomain.php">
						<span class="title"> > Sub Domains </span>
					</a>
				</li>
				<li>
					<a href="/domains/AddParkedDomain.php">
						<span class="title"> > Parked Domains </span>
					</a>
				</li>
				
				<?php
				if($oUser->Role == "admin")
				{
				?>
					<li>
						<a href="/ssl/index.php">
							<span class="title"> > SSL </span>
						</a>
					</li>
					<li>
						<a href="/freessl/index.php">
							<span class="title"> > Free SSL </span>
						</a>
					</li>
				<?php
				}
				?>

				
				
			</ul>
		</li>




	<?php
	}



	if( strstr($_SERVER["SCRIPT_FILENAME"], "/emails/"))
	{
		print "<li class=\"active\">";
	}
	else
	{
		print "<li>";
	}
	?>
 
		<a href="javascript:void(0)"><i class="fa fa-envelope-o"></i>
			<span class="title"> Emails </span><i class="icon-arrow"></i>
			<span class="selected"></span>
		</a>
		<ul class="sub-menu">
				<?php
				if(strstr($_SERVER["SCRIPT_FILENAME"], "/emails/index.php"))
				{
					print "<li class=\"active open\">";
				}
				else
				{
					print "<li>";
				}
				?>
				<a href="/emails/index.php">
					<span class="title"> Email Accounts </span>
				</a>
			</li>
				<?php
				if( (strstr($_SERVER["SCRIPT_FILENAME"], "/emails/forward.php") ) && ($ClientID > 0 ) )
				{
					print "<li class=\"active open\">";
				}
				else
				{
					print "<li>";
				}
				?>
				<a href="/emails/forward.php">
					<span class="title"> > Forwarding </span>
				</a>
			</li>
			</li>
				<?php
				if(strstr($_SERVER["SCRIPT_FILENAME"], "/emails/EmailTrace.php"))
				{
					print "<li class=\"active open\">";
				}
				else
				{
					print "<li>";
				}
				if( $ClientID > 0 )
				{
				?>
				<a href="/emails/EmailTrace.php">
				<?php
				}
				else
				{
					if( !isset($oEmail) )
					{
						$oEmail = new Email();
					}


					if( !isset($oSimpleNonce) )
					{
						$oSimpleNonce = new SimpleNonce();
					}

					$sidenav_user_name = "";
					$sidenav_local_part = "";
					$sidenav_domain_name = "";
					$sidenav_domainId = 0;

					$sidenav_NonceArrayMeta = array("loggedInId"=>$loggedInId);

					$sidenavNonceArray = $oSimpleNonce->GenerateNonce("emailTrace", $sidenav_NonceArrayMeta);



					$oEmail->GetEmailInfo($email_ClientID, $sidenav_user_name, $sidenav_local_part, $sidenav_domain_name, $sidenav_domainId);
					print "<a href=\"/emails/DoEmailTrace.php?Nonce=".$sidenavNonceArray["Nonce"]."&TimeStamp=".$sidenavNonceArray["TimeStamp"]."&SearchTerm=".$sidenav_local_part."@".$sidenav_domain_name."\">";
				}
				?>
					<span class="title"> > Email Trace </span>
				</a>
			</li>


				<?php
				if( $ClientID > 0)
				{
					if(strstr($_SERVER["SCRIPT_FILENAME"], "/emails/catchall/index.php"))
					{
						print "<li class=\"active open\">";
					}
					else
					{
						print "<li>";
					}
					?>
					<a href="/emails/catchall/index.php">
						<span class="title"> > Catch All</span>
					</a>
				</li>


				<?php
				}

				if(strstr($_SERVER["SCRIPT_FILENAME"], "/emails/autoreply/index.php"))
				{
					print "<li class=\"active open\">";
				}
				else
				{
					print "<li>";
				}
				?>
				
				<?php
				if( $ClientID > 0)
				{
				?>
					<a href="/emails/autoreply/index.php">
				<?php
				}
				else
				{
				?>
					<!--a href="/emails/autoreply/AddAutoReply.php"-->
					<a href="/emails/autoreply/index.php">
				<?php
				}
				?>
					<span class="title"> > Auto Reply</span>
				</a>
			</li>

				<?php
				if(strstr($_SERVER["SCRIPT_FILENAME"], "/emails/spamassassin/index.php"))
				{
					print "<li class=\"active open\">";
				}
				else
				{
					print "<li>";
				}
				?>
				<a href="/emails/spamassassin/index.php">
					<span class="title"> > Spam Assassin</span>
				</a>
			</li>
				

				<?php
				
				if($oUser->Role == "admin")	
				{
				
					if(strstr($_SERVER["SCRIPT_FILENAME"], "/emails/spamguard/index.php"))
					{
						print "<li class=\"active open\">";
					}
					else
					{
						print "<li>";
					}
					?>
					<a href="/emails/spamguard/index.php">
						<span class="title"> > Spamguard</span>
					</a>
					</li>
				<?php
				}
				?>	

				<?php
				if(strstr($_SERVER["SCRIPT_FILENAME"], "/emails/blacklist/index.php"))
				{
					print "<li class=\"active open\">";
				}
				else
				{
					print "<li>";
				}
				?>
				<a href="/emails/blacklist/index.php">
					<span class="title"> > Black List</span>
				</a>
			</li>


				<?php
				if(strstr($_SERVER["SCRIPT_FILENAME"], "/emails/whitelist/index.php"))
				{
					print "<li class=\"active open\">";
				}
				else
				{
					print "<li>";
				}
				?>
				<a href="/emails/whitelist/index.php">
					<span class="title"> > White List</span>
				</a>
			</li>



		<?php
		if($oUser->Role == "admin")
		{
		?>
				<?php
				if(strstr($_SERVER["SCRIPT_FILENAME"], "/emails/general_limits.php"))
				{
					print "<li class=\"active open\">";
				}
				else
				{
					print "<li>";
				}
				?>
				<a href="/emails/general_limits.php">
					<span class="title"> > General Limits </span>
				</a>
			</li>
				<?php
				if(strstr($_SERVER["SCRIPT_FILENAME"], "/emails/domain_limits.php"))
				{
					print "<li class=\"active open\">";
				}
				else
				{
					print "<li>";
				}
				?>
				<a href="/emails/domain_limits.php">
					<span class="title"> > Domain Specific Limits </span>
				</a>
			</li>
				<?php
				if(strstr($_SERVER["SCRIPT_FILENAME"], "/emails/routing/index.php"))
				{
					print "<li class=\"active open\">";
				}
				else
				{
					print "<li>";
				}
				?>
				<a href="/emails/routing/index.php">
					<span class="title"> > Mail Routing </span>
				</a>
			</li>
				<?php
				if(strstr($_SERVER["SCRIPT_FILENAME"], "/emails/dkim/index.php"))
				{
					print "<li class=\"active open\">";
				}
				else
				{
					print "<li>";
				}
				?>
				<a href="/emails/dkim/index.php">
					<span class="title"> > Enable Dkim </span>
				</a>
			</li>

		<?php
		}
		?>
		</ul>
        </li>


	<?php
	if( $oUser->Role == "client" || $oUser->Role == "admin" || $oUser->Role == "reseller" )
	{

	if( strstr($_SERVER["SCRIPT_FILENAME"], "/ftp/"))
	{
		print "<li class=\"active\">";
	}
	else
	{
		print "<li>";
	}
	
	print "<a href=\"/ftp/index.php\"><i class=\"fa fa-files-o\"></i>";
		print "<span class=\"title\"> FTP </span><span class=\"selected\"></span>";
	print "</a>";
	print "</li>";
	




	if($oUser->Role != "admin")
	{
		if( strstr($_SERVER["SCRIPT_FILENAME"], "/mysql/"))
		{
			print "<li class=\"active\">";
		}
		else
		{
			print "<li>";
		}
		
		print "<a href=\"/mysql/index.php\"><i class=\"clip-database\"></i>";
			print "<span class=\"title\"> MySQL </span><span class=\"selected\"></span>";
		print "</a>";
		print "</li>";
	}
	else
	{



	if( strstr($_SERVER["SCRIPT_FILENAME"], "/mysql/"))
	{
		print "<li class=\"active\">";
	}
	else
	{
		print "<li>";
	}
	?>
 
		<a href="javascript:void(0)"><i class="clip-database"></i>
			<span class="title"> MySQL </span><i class="icon-arrow"></i>
			<span class="selected"></span>
		</a>
		<ul class="sub-menu">
				<?php
				if(strstr($_SERVER["SCRIPT_FILENAME"], "/mysql/index.php"))
				{
					print "<li class=\"active open\">";
				}
				else
				{
					print "<li>";
				}
				?>
				<a href="/mysql/index.php">
					<span class="title"> MySQL </span>
				</a>
			</li>
				<?php
				if(strstr($_SERVER["SCRIPT_FILENAME"], "/mysql/EditRootPassword.php"))
				{
					print "<li class=\"active open\">";
				}
				else
				{
					print "<li>";
				}
				?>
				<a href="/mysql/EditRootPassword.php">
					<span class="title"> > Change Root Password</span>
				</a>
			</li>
		</ul>

        </li>
	<?php
	}
	?>

	<?php
	if( strstr($_SERVER["SCRIPT_FILENAME"], "/cron/"))
	{
		print "<li class=\"active\">";
	}
	else
	{
		print "<li>";
	}
	
	print "<a href=\"/cron/index.php\"><i class=\"clip-clock-2\"></i>";
		print "<span class=\"title\"> CRON </span><span class=\"selected\"></span>";
	print "</a>";
	print "</li>";


	if( ($oUser->Role == "admin") || ($oUser->Role == "reseller") )
	{
		if( strstr($_SERVER["SCRIPT_FILENAME"], "/packages/"))
		{
			print "<li class=\"active\">";
		}
		else
		{
			print "<li>";
		}
		
		print "<a href=\"/packages/index.php\"><i class=\"clip-cube-2\"></i>";
			print "<span class=\"title\"> Packages </span><span class=\"selected\"></span>";
		print "</a>";
		print "</li>";
	}





	if( strstr($_SERVER["SCRIPT_FILENAME"], "/Editor/") || strstr($_SERVER["SCRIPT_FILENAME"], "/installer/") )
	{
		print "<li class=\"active\">";
	}
	else
	{
		print "<li>";
	}
	?>
		<a href="javascript:void(0)"><i class="clip-file-plus"></i>
			<span class="title"> File Manager </span><i class="icon-arrow"></i>
			<span class="selected"></span>
		</a>
		<ul class="sub-menu">
				<?php
				if(strstr($_SERVER["SCRIPT_FILENAME"], "/Editor/index.php"))
				{
					print "<li class=\"active open\">";
				}
				else
				{
					print "<li>";
				}
				?>
				<a href="/Editor/index.php">
					<span class="title"> File Editor </span>
				</a>
			</li>

			<?php
			if(strstr($_SERVER["SCRIPT_FILENAME"], "/installer/index.php"))
			{
				print "<li class=\"active open\">";
			}
			else
			{
				print "<li>";
			}
			?>
			<a href="/installer/index.php">
				<span class="title"> Installer </span>
			</a>
			</li>
		</ul>
	</li>









	<?php
	if($oUser->Role == "admin")
	{
		if( strstr($_SERVER["SCRIPT_FILENAME"], "/mass_mail/"))
		{
			print "<li class=\"active\">";
		}
		else
		{
			print "<li>";
		}
	
		print "<a href=\"/mass_mail/index.php\"><i class=\"fa fa-bullhorn\"></i>";
			print "<span class=\"title\"> Mass Mail </span><span class=\"selected\"></span>";
		print "</a>";
		print "</li>";
	}


	if( strstr($_SERVER["SCRIPT_FILENAME"], "/passwd") || strstr($_SERVER["SCRIPT_FILENAME"], "/fail2ban/") )
	{
		print "<li class=\"active\">";
	}
	else
	{
		print "<li>";
	}
	?>
		<a href="javascript:void(0)"><i class="clip-locked"></i>
			<span class="title"> Security </span><i class="icon-arrow"></i>
			<span class="selected"></span>
		</a>
		<ul class="sub-menu">
				<?php
				if(strstr($_SERVER["SCRIPT_FILENAME"], "/passwd/index.php"))
				{
					print "<li class=\"active open\">";
				}
				else
				{
					print "<li>";
				}
				?>
				<a href="/passwd/index.php">
					<span class="title"> Password Protect Directories </span>
				</a>
			</li>

			<?php
			$FirewallControl = "";
			if($oUser->Role == "reseller")
			{
				$FirewallControl = $oReseller->GetResellerSetting($oUser->ClientID, "FirewallControl");
			}

			if($FirewallControl != "on")
			{
				$FirewallControl = "";
			}
	
			if( ($oUser->Role == "admin") || ($FirewallControl == "on") )
			{
				if(strstr($_SERVER["SCRIPT_FILENAME"], "/fail2ban/index.php"))
				{
					print "<li class=\"active open\">";
				}
				else
				{
					print "<li>";
				}
				?>
				<a href="/fail2ban/index.php">
					<span class="title"> Firewall </span>
				</a>
				</li>

				<?php
				if(strstr($_SERVER["SCRIPT_FILENAME"], "/fail2ban/ViewModsecWhiteList.php"))
				{
					print "<li class=\"active open\">";
				}
				else
				{
					print "<li>";
				}
				?>
				<a href="/fail2ban/ViewModsecWhiteList.php">
					<span class="title"> Modsec White List </span>
				</a>
				</li>
			<?php
			}
			?>
		</ul>
	</li>






	<?php
	if( strstr($_SERVER["SCRIPT_FILENAME"], "/quota") || strstr($_SERVER["SCRIPT_FILENAME"], "/skel") || strstr($_SERVER["SCRIPT_FILENAME"], "/server/") )
	{
		print "<li class=\"active\">";
	}
	else
	{
		print "<li>";
	}
	?>
		<a href="javascript:void(0)"><i class="clip-network"></i>
			<span class="title"> Server </span><i class="icon-arrow"></i>
			<span class="selected"></span>
		</a>
		<ul class="sub-menu">
				<?php
				if(strstr($_SERVER["SCRIPT_FILENAME"], "/quota/index.php"))
				{
					print "<li class=\"active open\">";
				}
				else
				{
					print "<li>";
				}
				?>
				<a href="/quota/index.php">
					<span class="title"> View Quota Usage </span>
				</a>
			</li>

			<?php
			if($oUser->Role == "admin")
			{
				if(strstr($_SERVER["SCRIPT_FILENAME"], "/server/settings.php"))
				{
					print "<li class=\"active open\">";
				}
				else
				{
						print "<li>";
				}
				?>
				<a href="/server/settings.php">
					<span class="title"> Server Settings </span>
				</a>
				</li>
				
				<?php
				if(strstr($_SERVER["SCRIPT_FILENAME"], "/skel/index.php"))
				{
					print "<li class=\"active open\">";
				}
				else
				{
						print "<li>";
				}
				?>
				<a href="/skel/index.php">
					<span class="title"> Skel Directory </span>
				</a>
				</li>


				<?php
				if(strstr($_SERVER["SCRIPT_FILENAME"], "/server/ip/index.php"))
				{
					print "<li class=\"active open\">";
				}
				else
				{
						print "<li>";
				}
				?>
				<a href="/server/ip/index.php">
					<span class="title"> IP Addresses </span>
				</a>
				</li>


			<?php
			}
			?>
		</ul>
	</li>




















	<?php

	if( (strstr($_SERVER["SCRIPT_FILENAME"], "/backups/")) || (strstr($_SERVER["SCRIPT_FILENAME"], "/restore/")) )
	{
		print "<li class=\"active\">";
	}
	else
	{
		print "<li>";
	}
	?>
		<a href="javascript:void(0)"><i class="clip-cloud"></i>
			<span class="title"> Backups </span><i class="icon-arrow"></i>
			<span class="selected"></span>
		</a>
		<ul class="sub-menu">
				<?php
				if(strstr($_SERVER["SCRIPT_FILENAME"], "/backups/index.php"))
				{
					print "<li class=\"active open\">";
				}
				else
				{
					print "<li>";
				}
				?>
				<a href="/backups/index.php">
					<span class="title"> Ad Hoc </span>
				</a>
			</li>

			<?php
			if($oUser->Role == "admin")
			{
				if(strstr($_SERVER["SCRIPT_FILENAME"], "/backups/daily.php"))
				{
					print "<li class=\"active open\">";
				}
				else
				{
					print "<li>";
				}
				?>
				<a href="/backups/daily.php">
					<span class="title"> Daily </span>
				</a>
			</li>
				<?php
				if(strstr($_SERVER["SCRIPT_FILENAME"], "/backups/weekly.php"))
				{
					print "<li class=\"active open\">";
				}
				else
				{
					print "<li>";
				}
				?>
				<a href="/backups/weekly.php">
					<span class="title"> Weekly </span>
				</a>
			</li>
			
			</li>
				<?php
				if(strstr($_SERVER["SCRIPT_FILENAME"], "/backups/monthly.php"))
				{
					print "<li class=\"active open\">";
				}
				else
				{
					print "<li>";
				}
				?>
				<a href="/backups/monthly.php">
					<span class="title"> Monthly </span>
				</a>
			</li>

			<li>
				<?php
				if(strstr($_SERVER["SCRIPT_FILENAME"], "/restore/index.php"))
				{
					print "<li class=\"active open\">";
				}
				else
				{
					print "<li>";
				}
				?>
				<a href="/restore/index.php">
					<span class="title"> Restore </span>
				</a>
			</li>
			<li>
				<?php
				if(strstr($_SERVER["SCRIPT_FILENAME"], "/backups/settings.php"))
				{
					print "<li class=\"active open\">";
				}
				else
				{
					print "<li>";
				}
				?>
				<a href="/backups/settings.php">
					<span class="title"> Settings </span>
				</a>
			</li>
			<?php
			}
			?>
		</ul>
	</li>

	<?php

	}


	if($oUser->Role == "admin")
	{
		$ServerType = $oDNS->GetSetting("server_type");

		if( (strstr($_SERVER["SCRIPT_FILENAME"], "/dns/")) )
		{
			print "<li class=\"active\">";
		}
		else
		{
			print "<li>";
		}
	?>
		<a href="javascript:void(0)"><i class="fa fa-random"></i>
			<span class="title"> DNS </span><i class="icon-arrow"></i>
			<span class="selected"></span>
		</a>
		<ul class="sub-menu">
		
		<?php
		if(strstr($_SERVER["SCRIPT_FILENAME"], "/dns/index.php"))
		{
			print "<li class=\"active open\">";
		}
		else
		{
			print "<li>";
		}
		?>
		<a href="/dns/index.php">
			<span class="title"> DNS </span>
		</a>
		</li>

		<li>
		<?php
		if(strstr($_SERVER["SCRIPT_FILENAME"], "/dns/settings.php"))
		{
			print "<li class=\"active open\">";
		}
		else
		{
			print "<li>";
		}
		?>
		<a href="/dns/settings.php">
			<span class="title"> Settings </span>
		</a>
		</li>

		<?php
		if($ServerType == "master")
		{
		?>
		<li>
		<?php
		if(strstr($_SERVER["SCRIPT_FILENAME"], "/dns/slaves.php"))
		{
			print "<li class=\"active open\">";
		}
		else
		{
			print "<li>";
		}
		?>
		<a href="/dns/slaves.php">
			<span class="title"> Slaves </span>
		</a>
		</li>
		<?php
		}
		?>
		</ul>
	</li>
	<?php
	}
	?>




	<?php
	if($oUser->Role == "admin")
	{
		if( strstr($_SERVER["SCRIPT_FILENAME"], "/modules/") )
		{
			print "<li class=\"active\">";
		}
		else
		{
			print "<li>";
		}
		?>
			<a href="javascript:void(0)"><i class="clip-tree"></i>
				<span class="title"> Modules </span><i class="icon-arrow"></i>
				<span class="selected"></span>
			</a>
			<ul class="sub-menu">
					<?php
					if(strstr($_SERVER["SCRIPT_FILENAME"], "/modules/billing/whmcs/index.php"))
					{
						print "<li class=\"active open\">";
					}
					else
					{
						print "<li>";
					}
					?>
					<a href="/modules/billing/whmcs/index.php">
						<span class="title"> Billing - WHMCS </span>
					</a>
				</li>
			</ul>
		</li>
	<?php
	}
	?>	





		<?php
		if( strstr($_SERVER["SCRIPT_FILENAME"], "/help/") )
		{
			print "<!--li class=\"active\">";
		}
		else
		{
			print "<!--li>";
		}
		?>
			<a href="javascript:void(0)"><i class="clip-tree"></i>
				<span class="title"> Help </span><i class="icon-arrow"></i>
				<span class="selected"></span>
			</a>
			<ul class="sub-menu">
				<li>
					<a href="http://bug.webcp.pw/bug_report_page.php" target="_new">
						<span class="title"> Bug Report </span>
					</a>
				</li>
				<li>
					<a href="http://bug.webcp.pw/bug_report_page.php" target="_new">
						<span class="title"> Feature Request </span>
					</a>
				</li>
			</ul>
		</li-->




</ul>
<!-- end: MAIN NAVIGATION MENU -->
