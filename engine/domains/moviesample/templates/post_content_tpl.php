<?php
            while($video = $this->post->next()) {
                $posted_date = date("H:i D F j, Y", $video->post_time);
                $duration = $video->duration;
                //convert to minutes and seconds
                $minutes = floor($duration / 60);
                $seconds = $duration % 60;
                if($seconds < 10) $seconds = "0$seconds";
                $duration = "$minutes:$seconds";
                $title = htmlspecialchars($video->title);
                $buttons = "<a href=\"/video/$video->pagename\"><button>View This Video</button></a><button class=\"green\">Edit This Video</button>";
                if($video->display_state == "display") {
                    $buttons .= "<button class=\"orange\" onclick=\"hideVideo($video->post_id);\">Hide This Video</button>";
                } else {
                    $buttons .= "<button class=\"purple\" onclick=\"unhideVideo($video->post_id);\">Unhide This Video</button>";
                }
?>
<div class="card">
<div class="acc-thmb">
<img src="/<?=$video->url_thumbnail;?>" loading="lazy" alt="thumbnail" />
</div>
<div class="acc-info">
<p>Title: <?=$title;?></p>
<p>Duration: <?=$duration;?></p>
<p>Viewed <?=$video->total_clicks;?> times</p>
<p>You posted this video on <?=$posted_date;?></p>
<p><?=(($video->display_state == "display") ? "This video is live in the <b>$video->channel_name</b> channel." : "<b>This video is hidden.</b>");?></p>
<p><?=$buttons;?></p>
</div>
</div>
<?php
            }
?>