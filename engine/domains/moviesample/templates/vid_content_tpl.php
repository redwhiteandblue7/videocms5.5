<?php
            $n = $num_of_videos;
            if($n > $limit) $n = $limit;
            for($i = 0; $i < $n; $i++) {
                $video = $vids[$i];
                $upload_date = date("H:i D F j, Y", $video->time_added);
                $duration = $video->duration;
                //convert to minutes and seconds
                $minutes = floor($duration / 60);
                $seconds = $duration % 60;
                if($seconds < 10) $seconds = "0$seconds";
                $duration = "$minutes:$seconds";
                $res = $video->highest_res;
                if($res == "low") $res = "low resolution";
                $delete_button = "<button class=\"red\" onclick=\"deleteVideo($video->video_id);\">Delete This Video</button>";
                $progress_button = "<button class=\"yellow\" onclick=\"watchVideoStatus($video->video_id);\">See Progress</button>";
                if($video->process_state == "transcoded") {
                    $buttons = "<b>This video is ready to post.</b><br /><button class=\"green\" onclick=\"videoPostInit($video->video_id);\">Post This Video</button>" . $delete_button;
                } elseif($video->process_state == "transcoding" || $video->process_state == "processing") {
                    $buttons = "I am encoding this video at the moment, $video->progress% done.<br />" . $progress_button;
                } elseif($video->process_state == "processed") {
                    $buttons = "<b>You didn't finish posting this video.</b><br /><button class=\"green\" onclick=\"videoMakeSprite($video->video_id);\">Continue Posting</button>" . $delete_button;
                } elseif($video->process_state == "ready") {
                    $buttons = "<b>You didn't finish posting this video.</b><br /><button class=\"green\" onclick=\"videoPostEdit($video->video_id);\">Continue Posting</button>" . $delete_button;
                } elseif($video->process_state == "pending") {
                    $t = time();
                    if($video->transcode_start < $t - 300 && $video->transcoding != "none" && $video->progress == 0) {
                        $buttons = "<b>Oops! Looks like the encoding didn't start properly.</b><br />" . $delete_button;
                    } else {
                        $buttons = "I am about to start encoding this video.<br />" . $progress_button;
                    }
                } else {
                    $buttons = "<b>Something went wrong, I couldn't process this video.</b><br />" . $delete_button;
                }
?>
<div class="card">
<div class="acc-thmb">
<img src="/<?=$video->url_thumbnail;?>" loading="lazy" alt="thumbnail" />
</div>
<div class="acc-info">
<p>You uploaded this video on <?=$upload_date;?></p>
<p>Filename: <?=$video->orig_filename;?></p>
<p>Duration: <?=$duration;?></p>
<p>Resolution: <?=$res;?> (<?=$video->orig_width;?>x<?=$video->orig_height;?>)</p>
<p><?=$buttons;?></p>
</div>
</div>
<?php
            }