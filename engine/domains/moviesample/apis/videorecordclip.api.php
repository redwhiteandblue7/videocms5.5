<?php
    require_once(INCLUDE_PATH . "domains/moviesample/apis/default.api.php");
    require_once(OBJECTS_PATH . "video.class.php");

class VideoRecordClipApi extends DefaultApi
{
    protected $return_text = "Error|video_clip";

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

        //first make a thumbnail from the poster we just made
        $video->thumbnailFromPoster();
        //then start making the preview clip
        if(!$video->preview($time)) {
            $this->return_text = "Error|preview_error";
            return false;
        }
        $this->return_text = "OK";
        return false;

    }
}
?>