<?php
    require_once(INCLUDE_PATH . "domains/moviesample/apis/default.api.php");
    require_once(OBJECTS_PATH . "video.class.php");

class VideoSavePreviewApi extends DefaultApi
{
    protected $return_text = "Error|video_preview";

    public function process()
    {
        if(!$this->user->logged_in) {
            $this->return_text = "Error|not_logged_in";
            return;
        }

        if(!isset($_POST["video_id"]) || !is_numeric($_POST["video_id"])) {
            $this->return_text = "Error|video_id";
            return;
        }

        if(!isset($_POST["time"]) || !is_numeric($_POST["time"])) {
            $this->return_text = "Error|no_time";
            return;
        }
        $time = $_POST["time"];

        $video = new Video($_POST["video_id"]);
        if(!$video->video_id) {
            $this->return_text = "Error|no_video";
            return;
        }

        if($video->vars()->user_id != $this->user->user_id) {
            $this->return_text = "Error|invalid_user";
            return;
        }

        $fps = $video->vars()->fps;
        $frame_time = 1 / $fps;
        //subtract some time from the video element time to get the time to seek for ffmpeg
        //Do not know why this needs to be done but it does!!!
        $time = $time - ($frame_time * 1.6);

        //make the video poster at the time specified and return the url
        $url = $video->poster($time);
        $this->return_text = "OK|/$url";
        return;

    }
}
?>