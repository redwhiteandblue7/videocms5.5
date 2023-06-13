<?php
    include(INCLUDE_PATH . "domains/moviesample/templates/header_template.php");
    include(INCLUDE_PATH . "domains/moviesample/templates/navbar_template.php");
?>
<main>
<div id="loggedin-content" class="topgap">
<?php
    include(INCLUDE_PATH . "domains/moviesample/templates/" . $this->account_template . "_tpl.php");
?>
</div>
</main>
<?php
    $include_script = true;
    include(INCLUDE_PATH . "domains/moviesample/templates/footer_template.php");
?>