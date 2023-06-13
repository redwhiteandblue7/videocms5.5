<?php
    if($this->num_of_rows) {
?>
<table>
<tr>
<th>#</th><th>Domain</th><th>Hits</th><th>Returning</th><th>Productivity</th><th>Time on site</th><th>Bounce rate</th><th>Search?</th></tr>
<?php
        $total_hits = 0;
        $total_bounces = 0;
        $total_clicks = 0;
        $total_rv = 0;
        $total_tos = 0;
		foreach($this->results as $row) {
			extract($row);

			$cellclass = (($rownum / 2) == round($rownum / 2)) ? "admin1" : "admin1x";
            echo "<tr class=\"$cellclass\">";
            echo "<td>$rownum</td><td>$domainstring</td>";
            echo "<td>$tot <a href=\"#\" onclick=\"modalWindowInit('a=showpageloads&type=domain&id=$dom_id');return false;\">[View]</a><a href=\"#\" onclick=\"modalWindowInit('a=showpageloads&type=alldomain&id=$dom_id');return false;\">[All]</a></td>";
            echo "<td>$rv</td><td>$prod</td><td>$t</td><td>$pc</td><td>";
            if($srch) {
                echo "Yes <a href=\"#\" onclick=\"changeSearch($dom_id, 'delsrch'); return false;\">";
            } else {
                echo "No <a href=\"#\" onclick=\"changeSearch($dom_id, 'addsrch'); return false;\">";
            }
            echo "[Change]</a>";
            echo "</td></tr>\n";
            $total_hits += $tot;
            $total_bounces += $co;
            $total_clicks += $ct;
            $total_rv += $rv;
            $total_tos += $tos;
        }
        $pc = (floor(($total_bounces * 1000) / $total_hits)) / 10;
        $prod = (floor(($total_clicks * 1000) / $total_hits)) / 10;
        $t = floor($total_tos / $total_hits);
?>
<tr class="<?=$cellclass;?>"><td>-</td><td>Total</td><td><?=$total_hits;?></td><td><?=$total_rv;?></td><td><?=$prod;?></td><td><?=$t;?></td><td><?=$pc;?></td><td>-</td></tr>
</table>
<?php
    } else {
        echo "<p>There are no results yet</p>";
    }
?>