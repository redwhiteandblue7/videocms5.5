<p><form name="filter" action="?a=ShowSites&p=<?=($this->page + 1);?>" method="POST">
Filter by sponsor <select name="sponsor_filter" onchange="this.form.submit()">
<option value="0">All</option>
<?php
    $sponsors = $sites->getSponsors();
    foreach($sponsors as $sponsor) {
        echo "<option value=\"{$sponsor->sponsor_id}\"";
        if($this->sponsor_filter == $sponsor->sponsor_id) echo " selected=\"selected\"";
        echo ">{$sponsor->sponsor_name}</option>\n";
    }
?>
</select>&nbsp;</form>

Filter by tag <select name="tagfilter" onchange="this.form.submit()">
<option value="0">All</option>
<?php
    $tags = $sites->getTags();
    foreach($tags as $tag) {
        echo "<option value=\"{$tag->tag_id}\"";
        if($this->tag_filter == $tag->tag_id) echo " selected=\"selected\"";
        echo ">{$tag->tag_name}</option>\n";
    }
?>
</select>
</form>
<form name="tagselect" action="?a=ShowSites&p=<?=($this->page + 1);?>" method="POST">
Auto tag with <select name="autotag" onchange="this.form.submit()">
<option value="0">-None-</option>
<?php
    foreach($tags as $tag) {
        echo "<option value=\"{$tag->tag_id}\"";
        if($this->autotag == $tag->tag_id) echo " selected=\"selected\"";
        echo ">{$tag->tag_name}</option>\n";
    }
?>
</select></form></p>
</nav></header>
<section class="tabledata">
<?php
    switch($this->action_status) {
        case "deleted":
            echo "<p class=\"success\">Site deleted.</p>";
            break;
        case "not_found":
            echo "<p class=\"error\">Site not found.</p>";
            break;
        case "error":
            echo "<p class=\"error\">There was an error doing whatever it was you just did.</p>";
            break;
        case "ok":
            echo "<p class=\"success\">Site saved.</p>";
            break;
        case "sites_imported":
            echo "<p class=\"success\">Site list imported.</p>";
            break;
        default:
            echo "<p>All Sites</p>";
    }

    if($this->num_of_rows) {
?>
&nbsp;Page:&nbsp;<?=$this->pagination();?><br />
<table>
<tr><th>#.</th><th>Site ID<br /><a href="?s=sortBySite&a=ShowSites">[Sort]</a></th>
<th>Site Name<<a href="?s=sortByName&a=ShowSites">[Sort]</a><br />Sponsor<a href="?s=sortBySponsor&a=ShowSites">[Sort]</a></th>
<th>Link</th>
<th>Preference<br /><a href="?s=sortByPriority&a=ShowSites">[Sort]</a></th>
<th>Tags</th>
<th>Domain / Slug</th></tr>
<?php
        while($row = $sites->next()) {
            $site_name = htmlspecialchars($row->site_name);
            $sponsor_name = htmlspecialchars($row->sponsor_name);
            $tcolour = ($row->enabled == 'enabled') ? "#e0e0e0" : "#e08080";
            $cellclass = (($row->rownum / 2) == round($row->rownum / 2)) ? "admin1" : "admin1x";
?>
<tr class="<?=$cellclass;?>">
<td><?=($row->rownum+1);?>.</td>
<td><?=$row->site_id;?></td>
<td><a href="?&a=SiteEdit&id=<?=$row->site_id;?>">[Edit]</a>&nbsp;<font color="<?=$tcolour;?>"><?=$site_name;?></font><br /><font color="#808080"><?=$sponsor_name;?></font></td>
<td><a href="<?=$row->site_ref;?>" target="_blank"><?=$row->site_ref;?></a></td>
<td><?=$row->pref;?></td>
<td>
<a href="?a=TagAdd&type=site&id=<?=$row->site_id;?>">[Add]</a>
<?php
            $tags = $sites->tags();
            foreach($tags as $tag) {
                echo "<nobr>{$tag->tag_name}<a href=\"?a=TagDelete&type=site&id={$tag->tag_id}\">[Del]</a>, ";
            }
?>
</td>
<td><font color="<?=$tcolour;?>"><?=$row->site_domain;?></font></td>
</tr>
<?php
        }
?>
</table>
<br /><br />
&nbsp;Page:&nbsp;<?=$this->pagination();?><br />
<?php
    }
    else
    {
        echo "<p>There is nothing to display, nada, zilch, nought.</p>";
    }
?>
</section>