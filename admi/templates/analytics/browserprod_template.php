<?php
    if($this->num_of_rows) {
?>
<table>
<tr><th>#</th><th>Platform</th><th>Hits</th><th>CT Productivity</th><th>Time On Site</th><th>Bounce rate</th></tr>
<?php
        $rownum = 1;
        $total_hits = 0;
        $total_bounces = 0;
        $total_clicks = 0;
        $total_tos = 0;
		foreach($this->results as $row) {
			extract($row);
            $pc = (floor(($bounce_count * 1000) / $hit_count)) / 10;
            $prod = (floor(($click_count * 1000) / $hit_count)) / 10;
            $t = floor($time_on_site / $hit_count);

			$cellclass = (($rownum / 2) == round($rownum / 2)) ? "admin1" : "admin1x";
            echo "<tr class=\"$cellclass\"><td>$rownum.</td><td>$browser</td><td>$hit_count</td><td>$prod</td><td>$t</td><td>$pc</td></tr>\n";
            $rownum++;
            if($browser != "Bot") {
                $total_hits += $hit_count;
                $total_bounces += $bounce_count;
                $total_clicks += $click_count;
                $total_tos += $time_on_site;
            }
        }
        $pc = (floor(($total_bounces * 1000) / $total_hits)) / 10;
        $prod = (floor(($total_clicks * 1000) / $total_hits)) / 10;
        $t = floor($total_tos / $total_hits);
        echo "<tr class=\"$cellclass\"><td>-</td><td>Total browsers excl. bots</td><td>$total_hits</td><td>$prod</td><td>$t</td><td>$pc</td></tr>\n";
?>
</table>
<?php
    } else {
        echo "<p>There is no data yet.</p>";
    }
?>