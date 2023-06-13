<?php
    include(INCLUDE_PATH . "domains/moviesample/templates/header_template.php");
    include(INCLUDE_PATH . "domains/moviesample/templates/navbar_template.php");
?>
<main>
<div class="mvs-grid topgap">
<?php
    $show_latest = true;
    include(INCLUDE_PATH . "domains/moviesample/templates/sidebar_template.php");
?>
<div class="wide"><h1>All Tags</h1></div>
<div class="wide tag-list"><ul class="tags">
<?php
    foreach($this->tag_names as $tag) {
        echo "<li><a href=\"/tag/$tag->tag_name\">#$tag->tag_name</a> - $tag->num_posts video" . (($tag->num_posts != 1) ? "s" : "") . "</li>";
    }
?>
</ul></div>
</div>
</main>
<?php
    $include_script = false;
    include(INCLUDE_PATH . "domains/moviesample/templates/footer_template.php");
?>