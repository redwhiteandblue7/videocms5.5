<section class="minidata">
<?php
    switch($this->action_status) {
        case "deleted":
            echo "<p class=\"success\">Page deleted.</p>";
            break;
        case "not_found":
            echo "<p class=\"error\">Page not found.</p>";
            break;
        case "error":
            echo "<p class=\"error\">There was an error deleting the page.</p>";
            break;
        case "ok":
            echo "<p class=\"success\">Page saved.</p>";
            break;
        default:
            echo "<p>All Pages</p>";
    }
?>
<?php
    if($this->num_of_rows) {
?>
<table>
<tr><th>Page name</th><th>Tags</th><th>Description exists</th><th>&nbsp;</th></tr>
<?php
        $rownum = 1;
        while($row = $page->next()) {
            $cellclass = (($rownum / 2) == round($rownum / 2)) ? "admin1" : "admin1x";
?>
<tr class="<?=$cellclass;?>"><td><?=$row->page_name;?> <a href="?a=PageEdit&id=<?=$row->id;?>">[Edit]</a></td><td>
<?php
            echo "<a href=\"?a=TagAdd&type=page&id=$row->page_id\">[Add] </a>";
            $tags = $page->tags();
            foreach($tags as $tag) {
                echo "<nobr>$tag->title<a href=\"?a=TagDelete&type=page&id=$row->id\">[Del]</a>, ";
            }
            $rownum++;
?>
</td>
<td><?=($row->description) ? "Yes" : "No";?></td>
<td><a href="?a=PageDelete&id=<?=$row->id;?>" onclick="return confirm('Are you sure? This cannot be undone. If this is a live page you should make it redirect instead');">[Delete Page]</a></td>
</tr>
<?php
        }
        echo "</table>\n";
    } else {
        echo "<p>There is nothing to display, nada, zilch, nought.</p>";
    }
?>
<br /><br />
</section>