<?php
    require_once(INCLUDE_PATH . "domains/moviesample/apis/default.api.php");
    require_once(OBJECTS_PATH . "video.class.php");

class VideoDataApi extends DefaultApi
{
    protected $return_text = "Error|video_data";

    public function process()
    {
        if(!$this->user->logged_in) {
            $this->return_text = "Error|not_logged_in";
            return;
        }

        if(!isset($_POST["video_id"])) {
            $this->return_text = "Error|video_id";
            return;
        }

        if(!is_numeric($_POST["video_id"])) {
            $this->return_text = "Error|video_id";
            return;
        }

        $video = new Video($_POST["video_id"]);
        if(!$video->video_id) {
            $this->return_text = "Error|no_video";
            return;
        }

        if($video->vars()->user_id != $this->user->user_id) {
            $this->return_text = "Error|invalid_user";
            return;
        }
        $res = $video->getHighestResolution();
        $this->return_text = "OK|$res|" . $video->vars()->fps . "|" . $video->vars()->duration . "|" . $video->vars()->orig_width . "|" . $video->vars()->orig_height;
    }
}
?>