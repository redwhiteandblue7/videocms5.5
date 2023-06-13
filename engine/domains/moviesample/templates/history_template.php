<?php
    include(INCLUDE_PATH . "domains/moviesample/templates/header_template.php");
    include(INCLUDE_PATH . "domains/moviesample/templates/navbar_template.php");
?>
<main>
<div class="mvs-grid topgap">
<?php
    include(INCLUDE_PATH . "domains/moviesample/templates/sidebar_template.php");
?>
<div id="history" class="wide label"><h1><?=$this->label;?></h1></div>
</div>
</main>
<?php
    $include_script = true;
    include(INCLUDE_PATH . "domains/moviesample/templates/footer_template.php");
?>