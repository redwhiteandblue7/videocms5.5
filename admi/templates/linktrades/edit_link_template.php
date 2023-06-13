<section class="edit"><div>
<?php
    if(isset($_POST["link_id"]))
    {
        extract($this->post_array);
    }
    elseif($this->did)
    {
        $row = $this->dbo->getArrayFromLinksTable($this->did);
        extract($row);
    }
    else
    {
        $link_id = 0;
        $status = 0;
        $request_status = 0;
    }

    if(sizeof($this->error_messages))
    {
        for($i = 0; $i < sizeof($this->error_messages); $i++)
        {
            echo "<p class=\"error centre\">{$this->error_messages[$i]}</p>\n";
        }
    }
    else
    {
        echo "<p>Add / Edit Link:</p>";
    }
?>
<form method="post" action="?a=edit_link&subpage=linktrades">
Referrer domain:<br />
<input type="text" name="domainstring" value="<?=$domainstring ?? "";?>" size="80" maxlength="128" /><br /><br />
Outgoing landing page:<br />
<input type="text" name="landing_page" value="<?=$landing_page ?? "";?>" size="80" maxlength="254" /><br /><br />
Ref code:<br />
<input type="text" name="ref_code" value="<?=$ref_code ?? 0;?>" size="5" maxlength="5" /><br /><br />
Anchor title:<br />
<input type="text" name="anchor" value="<?=$anchor ?? "";?>" size="80" maxlength="26" /><br /><br />
Description:<br />
<textarea name="description" rows="2" cols="80"><?=$description ?? "";?></textarea><br />
Status:
<select name="status">
<option value="0"<?=(!$status) ? " selected=\"selected\"" : "";?>>Suspend</option>
<option value="1"<?=($status == 1) ? " selected=\"selected\"" : "";?>>Pending</option>
<option value="2"<?=($status == 2) ? " selected=\"selected\"" : "";?>>Active</option>
<option value="3"<?=($status == 3) ? " selected=\"selected\"" : "";?>>Auto</option>
</select>&nbsp;&nbsp;
Request Status:
<select name="request_status">
<option value="0"<?=($request_status == 0) ? " selected=\"selected\"" : "";?>>Suspend</option>
<option value="1"<?=($request_status == 1) ? " selected=\"selected\"" : "";?>>Toplist Trade</option>
<option value="2"<?=($request_status == 2) ? " selected=\"selected\"" : "";?>>Traffic Trade</option>
</select><br /><br />
<input type="hidden" name="link_id" value="<?=$link_id;?>" />
<input type="submit" value="<?=($link_id) ? "Update" : "Add";?> Link" />
</form>
</div></section>