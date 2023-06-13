</nav></header>
<section class="edit"><div>
<?php
    switch($this->action_status) {
        case "ok":
            echo "<p class=\"success\">Channel saved successfully.</p>";
            break;
        case "vars_empty":
            echo "<p>Add / Edit channel:</p>";
            break;
        case "error":
            echo "<p class=\"error\">{$this->error}</p>";
            break;
        case "no_name":
            echo "<p class=\"error\">No channel name specified</p>";
            break;
        case "no_state":
            echo "<p class=\"error\">Please choose a display state</p>";
            break;
        case "not_found":
            echo "<p class=\"error\">Channel not found</p>";
            break;
        case "channel_exists":
            echo "<p class=\"error\">There is already a channel with that name already, sorry</p>";
            break;
        default:
            echo "<p class=\"error\">Unknown error</p>";
            break;
    }
?>
<form method="post" action="?a=ChannelEdit">
Channel name: (max 64 chars)<br />
<input type="text" name="channel_name" value="<?=$this->post_object->channel_name ?? "";?>" size="100" maxlength="64" /><br /><br />
Site: (optional)<br />
<select name="site_id"><option value="0">--None--</option>
<?php
    $sites = $channels->getSites();
    foreach($sites as $site) {
        echo "<option value=\"{$site->site_id}\"";
        if($this->post_object->site_id == $site->site_id) echo " selected=\"selected\"";
        echo ">{$site->site_name}</option>\n";
    }
?>
</select><br /><br />
Link URL: (optional)<br />
<input type="text" name="link_url" value="<?=$this->post_object->link_url ?? "";?>" size="100" maxlength="255" /><br /><br />
Display state:<br />
<?php
    $types = $channels->getDisplayStates();
	foreach($types as $type) {
		echo "<input type=\"radio\" name=\"display_state\" value=\"$type\"";
		if(($this->post_object->display_state ?? "") == $type) echo " checked=\"checked\"";
		echo " />&nbsp;" . ucfirst($type) . "&nbsp;&nbsp;\n";
	}
?>
<br /><br />
Owner:<br />
<select name="user_id">
<?php
    while($row = $users->next()) {
        echo "<option value=\"{$row->user_id}\"";
        if(($this->post_object->user_id ?? 0) == $row->user_id) echo " selected=\"selected\"";
        echo ">{$row->user_name}</option>\n";
    }
?>
</select><br /><br />
<input type="hidden" name="channel_id" value="<?=$this->post_object->channel_id;?>" />
<input type="hidden" name="id" value="<?=$this->post_object->id;?>" />
<input type="submit" value="<?=($this->post_object->id) ? "Update Channel" : "Add Channel";?>" />
</form></div>
<?php
	if($this->id) {
		echo "<br /><br />OR <a href=\"?a=DeleteChannel&id={$this->post_object->channel_id}\">[Delete this channel]</a>";
	}
?>
</section>