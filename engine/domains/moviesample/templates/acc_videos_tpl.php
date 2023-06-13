<?php
    if($this->user->logged_in) {
        $video_obj = new Video();
        $num_of_rows = $video_obj->videos(0, 9999, $this->user->user_id, 0);
        $vids = [];
        if($num_of_rows > 0) {
            while($video = $video_obj->next()) {
                $video->highest_res = $video_obj->getHighestTranscodeSize();
                if($video->process_state != "posted") {
                    $vids[] = $video;
                }
            }
        }
        $num_of_videos = count($vids);
?>
<h1>This is your uploaded videos page.</h1>
<div class="acc-grid accbtns">
<?php
        if($num_of_videos) {
?>
<div class="acc-span label">
<a href="/myaccount.html"><button class="large">Go Back To My Account Page</button></a>
<p>Here are all your uploaded videos you haven't posted. From here you can manage videos or check on progress.</p>
</div>
<?php
            $n = $num_of_videos;
            $limit = 9999;
            include(INCLUDE_PATH . "domains/moviesample/templates/vid_content_tpl.php");
        } else {
?>
<div class="acc-span label">
<a href="/myaccount.html"><button class="large">Go Back To My Account Page</button></a>
<p>No videos found.</p>
</div>
<?php
        }
?>
</div>
<?php
    } else {
?>
<p class="hugepad">You must be logged in to view this page.</p>
<?php
    }
?>