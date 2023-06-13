<?php
    require_once(HOME_DIR . 'admi/classes/apis/apiaction.class.php');
    require_once(OBJECTS_PATH . 'video.class.php');

class VideoMakePreview extends ApiAction
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

        //first make a thumbnail from the poster we just made
        $video->thumbnailFromPoster();
        //then start making the preview clip
        if(!$video->preview($time)) {
            $this->return_text = "Error - could not make preview";
            return false;
        }
        $this->return_text = "OK";
        return false;
    }
}