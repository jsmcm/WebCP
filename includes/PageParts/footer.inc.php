<div class="footer-inner">
<?php
include_once($_SERVER["DOCUMENT_ROOT"]."/vendor/autoload.php");

$oSettings = new Settings();
?>
	2018 &copy; <?php print $oSettings->GetWebCPLink(); ?> <?php include($_SERVER["DOCUMENT_ROOT"]."/includes/version.inc"); ?>
</div>
