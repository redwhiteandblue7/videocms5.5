</header>
<?php
    if($this->action_status == "none") echo "Checking database:<br /><br />";

    if(sizeof($this->status_messages))
    {
        echo "<br />";
        foreach($this->status_messages as $msg)
        {
            echo "$msg<br />\n";
        }
    }

    switch($this->action_status) {
        case "ok":
            echo "<br /><br />The database is now ready to accept users. ";
            echo " <a href=\"?a=Register\">Click here to create the admin account and login.</a>";
            break;
        case "none":
            echo "<br /><br />Shall I go ahead and build the initial database tables? <a href=\"?a=Setup&setup=yes\">Yes</a>";
            break;
        default:
            echo "<br /><br />Something went wrong with the database setup.";
            break;
    }
?>
</body></html>
    