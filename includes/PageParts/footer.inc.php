<div class="footer-inner">
<?php
include_once($_SERVER["DOCUMENT_ROOT"]."/vendor/autoload.php");

$oSettings = new Settings();

$scriptsVersion = file_get_contents("/usr/webcp/readme");
$scriptsVersion = substr($scriptsVersion, strpos($scriptsVersion, "*") + 1);
$scriptsVersion = substr($scriptsVersion, 0, strpos($scriptsVersion, "*"));

?>
	2018 &copy; <?php print $oSettings->GetWebCPLink(); ?> <?php include($_SERVER["DOCUMENT_ROOT"]."/includes/version.inc"); ?> | scripts <?php print trim($scriptsVersion); ?>
</div>
