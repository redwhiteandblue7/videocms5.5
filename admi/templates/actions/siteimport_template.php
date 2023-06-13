</nav></header>
<section class="edit"><div>
<?php
    switch($this->action_status) {
        case "vars_empty":
            echo "<p>Import Sites</p>";
            break;
        case "site_exists":
            echo "<p class=\"error\">One or more of the sites in the list already existed and have not been updated.</p>";
            break;
        case "no_sponsor":
            echo "<p class=\"error\">You must select a sponsor or type a new one.</p>";
            break;
        case "no_links":
            echo "<p class=\"error\">You must enter some links.</p>";
            break;
        case "not_enough_data":
            echo "<p class=\"error\">There is not enough data in the link dump.</p>";
            break;
        case "empty_site_name":
            echo "<p class=\"error\">One of the site names is empty.</p>";
            break;
        case "empty_link":
            echo "<p class=\"error\">One of the links is empty.</p>";
            break;
        case "sites_imported":
            echo "<p class=\"success\">Sites imported.</p>";
            break;
        case "ambiguous_sponsor":
            echo "<p class=\"error\">Please either select a sponsor from the list or type a new one, not both.</p>";
            break;
        default:
            echo "<p class=\"error\">Unknown error.</p>";
            break;
    }
?>
<form name="import_form" method="post" action="?a=SiteImport">
Sponsor:<br />
<select name="sponsor_id"><option value="0">--Select--</option>
<?php
    $sponsors = $site->getSponsors();
    foreach($sponsors as $sponsor) {
        echo "<option value=\"{$sponsor->sponsor_id}\"";
        if($this->post_object->sponsor_id == $sponsor->sponsor_id) echo " selected=\"selected\"";
        echo ">{$sponsor->sponsor_name}</option>\n";
    }
?>
</select><br />
Or enter a new sponsor name:<br />
<input type="text" name="sponsor_name" value="<?=$this->post_object->sponsorname ?? "";?>" size="100" maxlength="255" /><br /><br />
Dump the list of affiliate links in here in the form Sitename|Affiliate Link (e.g. dump from NATS)<br />
<textarea name="link_dump" rows="20" cols="96"><?=$this->post_object->link_dump ?? "";?></textarea><br /><br />
<input type="submit" value="Import Links" />
</form></div>
</section>