<?php
	ob_start("ob_gzhandler");
	$this->getPageDescription();
	$data = $this->getPageData();
	extract($data);
	$this->canonical_url = $this->canonical_base;
	$this->writeHeader($title, $meta, "", false, $keywords);
	$this->writeTitle();
	echo "<main>\n";
	ob_flush();
	flush();
	$this->getThumbList();
	
	while($row = $this->getNextGalleryRow())
	{
		$cat = $row["cat_title"];
		$this->cat_rows[$cat][] = $row;
	}

//order for columns:
//
//	British, German, Japanese, Thai
//	Czech, Russian, Dutch, Hungarian
//	Indian, Filipino, Latina, Canadian
//	French, Portuguese, African
//	
	echo "<div class=\"row container\">\n";
	echo "<div class=\"desk-col col-3\">";
	$this->writeThumbsColumn("British");
	$this->writeThumbsColumn("Czech");
	$this->writeThumbsColumn("Indian");
	$this->writeThumbsColumn("French");
	echo "</div>\n";	//end of col
	echo "<div class=\"desk-col col-3\">";
	$this->writeThumbsColumn("German");
	$this->writeThumbsColumn("Russian");
	$this->writeThumbsColumn("Filipino");
	$this->writeThumbsColumn("Portuguese");
	echo "</div>\n";	//end of col
	echo "<div class=\"desk-col col-3\">";
	$this->writeThumbsColumn("Japanese");
	$this->writeThumbsColumn("Dutch");
	$this->writeThumbsColumn("Latina");
	$this->writeThumbsColumn("African");
	echo "</div>\n";	//end of col
	echo "<div class=\"desk-col col-3\">";
	$this->writeThumbsColumn("Thai");
	$this->writeThumbsColumn("Hungarian");
	$this->writeThumbsColumn("Canadian");
	echo "</div>\n";	//end of col

	echo "<div class=\"tablet-col col-m-6\">";
	$this->writeThumbsColumn("British");
	$this->writeThumbsColumn("Japanese");
	$this->writeThumbsColumn("Czech");
	$this->writeThumbsColumn("Dutch");
	$this->writeThumbsColumn("Indian");
	$this->writeThumbsColumn("Latina");
	$this->writeThumbsColumn("French");
	$this->writeThumbsColumn("African");
	echo "</div>\n";	//end of col
	echo "<div class=\"tablet-col col-m-6\">";
	$this->writeThumbsColumn("German");
	$this->writeThumbsColumn("Thai");
	$this->writeThumbsColumn("Russian");
	$this->writeThumbsColumn("Hungarian");
	$this->writeThumbsColumn("Filipino");
	$this->writeThumbsColumn("Canadian");
	$this->writeThumbsColumn("Portuguese");
	echo "</div>\n";	//end of col
	
	echo "<div class=\"mobile-col col-12\">";
	$this->writeThumbsColumn("British");
	$this->writeThumbsColumn("German");
	$this->writeThumbsColumn("Japanese");
	$this->writeThumbsColumn("Thai");
	$this->writeThumbsColumn("Czech");
	$this->writeThumbsColumn("Russian");
	$this->writeThumbsColumn("Dutch");
	$this->writeThumbsColumn("Hungarian");
	$this->writeThumbsColumn("Indian");
	$this->writeThumbsColumn("Filipino");
	$this->writeThumbsColumn("Latina");
	$this->writeThumbsColumn("French");
	$this->writeThumbsColumn("Canadian");
	$this->writeThumbsColumn("Portuguese");
	$this->writeThumbsColumn("African");
	echo "</div>\n";	//end of col
	echo "</div>\n";

	echo "<div class=\"row\">";
	echo "<div class=\"col-8\"></div>";
	echo "<div class=\"col-2 col-m-6\"><p class=\"small-text right\"><span class=\"m-icon\"></span> Mobile Friendly Site.</p></div>";
	echo "<div class=\"col-2 col-m-6\"><p class=\"small-text right\"><span class=\"r-icon\"></span> Site Review Available.</p></div>";
	echo "</div>\n";
	echo "<div class=\"row\">";
	echo "<div class=\"col-12 col-m-12 card textwidget\">";
	echo "<h3>Welcome to PornLinksWorld!</h3>\n";
	$this->writeBlurb($content);
	echo "</div>";
	echo "</div>\n";
	echo "</main>\n";
	$this->writeFooter();
	echo "</body>\n</html>";

	ob_end_flush();

?>
