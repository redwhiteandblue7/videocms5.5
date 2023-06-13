</nav></header>
<section class="tabledata">
<?php
    if($this->action_status) {
        switch($this->action_status) {
            case "imported":
                echo "<p class=\"success\">Video imported.</p>";
                break;
            case "video_saved":
                echo "<p class=\"success\">Video saved.</p>";
                break;
            default:
                break;
        }
        $this->action_status = "";
    }

    if($this->num_of_videos) {
?>
<p>The <?=$video->numRows();?> latest videos:</p>
<table id="videos"><tr><th>#.</th><th>ID</th><th>Thumbnail</th><th>Status</th><th>Action</th><th>Being transcoded</th><th>Progress</th><th>1080p</th><th>720p</th><th>480p</th><th>Legacy</th><th>Preview</th><th>VTT</th><th>Channel</th><th>Original Filename</th><th>Imported name</th><th>Original<br />Size</th><th>Aspect</th><th>FPS</th><th>Orientation</th><th>Imported</th><th>Owner</th></tr>
<?php
        $rownum = 0;
        while($row = $video->next()) {
            $d = date("H:i:s \o\\n Y-m-d", $row->time_added);
            $cellclass = (($rownum / 2) == round($rownum / 2)) ? "admin1" : "admin1x";
?>
<tr class="<?=$cellclass;?>">
<td><?=($row->rownum);?>. <a href="?a=VideoEdit&id=<?=$row->video_id;?>">[Edit]</a></td>
<td><?=$row->video_id;?></td>
<td><?=($row->url_thumbnail) ? "<img src=\"/$row->url_thumbnail\" width=\"80\" height=\"45\" alt=\"Thumbnail\" />" : "";?></td>
<td><?=$row->process_state;?></td>
<td>
<?php
        $video_id = $row->video_id;
        $state = $row->process_state;
        $fps = $row->fps;
        if($state == "pending") {
            echo "<button type=\"button\" class=\"cyan\" onclick=\"transcodeInit($video_id);return false;\">Transcode</button>";
        } elseif($state == "transcoding") {
            echo "<button type=\"button\" class=\"black\" onclick=\"watchTranscodeInit($video_id);return false;\">Watch</button>";
        } elseif($state == "transcoded" || $state == "processed") {
            echo "<button type=\"button\" class=\"yellow\" onclick=\"videoPreviewInit($video_id, $fps);return false;\">Get Poster</button>";
        } elseif($state == "ready") {
            echo "<button type=\"button\" class=\"green\" onclick=\"videoPostInit($video_id);return false;\">Post Video</button>";
//            echo "<a href=\"?a=PostEdit&video_id=$id\"><button type=\"button\" class=\"green\">Post Video</button></a>";
        }
?>
</td>
<td><?=$row->transcoding;?></td>
<td><?=$row->progress . "%";?></td>
<td><?=($row->url_1080p) ? "<a href=\"/" . $row->url_1080p . "\" target=\"_blank\">Yes</a>" : "No";?></td>
<td><?=($row->url_720p) ? "<a href=\"/" . $row->url_720p . "\" target=\"_blank\">Yes</a>" : "No";?></td>
<td><?=($row->url_480p) ? "<a href=\"/" . $row->url_480p . "\" target=\"_blank\">Yes</a>" : "No";?></td>
<td><?=($row->url_low) ? "<a href=\"/" . $row->url_low . "\" target=\"_blank\">Yes</a>" : "No";?></td>
<td><?=($row->url_180p) ? "<a href=\"/" . $row->url_180p . "\" target=\"_blank\">Yes</a>" : "No";?></td>
<td><?=($row->url_vtt) ? "Yes" : "No";?></td>
<td><?=$row->channel_name;?></td>
<td><?=$row->orig_filename;?></td>
<td><?=$row->base_filename;?><br /><a href="?a=VideoInfo&id=<?=$row->video_id;?>">[Info]</a></td>
<td><?=$row->orig_width . " x<br />" . $row->orig_height;?></td>
<td><?=$row->aspect_ratio;?></td>
<td><?=$row->fps;?></td>
<td><?=$row->orientation;?></td>
<td><?=$d;?></td>
<td><?=$row->user_name;?></td>
</tr>
<?php
            $rownum++;
        }
?>
</table>
<?php
    } else {
        echo "<p>There no videos to display at all.</p>";
    }
?>
</section>
<?php
    require_once("modal.php");
?>
</body></html>