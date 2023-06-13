<section class="admin"><div>
<?php

    if($this->action_status) {
        switch($this->action_status) {
            case "ok":
                echo "<p>Done. It is gone, never to return (unless you add it again).<br /><br /><a href=\"?a=EditDomain\">Click here to continue</a></p>\n";
                break;
            case "none":
                echo "<p>Delete " . $this->dmo->domain_name . " and all its tables and data - Are you sure? <a href=\"?a=DeleteDomain&delete=yes\" onclick=\"return confirm('Are you fucking sure? This cannot be undone, you should probably backup the database first');\">Yes I'm sure</a></p>";
                break;
            default:
                echo "<p class=\"error centre\">There was an unspecified error that I cannot specify (" . $this->action_status . ").</p>\n";
                break;
        }
    }

    if(sizeof($this->status_messages)) {
        echo "<br />";
        foreach($this->status_messages as $msg) {
            echo "<p class=\"success centre\">$msg</p>\n";
        }
    }

?>
</div></section>
</body></html>