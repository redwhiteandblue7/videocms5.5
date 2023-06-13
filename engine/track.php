<?php

	require_once(INCLUDE_PATH . 'defines.php');
	require_once(INCLUDE_PATH . 'classes/visitor.class.php');

	ini_set('display_errors', '1');
	error_reporting(E_ALL & ~E_NOTICE);

	$visitor = new Visitor();

	if($visitor->searchbot)
	{
		header('X-Robots-Tag: noindex');
	}
	else
	{
		$visitor->getMoreBotSignals();
		$visitor->blockRobots();
	}

	if(($_GET["id"]) && ($_GET["type"]))
	{
		$lid = $_GET["id"];
		$linktype = $_GET["type"];
		$visitor->linkname = "@" . strip_tags(addslashes($linktype));
		$visitor->link_id = $lid;
		$visitor->addVisitorStat();
	}
?>
