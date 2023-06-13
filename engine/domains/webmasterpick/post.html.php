<?php

	$this->getPost();
	if($this->error) return;
	
	ob_start();

	$this->getInvisiblePostTagnames();
	$description = $this->post_vars->description;
	$orig_thumb = $this->post_vars->orig_thumb;
	$site_id = $this->post_vars->site_id;
	$trade_id = $this->post_vars->trade_id;
	$site_url = $this->post_vars->site_url;
	$display_state = $this->post_vars->display_state;
	$site_domain = $this->post_vars->site_domain;
	$title = htmlspecialchars($this->post_vars->title, ENT_QUOTES, "UTF-8");
	$site_name = htmlspecialchars($this->post_vars->site_name, ENT_QUOTES, "UTF-8");
	$meta = "";

	$xml=simplexml_load_string($description) or die("Error: Cannot create object");
	if(isset($xml->snippet))
	{
		$meta = $xml->snippet . " Read more at PornLinksWorld.com";
	}

	$robots = ($display_state == "display") ? false : true;
	$this->writeHeader($title . " Review - PornLinksWorld.com", $meta, "", $robots);
	$this->writeTitle();
	echo "<main>\n";
	ob_flush();
	flush();

	if($site_id == 0) $site_name = $title;

	$cat = $this->tag_names[0]["title"];
	$cat_url = $this->tag_names[0]["pagename"];
	echo "<p class=\"breadcrumb\"><a href=\"/\">Home</a> - <a href=\"$cat_url\">$cat Porn</a> - $site_name</p>\n";

	$anchor = $this->getAnchorTag($site_domain, $site_id, $site_url, $trade_id);
	$total = 0;
	$quality = 0;
	$value = 0;
	if(isset($xml->total)) $total = intval($xml->total);
	if(isset($xml->quality)) $quality = intval($xml->quality);
	if(isset($xml->value)) $value = intval($xml->value);
			
	echo "<div class=\"row\">\n";
	echo "<div class=\"col-12 col-m-12 card headwidget\">";
	echo "<h1>" . $anchor . $site_name . " Review</a></h1>\n";
	echo "<div class=\"col-12 col-m-12 post-col\">";
	echo $anchor;
	echo "<img src=\"$orig_thumb\" alt=\"$title\" title=\"\" />";
	echo "</a>";
	$this->writeRatingStars($total, $quality, $value);
	$description = nl2br(htmlentities($xml->fulltext, ENT_QUOTES, "UTF-8"));
	if(strpos($description, "[") !== false) $description = $this->bbcode($description);
	echo "<p>$description</p>\n";
	echo "<p class=\"post-cta\">" . $anchor . "Click here to visit $site_name</a></p>\n";
	echo "</div>\n";
	echo "</div>";
	echo "</div>\n";
	$this->page_vars->index_max_galleries = 4;
	$this->getSimilarPosts();
	if($this->getNumofGalleries())
	{
		echo "<div class=\"row\">";
		echo "<div class=\"col-12 col-m-12 card textwidget\">";
		echo "<h3>Similar sites:</h3>\n";
		while($row = $this->getNextGalleryRow())
		{
			extract($row);
			echo "<div class=\"col-3 col-m-6 prev-col\">";
			echo "<a href=\"/posts/$pagename.html\">";
			echo "<img src=\"$thumb_url\" alt=\"$title\" title=\"\" />";
			echo "</a>";
			echo "<p class=\"thumb\">$title</p>\n";
			echo "</div>\n";
		}
		echo "</div></div>\n";
	}
	
	echo "</main>\n";
	$this->writeFooter();
	echo "</body>\n</html>";

	ob_end_flush();
?>
