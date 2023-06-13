<?php
	require_once(INCLUDE_PATH . 'defines.php');
	require_once(INCLUDE_PATH . 'tools/dbtools.php');
	
	$dbc = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

	$tables = array();
	$q = "show tables";
	$r = $dbc->query($q) or die($dbc->error);
	while($row = $r->fetch_row())
	{
		$tables[] = $row[0];
	}
	$r->free();

	if(in_array("banners", $tables))
	{
		$q1 = "update banners set prev_prod=monthly_prod, monthly_clicks=0, monthly_views=0, monthly_prod=0";
		$r1 = $dbc->query($q1) or die($dbc->error);
	}
	
	$q = "select * from domains where `status`=1";
	$r = $dbc->query($q) or die($dbc->error);
	if(!$r->num_rows) exit();
	
	while($row = $r->fetch_assoc())
	{
		$dn = $row["subdomain"] . $row["domain_name"];
		$prefix = getTablesPrefix($dn);
		$domain_id = $row["domain_id"];
		$table_name = $prefix . "_flv_stats";
		if(in_array($table_name, $tables))
		{
			$q1 = "update $table_name set prev_prod=monthly_prod, monthly_clicks=0, monthly_views=0, monthly_prod=1000";
			$r1 = $dbc->query($q1) or die($dbc->error);
		}

	}
	$r->free();
	
	exit();
?>
