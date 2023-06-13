<?php

	require_once('ndefines.php');
	require_once('../engine/defines.php');
	
	ini_set('display_errors', '1');
//	error_reporting(E_ALL & ~(E_NOTICE + E_DEPRECATED));
	error_reporting(E_ALL);

//	ini_set('date.timezone', 'Europe/London');
	$dbc = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

	$type = "";
	if(isset($_GET['t'])) $type = $_GET['t'];
	if(isset($_GET['c'])) $columntype = $_GET['c'];
	if(isset($_GET['d']))
		$domain_id = $_GET['d'];
	else
		$domain_id=1;
	if(isset($_GET['g']))
		$domain_q = "";
	else
		$domain_q = "domain_id=$domain_id and ";
	if(isset($_GET['st']))
		$start_days = $_GET["st"];
	else
		$start_days = 0;

	$height = 320;
	$width = 1800;
	$top_margin = 44;
	$bottom_margin = 38;
	$left_margin = 1;
	$right_margin = $left_margin;
	$im = ImageCreateTrueColor($width, $height);
	$white = ImageColorAllocate($im, 240, 240, 240);
	$grey = ImageColorAllocate($im, 160, 160, 160);
	$black = ImageColorAllocate($im, 0,0,0);

	ImageFill($im, 0, 0, $grey);
	ImageRectangle($im, $left_margin, $top_margin, $width - $right_margin, $height - $bottom_margin, $black);

	switch($type)
	{
		case "visits":
			$blue = ImageColorAllocate($im, 0, 0, 200);
			$red = ImageColorAllocate($im, 224, 112, 56);
			$purple = ImageColorAllocate($im, 160, 0, 160);
			ImageString($im, 1, $left_margin, 5, "Daily Visits", $black);
			ImageString($im, 1, $left_margin + 80, 5, "Total Visits", $red);
			ImageString($im, 1, $left_margin + 160, 5, "Search hits", $blue);
			$x_ofs = 4;
			break;
		case "pages":
			$blue = ImageColorAllocate($im, 0, 160, 0);
			$red = ImageColorAllocate($im, 196, 160, 0);
			ImageString($im, 1, $left_margin, 5, "Daily Pageloads", $black);
			ImageString($im, 1, $left_margin + 100, 5, "Total Pageloads", $red);
			ImageString($im, 1, $left_margin + 200, 5, "Click thrus", $blue);
			$x_ofs = 4;
			break;
		default:
		    break;
	}

	switch($columntype)
	{
		case "1":
			$num_of_days = 90;
			$granularity = 1;
			break;
		case "2":
			$num_of_days = 630;
			$granularity = 7;
			break;
		case "3":
			$num_of_days = 2700;
			$granularity = 30;
			break;
		default:
			$num_of_days = 90;
			$granularity = 1;
			break;
	}

	$datearray = getdate();
	$day = $datearray["mday"];
	$month = $datearray["mon"];
	$weekday = $datearray["wday"];
	$year = $datearray["year"];
	$hour = $datearray["hours"];
	$minute = $datearray["minutes"];

	$today = gmmktime(0, 0, 0, $month, $day, $year);
	$start_day = $today - (TWENTYFOUR_HOURS * ($num_of_days - $granularity - $start_days));
	$stat_day = $start_day;

	$tid = 0;
	if(isset($_SESSION["se_track_id"])) $tid = $_SESSION["se_track_id"];

	$biggest = 0;

	$num_of_columns = $num_of_days / $granularity;
	for($i = 0; $i < $num_of_columns; $i++)
	{
		$next_day = $stat_day + (TWENTYFOUR_HOURS * $granularity);

		switch($type)
		{
			case "visits":
				$query = "select sum(visitors) as cnt from daily_stats where $domain_q stat_time>=$stat_day and stat_time<$next_day";
				$r = $dbc->query($query);
				$n = $r->fetch_row()[0];
				$query = "select sum(searches) as cnt from daily_stats where $domain_q stat_time>=$stat_day and stat_time<$next_day";
				$r = $dbc->query($query);
				$m = $r->fetch_row()[0];
				$query = "select sum(se_tracked) as cnt from daily_stats where $domain_q stat_time>=$stat_day and stat_time<$next_day";
				$r = $dbc->query($query);
				$o = $r->fetch_row()[0];
				break;
			case "pages":
				$query = "select sum(page_loads) as cnt from daily_stats where $domain_q stat_time>=$stat_day and stat_time<$next_day";
				$r = $dbc->query($query);
				$n = $r->fetch_row()[0];
				$query = "select sum(click_thrus) as cnt from daily_stats where $domain_q stat_time>=$stat_day and stat_time<$next_day";
				$r = $dbc->query($query);
				$m = $r->fetch_row()[0];
//				$m = $m - $n;
				$o = 0;
				break;
			default:
				$m = 1;
				$n = 2;
				$o = 0;
				break;
		}

		$results[] = array("n"=>$n, "m"=>$m, "o"=>$o);

		if($n > $biggest) $biggest = $n;

		$stat_day = $next_day;
	}

	if($biggest == 0) $biggest = 1;

	$x_multiplier = ($width - 20) / ($num_of_days / $granularity);
	$y_multiplier = ($height - $top_margin - $bottom_margin) / ($biggest);
//	$bar_width = floor($x_multiplier) - 1;
//	$bar_width = floor($x_multiplier / 2);
//	if($bar_width < 5) $bar_width = 5;
	$bar_width = 12;

	$stat_day = $start_day;

	for($i = 0; $i < $num_of_days / $granularity; $i++)
	{
		$next_day = $stat_day + (60 * 60 * 24 * $granularity);

		$reds = $results[$i]["n"];
		$blues = $results[$i]["m"];

		$rred = round($reds * $y_multiplier);
		$rblue = round($blues * $y_multiplier);

		$ix = round($i * $x_multiplier) + 10;
		$iy_r = $height - $rred - $bottom_margin;
		$iy_b = $height - $rblue - $bottom_margin;
		ImageFilledRectangle($im, $ix, $iy_r, $ix + $bar_width, $height - $bottom_margin, $red);
//		ImageFilledRectangle($im, $ix + $x_ofs, $iy_b, $ix + ($bar_width / 2) + 2 + $x_ofs, $height - $bottom_margin, $blue);
		ImageFilledRectangle($im, $ix, $iy_b, $ix + $bar_width, $height - $bottom_margin, $blue);
		ImageStringUp($im, 2, $ix, $top_margin - 2, "$reds", $black);
		ImageStringUp($im, 2, $ix, $iy_b - 1, "$blues", $black);

		if($type == "visits")
		{
			$purples = $results[$i]["o"];
			$rpurple = round($purples * $y_multiplier);
			$iy_p = $height - $rpurple - $bottom_margin;
//			ImageFilledRectangle($im, $ix + $x_ofs + 1, $iy_p, $ix + ($bar_width / 2) + 1 + $x_ofs, $height - $bottom_margin, $purple);
			ImageFilledRectangle($im, $ix, $iy_p, $ix + $bar_width, $height - $bottom_margin, $purple);
			ImageStringUp($im, 2, $ix - 2, $iy_p - 1, "$purples", $white);
		}

		$day_str = gmdate('D', $stat_day);
		$day_num = gmdate('d', $stat_day);
		$day_mon = gmdate('M', $stat_day);
		$day_yr = gmdate('y', $stat_day);

		ImageString($im, 1, $ix, $height - $bottom_margin + 2, $day_str, $black);
		ImageString($im, 1, $ix, $height - $bottom_margin + 10, $day_num, $black);
		ImageString($im, 1, $ix, $height - $bottom_margin + 18, $day_mon, $black);
		ImageString($im, 1, $ix, $height - $bottom_margin + 26, $day_yr, $black);

		$stat_day = $next_day;
	}

	if($dbc !== "") $dbc->close();

	header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1.
	header("Pragma: no-cache"); // HTTP 1.0.
	header("Expires: 0"); // Proxies.
	header('Content-type: image/png');
	ImagePng($im);

	ImageDestroy($im);
?>
