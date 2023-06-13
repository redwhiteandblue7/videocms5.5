</nav></header>
<section class="edit">
<?php
    if(sizeof($this->error_messages))
    {
        echo "<p class=\"error centre\">{$this->error_messages[0]}</p>\n";
    }
    elseif(sizeof($this->status_messages))
    {
        foreach($this->status_messages as $message)
        {
             echo "<p class=\"success centre\">$message</p>\n";
        }
    }
    elseif($label = $this->dbo->getNextMessage())
    {
        echo "<p>$label</p>\n";
    }
?>
<form>
<?php
    $post_count = 0;
    while($row = $this->dbo->getNextResultsRow())
    {
        extract($row);
        if(($description) && strpos($description, "<?xml") === false)
        {
            $description = "<?xml version='1.0' encoding='UTF-8'?>\r\n<post>\r\n<fulltext><![CDATA[$description]]></fulltext>\r\n<snippet></snippet>\r\n</post>";
            $description = addslashes($description);
            $this->dbo->updateTableColumn("post_descriptions", "description", $description, "post_id", $post_id, true);
            $post_count++;
        }
    }
?>
<br /><br /><?=$post_count;?> posts updated</form>
</section>