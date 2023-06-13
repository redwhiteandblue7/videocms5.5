<?php
    if($this->num_of_rows) {
?>
<table>
<tr>
<th>#</th><th>Referring Page</th><th>Hits</th><th>Total Clicks</th><th>@link Clicks</th><th>CTR%</th></tr>
<?php
        $rownum = 1;
		foreach($this->results as $row) {
			extract($row);

			$cellclass = (($rownum / 2) == round($rownum / 2)) ? "admin1" : "admin1x";
            $ctr = ($page_hits) ? round(($total_clicks * 1000) / $page_hits) / 10 : 0;
            echo "<tr class=\"$cellclass\">";
            echo "<td>$rownum.</td><td>$referstring</td><td>$page_hits</td><td>$total_clicks</td><td>$link</td><td>$ctr</td></tr>\n";
            $rownum++;
        }
?>
</table>
<?php
    } else {
        echo "<p>There is no data to show yet</p>";
    }
?>