<?php
    if($this->num_of_videos) {
        while($video = $this->post->next()) {
            include(INCLUDE_PATH . "domains/moviesample/templates/thmb_template.php");
        }
    } else {
        echo "<div class=\"wide left\">No videos found.</div>";
    }
?>