<?php
    include(INCLUDE_PATH . "domains/moviesample/templates/vheader_template.php");
    include(INCLUDE_PATH . "domains/moviesample/templates/navbar_template.php");

    $post_id = $this->post->vars()->post_id;
    $poster = "/" . $this->video->vars()->url_poster;
    $vtt = "/" . $this->video->vars()->url_vtt;
    $orig_width = $this->video->vars()->orig_width;
    $orig_height = $this->video->vars()->orig_height;
    $videos = $this->videosList();
    $tags = $this->post->tags();
    $time = $this->getTimeSpan(time() - $this->post->vars()->time_added);
    $base_url = $_SERVER["REQUEST_SCHEME"] . "://" . $_SERVER["HTTP_HOST"];
?>
<main>
<div class="mvs-grid topgap">
<?php
    $show_related = true;
    include(INCLUDE_PATH . "domains/moviesample/templates/sidebar_template.php");
?>
<div class="wide">
<div class="vid_contain"><div class="vid_frame">
<video id="test_video" width="<?=$orig_width;?>" height="<?=$orig_height;?>" class="video-js vjs-fill vjs-big-play-centered vjs-has-started vjs-show-big-play-button-on-pause" controls preload="auto" autoplay="true" poster="<?=$poster;?>" data-setup="{}">
<?php
    $i = 1;
    foreach($videos as $video)
    {
        echo "<source src=\"" . $video["src"] . "\" type=\"video/mp4\" label=\"" . $video["label"] . "\" ";
        if($this->visitor->desktop && $i == 1) {
            //if this is the first video and the visitor is on a desktop, make it selected
            echo "selected=\"true\"";
        } elseif(!$this->visitor->desktop && $i == sizeof($videos)) {
            //and if the visitor is on a mobile device, make the last video selected
            echo "selected=\"true\"";
        }
        echo "/>\n";
        $i++;
    }
?>
    <p class="vjs-no-js">
        To view this video please enable JavaScript, and consider upgrading to a
        web browser that
        <a href="https://videojs.com/html5-video-support/" target="_blank">supports HTML5 video</a></p>
</video>
<script>
    var player = videojs('test_video');
    player.controlBar.addChild('QualitySelector');
    player.vttThumbnails({
        src: '<?=$vtt;?>',
        baseUrl: '<?=$base_url;?>'
    });
</script>
</div></div></div>
<div class="wide post">
<?php
    echo "<h1>" . htmlspecialchars($this->post->vars()->title, ENT_QUOTES, "UTF-8") . "</h1>";
    foreach($this->post->description()->post_texts as $text) {
        echo "<p>" . $text["text"] . "</p>";
    }
    if($tags) {
        echo "<ul class=\"tags\">";
        foreach($tags as $tag) {
            echo "<li><a href=\"/tag/$tag->tag_name\">#$tag->tag_name</a></li>";
        }
        echo "</ul>";
    }
    echo "<div>Posted in <a href=\"/channel/{$this->video->vars()->channel_id}\">" . htmlspecialchars($this->video->vars()->channel_name, ENT_QUOTES, "UTF-8") . "</a> about $time ago.";
    if($this->post->vars()->total_clicks > 2) {
        echo " Viewed " . $this->post->vars()->total_clicks . " times.";
    }
    echo "</div>";
?>
</div>
<div class="wide label"><h3>More From This Channel</h3></div>
<?php
    while($video = $this->post->next()) {
        include(INCLUDE_PATH . "domains/moviesample/templates/thmb_template.php");
    }
?>
</div>
</main>
<?php
    $include_script = true;
    $include_add_history = true;
    include(INCLUDE_PATH . "domains/moviesample/templates/footer_template.php");
?>