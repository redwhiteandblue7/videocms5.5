</nav></header>
<script src="/admi/js/tools.js"></script>
<section class="edit"><div>
<?php
	switch($this->action_status) {
		case "vars_empty":
			echo "<p>Add / Edit Post:</p>";
			break;
		case "not_found":
			echo "<p class=\"error\">Post not found</p>";
			break;
		case "no_post_type":
			echo "<p class=\"error\">Please select a post type</p>";
			break;
		case "no_title":
			echo "<p class=\"error\">Please type a title</p>";
			break;
		case "ok":
			echo "<p class=\"success\">Post saved</p>";
			break;
		default:
			echo "<p class=\"error\">Unknown error</p>";
			break;
	}

	$thumb_url = $this->post_object->thumb_url ?? "";
	$orig_thumb = $this->post_object->orig_thumb ?? "";
	$opti_thumb = $this->post_object->opti_thumb ?? "";
	$video_url = $this->post_object->video_url ?? "";
	$icon_url = $this->post_object->icon_url ?? "";
	$description = $this->post_object->description ?? "";
	$title = $this->post_object->title ?? "";
	$alt_title = $this->post_object->alt_title ?? "";
	$site_url = $this->post_object->site_url ?? "";
	$pagename = $this->post_object->pagename ?? "";
	$time_visible = $this->post_object->time_visible ?? time();
	$duration = $this->post_object->duration ?? 0;
	$orig_width = $this->post_object->orig_width ?? 0;
	$orig_height = $this->post_object->orig_height ?? 0;
	$priority = $this->post_object->priority ?? 0;
	$trade_id = $this->post_object->trade_id ?? 0;

?>
<form method="post" action="?a=PostEdit" name="postdata">
Site: <?=(isset($this->post_object->site_name)) ? $this->post_object->site_name : "Not defined";?><br />
<select name="site_id">
<option value="0">-None-</option>
<?php
	$sites = $post->getSites();
	foreach($sites as $site) {
		echo "<option value=\"" . $site->site_id . "\"";
		if($site->site_id == $this->post_object->site_id) echo " selected=\"selected\"";
		echo ">" . $site->site_name . "</option>\n";
	}
?>
</select><br /><br />
Video ID:<br />
<input type="text" name="video_id" value="<?=$this->post_object->video_id ?? 0;?>" size="10" maxlength="255" /><br />
Type of post:<br />
<?php
    $post_types = $post->getPostTypes();
	foreach($post_types as $type) {
		echo "<input type=\"radio\" name=\"post_type\" value=\"$type\"";
		if(($this->post_object->post_type ?? "") == $type) echo " checked=\"checked\"";
		echo " />&nbsp;" . ucfirst($type) . "&nbsp;&nbsp;\n";
	}
//	if($description) $description = $this->cleanFromWP($description);
	if($thumb_url) $thumb_url = $domain->fullURL($thumb_url);
	if($orig_thumb) $orig_thumb = $domain->fullURL($orig_thumb);
	if($opti_thumb) $opti_thumb = $domain->fullURL($opti_thumb);
?>
<br /><br />
Video URL:<br />
<input type="text" name="video_url" value="<?=$video_url;?>" size="100" maxlength="255" /><br />
Poster URL:<br />
<input type="text" name="orig_thumb" value="<?=$this->post_object->orig_thumb ?? "";?>" size="100" maxlength="255" /><br />
<?=($orig_thumb ?? "") ? "<img src=\"$orig_thumb\" alt=\"\" width=\"150\" onmousemove=\"showOverImage('poster_overlay', event);\" onmouseout=\"hideOverImage('poster_overlay');\" /><br />" : "";?>
<br />
Mobile Poster URL:<br />
<input type="text" name="opti_thumb" value="<?=$this->post_object->opti_thumb ?? "";?>" size="100" maxlength="255" /><br />
<?=($opti_thumb ?? "") ? "<img src=\"$opti_thumb\" alt=\"\" width=\"150\" onmousemove=\"showOverImage('mposter_overlay', event);\" onmouseout=\"hideOverImage('mposter_overlay');\" /><br />" : "";?>
<br />
Thumbnail URL:<br />
<input type="text" name="thumb_url" value="<?=$this->post_object->thumb_url ?? "";?>" size="100" maxlength="255" /><br />
<?=($thumb_url ?? "") ? "<img src=\"$thumb_url\" alt=\"\" width=\"150\" onmousemove=\"showOverImage('thumb_overlay', event);\" onmouseout=\"hideOverImage('thumb_overlay');\" /><br />" : "";?>
<br />
Icon URL:<br />
<input type="text" name="icon_url" value="<?=$icon_url;?>" size="100" maxlength="255" /><br /><br />
Title:<br />
<input type="text" name="title" value="<?=$title;?>" size="100" maxlength="255" /><br />
Page tag:<br />
<input type="text" name="pagename" value="<?=$pagename;?>" size="100" maxlength="255" /><br /><br />
Description (<span id="desc_cnt">0</span> words):
<?php
	if(!($description))
    {
        echo " [<a href=\"#\" onclick=\"addXML();return false;\">Fill XML template</a>]";
        echo " [<a href=\"#\" onclick=\"addReviewXML();return false;\">Fill XML Review template</a>]";
    }
?>
<br />
<textarea name="description" id="desctext" onkeyup="wordCount(this.value)" rows="20" cols="120"><?=$description;?></textarea><br />

<script>
	var tObj = document.getElementById('desctext');
	var wTxt = tObj.value;
	wordCount(wTxt);
</script>
<input type="hidden" name="time_visible" value="<?=$time_visible;?>" />
<br />Alternative title (e.g. heading):<br />
<input type="text" name="alt_title" value="<?=$alt_title;?>" size="100" maxlength="255" /><br />
Site link:<br />
<input type="text" name="site_url" value="<?=$site_url;?>" size="100" maxlength="127" /><br />
Link type:<br />
<?php
	$link_types = $post->getLinkTypes();
	foreach($link_types as $type) {
		echo "<input type=\"radio\" name=\"link_type\" value=\"$type\"";
		if(($this->post_object->link_type ?? "") == $type) echo " checked=\"checked\"";
		echo " />&nbsp;" . ucfirst($type) . "&nbsp;&nbsp;\n";
	}
?>
<br /><br />
Duration:
<input type="text" name="duration" value="<?=$duration ?? "";?>" size="10" maxlength="5" />
Original width:
<input type="text" name="orig_width" value="<?=$orig_width ?? "";?>" size="10" maxlength="5" />
Original height:
<input type="text" name="orig_height" value="<?=$orig_height ?? "";?>" size="10" maxlength="5" /><br />
<br />Priority:
<input type="text" name="priority" value="<?=$priority ?? "";?>" size="5" maxlength="5" />
 Trade ID:
<input type="text" name="trade_id" value="<?=$trade_id ?? "";?>" size="5" maxlength="5" /><br /><br />
Date visible from:<br />
<?php
	$datearray = getdate($time_visible);
	$day = $datearray["mday"];
	$month = $datearray["mon"];
	$year = $datearray["year"];
	$hour = $datearray["hours"];

	echo "<select name=\"vf_hours\">\n";
	for($i=0; $i<24; $i++)
	{
		echo "<option value=\"$i\"";
		if($i == $hour) echo " selected=\"selected\"";
		echo ">$i:00</option>\n";
	}
	echo "</select>\n";
	echo "<select name=\"vf_date\">\n";
	for($i=1; $i<32; $i++)
	{
		echo "<option value=\"$i\"";
		if($i == $day) echo " selected=\"selected\"";
		echo ">$i</option>\n";
	}
	echo "</select>\n";
	echo "&nbsp;<select name=\"vf_month\">\n";
	for($i=1; $i<13; $i++)
	{
		echo "<option value=\"$i\"";
		if($i == $month) echo " selected=\"selected\"";
		echo ">" . date("F", mktime(0, 0, 0, $i, 1, $year)) . "</option>\n";
	}
	echo "</select>\n";
	echo "&nbsp;<select name=\"vf_year\">\n";
	for($i = $year; $i < $year + 5; $i++)
	{
		echo "<option value=\"$i\"";
		if($i == $year) echo " selected=\"selected\"";
		echo ">" . date("Y", mktime(0, 0, 0, 1, 1, $i)) . "</option>\n";
	}
	echo "</select><br />\n";
?>
Time last updated: <?=($time_updated ?? 0) ? date("l jS F Y", $time_updated) : "Never";?> - update? <input type="checkbox" name="update_time" value="update_time" />
<br /><br />Display state:<br />
<?php
	$types = $post->getDisplayStates();
	foreach($types as $type) {
		echo "<input type=\"radio\" name=\"display_state\" value=\"$type\"";
		if($this->post_object->display_state == $type) echo " checked=\"checked\"";
		echo " />&nbsp;" . ucfirst($type) . "&nbsp;&nbsp;\n";
	}
	echo "<br /><br />\n";

	if(($video_url ?? "") != "") {
		if((substr($video_url, -4) == ".mp4") || (substr($video_url, -4) == ".m4v") || (substr($video_url, -4) == ".mov")) {
			echo "<video poster=\"$orig_thumb\" preload=\"auto\" controls>\n<source src=\"$video_url\" type=\"video/mp4\">\nBrowser not HTML5</video>\n";
		} else {
			echo "<p>Video URL does not contain mp4 video type</p>\n";
		}
	}
?>
<input type="hidden" name="preserve_domain_id" value="<?=$this->domain_id;?>" />
<input type="hidden" name="post_id" value="<?=$this->post_object->post_id;?>" />
<input type="hidden" name="id" value="<?=$this->post_object->id;?>" />
<input type="submit" value="<?=($this->post_object->id) ? "Update" : "Add";?> Post" />
</form>
</div>
<?php
	if($this->post_object->id ?? 0) {
		echo "<br /><br /><p>OR <a href=\"?a=PostDelete&id={$this->post_object->post_id}\" onclick=\"return confirm('Are you sure? This cannot be undone. If this post is live you should set the display status to \'hide\' or \'delete\' instead.');\">[Delete this post]</a></p><br /><br />";
	}
?>
</section>
<div id="thumb_overlay" style="display:none;background-color:#303030;padding:10px;"><img src="<?=$thumb_url ?? "";?>" alt="" /></div>
<div id="poster_overlay" style="display:none;background-color:#303030;padding:10px;"><img src="<?=$orig_thumb ?? "";?>" alt="" /></div>
<div id="mposter_overlay" style="display:none;background-color:#303030;padding:10px;"><img src="<?=$opti_thumb ?? "";?>" alt="" /></div>
