<?php
	ini_set('display_errors', '1');
	error_reporting(E_ALL);

	require_once(INCLUDE_PATH . 'classes/datafunctions.class.php');

	header("Cache-Control: no-cache, no-store, must-revalidate");

	$dbo = new DataFunctions($dn);
	if($dbo->error)
	{
		echo $dbo->error;
		exit();
	}

	if(!$dbo->updateDomain()) exit();

	$dbo->buildPageloadStats(1000);
	$dbo->updateReferrerStats();
	$dbo->storeDailyStats();
	exit();
?>
