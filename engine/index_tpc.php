<?php
	session_start();
	ini_set('display_errors', '1');
	error_reporting(E_ALL & ~E_NOTICE);

	require_once(INCLUDE_PATH . 'defines.php');

	$_SESSION["timer"] = microtime(true);

	if(isset($_GET["u"])) {
		$u = $_GET["u"];
	} else {
	    $u = "index.html";
	}

	if((substr($u, -4) == ".jpg") || (substr($u, -4) == ".png")) {
		header('HTTP/1.0 404 Not Found');
		exit();
	}

//	$protocol = "http";
	$protocol = "https";

	$m = array();
//	$dn = $_SERVER["HTTP_HOST"];
//	if(substr($dn, 0, 4) == "www.") $dn = substr($dn, 4);
//	if(substr($dn, 0, 5) == "test.") $dn = substr($dn, 5);
	$dn = "theporncollection.com";

	$u = explode("/", $u);
	$usize = sizeof($u);

	if(($usize == 2) && ($u[0] == "visit")) {
		$u[2] = "";
		$usize = 3;
	}

//this is to deal with the old site structure where /porn/ was the categories directory, need to redirect every page to "/categories/*"

	$newpage = "";
	if(($usize > 1) && ($u[0] == "porn")) {
		$oldpage = $u[1];
		if(substr($oldpage, 0, 8) == "amateurs") $newpage = "amateurs";
		if(substr($oldpage, 0, 4) == "anal") $newpage = "anal";
		if(substr($oldpage, 0, 5) == "asian") $newpage = "asian";
		if(substr($oldpage, 0, 5) == "babes") $newpage = "babes";
		if(substr($oldpage, 0, 8) == "bisexual") $newpage = "bisexual";
		if(substr($oldpage, 0, 5) == "black") $newpage = "black";
		if(substr($oldpage, 0, 5) == "boobs") $newpage = "big-tits";
//		if(substr($oldpage, 0, 6) == "celebs") $newpage = "celebs";
		if(substr($oldpage, 0, 7) == "cumshot") $newpage = "cumshot";
//		if(substr($oldpage, 0, 8) == "hardcore") $newpage = "hardcore";
		if(substr($oldpage, 0, 6) == "indian") $newpage = "indian";
		if(substr($oldpage, 0, 11) == "interracial") $newpage = "interracial";
		if(substr($oldpage, 0, 7) == "lesbian") $newpage = "lesbian";
//		if(substr($oldpage, 0, 8) == "lingerie") $newpage = "lingerie";
		if(substr($oldpage, 0, 6) == "mature") $newpage = "mature";
		if(substr($oldpage, 0, 8) == "pregnant") $newpage = "pregnant";
//		if(substr($oldpage, 0, 7) == "reality") $newpage = "reality";
		if(substr($oldpage, 0, 4) == "teen") $newpage = "teen";
		if(substr($oldpage, 0, 6) == "voyeur") $newpage = "voyeur";
		if(substr($oldpage, 0, 6) == "movies") $newpage = "tubes";
		if(substr($oldpage, 0, 4) == "gays") $newpage = "gay";
		if(substr($oldpage, 0, 4) == "gay-") $newpage = "gay";
		if(substr($oldpage, 0, 3) == "fat") $newpage = "bbw";
//		if(substr($oldpage, 0, 7) == "bondage") $newpage = "fetish";
		if(substr($oldpage, 0, 8) == "groupsex") $newpage = "group-sex";
		if(substr($oldpage, 0, 7) == "hirsute") $newpage = "hairy";
		if(substr($oldpage, 0, 10) == "porn-stars") $newpage = "pornstars";
//		if(substr($oldpage, 0, 6) == "shaved") $newpage = "fetish";
//		if(substr($oldpage, 0, 7) == "smoking") $newpage = "fetish";
		if(substr($oldpage, 0, 7) == "shemale") $newpage = "trans";
//		if(substr($oldpage, 0, 7) == "panties") $newpage = "lingerie";
//		if(substr($oldpage, 0, 9) == "pantyhose") $newpage = "lingerie";
		if(substr($oldpage, 0, 11) == "watersports") $newpage = "peeing";

		if($newpage)
		{
			$new_url = "$protocol://www.theporncollection.com/categories/$newpage.html";
			header("HTTP/1.1 301 Moved Permanently");
			header("Location: $new_url");
			exit();
		}
	}

	if(($usize < 3) && ($u[$usize - 1] == "")) $u[$usize - 1] = "index.html";	//can't have a page without a name so change it so we can find it in the page table

	$prefix = getTablePrefix($dn);

	$dbc = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
	$q = "select * from domains where domain_name='$dn' and `status`=1";
	$r = $dbc->query($q) or die($dbc->error);
	if(!$r->num_rows) {
		$r->free();
		$dbc->close();
?>
<html>
<head><title><?=$dn ?></title></head>
<body style="color:#606060;background-color:white;font-size:14pt;"><p>Under construction</p>
</body></html>
<?php
		exit();
	} else {
//we have the domain in the database now try to figure out the directory structure
		$dv = $r->fetch_object();
		$r->free();
		if($usize == 1) {
			//no directory just the page name
			$pagename = $u[0];
			if(preg_match("/^page[0-9]+\\.html$/", $pagename)) $pagename = "index.html";
		} elseif($usize == 2) {
			//one directory so figure out if it's a category or a post index
			$pagename = $u[1];
			if(preg_match("/^page[0-9]+\\.html$/", $pagename)) $pagename = "index.html";
			$pagename = $u[0] . "/" . $pagename;
			if(is_numeric($u[0])) $pagename = "post.html";
		} else {
			//the only thing allowed here is the "visit" outbound link redirect, anything else is 404
			if(($u[0] == "visit") && ($u[2] == "")) {
				require(INCLUDE_PATH . "visit_handler.php");
			} else {
				$pagename = "error.html";
			}
		}
	}

//if we make it to here we must have a live domain, a database connection and a pagename (complete path) to try and find

//look for a page that matches $pagename (including any directories)
	$q = "select * from {$prefix}_dynamic_page where page_name='$pagename'";
	$r = $dbc->query($q) or die($dbc->error);
	if($pv = $r->fetch_object()) {
		$r->free();
		loadPage($dv, $pv, $u, $prefix);
		exit();
	}
	$r->free();
//if not found look for a wildcard page that can handle this page type (the directory name)
	$pagename = $u[0] . "/*.html";
	$q = "select * from {$prefix}_dynamic_page where page_name='$pagename'";
	$r = $dbc->query($q) or die($dbc->error);
	if($pv = $r->fetch_object()) {
		loadPage($dv, $pv, $u, $prefix);
		exit();
	}
//No luck so look for the error page
	$q = "select * from {$prefix}_dynamic_page where page_name='error.html'";
	$r = $dbc->query($q) or die($dbc->error);
	$pv = $r->fetch_object();
	$r->free();
	$dbc->close();
	if($pv) {
		loadPage($dv, $pv, $u, $prefix);
		exit();
	}

//not found so 404
	header('HTTP/1.0 404 Not Found');
	exit();

	function loadPage($dv, $pv, $u, $prefix) 
	{
//if there is a dest_url it means this page must be redirected, either to the url or if it's just 410 then send a 410 error
		if($pv->dest_url) {
			if($pv->dest_url == "410") {
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
        if(substr($hostname, 0, 4) == "www.") {
            $hostname = substr($hostname, 4);
        } elseif(substr($hostname, 0, 5) == "test.") {
            $hostname = substr($hostname, 5);
        }
        $dom = explode(".", $hostname);
        $domainstring = $dom[0];
        $domainstring = str_replace("-", "_", $domainstring);
        $domainstring = strtolower($domainstring);
        return $domainstring;
    }

?>
