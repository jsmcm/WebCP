<?php

include 'class.api.DNS.php';

$options = array('uri' => "http://".$_SERVER["SERVER_NAME"]);
$server = new SoapServer(NULL, $options);
$server->setClass('API_DNS');
$server->handle();

