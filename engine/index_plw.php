<?php
	require_once(INCLUDE_PATH . 'defines.php');

	session_start();
	ini_set('display_errors', '1');
	error_reporting(E_ALL & ~E_NOTICE);

	$_SESSION["timer"] = microtime(true);

	if(isset($_GET["u"]))
	{
		$u = $_GET["u"];
	}
	else
	{
	    $u = "";
	}

	if((substr($u, -4) == ".jpg") || (substr($u, -4) == ".png") || (substr($u, -4) == ".gif") || (substr($u, -4) == ".ico") || (substr($u, -4) == ".txt"))
	{
		header('HTTP/1.0 404 Not Found');
		exit();
	}

//	$subdomain = "test";
	$subdomain = "www";
//	$protocol = "http";
	$protocol = "https";

	$dn = $_SERVER["HTTP_HOST"];
	if(substr($dn, 0, 4) == "www.")
		$dn = substr($dn, 4);
	elseif(substr($dn, 0, 5) == "test.")
		$dn = substr($dn, 5);

	$s = $_SERVER["REQUEST_URI"];
	if(substr($s, 0, 11) == "/index.html")
	{
		header("HTTP/1.1 301 Moved Permanently");
		header("Location: $protocol://$subdomain.$dn/");
		exit();
	}

	$u = explode("/", $u);
	$usize = sizeof($u);

	if(($usize == 3) && ($u[0] == "category"))
	{
		$s = explode("-", $u[1]);
		$url = $protocol . "://$subdomain.$dn/" . $s[0] . "/";
		header("HTTP/1.1 301 Moved Permanently");
		header("Location: $url");
		exit();
	}

//	if(($usize == 2) && ($u[1] == ""))
//	{
//		$url = $protocol . "://$subdomain.$dn/posts/" . $u[0] . ".html";
//		header("HTTP/1.1 301 Moved Permanently");
//		header("Location: $url");
//		exit();
//	}

	if($u[$usize-1] == "index.html")
	{
		$url = $protocol . "://$subdomain.$dn/";
		for($i = 0; $i < $usize; $i++)
		{
			$url .= $u[$i] . "/";
		}
		header("HTTP/1.1 301 Moved Permanently");
		header("Location: $url");
		exit();
	}

	if(($usize < 3) && ($u[$usize - 1] == "")) $u[$usize - 1] = "index.html";	//can't have a page without a name so change it so we can find it in the page table

	$pagename = $u[$usize - 1];

	$prefix = getTablePrefix($dn);

	$dbc = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
	$q = "select * from domains where domain_name='$dn' and `status`=1";
	$r = $dbc->query($q) or die($dbc->error);
	if(!$r->num_rows)
	{
		$r->free();
		$dbc->close();
?>
<html>
<head><title><?=$dn ?></title></head>
<body style="color:#606060;background-color:white;font-size:12pt;"><p>Coming soon</p>
</body></html>
<?php
		exit();
	}
	else
	{
//we have the domain in the database now try to figure out the directory structure
		$dv = $r->fetch_object();
		$r->free();
		if($usize == 1)
		{
//no directory just the page name
			$pagename = $u[0];
			if(preg_match("/^page[0-9]+\\.html$/", $pagename)) $pagename = "index.html";
		}
		elseif($usize == 2)
		{
//one directory so figure out if it's a category or a post index
			$pagename = $u[1];
			if(preg_match("/^page[0-9]+\\.html$/", $pagename)) $pagename = "index.html";
			$pagename = $u[0] . "/" . $pagename;
		}
		else
		{
//the only thing allowed here is the "go" outbound link redirect, anything else is 404
			if(($u[0] == "go") && ($u[2] == ""))
			{
				require(INCLUDE_PATH . "go_handler.php");
			}
			else
			{
				$pagename = "error.html";
			}
		}
	}

//if we make it to here we must have a live domain, a database connection and a pagename (complete path) to try and find

//look for a page that matches $pagename (including any directories)
	$q = "select * from {$prefix}_dynamic_page where page_name='$pagename'";
	$r = $dbc->query($q) or die($dbc->error);
	if($pv = $r->fetch_object())
	{
		$r->free();
		loadPage($dv, $pv, $u, $prefix);
		exit();
	}
	$r->free();
//if not found look for a wildcard page that can handle this page type (the directory name)
	$pagename = $u[0] . "/*.html";
	$q = "select * from {$prefix}_dynamic_page where page_name='$pagename'";
	$r = $dbc->query($q) or die($dbc->error);
	if($pv = $r->fetch_object())
	{
		loadPage($dv, $pv, $u, $prefix);
		exit();
	}
	$r->free();
//we get here if we can't find the exact page name or a directory catch all
//it's probably the old wordpress (shite) url structure with a trailing slash, use posts/*.html to sort it out.
	$q = "select * from {$prefix}_dynamic_page where page_name='posts/*.html'";
	$r = $dbc->query($q) or die($dbc->error);
	if($pv = $r->fetch_object())
	{
		loadPage($dv, $pv, $u, $prefix);
		exit();
	}
	$r->free();
//No luck so look for the error page
	$q = "select * from {$prefix}_dynamic_page where page_name='error.html'";
	$r = $dbc->query($q) or die($dbc->error);
	$pv = $r->fetch_object();
	$r->free();
	$dbc->close();
	if($pv)
	{
		loadPage($dv, $pv, $u, $prefix);
		exit();
	}

//not found so 404
	header('HTTP/1.0 404 Not Found');
	exit();

	function loadPage($dv, $pv, $u, $prefix)
	{
//if there is a dest_url it means this page must be redirected, either to the url or if it's just 410 then send a 410 error
		if($pv->dest_url)
		{
			if($pv->dest_url == "410")
			{
				header("HTTP/1.1 410 Gone");
				exit();
			}

			header("HTTP/1.1 301 Moved Permanently");
			header("Location: {$pv->dest_url}");
			exit();
		}
//dynamically load the page which will include and instantiate the page class and set the template for the class to include
		$path = "domains/$prefix/{$pv->page_filename}";
		require(INCLUDE_PATH . $path);
		exit;
	}

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
