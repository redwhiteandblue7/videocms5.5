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
    foreach($this->channels as $channel) {
            include(INCLUDE_PATH . "domains/moviesample/templates/chan_template.php");
    }
?>
</div>
</main>
<?php
    $include_script = true;
    include(INCLUDE_PATH . "domains/moviesample/templates/footer_template.php");
?>