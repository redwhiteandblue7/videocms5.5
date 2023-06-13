<?php
	require_once(INCLUDE_PATH . 'pages/page.class.php');
	require_once(INCLUDE_PATH . 'pages/plwo.class.php');	//functions that are common to all sites on the PornLinksWorld server

class dynamicPage extends commonPage
{
	protected $errors = array();
	protected $cat_rows = array();
	protected $done_toplist = false;
	public $placeholder_image = "/images/loading300x300.gif";
	public $hostname1 = "";

	protected function writeHeader($title, $meta, $css = "", $robots = false, $keywords = "")
	{
		$this->pageload_stat_id = $this->getPageloadStat();
		if($css == "") $css = "/main.css";
		echo "<!DOCTYPE html>\n";
		echo "<html lang=\"en\">\n<head>\n<meta charset=\"UTF-8\">\n";
		echo "<title>$title</title>\n";
		echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"$css\" />\n";
		echo "<meta name=\"viewport\" content=\"width=device-width, initial-scale=1\" />\n";
//		echo "<meta name=\"referrer\" content=\"unsafe-url\" />\n";
		if($robots) echo "<meta name=\"robots\" content=\"noindex\">\n";
		if($this->canonical_url) echo "<link rel=\"canonical\" href=\"" . $this->canonical_url . "\" />\n";
		if($keywords != "") echo "<meta name=\"keywords\" content=\"$keywords\" />\n";
		if($meta != "") echo "<meta name=\"description\" content=\"$meta\" />\n";
		echo "</head>\n";
	}

	protected function writeTitle()
	{
		echo "<body>\n<header>\n";
		echo "<div class=\"row\"><div class=\"card\"><img src=\"" . $this->hostname1 . "/imgs/pornlinksworldlogo-2.jpg\" alt=\"Porn Links World logo\" width=\"1140\" height=\"200\" /></div></div>\n";
		echo "</header>\n";
	}
	
	protected function writeFooter()
	{
		echo "<footer>";
		echo "<div>";
		echo "<p class=\"center\">PornLinksWorld.com (c)";
		echo date("Y");
		echo ". Not powered by WordPress.</p></div></footer>\n";
		$this->writeFooterScript();
	}

	protected function writeThumbsColumn($key)
	{
		$page = $this->cat_rows[$key][0]["cat_pagename"];
		echo "<div class=\"card cat-col\">";
		echo "<a href=\"$page\"><h2>$key Porn Sites</h2></a>";
		echo "<ol>";
		foreach($this->cat_rows[$key] as $row)
		{
			echo "<li>";
			extract($row);
			$title = htmlspecialchars($title, ENT_QUOTES, "UTF-8");
			if($icon_url == "") $icon_url = "/post-content/default.png";
			$icon_url = $this->hostname1 . $icon_url;
			echo "<span class=\"list-icon\"><img width=\"16\" height=\"16\" alt=\"$title\" src=\"$icon_url\" /></span>";
			echo " <a href=\"/posts/$pagename.html\">$title";
			echo " <span class=\"m-icon\"></span></a>";
			echo "<div><a href=\"/posts/$pagename.html\"><span class=\"r-icon\"></span></a></div>";
			echo "</li>";
		}
		echo "</ol>\n";
		echo "</div>\n";
	}

	protected function writeThumbsRows()
	{
		while($row = $this->getNextGalleryRow())
		{
			extract($row);
			if(strpos($description, "<?xml") !== false)
			{
				$xml=simplexml_load_string($description) or die("Error: Cannot create object from post");
				if(isset($xml->snippet))
				{
					$snippet = $xml->snippet;
				}
			}
			if($snippet == "")
			{
				$snippet = $xml->fulltext;
				$snippet = strip_tags(str_replace("##site", $tl, $snippet));
				$snippet = $this->bbcodeRemoval($snippet);
				if(strlen($snippet) > 248) $snippet = substr($snippet, 0, 245) . "...";
				$snippet = htmlspecialchars($snippet, ENT_QUOTES, "UTF-8");
				
			}
			$total = 0;
			$quality = 0;
			$value = 0;
			if(isset($xml->total)) $total = intval($xml->total);
			if(isset($xml->quality)) $quality = intval($xml->quality);
			if(isset($xml->value)) $value = intval($xml->value);
			
			$anchor = $this->getAnchorTag($site_domain, $site_id, $site_url, $trade_id);
			echo "<div class=\"row\">";
			echo "<div class=\"col-12 col-m-12 card textwidget\">";
			$title = htmlspecialchars($title, ENT_QUOTES, "UTF-8");
			$thumb_url = $this->hostname1 . $thumb_url;
			if($site_id == 0)
				echo "<h2>" . $anchor . $title . "</a></h2>";				
			else
				echo "<h2><a href=\"/posts/$pagename.html\">$title</a></h2>";
			echo "<div class=\"col-3 col-m-4 prev-col\">";
			echo $anchor;
			echo "<img src=\"$thumb_url\" alt=\"$title\" title=\"\" />";
			echo "</a>";
			echo "</div>\n";
			echo "<div class=\"col-9 col-m-8 prev-col\">";
			echo "<p>$snippet <a href=\"/posts/$pagename.html\">[Read the full review]</a></p>";
			$this->writeRatingStars($total, $quality, $value);
			echo "<p class=\"bold\">" . $anchor . "Visit $title</a></p>";
			echo "</div>\n";
			echo "</div></div>\n";
		}
	}

	protected function writeRatingstars($total, $quality, $value)
	{
		echo "<div class=\"rating-stars\">";
		$this->writeRatingStar("Total Score", $total);
		$this->writeRatingStar("Quality", $quality);
		$this->writeRatingStar("Value", $value);
		echo "</div>\n";
	}

	private function writeRatingStar($legend, $value)
	{
		echo "<p class=\"bold\">$legend</p>";
		if($value == 0)
		{
			$n = 0;
			$value = "N/A";
		}
		else
		{
			$n = floor($value / 10) + 1;
			$value .= "%";
		}
		echo "<img src=\"" . $this->hostname1 . "/imgs/ratingw$n.gif\" width=\"80\" height=\"16\" alt=\"$legend $value\" />";
		echo " $value";
	}

	protected function writeCategoryThumbs()
	{
		if($this->getNumOfGalleries())
		{
			$this->writeThumbsRows();
		}
		else
		{
			echo "<br /><br />\n<p>There's nothing to see here yet.</p>\n<br /><br />\n";
		}
	}	
}