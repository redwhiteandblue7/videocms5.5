</nav></header>
<section class="edit"><div>
<?php
    if($this->action_status == "ready" || $this->action_status == "none") {
        echo "<p>Checking database for changes needed:</p><br />";
    } elseif($this->action_status == "error") {
        echo "<p class=\"error centre\">Something went wrong:</p>\n";
    } else {
        echo "<p>Running SQL to update database structure</p><br />";
    }

    if(sizeof($this->status_messages)) {
        foreach($this->status_messages as $msg) {
             echo "<p class=\"success centre\">$msg</p>\n";
        }
    }

    echo "<br /><br />";
    switch($this->action_status) {
        case "ready":
            echo "<p>Shall I go ahead and run the above SQL to update the database? <a href=\"?a=UpdateTables&setup=yes\">Yes</a></p>";
            break;
        case "done":
            echo "<p>Database is up to date.</p>";
            break;
        case "error":
            echo "<p>Check database schema in dbclasses/tabledata...</p>";
            break;
        case "none":
            echo "<p>Database is up to date, no changes found.</p>";
            break;
        default:
            echo "<p>Something went wrong</p>";
    }
?>
</section>