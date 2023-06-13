<?php
	$description = new stdClass();
    $description->title = "Error - Page not found - MovieSample.net";
    $description->robots = true;
    $this->page->description($description);

    header("HTTP/1.0 404 Not Found", true, 404);

    include(INCLUDE_PATH . "domains/moviesample/templates/header_template.php");
    include(INCLUDE_PATH . "domains/moviesample/templates/navbar_template.php");
?>
<main>
<div class="mvs-flex topgap">
<p>Error - Page not found</p>
</div>
</main>
<?php
    $include_script = false;
    include(INCLUDE_PATH . "domains/moviesample/templates/footer_template.php");
?>