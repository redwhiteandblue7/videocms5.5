</nav></header>
<section class="edit"><div>
<?php
    switch($this->action_status) {
        case "ok":
            echo "<p class=\"success\">Video imported successfully.</p>";
            break;
        case "vars_empty":
            echo "<p>Import new uploaded video:</p>";
            break;
        case "error":
            echo "<p class=\"error\">{$this->error}</p>";
            break;
        case "no_name":
            echo "<p class=\"error\">No filename specified</p>";
            break;
        case "no_dims":
            echo "<p class=\"error\">Width and height must be positive integers > 0</p>";
            break;
        case "no_fps":
            echo "<p class=\"error\">Frame rate must be a positive integer > 0</p>";
            break;
        case "no_rfps":
            echo "<p class=\"error\">Real frame rate must be a positive integer > 0</p>";
            break;
        case "no_duration":
            echo "<p class=\"error\">Duration must be a positive integer > 0</p>";
            break;
        default:
            echo "<p class=\"error\">Unknown error ($this->action_status)</p>";
            break;
    }

    $duration_text = (floor($this->post_object->duration / 60)) . "m " . ($this->post_object->duration % 60) . "s";
?>
<form name="import_form" method="post" action="?a=VideoImport">
Filename:<br />
<input type="text" name="import_filename" value="<?=$this->post_object->import_filename;?>" size="100" maxlength="255" /><br />
Video URL: <?=$this->post_object->video_url;?><input type="hidden" name="video_url" value="<?=$this->post_object->video_url;?>" />
<br /><br />
Duration: (<?=$duration_text;?>)<br />
<input type="text" name="duration" value="<?=$this->post_object->duration;?>" size="10" maxlength="10" /><br />
Frame rate:<br />
<input type="text" name="fps" value="<?=$this->post_object->fps;?>" size="10" maxlength="3" /><br />
Real frame rate:<br />
<input type="text" name="r_fps" value="<?=$this->post_object->r_fps;?>" size="10" maxlength="3" /><br />
Pixel Width:<br />
<input type="text" name="orig_width" value="<?=$this->post_object->orig_width;?>" size="10" maxlength="10" /><br />
Pixel Height:<br />
<input type="text" name="orig_height" value="<?=$this->post_object->orig_height;?>" size="10" maxlength="10" /><br />
Orientation:<br />
<select name="orientation">
<option value="landscape"<?=($this->post_object->orientation == "landscape" ? " selected=\"selected\"" : "");?>>Landscape</option>
<option value="portrait"<?=($this->post_object->orientation == "portrait" ? " selected=\"selected\"" : "");?>>Portrait</option>
</select><br />
<input type="submit" value="Import Video" />
<br /><br /><br />
<?php
    if(($this->post_object->import_filename ?? "") != "") {
        if((substr($this->post_object->import_filename, -4) == ".mp4") || (substr($this->post_object->import_filename, -4) == ".m4v") || (substr($this->post_object->import_filename, -4) == ".mov")) {
            echo "<video controls>\n<source src=\"{$this->post_object->video_url}\" type=\"video/mp4\">\nBrowser not HTML5</video>\n";
        } else {
            echo "<p>Video URL does not contain mp4 video type</p>\n";
        }
    }
?>
</form></div>
</section>
