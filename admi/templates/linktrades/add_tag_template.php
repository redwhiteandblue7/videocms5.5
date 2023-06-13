</nav></header>
<section class="admin">
<?php
    if(sizeof($this->error_messages))
    {
        for($i = 0; $i < sizeof($this->error_messages); $i++)
        {
            echo "<p class=\"error centre\">{$this->error_messages[$i]}</p>\n";
        }
    }
    else
    {
        echo "<p>Select a tag from the list below:</p>";
    }
?>
<br />
<?php
    while($row = $this->dbo->getNextTableRow())
	{
		extract($row);
		echo "<a href=\"?subpage=linktrades&a={$this->action}&did={$this->did}&tid=$tag_id\">$title</a><br />\n";
	}
?>
</section>