<?php
    require_once(HOME_DIR . 'admi/classes/apis/apiaction.class.php');
    require_once(OBJECTS_PATH . 'video.class.php');

class VideoSavePreview extends ApiAction
{
    public function process() : bool
    {
        if(!$video_id = $this->id) {
            $this->return_text = "Error - video ID not set";
            return false;
        }

        if(!isset($_GET["time"]) || !is_numeric($_GET["time"])) {
            $this->return_text = "Error - video time not set";
            return false;
        }
        $time = $_GET["time"];

        $video = new Video($video_id);
        if(!$video->video_id) {
            $this->return_text = "Error - video not found";
            return false;
        }

        $fps = $video->vars()->fps;
        $frame_time = 1 / $fps;
        //subtract some time from the video element time to get the time to seek for ffmpeg
        //Do not know why this needs to be done but it does!!!
        $time = $time - ($frame_time * 1.6);

        //make the video poster at the time specified and return the url
        $url = $video->poster($time);
        $this->return_text = "OK|/$url";
        return false;
    }
}