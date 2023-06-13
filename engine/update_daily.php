<?php
	require_once(INCLUDE_PATH . 'classes/datafunctions.class.php');

	ini_set('display_errors', '1');
	error_reporting(E_ALL & ~E_NOTICE);

	$dbo = new DataFunctions($dn);
	if($dbo->error)
	{
		echo $dbo->error;
		exit();
	}

	$dbo->dailyUpdate();			//reset views and clicks counts
	$s = $dbo->clearStatsData();	//clear out of date stats data
	echo "Cleared $s stats";
	exit();
?>
