<?php
	ob_start("ob_gzhandler");
	$this->writeHeader("410 Gone", "", "");
	$this->writeTitle();
	echo "<main>\n";
	echo "<div class=\"row\">\n";
	echo "<div class=\"col-12 col-m-12 card textwidget\">";
	echo "<h1>410 Page removed</h1>\n";
	echo "<p class=\"left maintext\">That page is no longer available.</p>";
	echo "<p class=\"left maintext\"><a href=\"/\">Click here</a> to return to the home page.</p>";
	echo "</div>";
	echo "</div>\n";

	echo "</main>\n";
	$this->writeFooter();
	echo "</body>\n</html>";

	ob_end_flush();
?>
