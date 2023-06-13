</nav></header>
<section class="edit"><div>
<?php
    switch($this->action_status) {
        case "no_name":
            echo "<p class=\"error\">You must enter the original filename for the video.</p>";
            break;
        case "no_base":
            echo "<p class=\"error\">You must enter the base filename for the video.</p>";
            break;
        case "no_url":
            echo "<p class=\"error\">You must enter the base URL for the video.</p>";
            break;
        case "no_duration":
            echo "<p class=\"error\">You must enter a duration for the video.</p>";
            break;
        case "no_dims":
            echo "<p class=\"error\">You must enter the dimensions of the video.</p>";
            break;
        case "no_fps":
            echo "<p class=\"error\">You must enter the frames per second of the video.</p>";
            break;
        case "error":
            echo "<p class=\"error\">There was an error importing the video: " . $this->error_message . "</p>";
            break;
        case "testing":
            echo "<p>Testing</p>";
            break;
        case "vars_empty":
            echo "<p>Add / Edit Video</p>";
            break;
        case "not_found":
            echo "<p class=\"error\">Video not found.</p>";
            break;
        case "ok":
            echo "<p class=\"success\">Video saved successfully.</p>";
            break;
        default:
            echo "<p class=\"error\">Unknown error.</p>";
            break;
    }

    $id = $this->post_object->id ?? 0;
    $duration = $this->post_object->duration;
    $fps = $this->post_object->fps;
    $r_fps = $this->post_object->r_fps;
    $orig_width = $this->post_object->orig_width;
    $orig_height = $this->post_object->orig_height;
    $orig_filename = $this->post_object->orig_filename ?? "";
    $base_filename = $this->post_object->base_filename ?? "";
    $base_url = $this->post_object->base_url ?? "";
    $url_1080p = $this->post_object->url_1080p ?? "";
    $url_720p = $this->post_object->url_720p ?? "";
    $url_480p = $this->post_object->url_480p ?? "";
    $video_id = $this->post_object->video_id ?? 0;
    $transcoding = $this->post_object->transcoding ?? "none";
    $process_state = $this->post_object->process_state ?? "pending";
    $progress = $this->post_object->progress ?? 0;
    $url_low = $this->post_object->url_low ?? "";
    $url_poster = $this->post_object->url_poster ?? "";
    $url_180p = $this->post_object->url_180p ?? "";
    $url_vtt = $this->post_object->url_vtt ?? "";
    $url_thumbnail = $this->post_object->url_thumbnail ?? "";

    $duration_text = (floor($duration / 60)) . "m " . ($duration % 60) . "s";

?>
<form method="post" action="?a=VideoEdit">
<br />
These settings should not normally need to be edited unless you are changing the video files manually.<br />
If something went wrong during transcoding or making the preview files, set the processing state back to "pending" or "transcoded"<br />
and the transcoding type to "none" and start the process again using the button in the videos view.<br /><br />
Ensure that transcoding is not running before editing these settings.<br /><br />
Original Filename:<br />
<input type="text" name="orig_filename" value="<?=$orig_filename;?>" size="100" maxlength="255" /><br /><br />
Processing state:<br />
<select name="process_state"><option value="0">--Select--</option>
<?php
    $states = $video->getProcessStates();
    foreach($states as $state) {
        echo "<option value=\"$state\"";
        if($process_state == $state) echo " selected=\"selected\"";
        echo ">$state</option>\n";
    }
?>
</select><br />
Transcoding type:<br />
<select name="transcoding"><option value="0">--Select--</option>
<?php
    $types = $video->getTranscodeTypes();
    foreach($types as $type) {
        echo "<option value=\"$type\"";
        if($transcoding == $type) echo " selected=\"selected\"";
        echo ">$type</option>\n";
    }
?>
</select><br /><br />
Duration: (<?=$duration_text;?>)<br />
<input type="text" name="duration" value="<?=$duration;?>" size="10" maxlength="10" /><br />
Frames/second:<br />
<input type="text" name="fps" value="<?=$fps;?>" size="10" maxlength="3" /><br />
Real Frames/second:<br />
<input type="text" name="r_fps" value="<?=$r_fps;?>" size="10" maxlength="3" /><br />
Pixel Width:<br />
<input type="text" name="orig_width" value="<?=$orig_width;?>" size="10" maxlength="10" /><br />
Pixel Height:<br />
<input type="text" name="orig_height" value="<?=$orig_height;?>" size="10" maxlength="10" /><br /><br />
Base Filename:<br />
<input type="text" name="base_filename" value="<?=$base_filename;?>" size="100" maxlength="80" /><br />
Base URL:<br />
<input type="text" name="base_url" value="<?=$base_url;?>" size="100" maxlength="128" /><br /><br />
1080p version URL:<br />
<input type="text" name="url_1080p" value="<?=$url_1080p;?>" size="100" maxlength="80" /><br />
720p version URL:<br />
<input type="text" name="url_720p" value="<?=$url_720p;?>" size="100" maxlength="80" /><br />
480p version URL:<br />
<input type="text" name="url_480p" value="<?=$url_480p;?>" size="100" maxlength="80" /><br />
Preview version URL:<br />
<input type="text" name="url_180p" value="<?=$url_180p;?>" size="100" maxlength="80" /><br />
Legacy version URL:<br />
<input type="text" name="url_low" value="<?=$url_low;?>" size="100" maxlength="80" /><br />
Poster URL:<br />
<input type="text" name="url_poster" value="<?=$url_poster;?>" size="100" maxlength="80" /><br />
Thumbnail URL:<br />
<input type="text" name="url_thumbnail" value="<?=$url_thumbnail;?>" size="100" maxlength="80" /><br />
VTT URL:<br />
<input type="text" name="url_vtt" value="<?=$url_vtt;?>" size="100" maxlength="80" /><br />
<input type="hidden" name="video_id" value="<?=$video_id;?>" />
<input type="hidden" name="id" value="<?=$id;?>" />
<input type="submit" value="<?=($id) ? "Update Video" : "Add Video";?>" />
</form></div>
</section>