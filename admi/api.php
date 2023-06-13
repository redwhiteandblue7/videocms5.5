<?php
	session_start();
	ini_set('display_errors', '1');
//	error_reporting(E_ALL & ~(E_NOTICE + E_DEPRECATED));
	error_reporting(E_ALL);

	require_once('ndefines.php');
	require_once("classes/apis.class.php");

	$api = new ApisPage();
	$api->exec();
    exit();