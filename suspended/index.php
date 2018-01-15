<html>
<head>
<style type="text/css">
a
{
  color: #A51F21;
  font-weight: bold;
  font-size: 15px;
  text-decoration: none;
}
</style>
<title>Account Suspended</title>
</head>
<body style="margin: 0px;color: white;">
<div style="visibility:hidden; display:none;">
<?php

if( ! file_exists($_SERVER["DOCUMENT_ROOT"]."/pwh_dnsbl.txt"))
{
        touch($_SERVER["DOCUMENT_ROOT"]."/pwh_dnsbl.txt");
}

$datetime1 = new DateTime(date("Y-m-d", filemtime($_SERVER["DOCUMENT_ROOT"]."/pwh_dnsbl.txt")));
$datetime2 = new DateTime(date("Y-m-d"));
$interval = $datetime1->diff($datetime2);

$EmailAddress = file_get_contents($_SERVER["DOCUMENT_ROOT"]."/pwh_dnsbl.txt");

if( ((int)$interval->format('%a') > 0) || ($EmailAddress == "") )
{
        $options2 = array(
             'uri' => 'http://dnsbl.phpwebhost.co.za',
             'location' => 'http://dnsbl.phpwebhost.co.za/api/DNSBL.php',
             'trace' => 1);

        $client = new SoapClient(NULL, $options2);
        $EmailAddress = $client->GetRandomEmailAddress();

        file_put_contents($_SERVER["DOCUMENT_ROOT"]."/pwh_dnsbl.txt", $EmailAddress);
}

?>
email: <?php print $EmailAddress; ?><p>
<a href="mailto:<?php print $EmailAddress; ?>"><?php print $EmailAddress; ?></a>

</div>

<table width="100%" height="100%" cellpadding="0" cellspacing="0">
<tr>
<td align="center" valign="top" bgcolor="#A51F21">
<p style="font-size:48px; font-weight: bold;">Account Suspended!
<p style="font-size:22px;">
This account has been suspended and cannot display at the moment.
<br>
If you are the site owner, please contact support to get your site back online.
</p>
</td>
</tr>

</table>

</body>
</html>

