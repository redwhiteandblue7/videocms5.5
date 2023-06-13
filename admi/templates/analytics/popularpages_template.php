<?php
    if($this->num_of_rows) {
?>
<table >
<tr>
<th>#</th><th>Page</th><th>Visits</th><th>View Hits</th></tr>
<?php
        $i = 1;
		foreach($this->results as $row) {
			extract($row);
			$cellclass = (($i / 2) == round($i / 2)) ? "admin1" : "admin1x";
            echo "<tr class=\"$cellclass\"><td>$i.</td><td>$pagename</td><td>$cnt</td><td><a href=\"#\" onclick=\"modalWindowInit('a=ShowPageloads&type=pages&id=$pagename_id');return false;\">[View]</a></td></tr>\n";
            $i++;
        }
?>
</table>
<?php
    } else {
        echo "<p>No pages have been visited yet</p>";
    }
?>