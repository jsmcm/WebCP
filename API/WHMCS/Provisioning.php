<?php

include 'class.Provisioning.php';

$options = array('uri' => 'http://'.$_SERVER["SERVER_NAME"]);
$server = new SoapServer(NULL, $options);
$server->setClass('Provisioning');
$server->handle();
