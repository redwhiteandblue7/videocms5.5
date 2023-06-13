<?php
	session_start();
	ini_set('display_errors', '1');
	error_reporting(E_ALL);

	require_once(INCLUDE_PATH . 'classes/controller.class.php');

	$_SESSION["timer"] = microtime(true);

	if(isset($_GET["u"])) {
		$u = $_GET["u"];
	} else {
	    $u = "index.html";
	}

	if((substr($u, -4) == ".jpg") || (substr($u, -4) == ".png")) {
		header('HTTP/1.0 404 Not Found');
		exit();
	}

	$protocol = "http";
//	$protocol = "https";

	$dn = "moviesample.net";

	$u = explode("/", $u);
	$usize = sizeof($u);

	if(($usize == 2) && ($u[0] == "visit")) {
		$u[2] = "";
		$usize = 3;
	}

	if(($usize < 3) && ($u[$usize - 1] == "")) $u[$usize - 1] = "index.html";	//can't have a page without a name so change it so we can find it in the page table

	$controller = new Controller($u, $dn);
	$controller->initPage();
	$controller->writePage();
	exit();
?>
