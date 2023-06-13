<section class="edit"><div>
<?php
    if($submit_id = $this->gid)
    {
        $row = $this->dbo->getPendingSubmitArray($submit_id);
        extract($row);
    }
    else
    {
        extract($this->post_echo_array);
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
        echo "<p>Review submission:</p>";
    }
?>
<form method="post" action="?a=import_site&subpage=submittools" name="postdata">
Username: <?=$user_name;?><br />
Email: <?=$email_addr;?><br />
Time submitted: <?=gmdate("H:i:s d-M-y", $submit_time);?>, IP address <?=$ip_address;?><br />
<?php
    $user1 = $this->dbo->findIDFromUsername($user_name);
    $user2 = $this->dbo->findIDFromEmail($email_addr);
    if(($user1 == "") && ($user2 == ""))
    {
        echo "User not found. ";
        $user_id = 0;
    }
    elseif($user1 == $user2)
    {
        echo "User found, ID=";
        $user_id = $user1;
    }
    elseif($user1 == "")
    {
        echo "User found under different username, ID=";
        $user_id = $user2;
    }
    elseif($user2 == "")
    {
        echo "User found under different email, ID=";
        $user_id = $user1;
    }
    else
    {
        echo "Different users found for username and email, using ID ";
        $user_id = $user1;
    }
?>
<input type="text" name="user_id" size="3" maxlength="3" value="<?=$user_id;?>" /><br /><br />
Site title:<br />
<input type="text" name="submit_title" size="80" maxlength="255" value="<?=$submit_title;?>" /><br />
Page tag:<br />
<input type="text" name="pagename" value="<?=$pagename;?>" size="80" maxlength="255" /><br /><br />
Site category:<br />
<input type="text" name="submit_category" size="80" maxlength="255" value="<?=$submit_category;?>" /><br />
Site tags:<br />
<input type="text" name="submit_tags" size="80" maxlength="255" value="<?=$submit_tags;?>" /><br />
<?php
    if(substr($submit_url, 0, 4) != "http") $submit_url = "http://" . $submit_url;
?>
Site URL:<br />
<input type="text" name="submit_url" size="80" maxlength="255" value="<?=$submit_url;?>" /><br />
<br />
Description:<br />
<?=nl2br($submit_content);?><br />
<textarea  name="submit_content" rows="6" cols="120"><?=$submit_content;?></textarea><br /><br />
Alt title:<br />
<input type="text" name="alt_title" size="80" maxlength="255" value="<?=$alt_title;?>" /><br />
<input type="hidden" name="submit_id" value="<?=$submit_id;?>" />
<input type="hidden" name="user_id" value="<?=$user_id;?>" />
<input type="hidden" name="submit_time" value="<?=$submit_time;?>" />
<input type="hidden" name="user_name" value="<?=$user_name;?>" />
<input type="hidden" name="email_addr" value="<?=$email_addr;?>" />
<input type="submit" value="Import This Into A Post" />
</form>
</div>
<?php
	if($submit_id)
	{
		echo "<br /><br /><p>OR <a href=\"?subpage=submittools&a=reject_submit&gid=$submit_id\">[Reject this submission]</a></p><br /><br />";
	}
?>
</section>
