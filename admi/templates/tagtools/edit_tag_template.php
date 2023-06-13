</nav>
</header>
<section class="edit"><div>
<?php
    if(isset($_POST["tag_id"]))
    {
        extract($this->post_array);
    }
    elseif($this->did)
    {
        $row = $this->dbo->getArrayFromTableRowById("tags", "tag_id", $this->did, true);
        extract($row);
    }
    else
    {
        $tag_id = 0;
        $invisible = "false";
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
        echo "<p>Add / Edit Tag:</p>";
    }
?>
<form method="post" action="?subpage=tagtools&a=edit_tag">
Tag Name (e.g. category name, this is usually what will be visible to users):<br />
<input type="text" name="title" value="<?=$title ?? "";?>" size="30" maxlength="64" /><br /><br />
Landing page (where the tag links to, e.g. a category page). Use forward slash if unknown:<br />
<input type="text" name="pagename" value="<?=$pagename ?? "";?>" size="80" maxlength="128" /><br /><br />
<input type="radio" name="invisible" value="false"<?=($invisible == "false") ? " checked=\"checked\"" : "";?> /> Visible&nbsp;&nbsp;
<input type="radio" name="invisible" value="true"<?=($invisible == "true") ? " checked=\"checked\"" : "";?> /> Invisible
<input type="hidden" name="tag_id" value="<?=$tag_id;?>" />
<br /><br />
<input type="submit" value="<?=($tag_id) ? "Edit" : "Add";?> Tag" />
</form>
</div></section>