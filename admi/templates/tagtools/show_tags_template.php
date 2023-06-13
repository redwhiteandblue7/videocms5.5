<p><form name="filter" action="?a=show_tags&subpage=tagtools" method="POST">
Order by <select name="filtermenu" onchange="this.form.submit()">
<option value="title"<?=($this->filter == "title") ? "selected=\"selected\"" : "";?>>Alphabetic Order</option>
<option value="tag_id"<?=($this->filter == "tag_id") ? "selected=\"selected\"" : "";?>>ID Order</option>
</select>
</form></p>
</nav>
</header>
<section class="minidata">
<?php
    if($this->num_of_rows)
    {
?>
<table>
<tr>
<th>#.</th>
<th>ID</th>
<th>Name</th>
<th>Landing page</th>
<th>Visibility</th>
</tr>
<?php
        $rownum = 0;
        while($row = $this->dbo->getNextTableRow())
        {
            extract($row);
            $invis = ($invisible == "true") ? "Invisible" : "Visible";
            $cellclass = (($rownum / 2) == round($rownum / 2)) ? "admin1" : "admin1x";
?>
<tr class="<?=$cellclass;?>">
<td><?=($rownum+1);?>.</td>
<td><?=$tag_id;?>&nbsp;<a href="?subpage=tagtools&a=edit_tag&did=<?=$tag_id;?>">[Edit]</a></td>
<td><?=$title;?></td><td><?=$pagename;?></td>
<td><?=$invis;?></td>
</tr>
<?php
            $rownum++;
        }
?>
</table>
<br /><br />
<?php
    }
    else
    {
        echo "<p>There are no tags yet.</p>";
    }
?>