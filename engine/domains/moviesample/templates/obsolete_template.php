<?php
	$description = new stdClass();
    $description->title = "Error - Page deleted - MovieSample.net";
    $description->robots = true;
    $this->page->description($description);

    header("HTTP/1.0 410 Gone", true, 410);

    include(INCLUDE_PATH . "domains/moviesample/templates/header_template.php");
    include(INCLUDE_PATH . "domains/moviesample/templates/navbar_template.php");
?>
<main>
<div class="mvs-flex topgap">
<p>This page is no longer available.</p>
</div>
</main>
<?php
    $include_script = false;
    include(INCLUDE_PATH . "domains/moviesample/templates/footer_template.php");
?>