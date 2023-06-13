<?php
	require_once(INCLUDE_PATH . 'classes/server.class.php');

class Visitor
{
	public $ip_address;
	public $ip4;
	protected $ip3;
	protected $ip2;
	protected $ip;
	public $country;
	protected $first_time_cookie_time = 0;
	protected $last_time_cookie_time = 0;
	protected $first_time_cookie_value = 0;
	protected $last_time_cookie_value = 0;
	private $first_time_cookie_name;
	private $last_time_cookie_name;
	protected $cookie_id = 0;
	private $cookie_id_name;
	protected $stime;

	private $blocked_time = 0;
	public $referrer;
	public $useragent;
	public $platform;
	public $browser;
	public $browser_version;
	public $platform_browser;
	public $searchbot = false;
	public $fromsearch = false;
	public $fromexternal = true;
	public $chinese = false;
	public $refs;
	private $ref_id = 0;
	public $no_ref = false;
	public $no_session = false;
	public $no_cookie = false;
	private $bad_ref = false;
	private $bad_ua = false;
	public $bot = false;
	public $malicious = false;
	public $old_browser = false;
	public $bot_score = 0;
	public $link_id = 0;
	public $site_id = 0;
	public $gallery_id = 0;
	public $linkname;
	public $refresh = false;
	public $uncloak = false;
	public $desktop = true;
	public $nativeLL = true;    //can we do native lazy loading and allow browsers that don't have it to fall back to full loading? False only if Safari 12.1+
	public $testgroup = 0;
	private $protocol = "http";
	private $domain;
	private $request_url;
	private $request_page;
	private $domain_vars;
	private $domain_id;
	private $table_prefix;

	public function __construct()
	{
		$useragent = $_SERVER['HTTP_USER_AGENT'];

		if($_SERVER['REMOTE_ADDR'] == "66.115.130.199")
		{
			$this->bot = true;
		}

		if($_SERVER["HTTPS"] ?? "") $this->protocol = "https";
		elseif($_SERVER["REQUEST_SCHEME"] ?? "") $this->protocol = $_SERVER["REQUEST_SCHEME"];
		elseif($_SERVER["SCRIPT_URI"] ?? "") $this->protocol = parse_url($_SERVER["SCRIPT_URI"], PHP_URL_SCHEME);

		$dn = $_SERVER["HTTP_HOST"];
		$this->request_page = trim($_SERVER["REQUEST_URI"]);
		$this->request_url = $this->protocol . "://" . $dn . $this->request_page;
		if(substr($dn, 0, 4) == "www.")
		{
			$dn = substr($dn, 4);
		}
		elseif(substr($dn, 0, 5) == "test.")
		{
			$dn = substr($dn, 5);
		}
		$this->domain = $dn;
		if(isset($_SESSION["blocked_time"]) && is_numeric($_SESSION["blocked_time"])) $this->blocked_time = $_SESSION["blocked_time"];

		$this->stime = time();

		if(isset($_GET["ref"]) && is_numeric($_GET["ref"])) $this->ref_id = $_GET["ref"];

		$ip4 = $_SERVER['REMOTE_ADDR'];
		$ip = explode(".", $ip4);        //get the ip address
		$this->ip_address = $ip[3] + (256 * $ip[2]) + (65536 * $ip[1]) + (16777216 * $ip[0]);
		$this->ip2 = $ip[0] . "." . $ip[1];
		$this->ip3 = $this->ip2 . "." . $ip[2];
		$this->ip4 = $ip4;
		$this->ip = $ip;

		$this->table_prefix = ServerFuncs::domainPrefix();
		$cookie_name = strtoupper(bin2hex($this->table_prefix));
		$this->first_time_cookie_name = $cookie_name . "1";
		$this->last_time_cookie_name = $cookie_name . "2";
		$this->cookie_id_name = $cookie_name . "3";

		if(!isset($_SESSION["cookie_id"])) $this->no_session = true;
		$this->getCookies();
		$this->setCookies();

		$referrer = ($_SERVER['HTTP_REFERER'] ?? "");
		if(($qp = strpos($referrer, "?x=")) !== false) $referrer = substr($referrer, 0, $qp);
		if(strpos($referrer, "proxy.startpage.com") !== false) $referrer = "proxy.startpage.com";

		$linkname = ($_SERVER["REQUEST_URI"] ?? "");
		if(($qp = strpos($linkname, "?x=")) !== false) $linkname = substr($linkname, 0, $qp);

		if(substr($useragent, 0, 10) == "User-Agent") $useragent = "Fake UA";

		$this->referrer = $referrer;
		$this->useragent = $useragent;
		$this->linkname = $linkname;

		$this->country = strtolower($_SERVER['HTTP_ACCEPT_LANGUAGE']);
		if(strlen($this->country) > 5) $this->country = substr($this->country, 0, 5);

		$this->getPlatformAndBrowser();
		$this->getBotSignals();

		if(!$this->bot)
		{
			if(!isset($_SESSION["referrer_domain"]))
			{
				$referrer = strip_tags(trim($this->referrer));
				$referrer = strtolower($referrer);
				if(substr($referrer, 0, 7) == "http://") $referrer = substr($referrer, 7);
				if(substr($referrer, 0, 8) == "https://") $referrer = substr($referrer, 8);
				if(substr($referrer, 0, 4) == "www.") $referrer = substr($referrer, 4);
				$referrer = explode("/", $referrer);
				$_SESSION["referrer_domain"] = $referrer[0];
			}

			if(isset($_SESSION["FromSearch"]))
			{
				$this->fromsearch = true;
			}
			elseif((strpos($this->referrer, ".google.") !== false) ||
			   (strpos($this->referrer, ".yahoo.") !== false) ||
			   (strpos($this->referrer, ".altavista.") !== false) ||
			   (strpos($this->referrer, ".bing.") !== false) ||
			   (strpos($this->referrer, "search.") !== false))
			{
				$this->fromsearch = true;
				$_SESSION["FromSearch"] = 1;
			}
		}

		if($this->browser == "Safari" && $this->browser_version > "12.0") $this->nativeLL = false;
	}

	protected function getCookies()
	{
		$this->first_time_cookie_time = $this->stime;
		$this->last_time_cookie_time = $this->stime;

		if(isset($_COOKIE[$this->cookie_id_name]))
		{
			$this->cookie_id = $_COOKIE[$this->cookie_id_name];
		}
		else
		{
			$this->no_cookie = true;
			if(isset($_SESSION["cookie_id"]))
			{
				$this->cookie_id = $_SESSION["cookie_id"];
			}
		}

		if(isset($_COOKIE[$this->first_time_cookie_name]))
		{
			$this->first_time_cookie_value = $_COOKIE[$this->first_time_cookie_name];
			if(is_numeric($this->first_time_cookie_value)) $this->first_time_cookie_time = $this->first_time_cookie_value;
		}
		else
		{
			if(isset($_SESSION["first_time_cookie"]))
			{
				$this->first_time_cookie_value = $_SESSION["first_time_cookie"];
				if(is_numeric($this->first_time_cookie_value)) $this->first_time_cookie_time = $this->first_time_cookie_value;
			}
		}

		if(isset($_COOKIE[$this->last_time_cookie_name]))
		{
			$this->last_time_cookie_value = $_COOKIE[$this->last_time_cookie_name];
			if(is_numeric($this->last_time_cookie_value)) $this->last_time_cookie_time = $this->last_time_cookie_value;
		}
		else
		{
			if(isset($_SESSION["last_time_cookie"]))
			{
				$this->last_time_cookie_value = $_SESSION["last_time_cookie"];
				if(is_numeric($this->last_time_cookie_value)) $this->last_time_cookie_time = $this->last_time_cookie_value;
			}
		}
	}

	protected function setCookies()
	{
		$cookie_id = $this->cookie_id;
		if(!$cookie_id) $cookie_id = ServerFuncs::createCookieID($this->ip_address, $this->stime);
		$_SESSION["cookie_id"] = $cookie_id;
		$_SESSION["first_time_cookie"] = $this->first_time_cookie_time;
//		$_SESSION[$this->last_time_cookie_name] = $this->last_time_cookie_time;
		setcookie($this->cookie_id_name, $cookie_id, time() + 31536000, "/", $this->domain);
		setcookie($this->first_time_cookie_name, $this->first_time_cookie_time, time() + 31536000, "/", $this->domain);
		setcookie($this->last_time_cookie_name, $this->last_time_cookie_time, time() + 3600, "/", $this->domain);
	}

	//must have domain vars set up before calling this function
	public function setTestGroup()
	{
		if($this->bot) return;
		if($this->ip4 == $this->domain_vars->admin_ip)
		{
			$this->testgroup = $this->domain_vars->admin_test_group;
			return;
		}

		if($this->domain_vars->test_groups == 0) return;
//		if($this->domain_vars->test_groups < 2) return;		//if set to 1 it's just for me to test page

		//get the test group according to timestamp
		if(isset($_COOKIE["group"]))
		{
			$this->testgroup = $_COOKIE["group"];
			$_SESSION["testgroup"] = $this->testgroup;
		}
		else
		{
			if(isset($_SESSION["testgroup"]))
			{
				$this->testgroup = $_SESSION["testgroup"];
			}
			else
			{
				//decide the test group from the time stamp or whatever else
//				$t = time();
//				$this->testgroup = ($t % 2) + 1;
				$this->testgroup = rand(1, 2);
				$_SESSION["testgroup"] = $this->testgroup;
			}
			setcookie("group", $this->testgroup, time() + 604800, "/", $this->domain);
		}
	}

	private function getBotSignals()
	{
		$this->no_ref = (trim($this->referrer) == "") ? true : false;

		if(!isset($_SESSION["hit_times"])){
			$_SESSION["hit_times"] = $this->stime;
		} else {
			$_SESSION["hit_times"] .= "," . $this->stime;
		}


		if($this->blocked_time + 3 > $this->stime) {
			$this->bot = true;
			$this->useragent .= "*Within blocked time* ";
			$this->malicious = true;
			return;
		}

		if(!$this->no_ref) {
			if((substr($this->referrer, 0, 4) != "http") && (substr($this->referrer, 0, 11) != "android-app")) {
				$this->bot = true;
				$this->useragent .= "*Bad ref* ";
				$this->malicious = true;
				return;
			}
		}

		if($this->ip4 == "54.224.166.25") {
			//linkspun
			$this->bot = true;
			return;
		}

//Google IP ranges
//66.249.64.0 - 66.249.95.255
//66.102.0.0 - 66.102.15.255
//64.233.172.0 - 64.233.173.255
//74.125.208.0 - 74.125.209.255

		$google_ips = array(
			"34.64.82.64/28",
			"34.65.242.112/28",
			"34.80.50.80/28",
			"34.88.194.0/28",
			"34.89.10.80/28",
			"34.89.198.80/28",
			"34.96.162.48/28",
			"34.100.182.96/28",
			"34.101.50.144/28",
			"34.118.254.0/28",
			"34.118.66.0/28",
			"34.126.178.96/28",
			"34.146.150.144/28",
			"34.147.110.144/28",
			"34.151.74.144/28",
			"34.152.50.64/28",
			"34.154.114.144/28",
			"34.155.98.32/28",
			"34.165.18.176/28",
			"34.175.160.64/28",
			"34.176.130.16/28",
			"35.247.243.240/28",
			"64.18.0.0/20",
			"64.233.160.0/19",
			"66.102.0.0/20",
			"66.249.64.0/19",
			"72.14.192.0/18",
			"74.125.0.0/16",
			"108.177.8.0/21",
			"172.217.0.0/19",
			"173.194.0.0/16",
			"207.126.144.0/20",
			"209.85.128.0/17",
			"216.58.192.0/19",
			"216.239.32.0/19"
		);

		$ms_ips = array(
			"20.33.0.0/16",
			"20.128.0.0/16",
			"20.34.0.0/15",
			"20.48.0.0/12",
			"20.36.0.0/14",
			"20.40.0.0/13",
			"20.64.0.0/10",
			"20.128.0.0/16",
			"40.126.128.0/17",
			"40.127.0.0/16",
			"52.152.0.0/13",
			"52.145.0.0/16",
			"52.146.0.0/15",
			"52.148.0.0/14",
			"52.160.0.0/11"
		);

		$aws_ips = array(
			"18.128.0.0/9",
			"18.64.0.0/10",
			"18.32.0.0/11",
			"23.20.0.0/14",
			"34.192.0.0/10",
			"50.16.0.0/14",
			"52.192.0.0/12",
			"52.208.0.0/13",
			"52.216.0.0/14",
			"52.220.0.0/15",
			"52.222.0.0/16",
			"52.223.0.0/17",
			"52.223.128.0/18",
			"54.212.0.0/15",
			"54.160.0.0/11",
			"54.220.0.0/15",
			"54.228.0.0/15",
			"54.144.0.0/12",
			"54.208.0.0/13",
			"54.216.0.0/14",
			"54.192.0.0/12",
			"67.202.0.0/18",
			"75.101.128.0/17",
			"107.20.0.0/14",
			"174.129.0.0/16",
			"184.72.0.0/15",
			"204.236.128.0/17"
		);

		$ip = $this->ip4;
		$google_ip = false;
		$ms_ip = false;
		$aws_ip = false;
		foreach($google_ips as $cidr) {
			if (ServerFuncs::ip_in_cidr($ip, $cidr)) {
				$google_ip = true;
				break;
			}
		}
		if(!$google_ip) {
			foreach($ms_ips as $cidr) {
				if (ServerFuncs::ip_in_cidr($ip, $cidr)) {
					$ms_ip = true;
					break;
				}
			}
			if(!$ms_ip) {
				foreach($aws_ips as $cidr) {
					if (ServerFuncs::ip_in_cidr($ip, $cidr)) {
						$aws_ip = true;
						break;
					}
				}
			}
		}

		if((strpos($this->useragent, "Googlebot") !== false) ||
			(strpos($this->useragent, "FeedFetcher-Google") !== false) ||
			(strpos($this->useragent, "APIs-Google") !== false) ||
			(strpos($this->useragent, "AdsBot-Google") !== false) ||
			(strpos($this->useragent, "Google Favicon") !== false) ||
			(strpos($this->useragent, "Google-Read-Aloud") !== false) ||
			(strpos($this->useragent, "Google Web Preview") !== false) ||
			(strpos($this->useragent, "googleweblight") !== false) ||
			(strpos($this->useragent, "Yahoo!") !== false) ||
			(strpos($this->useragent, "YahooCacheSystem") !== false) ||
			(strpos($this->useragent, "AltaVista") !== false) ||
			(strpos($this->useragent, "bingbot") !== false) ||
			(strpos($this->useragent, "BingPreview") !== false) ||
			(strpos($this->useragent, "msnbot") !== false) ||
			(strpos($this->useragent, "DuckDuckBot") !== false) ||
			(strpos($this->useragent, "DuckDuckPreview") !== false) ||
			(strpos($this->useragent, "DuckDuckGo-Favicons-Bot") !== false) ||
			(strpos($this->useragent, "YandexBot") !== false) ||
			(strpos($this->useragent, "MJ12bot") !== false) ||
			(strpos($this->useragent, "AhrefsBot") !== false) ||
			(strpos($this->useragent, "rogerbot") !== false) ||
			(strpos($this->useragent, "opensiteexplorer.org/dotbot") !== false) ||
			(strpos($this->useragent, "Twitterbot") !== false) ||
			(strpos($this->useragent, "Applebot") !== false) ||
			(strpos($this->useragent, "MojeekBot") !== false) ||
			(strpos($this->useragent, "Twingly") !== false) ||
			(strpos($this->useragent, "SeznamBot") !== false) ||
			(strpos($this->useragent, "SemrushBot") !== false) ||
			(strpos($this->useragent, "TE spider") !== false) ||
			(strpos($this->useragent, "FTT2 Ping Bot") !== false) ||
			(strpos($this->useragent, "Buzzbot") !== false) ||
			(strpos($this->useragent, "TweetmemeBot") !== false) ||
			(strpos($this->useragent, "OpenHoseBot") !== false) ||
			(strpos($this->useragent, "MetaURI") !== false) ||
			(strpos($this->useragent, "Nicecrawler") !== false) ||
			(strpos($this->useragent, "ia_archiver") !== false))
		{
			if((strpos($this->useragent, "Googlebot") !== false) && (!$google_ip)) {
				$this->bot = true;
				$this->malicious = true;
				$this->useragent .= "*Fake googlebot*";
				$this->blocked_time = time();
				return;
			} else {
				$this->searchbot = true;
				$this->bot = true;
				return;
			}
		} elseif($google_ip) {
			$this->useragent .= "*Googlebot*";
			$this->searchbot = true;
			$this->bot = true;
			return;
		}

		if($ms_ip) {
			$this->useragent .= "*Microsoft*";
			$this->bot = true;
			return;
		}

		if($aws_ip) {
			$this->useragent .= "*Amazon AWS*";
			$this->bot = true;
			$this->malicious = true;
			return;
		}

		if((strpos($this->useragent, "Headless") !== false) ||
			(strpos($this->useragent, "WordPress") !== false) ||
			(strpos($this->useragent, "cfnetwork") !== false) ||
			(strpos($this->useragent, "woobot") !== false) ||
			(strpos($this->useragent, "DowntimeDetector") !== false) ||
			(strpos($this->useragent, "checkgzipcompression.com") !== false) ||
			(strpos($this->useragent, "deadlinkchecker.com") !== false) ||
			(strpos($this->useragent, "StatusCake") !== false) ||
			(strpos($this->useragent, "LinkSpun.com") !== false))
		{
			$this->bot = true;
			return;
		}

		if(strpos($this->linkname, "/api/") === false) {
			if((strpos($this->linkname, "wp-") !== false) ||
					(strpos($this->linkname, "upload") !== false) ||
					(strpos($this->linkname, "execute") !== false) ||
					(strpos($this->linkname, "plugin") !== false) ||
					(strpos($this->linkname, "admin") !== false) ||
					(strpos($this->linkname, "login") !== false))
			{
				$this->bot = true;
				$this->useragent .= "*Exploit scanner*";
				$this->malicious = true;
				$this->blocked_time = time();
				return;
			}
		}
		
		$bv = $this->browser_version;
		$bv = explode(".", $bv);
		$bv = $bv[0];
		if($this->browser == "Chrome") {
			if(($bv < 75) || (($bv < 87) && $this->no_ref)) {
				$this->bot = true;
				$this->old_browser = true;
				$this->useragent .= "*Old browser*";
				$this->blocked_time = time();
				$this->malicious = true;
				return;
			}
		} elseif($this->browser == "Firefox") {
			if(($bv < 75) || (($bv < 88) && $this->no_ref)) {
				$this->bot = true;
				$this->old_browser = true;
				$this->useragent .= "*Old browser*";
				$this->blocked_time = time();
				$this->malicious = true;
				return;
			}
		}

		$lang = substr($this->country, 0, 2);
		if(substr($this->country, 0, 4) == "text") {
			$this->bot = true;
			$this->malicious = true;
			$this->useragent .= "*Spam*";
			return;
		}

		if((trim($lang) == "") || (trim($lang) == "*")) {
			$this->bot = true;
			$this->malicious = true;
			$this->useragent .= "*No language set*";
			$this->blocked_time = time();
			return;
		}

		if(($this->country == "en;q=") || ($this->country == "zh-cn") || (trim($this->country) == "zh") || (trim($this->country) == "en")) {
			$this->bot = true;
			$this->malicious = true;
			$this->useragent .= "*Bad language*";
			$this->blocked_time = time();
			return;
		}

		if(!$this->no_ref) {
			$this->fromexternal = (strpos($this->referrer, $this->domain) === false) ? true : false;
		} else {
			if(isset($_SESSION["LastPage"])) $this->fromexternal = false;
		}
/*
		if($this->referrer)
		{
			$r = parse_url($this->referrer);
			$referrer_page = trim($r["path"]);
			if(!$referrer_page) $referrer_page = "/";
			$referrer_domain = $r["host"];
			if(substr($referrer_domain, 0, 4) == "www.") $referrer_domain = substr($referrer_domain, 4);

			//never have pages linking to themselves so any request referred by the same page must be a bot faking the referrer string
			if(($referrer_page == $this->request_page) && ($referrer_domain == $this->domain))
			{
				//we get here if the referring page is the http/https or www/non-www version of the requested page
	//			$this->bad_ref = true;
	//			$this->bot_score += 9;
				//the only page that can refer to itself is a page with a form that posts back to itself, anything else is a bot faking the referrer string
				if(empty($_POST))
				{
					$this->bot = true;
					$this->useragent .= "*Bad ref and no session* ";
					$this->malicious = true;
					$this->blocked_time = time();
					return;
				}
			}

		}
*/
		if((strpos($this->referrer, "seo-traffic") !== false) || (strpos($this->referrer, "naver.com") !== false)) {
			$this->bot = true;
			$this->malicious = true;
			$this->useragent .= "*Spam*";
			return;
		}

		if((substr($this->referrer, -4) == ".xyz") && ($lang == "es")) {
			$this->bot = true;
			$this->malicious = true;
			$this->useragent .= "*Spam*";
			return;
		}

		if(substr($this->referrer, -3) == ".ru") {
			$this->bad_ref = true;
			$this->bot_score += 9;
		}

		$ua = strtolower($this->useragent);

		if(strlen($ua) < 36) {
			$this->bot = true;
			$this->useragent .= "*Short UA* ";
			$this->malicious = true;
			$this->blocked_time = time();
			return;
		}

		if((strpos($ua, "crawler") !== false)
			|| (strpos($ua, "bot/") !== false)
			|| (strpos($ua, "spider") !== false)
			|| (strpos($ua, "http://") !== false)
			|| (strpos($ua, "https://") !== false)
			|| (strpos($ua, "windows 98") !== false)
			|| (strpos($ua, "windows 95") !== false)
			|| (strpos($ua, "msie 5") !== false)
			|| (strpos($ua, "msie 6") !== false)
			|| (strpos($ua, "msie 7") !== false)
			|| (strpos($ua, "msie 8") !== false)
			|| (strpos($ua, "msie 9") !== false)
			|| (strpos($ua, "compatible;msie") !== false)
			|| (strpos($ua, "download") !== false)
			|| (strpos($ua, "linux i686") !== false)
			|| (strpos($ua, "gzip(gfe)") !== false)
			|| (strpos($ua, "ezooms") !== false))
		{
			$this->bot = true;
			$this->useragent .= "*Banned on UA* ";
			$this->malicious = true;
			$this->blocked_time = time();
			return;
		}

/*
		if(strpos($ua, "x11; linux x86_64") !== false)
		{
		    $this->bot_score += 6;
		    if($this->no_ref) $this->bot = true;
		}
*/
		if(strlen($ua) < 51) {
			$this->bot_score += 4;
			if(strlen($ua) < 50) {
				$this->bot_score += 4;
			}
		}

		if($this->no_ref) {
			$this->bot_score += 2;
			if($this->bad_ua) $this->bot_score += 10;
		}

		if($this->bad_ua && $this->bad_ref) {
			$this->bot = true;
			$this->useragent .= "*Bad UA and bad ref* ";
			$this->malicious = true;
			$this->blocked_time = time();
			return;
		}

		if($this->no_session) $this->bot_score +=4;

		$hits = explode(",", $_SESSION["hit_times"]);
		$hit_count = sizeof($hits);

		if($hit_count > 1) {
			//get the average time between hits in milliseconds
			$t1 = (($hits[$hit_count - 1] - $hits[0]) * 1000) / ($hit_count - 1);

			//3 hits in 1 second is too many
			//5 hits in 2.5 seconds is too many
			//10 hits in 7 seconds is too many
			if((($hit_count > 2) && ($t1 < 500))
				|| (($hit_count > 4) && ($t1 < 600))
				|| (($hit_count > 9) && ($t1 < 750))
			)
			{
				$this->useragent .= "*Too many hits ($hit_count)* ";
				$this->malicious = true;
				$this->bot = true;
				$this->blocked_time = time();
				return;
			}
		}

		if($this->bot_score > 9) {
			$this->bot = true;
		}

		if($this->bot_score > 20) {
			$this->useragent .= "*Failed bot test* ";
			$this->malicious = true;
			return;
		}

	}

	public function getBrowserType()
	{
		if(($this->platform == "Windows") || ($this->platform == "Linux") || ($this->platform == "Macintosh")) {
			$this->desktop = true;
		} else {
			$this->desktop = false;
		}
	}

	public function getPlatformAndBrowser()
	{
		$ua = str_replace("'", "", $this->useragent);

		// Enumerate all common platforms, this is usually placed in braces (order is important! First come first serve..)
		$platforms = "Windows|iPad|iPhone|Macintosh|Android|BlackBerry|Linux\ x86_64|CrOS";

		// All browsers except MSIE/Trident and..
		// NOT for browsers that use this syntax: Version/0.xx Browsername
		$browsers = "Edge|Firefox|Chrome|GSA|CriOS|FxiOS";

		// Specifically for browsers that use this syntax: Version/0.xx Browername
		$browsers_v = "Safari"; // Mobile is mentioned in Android and BlackBerry UA's

		// Fill in your most common engines..
		$engines = "Gecko|Trident|Webkit|Presto";

		// Regex the crap out of the user agent, making multiple selections and..
		$regex_pat = "/((Mozilla)\/[\d\.]+|(Opera)\/[\d\.]+)\s\(.*?((MSIE)\s([\d\.]+).*?(Windows)|({$platforms})).*?\s.*?({$engines})[\/\s]+[\d\.]+(\;\srv\:([\d\.]+)|.*?).*?(Version[\/\s]([\d\.]+)(.*?({$browsers_v})|$)|(({$browsers})[\/\s]+([\d\.]+))|$).*/i";

		// .. placing them in this order, delimited by |
		$replace_pat = '$7$8|$2$3|$9|${17}${15}$5$3|${18}${13}$6${11}';

		// Run the preg_replace .. and explode on |
		$ua_array = explode("|",preg_replace($regex_pat, $replace_pat, $ua, PREG_PATTERN_ORDER));

		if((count($ua_array)>1) && (substr($ua, 0, 8) == "Mozilla/"))
		{
			$return['platform']  = $ua_array[0];  // Windows / iPad / MacOS / BlackBerry
			$return['type']      = $ua_array[1];  // Mozilla / Opera etc.
			$return['renderer']  = $ua_array[2];  // WebKit / Presto / Trident / Gecko etc.
			$return['browser']   = $ua_array[3];  // Chrome / Safari / MSIE / Firefox

/*
Not necessary but this will filter out Chromes ridiculously long version
numbers 31.0.1234.122 becomes 31.0, while a "normal" 3 digit version number
like 10.2.1 would stay 10.2.1, 11.0 stays 11.0. Non-match stays what it is.
*/

			if (preg_match("/^[\d]+\.[\d]+(?:\.[\d]{0,2}$)?/",$ua_array[4],$matches))
			{
				$return['version'] = $matches[0];
			}
			else
			{
				$return['version'] = $ua_array[4];
			}
		}
		else
		{
/*
Unknown browser..
This could be a deal breaker for you but I use this to actually keep old
browsers out of my application, users are told to download a compatible
browser (99% of modern browsers are compatible. You can also ignore my error
but then there is no guarantee that the application will work and thus
no need to report debugging data.
*/
			$return['platform'] = "Other";
			$return['type'] = "";
			$return['renderer'] = "";
			$return['browser'] = "";
			$return['version'] = "";
		}

// Replace some browsernames e.g. MSIE -> Internet Explorer
		switch(strtolower($return['browser']))
		{
			case "msie":
			case "internet explorer":
			case "trident":
				$return['browser'] = "IE";
				break;
			case "crios":
				$return['browser'] = "Chrome";
				break;
			case "": // IE 11 is a steamy turd (thanks Microsoft...)
				if (strtolower($return['renderer']) == "trident")
				{
					$return['browser'] = "IE";
				}
				break;
			default:
				break;
		}

		switch(strtolower($return['platform']))
		{
			case "android":    // These browsers claim to be Safari but are BB Mobile
			case "blackberry": // and Android Mobile
				if ($return['browser'] =="Safari" || $return['browser'] == "Mobile" || $return['browser'] == "")
				{
					$return['browser'] = "Mobile";
				}
				break;
			case "cros":
				$return['platform'] = "ChromeOS";
				break;
			case "linux x86_64":
				$return['platform'] = "Linux";
				break;
			case "windows":
				if(strpos($ua, "Touch") !== false)
				{
					$return['platform'] = "Windows Tablet";
				}
				if(strpos($ua, "Phone") !== false)
				{
					$return['platform'] = "Windows Phone";
				}
			default:
				break;
		}

		$this->platform = $return["platform"];
		$this->browser = $return["browser"];
		$this->browser_version = $return["version"];
		$this->platform_browser = $return["platform"] . " " . $return["browser"] . "/" . $return["version"];
		return;
	}

	public function getDomainVars($dr)
	{
		$this->domain_vars = $dr->fetch_object();
		$this->domain_id = $this->domain_vars->domain_id;
	}

	public function copyDomainVars($do)
	{
		$this->domain_vars = $do;
		$this->domain_id = $this->domain_vars->domain_id;
	}

	private function getDomainPrefix($domainstring)
	{
		$domainstring = str_replace(".", "_", $domainstring);
		$domainstring = str_replace("-", "_", $domainstring);
		$domainstring = strtolower($domainstring);

		return $domainstring;
	}

	private function getTablesPrefix($hostname)
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
		$domain = $dom[0];
		return $this->getDomainPrefix($domain);
	}

	private function domainPrefix()
	{
		$hostname = $_SERVER["HTTP_HOST"];
		return $this->getTablesPrefix($hostname);
	}

	public function getMoreBotSignals()
	{
		if($this->no_session) $this->bot_score += 5;
		if($this->no_cookie) $this->bot_score += 1;

		if($this->bot_score > 14)
		{
			$this->bot = true;
			$this->malicious = true;
		}
	}

	public function blockRobots()
	{
		$_SESSION["blocked_time"] = $this->blocked_time;
		if($this->malicious)
		{
			$this->linkname = "[403]" . $this->linkname;
			$this->useragent .= "*Bot score " . $this->bot_score . "*";

			$this->addVisitorPage();
			header('HTTP/1.1 403 Forbidden');
			exit();
		}

		if($this->old_browser)
		{
			$this->addVisitorPage();
			echo "Your browser version is out of date. Please upgrade to a newer version and try again.";
			exit();
		}
	}

// Use this to store clicks
	public function addVisitorStat()
	{
		$dbc = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
		$prefix = $this->table_prefix;

		$visit_type = ($this->bot || $this->chinese) ? "bot" : "click";
		if($this->bot)
		{
			$this->platform = "Bot";
			$this->platform_browser = "Bot";
		}

		$referrer = $dbc->real_escape_string($this->referrer);
		$linkname = $dbc->real_escape_string($this->linkname);
		$useragent = $dbc->real_escape_string($this->useragent);
		$country = $dbc->real_escape_string($this->country);
		$platform = $dbc->real_escape_string($this->platform);
		$browser = $dbc->real_escape_string($this->platform_browser);
		$cookie_id = $dbc->real_escape_string($this->cookie_id);
		if(!is_numeric($cookie_id)) $cookie_id = 999;
		$first_time = $dbc->real_escape_string($this->first_time_cookie_value);
		$last_time = $dbc->real_escape_string($this->last_time_cookie_value);

		$q = "insert into {$prefix}_stats set
					stime=$this->stime,
					visit_type='$visit_type',
					referrer='$referrer',
					pagename='$linkname',
					site_id=$this->site_id,
					link_id=$this->link_id,
					gallery_id=$this->gallery_id,
					ip_address=$this->ip_address,
					useragent='$useragent',
					cookie_id=$cookie_id,
					first_cookie_time=$first_time,
					last_cookie_time=$last_time,
					country='$country',
					platform='$platform',
					browser='$browser',
					testgroup=$this->testgroup
					";
		$r = $dbc->query($q) or die($dbc->error . " query was $q in " . __FILE__ . " at line " . __LINE__);
		$id = $dbc->insert_id;
		$dbc->close();
		return $id;
	}

// Use this one to store pageloads and bot traffic
	public function addVisitorPage()
	{
		$dbc = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
		$prefix = $this->table_prefix;

		//set the LastTime cookie to expire in one hour
//		setcookie($this->cookie_name, $this->cookie_value, $this->stime + FIRST_COOKIE_EXPIRE, "/");

		$visit_type = ($this->bot || $this->chinese) ? "bot" : "page";
		if($this->bot)
		{
			$this->platform = "Bot";
			$this->platform_browser = "Bot";
		}

		$referrer = $dbc->real_escape_string($this->referrer);
		$linkname = $dbc->real_escape_string($this->linkname);
		$useragent = $dbc->real_escape_string($this->useragent);
		$country = $dbc->real_escape_string($this->country);
		$platform = $dbc->real_escape_string($this->platform);
		$browser = $dbc->real_escape_string($this->platform_browser);
		$cookie_id = $dbc->real_escape_string($this->cookie_id);
		if(!is_numeric($cookie_id)) $cookie_id = 999;
		$first_time = $dbc->real_escape_string($this->first_time_cookie_value);
		$last_time = $dbc->real_escape_string($this->last_time_cookie_value);

		$q = "insert into {$prefix}_stats set
					stime=$this->stime,
					visit_type='$visit_type',
					referrer='$referrer',
					pagename='$linkname',
					ref_id=$this->ref_id,
					ip_address=$this->ip_address,
					useragent='$useragent',
					cookie_id=$cookie_id,
					first_cookie_time=$first_time,
					last_cookie_time=$last_time,
					country='$country',
					platform='$platform',
					browser='$browser',
					testgroup=$this->testgroup
					";
		$r = $dbc->query($q) or die($dbc->error . " query was $q in " . __FILE__ . " at line " . __LINE__);
		$id = $dbc->insert_id;
		$dbc->close();
		return $id;
	}
}

?>
