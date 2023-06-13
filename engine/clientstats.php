<?php
	require_once(INCLUDE_PATH . 'defines.php');

	$dn = $_SERVER["HTTP_HOST"];
	$prefix = getTablePrefix($dn);

	$dbc = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

	$useragent = $_SERVER['HTTP_USER_AGENT'];
	if((strpos($useragent, "Googlebot") !== false) ||
		(strpos($useragent, "Feedfetcher-Google") !== false) ||
		(strpos($useragent, "APIs-Google") !== false) ||
		(strpos($useragent, "Google Favicon") !== false) ||
		(strpos($useragent, "Google-Read-Aloud") !== false) ||
		(strpos($useragent, "Google Web Preview") !== false) ||
		(strpos($useragent, "googleweblight") !== false) ||
		(strpos($useragent, "Yahoo!") !== false) ||
		(strpos($useragent, "YahooCacheSystem") !== false) ||
		(strpos($useragent, "AltaVista") !== false) ||
		(strpos($useragent, "bingbot") !== false) ||
		(strpos($useragent, "BingPreview") !== false) ||
		(strpos($useragent, "msnbot") !== false) ||
		(strpos($useragent, "DuckDuckBot") !== false) ||
		(strpos($useragent, "DuckDuckPreview") !== false) ||
		(strpos($useragent, "DuckDuckGo-Favicons-Bot") !== false) ||
		(strpos($useragent, "YandexBot") !== false) ||
		(strpos($useragent, "Baiduspider") !== false))
	{
		header('X-Robots-Tag: noindex');
		exit();
	}
	if(isset($_GET["R"]))
	{
//		$referrer = urldecode($_GET['R']);                  //get the referrer string
		$referrer = $_GET['R'];                             //***no url-decode needed on this PHP setup?***
		if($referrer == "") $referrer = "-";
		$screenHeight = $_GET['H'];                         //get the screen height
		$screenWidth = $_GET['W'];                          //get the screen width
		$stat_id = $_GET['I'];

		$ctime = time();
		$scr = $screenWidth + (65536 * $screenHeight);

		$referrer = addslashes($referrer);

		$dbc = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

		$q = "update {$prefix}_stats set
				ctime=$ctime,
				screentype=$scr,
				creferrer='$referrer'
				where
				stat_id=$stat_id
				";
		$r = $dbc->query($q);

		$dbc->close();
	}

	if(isset($_GET["VID"]))
	{
		$vids = $_GET["VID"];
		$q = "update {$prefix}_flv_stats set
			daily_views=daily_views+1,
			monthly_views=monthly_views+1,
			daily_prod=round(1000*(daily_clicks+1)/(daily_views+1)),
			monthly_prod=round(1000*(monthly_clicks+1)/(monthly_views+1))
			where flv_id in($vids)";
		$r = $dbc->query($q);
		$dbc->close();
	}

	if(isset($_GET["BAN"]))
	{
		$banners = $_GET["BAN"];
		$q = "update banners set
			monthly_views=monthly_views+1,
			monthly_prod=round(1000*monthly_clicks/(monthly_views+1))
			where banner_id in($banners)";
		$r = $dbc->query($q);
		$dbc->close();
	}

	header('X-Robots-Tag: noindex');
	exit();

    function getTablePrefix($hostname)
    {
        if(substr($hostname, 0, 4) == "www.")
        {
            $hostname = substr($hostname, 4);
        }
        elseif(substr($hostname, 0, 5) == "test.")
        {
            $hostname = substr($hostname, 5);
        }
        $dom = explode(".", $hostname);
        $domainstring = $dom[0];
        $domainstring = str_replace("-", "_", $domainstring);
        $domainstring = strtolower($domainstring);
        return $domainstring;
    }

?>
