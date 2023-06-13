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

//this is to deal with the old site structure
	$newpage = "";
	if($usize > 1)
	{
		$oldpage = $u[0];
		if(substr($oldpage, 0, 7) == "Amateur") $newpage = "amateur";
		if(substr($oldpage, 0, 8) == "Anal-Sex") $newpage = "anal-sex";
		if(substr($oldpage, 0, 5) == "Asian") $newpage = "asian";
		if(substr($oldpage, 0, 5) == "Babes") $newpage = "babes";
		if(substr($oldpage, 0, 9) == "Big-Cocks") $newpage = "big-cocks";
		if(substr($oldpage, 0, 8) == "Big-Tits") $newpage = "big-tits";
		if(substr($oldpage, 0, 8) == "Bisexual") $newpage = "bisexual";
		if(substr($oldpage, 0, 9) == "Cum-Shots") $newpage = "cumshots-and-facials";
		if(substr($oldpage, 0, 5) == "Ebony") $newpage = "ebony";
		if(substr($oldpage, 0, 6) == "FemDom") $newpage = "femdom";
		if(substr($oldpage, 0, 6) == "Fetish") $newpage = "fetish";
		if(substr($oldpage, 0, 7) == "Fisting") $newpage = "fisting";
		if(substr($oldpage, 0, 3) == "Gay") $newpage = "gay";
		if(substr($oldpage, 0, 9) == "Group-Sex") $newpage = "group-sex";
		if(substr($oldpage, 0, 8) == "Hand-Job") $newpage = "hand-jobs";
		if(substr($oldpage, 0, 8) == "Hardcore") $newpage = "hardcore";
		if(substr($oldpage, 0, 6) == "Indian") $newpage = "indian";
		if(substr($oldpage, 0, 11) == "Interracial") $newpage = "interracial";
		if(substr($oldpage, 0, 6) == "Latina") $newpage = "latinas";
		if(substr($oldpage, 0, 8) == "Lesbians") $newpage = "lesbians";
		if(substr($oldpage, 0, 10) == "Link_Sites") $newpage = "link-lists";
		if(substr($oldpage, 0, 6) == "Mature") $newpage = "milfs";
		if(substr($oldpage, 0, 5) == "Nylon") $newpage = "nylons";
		if(substr($oldpage, 0, 8) == "Oral-Sex") $newpage = "oral-sex";
		if(substr($oldpage, 0, 10) == "Porn-Stars") $newpage = "porn-stars";
		if(substr($oldpage, 0, 10) == "Public-Sex") $newpage = "public-sex";
		if(substr($oldpage, 0, 7) == "Shemale") $newpage = "shemale";
		if(substr($oldpage, 0, 8) == "Softcore") $newpage = "softcore";
		if(substr($oldpage, 0, 5) == "Teens") $newpage = "teens";
		if(substr($oldpage, 0, 6) == "Voyeur") $newpage = "voyeur";
		if(substr($oldpage, 0, 7) == "Webcams") $newpage = "webcams";
		if(substr($oldpage, 0, 12) == "Anime-Hentai") $newpage = "anime-and-hentai";
		if(substr($oldpage, 0, 3) == "BBW") $newpage = "bbw";
		if(substr($oldpage, 0, 4) == "BDSM") $newpage = "bdsm";
		if(substr($oldpage, 0, 7) == "Blondes") $newpage = "blondes";
		if(substr($oldpage, 0, 9) == "Brunettes") $newpage = "brunettes";
		if(substr($oldpage, 0, 8) == "Cartoons") $newpage = "cartoons";
		if(substr($oldpage, 0, 9) == "For-Women") $newpage = "for-women";
		if(substr($oldpage, 0, 11) == "Foot-Fetish") $newpage = "foot-fetish";
		if(substr($oldpage, 0, 5) == "Hairy") $newpage = "hairy";
		if(substr($oldpage, 0, 5) == "Panty") $newpage = "panties";
		if(substr($oldpage, 0, 15) == "Piercing-Tattoo") $newpage = "piercing-and-tattoos";
		if(substr($oldpage, 0, 7) == "Pissing") $newpage = "pissing";
		if(substr($oldpage, 0, 8) == "Pregnant") $newpage = "pregnant";
		if(substr($oldpage, 0, 8) == "Redheads") $newpage = "redheads";
		if(substr($oldpage, 0, 6) == "Shaved") $newpage = "shaved";
		if(substr($oldpage, 0, 7) == "Smoking") $newpage = "smoking";
		if(substr($oldpage, 0, 8) == "Spanking") $newpage = "spanking";
		if(substr($oldpage, 0, 9) == "Squirting") $newpage = "squirting";
		if(substr($oldpage, 0, 4) == "Toys") $newpage = "toys";
		if(substr($oldpage, 0, 7) == "Uniform") $newpage = "uniform";
		if(substr($oldpage, 0, 11) == "vintage-sex") $newpage = "vintage-porn";

		if($newpage)
		{
			$new_url = "$protocol://www.sexxx-porn-links.com/$newpage/";
			header("HTTP/1.1 301 Moved Permanently");
			header("Location: $new_url");
			exit();
		}
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
			if(($u[0] == "site") && ($u[2] == ""))
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
