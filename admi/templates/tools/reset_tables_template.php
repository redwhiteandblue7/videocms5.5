</nav></header>
<section class="edit"><div>
<?php
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
        echo "<p>This will delete rows from pageloads, badclicks, clickthrus and visitors tables<br />that are newer than the selected time and set the time_last_stat_update to now.</p>";
    }
?>
<form name="reset_form" method="post" action="?a=resettables&subpage=tools">
<p>
Select period:<select name="daterange">
<option value="today">So Far Today</option>
<option value="last24">Last 24 hours</option>
<option value="yesterday">Since Yesterday</option>
<option value="last48">Last 48 hours</option>
<option value="twodaysago">Since Two Days Ago</option>
<option value="last72">Last 72 hours</option>
<option value="thisweek">So Far This Week</option>
<option value="last7">Last 7 Days</option>
<option value="all">All In Table</option>
</select>
<br /><br />
<input type="submit" value="Reset Tables" />
</p></form></div>
</section>