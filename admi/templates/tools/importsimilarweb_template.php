</nav></header>
<section class="edit"><div>
<?php
    if(isset($_POST["data_dump"]))
    {
        extract($this->post_array);
    }

    if(sizeof($this->error_messages))
    {
        foreach($this->error_messages as $message)
        {
            echo "<p class=\"error centre\">$message</p>\n";
        }
    }
    elseif(sizeof($this->status_messages))
    {
        foreach($this->status_messages as $message)
        {
             echo "<p class=\"success centre\">$message</p>\n";
        }
    }
    else
    {
        echo "<p>Import Similarweb data:</p>";
    }
?>
<form name="import_form" method="post" action="?a=importsimilarweb&subpage=tools">
Dump the Similarweb data in here e.g. as exported by ScrapeStorm with column names in first line and columns separated by tabs NOT commas:<br />
<textarea name="data_dump" rows="32" cols="120"><?=$data_dump ?? "";?></textarea><br /><br />
<input type="submit" value="Import Data" />
</form></div>
</section>