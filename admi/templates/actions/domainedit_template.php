<section class="edit"><div>
<form method="post" action="?a=DomainEdit" name="domaindata">
<?php
    if(is_array($this->post_array)) {
        extract($this->post_array);
    }

    $name_err = false;
    $site_err = false;

	if(!isset($admin_ip) || is_null($admin_ip)) $admin_ip = "0";
    $ip4 = $_SERVER['REMOTE_ADDR'];
    if(isset($domain_id) && is_numeric($domain_id) && $domain_id > 0) {
        echo "<br />Admin IP is set to " . $admin_ip . " for this domain.";
        if($admin_ip != $ip4) echo " This does not match your current IP. Update to set new.";
        echo "<br /><br />\n";
    }

    if($this->action_status) {
        switch($this->action_status) {
            case "tables_err":
                echo "<p class=\"error centre\">Something went wrong with building the tables</p>\n";
                break;
            case "domain_exists":
                echo "<p class=\"error centre\">That domain already exists already. Sorry.</p>\n";
                $name_err = true;
                break;
            case "no_name":
                echo "<p class=\"error centre\">You must provide a domain name.</p>\n";
                $name_err = true;
                break;
            case "no_site":
                echo "<p class=\"error centre\">You must provide a site name.</p>\n";
                $site_err = true;
                break;
            case "vars_empty":
            case "no_domain":
                break;
            case "ok":
                echo "<p class=\"success centre\">That seemed to go okay. Check the updated details below.</p>\n";
                break;
            default:
                echo "<p class=\"error centre\">There was an unspecified error that I cannot specify (" . $this->action_status . ").</p>\n";
                break;
        }
    }

    if(sizeof($this->status_messages)) {
        echo "<br />";
        foreach($this->status_messages as $msg)
        {
            echo "<p class=\"success centre\">$msg</p>\n";
        }
    }

?>
<table><tr><th></th><th>Domain Definitions</th></tr>
<tr><td>Domain name:</td><td><input <?=($name_err) ? "class=\"errorf\" " : "";?>type="text" name="domain_name" value="<?=$domain_name ?? "";?>" size="64" /></td></tr>
<tr><td>Site name:</td><td><input <?=($site_err) ? "class=\"errorf\" " : "";?>type="text" name="site_name" value="<?=$site_name ?? "";?>" size="64" /></td></tr>
<tr><td>Sub domain:</td><td><input type="text" name="sub_domain" value="<?=$sub_domain ?? "";?>" size="64" /></td></tr>
<tr><td>Path to public folder:</td><td><input type="text" name="public_path" value="<?=$public_path ?? "";?>" size="112" /></td></tr>
<tr><td>Path to assets folder:</td><td><input type="text" name="asset_path" value="<?=$asset_path ?? "";?>" size="112" /></td></tr>
<tr><td>HTTP Scheme:</td><td><input type="text" name="http_scheme" value="<?=$http_scheme ?? "";?>" size="8" /></td></tr>
<tr><td>Default CSS file:</td><td><input type="text" name="default_css" value="<?=$default_css ?? "";?>" size="64" /></td></tr>
<tr><td>CSS version:</td><td><input type="text" name="css_version" value="<?=$css_version ?? "";?>" size="64" /></td></tr>
<tr><td>Description:</td><td><textarea name="description" rows="10" cols="96"><?=$description ?? "";?></textarea></td></tr>
<tr><td>Test Groups:</td><td><input type="text" name="test_groups" value="<?=$test_groups ?? 0;?>" size="4" /></td></tr>
<tr><td>Admin Test Group:</td><td><input type="text" name="admin_test_group" value="<?=$admin_test_group ?? 0;?>" size="4" /></td></tr>
<tr><td>SE tracking:</td><td><input type="text" name="se_tracking" value="<?=$se_tracking ?? "";?>" size="64" /></td></tr>
<tr><td>Domain Status:</td><td><select name="status"><option value="0"<?php if($status ?? 0 == 0) echo " selected=\"selected\""; ?>>Off</option><option value="1"<?php if($status ?? 0 == 1) echo " selected=\"selected\""; ?>>On</option></select></td></tr>
<tr><td></td><td><input type="submit" value="<?=(($domain_id ?? 0) == 0) ? "Add" : "Update";?> Domain" /></td></tr>
</table>
<input type="hidden" name="domain_id" value="<?=$domain_id ?? 0;?>" />
<input type="hidden" name="admin_ip" value="<?=$ip4;?>" />
</form>
</div>
<?php
	if($domain_id ?? 0) {
		echo "<br /><br />OR <a href=\"?a=DomainDelete&id=$domain_id\">[Delete this domain]</a>";
	}
?>
</section>
</body></html>