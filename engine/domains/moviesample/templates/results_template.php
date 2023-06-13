<?php
    include(INCLUDE_PATH . "domains/moviesample/templates/header_template.php");
    include(INCLUDE_PATH . "domains/moviesample/templates/navbar_template.php");
?>
<main>
<div class="mvs-grid topgap">
<?php
    include(INCLUDE_PATH . "domains/moviesample/templates/sidebar_template.php");
?>
<div class="wide label"><h1><?=$this->label;?></h1></div>
<?php
    if($this->num_of_videos) {
        while($video = $this->post->next()) {
            include(INCLUDE_PATH . "domains/moviesample/templates/thmb_template.php");
        }
    } else {
        echo "<div class=\"wide left\">No results found.</div>";
    }
?>
</div>
</main>
<?php
    $include_script = true;
    include(INCLUDE_PATH . "domains/moviesample/templates/footer_template.php");
?>