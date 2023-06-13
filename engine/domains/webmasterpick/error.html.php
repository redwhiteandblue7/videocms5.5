<?php
	ob_start("ob_gzhandler");
	$this->writeHeader("404 Page not found", "", "");
	$this->writeTitle();
	echo "<main>\n";
	echo "<div class=\"row\">\n";
	echo "<div class=\"col-12 col-m-12 card textwidget\">";
	echo "<h1>404 Not Found</h1>\n";
	echo "<p class=\"left maintext\">That page was not found.</p>";
	echo "<p class=\"left maintext\"><a href=\"/\">Click here</a> to return to the home page.</p>";
	echo "</div>";
	echo "</div>\n";

	echo "</main>\n";
	$this->writeFooter();
	echo "</body>\n</html>";

	ob_end_flush();
?>
	