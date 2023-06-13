<?php
    require_once(HOME_DIR . 'admi/classes/apis/apiaction.class.php');
    require_once(OBJECTS_PATH . 'video.class.php');

class VideoMakeVTT extends ApiAction
{
    public function process() : bool
    {
        if(!$video_id = $this->id) {
            $this->return_text = "Error - video ID not set";
            return false;
        }

        $video = new Video($video_id);
        if(!$video->video_id) {
            $this->return_text = "Error - video not found";
            return false;
        }

        //make the preview thumbnail sprite image and vtt file
        if(!$video->sprite()) {
            $this->return_text = "Error - could not make sprite";
            return false;
        }
        $url = $video->vars()->url_vtt;
        $this->return_text = "OK|/$url";
        return false;
    }
}