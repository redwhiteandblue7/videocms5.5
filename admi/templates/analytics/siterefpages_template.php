<?php
    if($this->num_of_rows) {
?>
<table>
<tr>
<th>#</th><th>Referring Page</th><th>Site</th><th>Clicks</th></tr>
<?php
        $rownum = 1;
		foreach($this->results as $row) {
			extract($row);

			$cellclass = (($rownum / 2) == round($rownum / 2)) ? "admin1" : "admin1x";
            echo "<tr class=\"$cellclass\">";
            echo "<td>$rownum.</td><td>$referstring</td><td>$site_name</td><td>$site_hits</td></tr>\n";
            $rownum++;
        }
?>
</table>
<?php
    } else {
        echo "<p>Nobody has clicked out to any sites yet :(</p>";
    }
?>