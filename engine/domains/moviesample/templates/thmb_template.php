<?php
        $res = "SD";
        if($video->url_1080p) {
            $res = "1080p";
        } else if($video->url_720p) {
            $res = "720p";
        } else if($video->url_480p) {
            $res = "480p";
        }
        $duration = $video->duration;
        $minutes = floor($duration / 60);
        $seconds = $duration % 60;
        if($seconds < 10) {
            $seconds = "0" . $seconds;
        }
        $duration = $minutes . ":" . $seconds;
        $title = htmlspecialchars($video->title);
        $alt_title = htmlspecialchars($video->alt_title);
        $url = "/video/" . $video->pagename;
        $description = $this->shorten($this->post->description()->post_texts[0]["text"], 60, 0);
//        if(substr($description, -3) == "...") {
//            $more = "<p class=\"desc more\"><a href=\"$url\">[read&nbsp;more]</a></p>";
//        } else {
            $more = "";
//        }
        $channel_name = htmlspecialchars($video->channel_name);
        $time = $this->getTimeSpan(time() - $video->post_time);
?>
<div class="thmb">
<div><a href="<?=$url;?>"><img src="/<?=$video->url_thumbnail;?>" width="384" height="216" loading="lazy" alt="<?=$alt_title;?>" /></a><span class="res"><?=$res;?></span><span class="time"><?=$duration;?></span></div>
<div><p><?=$title;?></p><p class="desc"><?=$description;?></p><?=$more;?></div>
<div><p class="chan"><a href="/channel/<?=$video->channel_id;?>"><?=$channel_name;?></a><br /><?=$time;?> ago</p><p class="views"><?=$video->total_clicks;?> views</p></div>
</div>