<script>
	const baseURL = '<?=$domain->baseURL();?>';
	const min_image_width = <?=$this->dbo->domain_obj->poster_width;?>;
	const min_image_height = <?=$this->dbo->domain_obj->poster_height;?>;
	const poster_width = <?=$this->dbo->domain_obj->poster_width;?>;
	const poster_height = <?=$this->dbo->domain_obj->poster_height;?>;
	const mbposter_width = <?=$this->dbo->domain_obj->mbposter_width;?>;
	const mbposter_height = <?=$this->dbo->domain_obj->mbposter_height;?>;
	const thumbnail_width = <?=$this->dbo->domain_obj->thumbnail_width;?>;
	const thumbnail_height = <?=$this->dbo->domain_obj->thumbnail_height;?>;
</script>
<script src="/admi/js/tools.js"></script>
<script src="/admi/js/screenshot_edit.js"></script>
<script src="/admi/js/update_post.js"></script>
<p><form name="sitefilter" action="?a=ShowPosts&p=<?=($this->page + 1);?>" method="post">
Filter by sitename <select name="site_filter" onchange="this.form.submit()">
<option value="0">All</option>
<?php
	$sites = $posts->getSites();
	foreach($sites as $site) {
		echo "<option value=\"$site->site_id\"";
		if($this->site_filter == $site->site_id) echo " selected=\"selected\"";
		echo ">$site->site_name</option>\n";
	}
?>
</select>&nbsp;
Filter by sponsor <select name="sponsor_filter" onchange="this.form.submit()">
<option value="0">All</option>
<?php
	$sponsors = $posts->getSponsors();
	foreach($sponsors as $sponsor) {
		echo "<option value=\"$sponsor->sponsor_id\"";
		if($this->sponsor_filter == $sponsor->sponsor_id) echo " selected=\"selected\"";
		echo ">$sponsor->sponsor_name</option>\n";
	}
?>
</select>&nbsp;
Filter by tag <select name="tag_filter" onchange="this.form.submit()">
<option value="0"<?=($this->tag_filter == 0) ? " selected=\"selected\"" : "";?>>All</option>
<option value="None"<?=($this->tag_filter === "None") ? " selected=\"selected\"" : "";?>>None</option>
<?php
	$tags = $posts->getTags();
	foreach($tags as $tag) {
		echo "<option value=\"$tag->tag_id\"";
		if($this->tag_filter == $tag->tag_id) echo " selected=\"selected\"";
		echo ">$tag->tag_name</option>\n";
	}
?>
</select>
</form>
<form name="tagselect" action="?a=ShowPosts&p=<?=($this->page + 1);?>" method="POST">
Auto tag with <select name="auto_tag" onchange="this.form.submit()">
<option value="0" <?=($this->autotag == 0) ? " selected=\"selected\"" : "";?>>-None-</option>
<?php
	foreach($tags as $tag) {
		echo "<option value=\"$tag->tag_id\"";
		if($this->autotag == $tag->tag_id) echo " selected=\"selected\"";
		echo ">$tag->tag_name</option>\n";
	}
?>
</select></form>
</p>
</nav></header>
<section class="tabledata">
<?php
    if($num_of_rows) {
?>
&nbsp;Page:&nbsp;<?=$this->pagination();?><br />
<table id="posts">
<tr>
<th>#.</th>
<th>Post&nbsp;ID<br /><a href="?s=sortByID&a=show_posts&subpage=posttools">[Sort&nbsp;Up]</a><br /><a href="?s=sortByIDR&a=show_posts&subpage=posttools">[Sort&nbsp;Down]</a></th>
<th>Rank<br /><a href="?s=sortByRank&a=show_posts&subpage=posttools">[Sort]</a><br />Priority<br /><a href="?s=sortByPriority&a=show_posts&subpage=posttools">[Sort]</a></th>
<th>Thumbnail</th>
<th>Poster</th>
<th>Mbl Poster</th>
<th>Site<br />Title <a href="?s=sortByTitle&a=show_posts&subpage=posttools">[Sort]</a><br />Alt title<br />Page name<br />Description</th>
<th>Site URL</th>
<th>Trade ID</th>
<th>Tags</th>
<th>Related Tags</th>
<th>Video URL<br />Duration</th>
<th>Daily Views<br /><a href="?s=sortByDaily&a=show_posts&subpage=posttools">[Sort]</a></th>
<th>Monthly Views<br /><a href="?s=sortByMonthly&a=show_posts&subpage=posttools">[Sort]</a></th>
<th>Time Updated <a href="?s=sortByUpdated&a=show_posts&subpage=posttools">[Sort]</a>
<br />Time Visible <a href="?s=sortByVisible&a=show_posts&subpage=posttools">[Sort]</a>
<br />Time Added <a href="?s=sortByAdded&a=show_posts&subpage=posttools">[Sort]</a></th>
</tr>
<?php
        while($row = $posts->next()) {
			$id = $row->id;
			$title = $row->title;
			$alt_title = $row->alt_title;
			$site_name = $row->site_name;
			$site_url = $row->site_url;
			$thumb_url = $row->thumb_url;
			$orig_thumb = $row->orig_thumb;
			$opti_thumb = $row->opti_thumb;
			$icon_url = $row->icon_url;
			$video_url = $row->video_url;
			$duration = $row->duration;
			$pagename = $row->pagename;
			$description = $row->description;
			$post_id = $row->post_id;
			$site_id = $row->site_id;
			$post_type = $row->post_type;
			$domainstring = $row->domainstring;
			$ranking = $row->ranking;
			$priority = $row->priority;
			$link_type = $row->link_type;
			$time_added = $row->time_added;
			$time_visible = $row->time_visible;
			$time_updated = $row->time_updated;
			$daily_clicks = $row->daily_clicks;
			$monthly_clicks = $row->monthly_clicks;
			
//			$title = htmlspecialchars($title, ENT_QUOTES);
			if($alt_title) $alt_title = htmlspecialchars($alt_title, ENT_QUOTES);
			if($site_name) $site_name = htmlspecialchars($site_name, ENT_QUOTES);
			$tcolour = ($time_visible > time()) ? "#808080" : "white";
			$time_visible = gmdate("H:i:s d-M-y", $time_visible);
			$time_added = gmdate("H:i:s d-M-y", $time_added);
			$time_updated = ($time_updated) ? gmdate("H:i:s d-M-y", $time_updated) : "Never";
			if(!($rank ?? 0)) $rank = 0;
			$has_reviews = false;
			if(($description) && strpos($description, "reviewurl") !== false) $has_reviews = true;
//			if(strlen($description) > 256) $description = substr($description, 0, 252) . "...";
			if($thumb_url == "")
				$thumb_url = "[None]";
			else
				$thumb_url = "<img src=\"" . $domain->fullURL($thumb_url) . "\" width=\"150px\" loading=\"lazy\" />";
			if($video_url == "")
				$video_url = "[None]";
			else
				$video_url = "<a href=\"$video_url\" target=\"_blank\">$video_url</a>";
			if($orig_thumb == "")
				$orig_thumb = "[None]";
			else
				$orig_thumb = "<img src=\"" . $domain->fullURL($orig_thumb) . "\" width=\"200px\" loading=\"lazy\" />";
			if($opti_thumb == "")
				$opti_thumb = "[None]";
			else
				$opti_thumb = "<img src=\"" . $domain->fullURL($opti_thumb) . "\" width=\"150px\" loading=\"lazy\" />";
			$p = $this->page;
			$site_link = "";
			if($site_url) {
				if($link_type == "none") {
					$site_link = $site_url;
				} else {
					$site_link = "<a href=\"$site_url\" target=\"_blank\">$site_url</a>";
				}
				$site_link .= "<br />";
				if($link_type == "nofollow") $site_link .= "[no follow]";
			}
			$cellclass = (($row->rownum / 2) == round($row->rownum / 2)) ? "admin1" : "admin1x";
?>
<tr class="<?=$cellclass;?>">
<td><?=($row->rownum+1);?>.<br /><a href="?a=PostEdit&id=<?=$post_id;?>">[Edit]</a></td>
<td><?=$post_id;?></td>
<td><?=$ranking;?><br /><?=$priority;?></td>
<td><?=$thumb_url;?></td>
<td><?=$orig_thumb;?></td>
<td><?=$opti_thumb;?></td>
<td><?=$title;?><br /><br />Site name: <?=$site_name;?><br />Alt title: <?=$alt_title;?><br />Page: <?=$post_type;?>/<?=$pagename;?><br />
<?=($icon_url) ? " <img src=\"" . $this->base_url . $icon_url . "\" width=\"16\" height=\"16\" alt=\"\" />" : (($site_url) ? "<a href=\"#\" onclick=\"fetchIconInit($post_id, '$site_url', '" . addslashes($title) . "');return false;\">[Fetch Icon]</a>" : "");?></td>
<td><?php
			if($site_url) {
?>
<?=$site_link;?><br /><?=($site_url) ? "<a href=\"#\" onclick=\"screenshotEditorInit('$site_url', $post_id);return false;\">[Get Screenshot]</a>" : "";?><br /><br />
<a href="#" onclick="reviewsScraperInit('<?=$pagename;?>', <?=$post_id;?>);return false;"><?=($has_reviews) ? "[Update Review Data]" : "[Get Review Data]";?></a>
<?php
			}
?>
</td>
<td><?=$domainstring;?><?=(($domainstring == "") && ($site_id == 0) && ($site_url != "")) ? "<br /><a href=\"?a=PostTrade&id=$post_id\">[Add Trade]</a>" : "";?></td>
<td>
<?php
			$tags = $posts->tags();
			foreach($tags as $tag) {
				echo "<nobr>{$tag->tag_name}<a href=\"?a=TagDelete&type=post&id={$tag->tag_id}\">[Del]</a><br /></nobr>";
			}
			echo "<a href=\"?a=TagAdd&type=post&id=$post_id\">[Add]</a></td>";
			echo "<td>";
			$tags = $posts->relatedTags();
			foreach($tags as $tag) {
				echo "<nobr>{$tag->tag_name}<a href=\"?a=TagDelete&&type=relatedpost&id=$tag->tag_id\">[Del]</a><br /></nobr>";
			}
			echo "<a href=\"?a=TagAdd&type=relatedpost&id=$post_id\">[Add]</a></td>";
?>
<td><?=$video_url;?><br /><?=$duration;?></td>
<td><?=$daily_clicks;?></td>
<td><?=$monthly_clicks;?></td>
<td><?=$time_updated;?><br /><font color="<?=$tcolour;?>"><?=$time_visible;?></font></nobr><br /><nobr><?=$time_added;?></nobr></td>
</tr>
<?php
        }
?>
</table>
<br />
&nbsp;Page:&nbsp;<?=$this->pagination();?><br />
<?php
    } else {
        echo "<p>There is nothing to display, nada, zilch, nought.</p>";
    }
?>
</section>
<?php
	include_once("modal.php");
?>
