<?php
	session_start();
	ini_set('display_errors', '1');
//	error_reporting(E_ALL & ~(E_NOTICE + E_DEPRECATED));
	error_reporting(E_ALL);
	
	require_once('ndefines.php');
	require_once("classes/admin.class.php");

	$_SESSION["counter1"] = microtime(true);

	$admin = new AdminPage();
	$admin->exec();

//	echo "<br />Time: " . (microtime(true) - $_SESSION["counter1"]);

	exit();

?>
