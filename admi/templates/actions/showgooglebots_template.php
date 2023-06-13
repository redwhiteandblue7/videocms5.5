</nav></header>
<?php
    if($this->num_of_rows) {
?>
<section class="minidata">
<table><tr><th>#</th><th>Page<br /><a href="?a=ShowGooglebots&s=page">[Sort]</a></th><th>Video ID</th><th>Views</th><th>Times visited<br /><a href="?a=ShowGooglebots&s=count">[Sort]</a></th></tr>
<?php
        $rownum = 1;
        foreach($this->results as $row) {
            $cellclass = (($rownum / 2) == round($rownum / 2)) ? "admin1" : "admin1x";
            extract($row);
            echo "<tr  class=\"$cellclass\"><td>$rownum</td><td>$pagename</td><td>$gallery_id</td><td>$views</td><td>$co</td></tr>\n";
            $rownum++;
        }
?>
</table>
<?php
    } else {
        echo "<p>There is nothing to display. Try updating the table first.</p>";
    }
?>
</section>