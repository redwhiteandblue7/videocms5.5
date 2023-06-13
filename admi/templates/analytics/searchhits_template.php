<?php
    if($this->num_of_rows) {
?>
<table>
<tr>
<th>#</th><th>Landing Page</th><th>Hits</th><th>Bounces</th><th>Bounce Rate</th><th>View Hits</th></tr>
<?php
        $rownum = 1;
        $total_hits = 0;
        $total_bounces = 0;
		foreach($this->results as $row) {
			extract($row);

			$cellclass = (($rownum / 2) == round($rownum / 2)) ? "admin1" : "admin1x";
            echo "<tr class=\"$cellclass\"><td>$rownum.</td><td>$pagename</td><td>$page_hits</td><td>$bounces</td><td>$bounce_rate</td>";
            echo "<td><a href=\"#\" onclick=\"modalWindowInit('a=ShowPageloads&type=spages&id=$page_id');return false;\">[View]</a></td>";
            echo "</tr>\n";
            $total_hits += $page_hits;
            $total_bounces += $bounces;
            $rownum++;
        }
        $pc = (floor(($total_bounces * 1000) / $total_hits)) / 10;
?>
<tr class="<?=$cellclass;?>"><td>-</td><td>Total</td><td><?=$total_hits;?></td><td><?=$total_bounces;?></td><td><?=$pc;?></td><td>-</td></tr>
</table>
<?php
    } else {
        echo "<p>No visitors came from any search engines, sorry :(</p>";
    }
?>