</nav></header>
<section class="admin"><div>
<?php
	if(!isset($_GET["yesimsure"]))
    {
        $gid = $this->get_vars->gid;
?>
<br /><br />Delete channel id <?=$gid;?> - Are you sure?<br /><br />
<a href="?a=delete_channel&subpage=videotools&gid=<?=$gid;?>&yesimsure=true" onclick="return confirm('Definitely sure you want this channel gone? This cannot be undone.');">Yes I'm sure</a>
<?php
    }

    if(sizeof($this->error_messages))
    {
        echo "<br />";
        for($i = 0; $i < sizeof($this->error_messages); $i++)
        {
            echo "<p class=\"error centre\">{$this->error_messages[$i]}</p>\n";
        }
    }
    elseif(sizeof($this->status_messages))
    {
        echo "<br />";
        for($i = 0; $i < sizeof($this->status_messages); $i++)
        {
            echo "<p class=\"centre\">{$this->status_messages[$i]}</p>\n";
        }
    }

?>
</div></section>