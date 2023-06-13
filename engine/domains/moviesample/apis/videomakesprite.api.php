<?php
    require_once(INCLUDE_PATH . "domains/moviesample/apis/default.api.php");
    require_once(OBJECTS_PATH . "video.class.php");

class VideoMakeSpriteApi extends DefaultApi
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

        $video = new Video($_POST["video_id"]);
        if(!$video->video_id) {
            $this->return_text = "Error|no_video";
            return;
        }

        if($video->vars()->user_id != $this->user->user_id) {
            $this->return_text = "Error|invalid_user";
            return;
        }

        //make the preview thumbnail sprite image and vtt file
        if(!$video->sprite()) {
            $this->return_text = "Error|sprite_error";
            return false;
        }
        $url = $video->vars()->url_vtt;
        $this->return_text = "OK|/$url";
        return false;
    }
}
?>