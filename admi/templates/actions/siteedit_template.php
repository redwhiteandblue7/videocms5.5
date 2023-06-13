</nav></header>
<section class="edit"><div>
<?php
    switch($this->action_status) {
        case "vars_empty":
            echo "<p>Add / Edit Site:</p>";
            break;
        case "site_exists":
            echo "<p class=\"error\">Site already exists.</p>";
            break;
        case "no_sponsor":
            echo "<p class=\"error\">You must select a sponsor or type a new one.</p>";
            break;
        case "no_site_name":
            echo "<p class=\"error\">You must enter a site name.</p>";
            break;
        case "no_site_ref":
            echo "<p class=\"error\">You must enter a site affiliate link.</p>";
            break;
        case "site_saved":
            echo "<p class=\"success\">Site saved.</p>";
            break;
        case "ambiguous_sponsor":
            echo "<p class=\"error\">Please either select a sponsor from the list or type a new one, not both.</p>";
            break;
        default:
            echo "<p class=\"error\">Unknown error.</p>";
            break;
    }
?>
<form method="post" action="?a=SiteEdit">
Sponsor:<br />
<select name="sponsor_id"><option value="0">--Select--</option>
<?php
    $sponsors = $sites->getSponsors();
    foreach($sponsors as $sponsor) {
        echo "<option value=\"{$sponsor->sponsor_id}\"";
        if($this->post_object->sponsor_id == $sponsor->sponsor_id) echo " selected=\"selected\"";
        echo ">{$sponsor->sponsor_name}</option>\n";
    }
?>
</select><br />
Or enter a new sponsor name:<br />
<input type="text" name="sponsor_name" value="<?=$this->post_object->sponsorname ?? "";?>" size="100" maxlength="255" /><br /><br />
Site name:<br />
<input type="text" name="site_name" value="<?=$this->post_object->site_name ?? "";?>" size="100" maxlength="255" /><br /><br />
Affiliate Link URL:<br />
<input type="text" name="site_ref" value="<?=$this->post_object->site_ref ?? "";?>" size="100" maxlength="255" /><?=(($this->post_object->site_ref ?? "") != "") ? " <a href=\"{$this->post_object->site_ref}\" target=\"_blank\">Visit</a>" : "";?><br /><br />
Site slug (used in outgoing links, use only a-z0-9):<br />
<input type="text" name="site_domain" value="<?=$this->post_object->site_domain ?? "";?>" size="100" maxlength="255" /><br /><br />
Preference, (controls exposure on page, you can leave blank):<br />
<input type="text" name="pref" value="<?=$this->post_object->pref ?? "";?>" size="5" maxlength="3" /><br /><br />
<br />
Site status:<br />
<input type="radio" name="enabled" value="enabled"<?=($this->post_object->enabled == "enabled") ? " checked=\"checked\"" : "";?> />&nbsp;Enabled&nbsp;
<input type="radio" name="enabled" value="disabled"<?=($this->post_object->enabled == "disabled") ? " checked=\"checked\"" : "";?> />&nbsp;Disabled
<input type="radio" name="enabled" value="removed"<?=($this->post_object->enabled == "removed") ? " checked=\"checked\"" : "";?> />&nbsp;Removed
<br /><br />
<input type="hidden" name="site_id" value="<?=$this->post_object->site_id;?>" />
<input type="submit" value="<?=($this->post_object->site_id) ? "Update Site" : "Add Site";?>" />
</form></div>
<?php
	if($this->post_object->site_id)
	{
		echo "<br /><br />OR <a href=\"?a=SiteDelete&id={$this->post_object->site_id}\" onclick=\"return confirm('Are you sure? This cannot be undone. It is better to set the site status to \'removed\' instead.');\">[Delete this site]</a>";
	}
?>
</section>