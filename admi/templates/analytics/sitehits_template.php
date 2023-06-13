<?php
    if($this->num_of_rows) {
?>
<table>
<tr>
<th>#</th><th>Site</th><th>Hits</th><th>View Hits</th></tr>
<?php
        $rownum = 1;
		foreach($this->results as $row) {
			extract($row);

			$cellclass = (($rownum / 2) == round($rownum / 2)) ? "admin1" : "admin1x";
            echo "<tr class=\"$cellclass\">";
            echo "<td>$rownum.</td><td>$site_name</td><td>$site_hits</td><td>[View]</td></tr>\n";
            $rownum++;
        }
?>
</table>
<?php
    } else {
        echo "<p>There is no data to show yet</p>";
    }
?>