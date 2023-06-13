<?php
    require_once(HOME_DIR . 'admi/classes/apis/apiaction.class.php');
    require_once(OBJECTS_PATH . 'video.class.php');

class VideoUrl extends ApiAction
{
    public function process() : bool
    {
        if(!$id = $this->id) {
            $this->return_text = "Error - video ID not set";
            return false;
        }

        $video = new Video($id);
        if(!$video->video_id) {
            $this->return_text = "Error - video not found";
            return false;
        }

        if($this->type == "preview") {
            $videoUrl = $video->vars()->url_180p;
            $posterUrl = $video->vars()->url_thumbnail;
            $this->return_text = "OK|/$posterUrl|/$videoUrl";
            return false;
        }

        $url = $video->getHighestResolution();
        $this->return_text = "OK|/$url";
        return false;
    }
}