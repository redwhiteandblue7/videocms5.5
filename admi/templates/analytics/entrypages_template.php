<?php
    if($this->num_of_rows) {
?>
<table>
<tr>
<th>#</th><th>Page</th><th>Hits</th><th>Bounces</th><th>Bounce rate</th><th>View hits</th></tr>
<?php
        $i = 1;
        $total_hits = 0;
        $total_bounces = 0;
		foreach($this->results as $row) {
            extract($row);
			$cellclass = (($i / 2) == round($i / 2)) ? "admin1" : "admin1x";
            echo "<tr class=\"$cellclass\">";
            echo "<td>$i.</td><td>$pagename</td><td>$tot</td><td>$co</td><td>$pc</td>";
            echo "<td><a href=\"#\" onclick=\"modalWindowInit('a=ShowPageloads&type=epages&id=$pagename_id');return false;\">[View]</a></td>";
            echo "</tr>\n";
            $i++;
            $total_hits += $tot;
            $total_bounces += $co;
        }
        $tpc = (floor(($total_bounces * 1000) / $total_hits)) / 10;
?>
<tr class="<?=$cellclass;?>"><td>-</td><td>Total</td><td><?=$total_hits;?></td><td><?=$total_bounces;?></td><td><?=$tpc;?></td><td>-</td></tr>
</table>
<?php
    } else {
        echo "<p>There are no results yet</p>";
    }
?>