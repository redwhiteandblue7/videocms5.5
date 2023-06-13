<?php
	require_once('ndefines.php');
	require_once('dbdefines.php');

	session_start();
	set_time_limit(300);             //give this script more time to complete
	if(!isset($_SESSION["domain_id"]))
	{
		echo "No domain ID available";
		exit();
	}
	$domain_id = $_SESSION["domain_id"];
	$dbc = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

	$q = "select domain_name from domains where domain_id='$domain_id'";
	$r = $dbc->query($q) or die($dbc->error);
	$dn = $r->fetch_row()[0];
	$r->close();
	if(substr($dn, 0, 4) == "www.") $dn = substr($dn, 4);
	$dom = explode(".", $dn);
	$domain = $dom[0];
	$prefix = getDomainPrefix($domain);

	if(isset($_GET["empty_tbls"]))
	{
		$tbl_type = $_GET["empty_tbls"];
		$t = time();

		if($tbl_type == "stats")
		{
			$stats_ttl = $t - 86400;
			$q = "select max(stime) as stm from {$prefix}_pageloads";
			$r = $dbc->query($q) or die($dbc->error . ", query was $q");
			$stime = $r->fetch_row()[0] ?? 0;
			$r->close();
			if($stime < $stats_ttl) $stats_ttl = $stime;
			$q = "delete from {$prefix}_stats where stime<$stats_ttl";
			$r = $dbc->query($q) or die($dbc->error . ", query was $q");
			echo $dbc->affected_rows . " stats";
		}
		elseif($tbl_type == "daily")
		{

			$q = "select max(stat_time) from daily_stats where domain_id=$domain_id";
			$r = $dbc->query($q) or die($dbc->error . ", query was $q");
			$max_stat_time = $r->fetch_row()[0] ?? 0;
			$r->close();
			$last_stat_time = $max_stat_time + TWENTYFOUR_HOURS;

			$q = "select max(stime) from {$prefix}_pageloads where 1";
			$r = $dbc->query($q) or die($dbc->error . ", query was $q");
			$last_stime = $r->fetch_row()[0] ?? 0;
			$r->close();

			if($last_stat_time < $last_stime)
			{
				$q = "insert into daily_stats set
						stat_time=$last_stat_time,
						domain_id=$domain_id
						";
				$r = $dbc->query($q) or die($dbc->error . ", query was $q");
				updateDailyStats($prefix, $domain_id, $last_stat_time, $dbc);
				echo $last_stime - $last_stat_time;
			}
			else
			{
				echo "OK";
			}
		}
		else
		{
			$q = "select max(stat_time) as mt from daily_stats where domain_id=$domain_id";
			$r = $dbc->query($q) or die($dbc->error . ", query was $q");
			$last_stat_time = $r->fetch_row()[0] ?? 0;
			$r->close();
			$visits_ttl = $t - VISITS_TTL;
			if($visits_ttl > $last_stat_time) $visits_ttl = $last_stat_time;

			$stq = ($tbl_type == "visitors") ? "last_time" : "stime";
			$q = "delete from {$prefix}_{$tbl_type} where $stq<$visits_ttl";
			$r = $dbc->query($q) or die($dbc->error . ", the query was $q");
			echo $dbc->affected_rows . " $tbl_type";
		}
	}
	elseif(isset($_GET["show_tbl"]))
	{
		$tbl_type = $_GET["show_tbl"];
		$daterange = $_GET["dr"];

		$groupclause = "";
		if(isset($_GET["show_grp"]))
		{
			$groupclause = " and testgroup=" . $_GET["show_grp"];
			$pgroupclause = " and {$prefix}_pageloads.testgroup=" . $_GET["show_grp"];
		}

		switch($tbl_type)
		{
			case "bounceua":
				$q = "select
					count(1) as co,
					browsers.browser_id,
					browsers.browser
					from
					{$prefix}_pageloads
					left join {$prefix}_visitors on {$prefix}_pageloads.visitor_id={$prefix}_visitors.visitor_id
					left join browsers on {$prefix}_visitors.browser_id=browsers.browser_id
					where
					{$prefix}_pageloads.stime>=$daterange and
					({$prefix}_pageloads.flag='N' or {$prefix}_pageloads.flag='R') and
					{$prefix}_visitors.session_time={$prefix}_visitors.last_time
					$pgroupclause
					group by browsers.browser_id
					order by co desc
					";
				$r = $dbc->query($q) or die($dbc->error . ", query was $q");
				$bounces = array();
				while($row = $r->fetch_assoc())
				{
					extract($row);
					$bounces["id-$browser_id"] = $co;
				}
				$r->close();

				$q = "select
					count(1) as co,
					vi.browser_id
					from
					(select
						{$prefix}_pageloads.stime,
						{$prefix}_visitors.visitor_id,
						browsers.browser_id
						from
						{$prefix}_pageloads
						left join {$prefix}_visitors on {$prefix}_pageloads.visitor_id={$prefix}_visitors.visitor_id
						left join browsers on {$prefix}_visitors.browser_id=browsers.browser_id
						where
						{$prefix}_pageloads.stime>=$daterange and
						({$prefix}_pageloads.flag='N' or {$prefix}_pageloads.flag='R')
						$pgroupclause
						) as vi
					left join {$prefix}_clickthrus on {$prefix}_clickthrus.visitor_id=vi.visitor_id
					where {$prefix}_clickthrus.stime>=vi.stime
					group by vi.browser_id
					";
				$r = $dbc->query($q) or die($dbc->error . ", query was $q");
				$clickthrus = array();
				while($row = $r->fetch_assoc())
				{
					extract($row);
					$clickthrus["id-$browser_id"] = $co;
				}
				$r->close();

				$q = "select
					count(1) as sc,
					vi.browser_id
					from
					(select
						{$prefix}_pageloads.stime,
						{$prefix}_visitors.visitor_id,
						browsers.browser_id
						from
						{$prefix}_pageloads
						left join {$prefix}_visitors on {$prefix}_pageloads.visitor_id={$prefix}_visitors.visitor_id
						left join browsers on {$prefix}_visitors.browser_id=browsers.browser_id
						where
						{$prefix}_pageloads.stime>=$daterange and
						({$prefix}_pageloads.flag='N' or {$prefix}_pageloads.flag='R')
						$pgroupclause
						) as vi
					left join {$prefix}_clickthrus on {$prefix}_clickthrus.visitor_id=vi.visitor_id
					where {$prefix}_clickthrus.stime>=vi.stime
					and {$prefix}_clickthrus.site_id!=0
					group by vi.browser_id
					";
				$r = $dbc->query($q) or die($dbc->error . ", query was $q");
				$sponsorclicks = array();
				while($row = $r->fetch_assoc())
				{
					extract($row);
					$sponsorclicks["id-$browser_id"] = $sc;
				}
				$r->close();

				$q = "select
					sum({$prefix}_visitors.last_time-{$prefix}_visitors.session_time) as tos,
					browsers.browser_id
					from
					{$prefix}_pageloads
					left join {$prefix}_visitors on {$prefix}_pageloads.visitor_id={$prefix}_visitors.visitor_id
					left join browsers on {$prefix}_visitors.browser_id=browsers.browser_id
					where
					{$prefix}_pageloads.stime>=$daterange and
					{$prefix}_visitors.session_time>0 and
					({$prefix}_pageloads.flag='N' or {$prefix}_pageloads.flag='R')
					$pgroupclause
					group by browsers.browser_id
					";
				$r = $dbc->query($q) or die($dbc->error . ", query was $q");
				$timeonsite = array();
				while($row = $r->fetch_assoc())
				{
					extract($row);
					$timeonsite["id-$browser_id"] = $tos;
				}
				$r->close();

				$q = "select
					count(1) as tot,
					browsers.browser_id,
					browsers.browser
					from
					{$prefix}_pageloads
					left join {$prefix}_visitors on {$prefix}_pageloads.visitor_id={$prefix}_visitors.visitor_id
					left join browsers on {$prefix}_visitors.browser_id=browsers.browser_id
					where
					{$prefix}_pageloads.stime>=$daterange and
					({$prefix}_pageloads.flag='N' or {$prefix}_pageloads.flag='R')
					$pgroupclause
					group by browsers.browser_id
					order by tot desc
					";
				$r = $dbc->query($q) or die($dbc->error . ", query was $q");
				echo "<table class=\"admin1 admin2\">\n";
				echo "<tr>\n";
				echo "<th>#</th>";
				echo "<th>Platform</th><th>Hits</th><th>CT Productivity</th><th>Sponsor CT</th><th>Time On Site</th><th>Bounce rate</th></tr>\n";
				$i = 1;
				$total_hits = 0;
				$total_bounces = 0;
				$total_clicks = 0;
				$total_tos = 0;
				$total_sclicks = 0;
				$results = array();
				while($row = $r->fetch_assoc())
				{
					extract($row);
					$co = $bounces["id-$browser_id"];
					if(!is_numeric($co)) $co = 0;
//					$pc = (floor(($co * 1000) / $tot)) / 10;

					$ct = $clickthrus["id-$browser_id"];
					if(!is_numeric($ct)) $ct = 0;
//					$prod = (floor(($ct * 1000) / $tot)) / 10;

					$sc = $sponsorclicks["id-$browser_id"];
					if(!is_numeric($sc)) $sc = 0;
//					$sprod = (floor(($sc * 1000) / $tot)) / 10;

					$tos = $timeonsite["id-$browser_id"];
//					$t = floor($tos / $tot);

					$br = explode("/", $browser);
					$browser_name = $br[0];
					$results[$browser_name][0] += $tot;	//hits
					$results[$browser_name][1] += $ct;	//clickthrus
					$results[$browser_name][2] += $tos;	//time on site
					$results[$browser_name][3] += $co;	//bounces
					$results[$browser_name][4] += $sc;	//sponsor clicks
/*
					$cellclass = "admin1";
					if(($i / 2) != round($i / 2)) $cellclass = "admin1x";
					echo "<tr class=\"$cellclass\">";
					echo "<td>$i.</td><td>$browser_name</td><td>$tot</td><td>$prod</td><td>$t</td><td>$co</td><td>$pc</td></tr>\n";
					$i++;
*/
					if($browser_name != "Bot")
					{
					    $total_hits += $tot;
					    $total_bounces += $co;
					    $total_clicks += $ct;
					    $total_tos += $tos;
					    $total_sclicks += $sc;
					}
				}
				$r->close();
				foreach($results as $key => $value)
				{
					$tot = $value[0];
					$ct = $value[1];
					$tos = $value[2];
					$co = $value[3];
					$sc = $value[4];

					if(!is_numeric($co)) $co = 0;
					$pc = (floor(($co * 1000) / $tot)) / 10;

					if(!is_numeric($ct)) $ct = 0;
					$prod = (floor(($ct * 1000) / $tot)) / 10;

					if(!is_numeric($sc)) $sc = 0;
					$sprod = (floor(($sc * 1000) / $tot)) / 10;

					$t = floor($tos / $tot);

					$cellclass = "admin1";
					if(($i / 2) != round($i / 2)) $cellclass = "admin1x";
					echo "<tr class=\"$cellclass\">";
					echo "<td>$i.</td><td>$key</td><td>$tot</td><td>$prod</td><td>$sprod</td><td>$t</td><td>$pc</td></tr>\n";
					$i++;
				}
				$pc = (floor(($total_bounces * 1000) / $total_hits)) / 10;
				$prod = (floor(($total_clicks * 1000) / $total_hits)) / 10;
				$sprod = (floor(($total_sclicks * 1000) / $total_hits)) / 10;
				$t = floor($total_tos / $total_hits);
				echo "<tr class=\"$cellclass\"><td>-</td><td>Total browsers excl. bots</td><td>$total_hits</td><td>$prod</td><td>$sprod</td><td>$t</td><td>$pc</td></tr>\n";
				echo "</table>\n";
				break;
			case "bannerhits":
				$q = "select page_id from {$prefix}_pages where pagename='@banner'";
				$r = $dbc->query($q) or die($dbc->error . ", query was $q");
				$page_id = $r->fetch_row()[0] ?? 0;
				$r->close();
				$q = "select
				        {$prefix}_site_names.site_id,
					site_name,
					count(1) as cnt
					from
					{$prefix}_site_names
					left join {$prefix}_clickthrus on {$prefix}_clickthrus.site_id={$prefix}_site_names.site_id
					where
					{$prefix}_clickthrus.stime>=$daterange
					and {$prefix}_clickthrus.page_id=$page_id
					$groupclause
					group by site_id
					order by cnt desc, site_name
					";
				$r = $dbc->query($q) or die($dbc->error . ", query was $q");
				echo "<table class=\"admin1 admin2\">\n";
				echo "<tr>\n";
				echo "<th>#</th>";
				echo "<th>Site</th><th>Hits</th><th>View Hits</th></tr>\n";

				$i = 1;
				while($row = $r->fetch_assoc())
				{
					extract($row);
					$cellclass = "admin1";
					if(($i / 2) != round($i / 2)) $cellclass = "admin1x";
					echo "<tr class=\"$cellclass\">";
					echo "<td>$i.</td><td>$site_name</td><td>$cnt</td><td><a href=\"?a=sclicks&subpage=stats&si=$site_id\">[View]</a></td></tr>\n";
					$i++;
				}
				$r->close();
				echo "</table>\n";
				break;
			case "linkhits":
				$q = "select page_id from {$prefix}_pages where pagename='@link'";
				$r = $dbc->query($q) or die($dbc->error . ", query was $q");
				$page_id = $r->fetch_row()[0] ?? 0;
				$r->close();
				$q = "select
				        {$prefix}_site_names.site_id,
					count(1) as cnt
					from
					{$prefix}_site_names
					left join {$prefix}_clickthrus on {$prefix}_clickthrus.site_id={$prefix}_site_names.site_id
					where
					{$prefix}_clickthrus.stime>=$daterange
					and {$prefix}_clickthrus.page_id=$page_id
					$groupclause
					group by site_id
					";
				$r = $dbc->query($q) or die($dbc->error . ", query was $q");
				$a = array();
				while($row = $r->fetch_assoc())
				{
					extract($row);
					$a["id-$site_id"] = $cnt;
				}
				$r->close();

				$q = "select
				        {$prefix}_site_names.site_id,
				        {$prefix}_site_names.site_name,
					count(1) as cnt,
					sum(duration) as dur
					from
					{$prefix}_flvs
					left join {$prefix}_clickthrus on {$prefix}_clickthrus.gallery_id={$prefix}_flvs.flv_id
					left join {$prefix}_site_names on {$prefix}_site_names.site_id={$prefix}_flvs.site_id
					where
					{$prefix}_clickthrus.stime>=$daterange
					$groupclause
					group by site_id
					order by cnt desc, site_name
					";
				$r = $dbc->query($q) or die($dbc->error . ", query was $q");
				echo "<table class=\"admin1 admin2\">\n";
				echo "<tr>\n";
				echo "<th>#</th>";
				echo "<th>Site</th><th>Page Hits</th><th>Avg Duration</th><th>Clicks</th><th>Clickthru Rate</th><th>View Hits</th></tr>\n";

				$i = 1;
				$total_hits = 0;
				$total_views = 0;
				while($row = $r->fetch_assoc())
				{
					extract($row);
					$co = $a["id-$site_id"];
					if(!is_numeric($co))
					{
						$co = 0;
						$pc = 0;
					}
					else
					{
						$pc = (round(($co * 1000) / $cnt)) / 10;
					}
					$d = round($dur / $cnt);
					$cellclass = "admin1";
					if(($i / 2) != round($i / 2)) $cellclass = "admin1x";
					echo "<tr class=\"$cellclass\">";
					echo "<td>$i.</td><td>$site_name</td><td>$cnt</td><td>$d</td><td>$co</td><td>$pc</td><td><a href=\"?a=sclicks&subpage=stats&si=$site_id\">[View]</a></td></tr>\n";
					$total_hits += $co;
					$total_views += $cnt;
					$i++;
				}
				$r->close();
				$pc = (round(($total_hits * 1000) / $total_views)) / 10;
				echo "<tr class=\"$cellclass\"><td>-</td><td>Total</td><td>$total_views</td><td>-</td><td>$total_hits</td><td>$pc</td><td>-</td></tr>\n";
				echo "</table>\n";
				break;
			default:
			    echo "Bad command";
			    break;
		}
	}

	if($dbc !== "") $dbc->close();
	exit();

	function getDomainPrefix($domainstring)
	{
		$domainstring = str_replace(".", "_", $domainstring);
		$domainstring = str_replace("-", "_", $domainstring);
		$domainstring = strtolower($domainstring);

		return $domainstring;
	}

	function sortResults($a, $b)
	{
	    if($a[1] == $b[1]) return strcmp($a[0], $b[0]);
	    return($a[1] > $b[1]) ? -1 : 1;
	}

	function updateDailyStats($prefix, $domain_id, $stat_day, $dbc)
	{
		$next_day = $stat_day + TWENTYFOUR_HOURS;

		$q = "select count(1) as cnt from {$prefix}_pageloads where stime>=$stat_day and stime<$next_day";
		$r = $dbc->query($q);
		$page_loads = $r->fetch_row()[0];
		$r->close();
		$q = "select count(distinct visitor_id) as cnt from {$prefix}_pageloads where stime>=$stat_day and stime<$next_day";
		$r = $dbc->query($q);
		$visitors = $r->fetch_row()[0];
		$r->close();
		$q = "select count(1) as cnt from searches, {$prefix}_pageloads where searches.dom_id={$prefix}_pageloads.dom_id and (flag='N' or flag='R') and stime>=$stat_day and stime<$next_day";
		$r = $dbc->query($q);
		$searches = $r->fetch_row()[0];
		$r->close();

		if(is_numeric($_SESSION["se_track_id"]))
		{
			$tid = $_SESSION["se_track_id"];
			$q = "select count(1) as cnt from
					{$prefix}_pageloads
					where
					{$prefix}_pageloads.dom_id=$tid
					and (flag='N' or flag='R')
					and stime>=$stat_day and stime<$next_day
					";
			$r = $dbc->query($q) or die($dbc->error);
			$tracked = $r->fetch_row()[0];
			$r->close();
		}
		else
		{
			$tracked = 0;
		}
		$q = "select count(1) as cnt from {$prefix}_clickthrus where stime>=$stat_day and stime<$next_day";
		$r = $dbc->query($q);
		$click_thrus = $r->fetch_row()[0];
		$r->close();
		$page_loads += $click_thrus;

		$q = "update daily_stats set
				page_loads=$page_loads,
				visitors=$visitors,
				searches=$searches,
				click_thrus=$click_thrus,
				se_tracked=$tracked
				where
				stat_time=$stat_day
				and domain_id=$domain_id";
		$r = $dbc->query($q) or die($dbc->error);
	}

	function getSearchTerms($url)
	{
		$s = strpos($url, "?");
		if($s === false)
		{
		    return "";
		}
		$url_query = substr($url, $s + 1);
		$url_host = substr($url, 0, $s);

		if((strpos($url_host, "images.google") !== false) || (strpos($url, "/imgres?") !== false))
		{
			$s = strpos($url_query, "prev=");
			if($s !== false)
			{
				$url_query = htmlspecialchars(urldecode(substr($url_query, $s + 5)));
				$s = strpos($url_query, "?");
				if($s !== false)
				{
					$url_query = substr($url_query, $s + 1);
				}
			}
		}
		$query_array = explode("&", $url_query);
		$search_params = array("vp=", "q=", "as_epq=", "as_q=", "query=", "p=", "searchfor=", "rdata=", "string=", "search_term=", "Q=", "term=", "qs=");

		for($j = 0; $j < sizeof($search_params); $j++)
		{
			$search_param = $search_params[$j];
			$l = strlen($search_param);
			for($i = 0; $i < sizeof($query_array); $i++)
			{
				if(substr($query_array[$i], 0, $l) == $search_param)
				{
					$search_terms = substr($query_array[$i], $l);
					return htmlspecialchars(urldecode($search_terms));
				}

			}
		}
		return "";
	}

	function getSearchPage($url)
	{
		$s = strpos($url, "?");
		if($s === false)
		{
		    return 0;
		}
		$url_query = substr($url, $s + 1);
		$url_host = substr($url, 0, $s);
		$query_vars = explode("&", $url_query);

		$result = "-";

		if(strpos($url_host, "google") !== false)
		{
			$result = 1;
			foreach($query_vars as $q)
			{
				if(substr($q, 0, 6) == "start=")
				{
					$result = substr($q, 6);
					$result = $result / 10;
					break;
				}
			}
		}
		elseif(strpos($url_host, "bing") !== false)
		{
			$result = 1;
			foreach($query_vars as $q)
			{
				if(substr($q, 0, 6) == "first=")
				{
					$result = substr($q, 6);
					$result = ($result - 1) / 10;
					break;
				}
			}
		}
		elseif(strpos($url_host, "yahoo") !== false)
		{
			$result = 1;
			foreach($query_vars as $q)
			{
				if(substr($q, 0, 2) == "b=")
				{
					$result = substr($q, 2);
					$result = ($result - 1) / 10;
					break;
				}
			}
		}

		return $result;
	}

	function cmp($a, $b)
	{
		if ($a["traffic"] == $b["traffic"])
		{
			return 0;
		}
		return ($a["traffic"] < $b["traffic"]) ? -1 : 1;
	}

	function getWebPage($url)
	{
		$response = get_web_page($url);

		if((!$response) || (!$response["content"]))
		{
			$result["msg"] = "Bad URL";
			$result["ok"] = false;
			$result["content"] = "";
			$result["code"] = 254;
		}
		elseif($response["http_code"] != 200)
		{
			$result["msg"] = "HTTP Response: " . $response["http_code"];
			$result["ok"] = false;
			$result["content"] = "";
			$result["code"] = 253;
		}
		else
		{
			$result["content"] = $response["content"];
			$result["msg"] = "OK";
			$result["ok"] = true;
			$result["code"] = 1;
		}
		return $result;
	}

	function checkPageForLink($page, $link)
	{
		$html = $page["content"];
		if(strpos($html, $link) === false)
		{
			$page["msg"] = "Not found";
			$page["code"] = 255;
			return $page;
		}
		return $page;
	}

	function stripComments($page)
	{
		do
		{
			$startof = strpos($page, "<!--");
			if($startof !== false)
			{
				$endof = strpos($page, "-->", $startof);
				if($endof !== false)
				{
					$page = substr($page, 0, $startof) . substr($page, $endof + 3);
				}
				else
				{
					break;
				}
			}

		} while($startof !== false);

		return $page;
	}

//Get a web file (HTML, XHTML, XML, image, etc.) from a URL.  Return an * array containing the header fields and content
	function get_web_page( $url )
	{
		$options = array( 'http' => array(
	        'user_agent'    => 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)',    // who am i
	        'max_redirects' => 3,          // stop after 10 redirects
	        'timeout'       => 8.0,         // timeout on response
		) );

		$context = stream_context_create($options);
		$page = @file_get_contents($url, false, $context);

		$result = array();
		if($page != false)
		{
			$result['content'] = $page;
		}
		else
		{
			if(!isset($http_response_header)) return null;    // Bad url, timeout
		}
	    // Save the header
		$result['header'] = $http_response_header;

	    // Get the *last* HTTP status code
		$nLines = count( $http_response_header );
		for ( $i = $nLines-1; $i >= 0; $i-- )
		{
			$line = $http_response_header[$i];
			if ( strncasecmp( "HTTP", $line, 4 ) == 0 )
			{
				$response = explode( ' ', $line );
				$result['http_code'] = $response[1];
				break;
			}
		}

		return $result;
	}

?>
