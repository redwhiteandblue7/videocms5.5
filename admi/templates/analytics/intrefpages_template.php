<?php
    if($this->num_of_rows) {
?>
<table>
<tr>
<th>#</th><th>Referring Page</th><th>Landing Page</th><th>Hits</th></tr>
<?php
        $rownum = 1;
		foreach($this->results as $row) {
			extract($row);

			$cellclass = (($rownum / 2) == round($rownum / 2)) ? "admin1" : "admin1x";
            echo "<tr class=\"$cellclass\"><td>$rownum.</td><td>$referstring</td><td>$pagename</td><td>$page_hits</td></tr>\n";
            $rownum++;
        }
?>
</table>
<?php
    } else {
        echo "<p>There is no internal page activity data yet, sorry.</p>";
    }
?>