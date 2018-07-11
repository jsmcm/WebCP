<div class="footer-inner">
<?php
require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.Settings.php");
$oSettings = new Settings();
?>
	2018 &copy; <?php print $oSettings->GetWebCPLink(); ?> <?php include($_SERVER["DOCUMENT_ROOT"]."/includes/version.inc"); ?>
</div>
