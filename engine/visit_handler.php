<?php
	require_once(INCLUDE_PATH . 'classes/visitor.class.php');

	$visitor = new Visitor();

	if(!$visitor->searchbot)
	{
		$visitor->getMoreBotSignals();
		$visitor->blockRobots($dbc, $prefix);
	}

	$slug = $u[1];
	$linkname = "link";
	$q = "select site_ref, site_id from {$prefix}_site_names where site_domain='$slug' and enabled!='removed'";
	$r = $dbc->query($q);
	if($r->num_rows)
	{
		$a = $r->fetch_object();
		$dest = $a->site_ref;
		$visitor->site_id = $a->site_id;
		$visitor->linkname = "@" . $linkname;
		$r->free();

		$visitor->addVisitorStat();
		$dbc->close();

		header("HTTP/1.1 301 Moved Permanently");
		header("Location: $dest");
		exit();
	}
	$r->free();
	$pagename = "obsolete.html";

?>
