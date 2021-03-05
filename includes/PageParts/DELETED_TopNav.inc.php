<div style="height:35px; background-color:blue; font-size:18px; padding-top:8px; padding-left:85px; color:white; font-family: 'Droid Sans', Verdana;">


<ul class="dropdown">

		<li>
			<a href="/users/index.php">Users</a>
		</li>


	<li>
		<a href="/domains/index.php">Domains</a>

		<ul>
			<li>
				<a href="/domains/AddSubDomain.php">Sub Domains</a>
			</li>
			
			<li>
				<a href="/domains/AddParkedDomain.php">Parked Domains</a>
			</li>
			
			<?php	
			if($oUser->Role == "admin")
			{
			?>
			<li>
				<a href="/domains/Suspensions.php">Suspend / Unsuspend</a>
			</li>
			<?php
			}
			?>
		</ul>
	</li>

	<li>
		<a href="/emails/index.php">Emails</a>
		<ul>
			<li>
				<a href="/emails/forward.php">Fowarding</a>
			</li>
		
			<?php	
			if($oUser->Role == "admin")
			{
				print "<li>";
					print "<a href=\"/emails/general_limits.php\">General Limits</a>";
				print "</li>";	
				print "<li>";
					print "<a href=\"/emails/domain_limits.php\">Domain Specific Limits</a>";
				print "</li>";	
			}
			?>
		</ul>
	</li>

	<li>
		<a href="/ftp/index.php">FTP</a>
	</li>

	<li>
		<a href="/mysql/index.php">MySQL</a>
		<?php	
		if($oUser->Role == "admin")
		{
			print "<ul>";
			print "<li>";
				print "<a href=\"/mysql/EditRootPassword.php\">Edit Root Password</a>";
			print "</li>";
			print "</ul>";
		}
		?>
	</li>

	<li> 
		<a href="/cron/index.php">Cron</a>
	</li>

<?php 
 
if(isset($oUser))
{
	if($oUser->Role == "admin")
	{
		print "<li>";
			print "<a href=\"/packages/index.php\">Packages</a>";
		print "</li>";
	}
}

		print "<li><a>File Manager</a>";
		print "<ul>";
		print "<li>";
			print "<a href=\"/Editor/index.php\">File Editor</a>";
		print "</li>";
		
		print "<li>";
		print "<a href=\"/installer/index.php\">Installer</a>";
		print "</li>";
		print "</ul>";
		print "</li>";

	if($oUser->Role == "admin")
	{
		print "<li>";
			print "<a href=\"/mass_mail/index.php\">Mass Mail</a>";
		print "</li>";
	}
			
	print "<li>";
		print "<a>Security</a>";
	
		print "<ul>";
		print "<li>";
		print "<a href=\"/passwd/index.php\">Password Protect Directories</a>";
		print "</li>";
	
		if($oUser->Role == "admin")
		{	
			print "<li>";
			print "<a href=\"/fail2ban/index.php\">Firewall</a>";
			print "</li>";
			print "<li>";
			print "<a href=\"/fail2ban/index.php\">Firewall</a>";
			print "</li>";
		}

		print "</ul>";

	print "</li>";

	if($oUser->Role == "admin")
	{
		print "<li>";
			print "<a href=\"/server/index.php\">Server</a>";
		
			print "<ul>";
			print "<li>";
				print "<a href=\"/quota/index.php\">View Disk Usage</a>";
			print "</li>";
			print "<li>";
				print "<a href=\"/server/settings.php\">Server Settings</a>";
			print "</li>";
			print "<li>";
				print "<a href=\"/skel/index.php\">Skel Directory</a>";
			print "</li>";
			print "</ul>";
		print "</li>";

	}
	else
	{
		print "<li>";
			print "<a href=\"/quota/index.php\">View Disk Usage</a>";
		print "</li>";
	}	

	print "<li>";
		print "<a href=\"/backups/index.php\">Backups</a>";
		
		if($oUser->Role == "admin")
		{
			print "<ul>";
			print "<li>";
				print "<a href=\"/backups/daily.php\">Daily</a>";
			print "</li>";
			print "<li>";
				print "<a href=\"/backups/weekly.php\">Weekly</a>";
			print "</li>";
			print "<!--li>";
				print "<a href=\"/backups/monthly.php\">Monthly</a>";
			print "</li-->";
			print "<li>";
				print "<a href=\"/restore/index.php\">Restore</a>";
			print "</li>";
			print "</ul>";
		}
	print "</li>";






	if($oUser->Role == "admin")
	{
		print "<li>";
		print "<a>Modules</a>";
		
		print "<ul>";
		print "<li>";
			print "<a href=\"/modules/billing/whmcs/\">Billing - WHMCS</a>";
		print "</li>";
		print "<ul>";
		
		print "</li>";
	}




?>
</ul>

</div>
