<?php
	ob_start("ob_gzhandler");
	$this->page_vars->index_max_galleries = 999;
	$this->s_target = " target=\"_blank\"";
	$this->getInvisiblePageTagnames();
	$this->getPageTaggedPostRows("priority desc, RAND()");
	$robots = ($this->getNumOfGalleries() < 2);
	$this->getPageDescription();
	$data = $this->getPageData();
	extract($data);
	$this->canonical_url = $this->canonical_base;
	$this->writeHeader($title, $meta, "", $robots, $keywords);
	$this->writeTitle();
	echo "<main>\n";
	ob_flush();
	flush();

	$cat = $this->tag_names[0]["title"];
	echo "<p class=\"breadcrumb\"><a href=\"/\">Home</a> - $cat Porn</p>";

	echo "<div class=\"row\">\n";
	echo "<div class=\"col-12 col-m-12 card headwidget\">";
	echo "<h1>$heading</h1>\n";
	echo "</div>";
	echo "</div>\n";

	$this->writeCategoryThumbs();

	if($content)
	{
		echo "<div class=\"row\">";
		echo "<div class=\"col-12 col-m-12 card textwidget\">";
		echo "<h3>Welcome to PornLinksWorld!</h3>\n";
		$this->writeBlurb($content);
		echo "</div>";
		echo "</div>\n";
	}
	echo "</main>\n";
	$this->writeFooter();
	echo "</body>\n</html>";

	ob_end_flush();
?>
