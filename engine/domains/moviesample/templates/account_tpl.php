<?php
    if($this->user->logged_in) {
?>
<h1>Hello <?=$this->user->username;?>, welcome to your account page.</h1>
<p>From this page you can upload new videos, create channels to post your videos to, post your videos for others to watch, and manage your videos or channels.</p>
<div class="acc-grid accbtns">
<?php
        $video_obj = new Video();
        $num_of_rows = $video_obj->videos(0, 9999, $this->user->user_id, 0);
        $posted = [];
        $ready = [];
        $unposted = [];
        if($num_of_rows > 0) {
            while($video = $video_obj->next()) {
                $video->highest_res = $video_obj->getHighestTranscodeSize();
                if($video->process_state == "posted") {
                    $posted[] = $video;
                } elseif($video->process_state == "transcoded" || $video->process_state == "processed" || $video->process_state == "ready") {
                    $ready[] = $video;
                } else {
                    $unposted[] = $video;
                }
            }
        }
        $num_of_videos = count($ready);
        if($num_of_videos) {
?>
<div class="acc-span label">
<button class="large green" onclick="uploadVideoInit();return false;">Upload A Video</button>
<?php
            if($num_of_videos > 1) {
?>
<p>You have <?=$num_of_videos;?> uploaded video<?=($num_of_videos == 1) ? "" : "s";?> ready to post<?=($num_of_videos > 8) ? ". Here are the latest" : "";?>:</p>
<?php
            } else {
?>
<p>Here is your uploaded video waiting to be posted:</p>
<?php
            }
?>
</div>
<?php
            $vids = $ready;
            $n = $num_of_videos;
            $limit = 8;
            include(INCLUDE_PATH . "domains/moviesample/templates/vid_content_tpl.php");
        } else {
?>
<div class="acc-span label"><p>You have no videos to post.</p>
<button class="large" onclick="uploadVideoInit();return false;">Upload A Video</button></div>
<?php
        }
        $num_of_videos = count($unposted);
        if($num_of_videos) {
?>
<div class="acc-span label">
<?php
            echo "<p>You have uploaded $num_of_videos video" . (($num_of_videos == 1) ? " that is" : "s that are") . " not ready to post yet.";
            if($num_of_videos > 8) echo " Here are the latest:";
            echo "</p></div>";
            $vids = $unposted;
            $n = $num_of_videos;
            $limit = 8;
            include(INCLUDE_PATH . "domains/moviesample/templates/vid_content_tpl.php");
?>
<div class="acc-span label">
<a href="/myvideos.html"><button class="large">See All My Video Uploads</button></a>
</div>
<?php
        }
?>
</div>
<?php
        $this->post = new Post();
        $this->num_of_videos = $this->post->videoPosts(0, 8, "sortByAdded", 0, 0, $this->user->user_id);
        if($this->num_of_videos) {
?>
<div class="acc-grid accbtns">
<div class="acc-span label"><p>Here <?=($this->num_of_posts == 1) ? "is the video" : "are the latest videos";?> you have posted:</p></div>
<?php
            include(INCLUDE_PATH . "domains/moviesample/templates/post_content_tpl.php");
?>
<div class="acc-span label"><a href="/myposts.html"><button class="large">See All My Posted Videos</button></a></div>
</div>
<br />
<?php
        }
?>
<div class="accbtns">
<?php
        $channel_obj = new Channel();
        $num_of_rows = $channel_obj->channels(0, 9999, $this->user->user_id);
        if($num_of_rows) {
            echo "<div class=\"label\"><p>You have $num_of_rows channels.</p><button class=\"large green\" onclick=\"channelCreate();\">Create A Channel</button></div>";
            echo "<ul class=\"channel-label\">";
            while($channel = $channel_obj->next()) {
                echo "<li><p>$channel->channel_name - <a href=\"/channel/$channel->channel_id\"><button>Visit This Channel</button></a> <button class=\"green\">Edit This Channel</button></p></li>";
            }
            echo "</ul>";
        } else {
?>
<div class="label"><p>You have no channels to put your videos in.</p><button class="large green" onclick="channelCreate();">Create A Channel</button></div>
<?php
        }
?>
</div>
<?php
    } else {
?>
<p class="hugepad topgap">You must be logged in to view this page.</p>
<?php
    }
?>