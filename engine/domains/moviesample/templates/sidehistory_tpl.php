<?php
    if($this->num_of_videos) {
        echo "<div class=\"side-grid\"><div class=\"side-wide\"><h3>Recently Viewed</h3></div>";
        while($video = $this->post->next()) {
            include(INCLUDE_PATH . "domains/moviesample/templates/sthmb_template.php");
        }
        echo "</div><div class=\"side-wide side-link\"><a href=\"/history.html\">See All</a></div>";
    }
?>