<?php
    require_once(INCLUDE_PATH . "domains/moviesample/apis/default.api.php");
    require_once(OBJECTS_PATH . "post.class.php");
    require_once(OBJECTS_PATH . "video.class.php");

class VideoUnhideApi extends DefaultApi
{
    protected $return_text = "Error|video_unhide";

    public function process()
    {
        if(!$this->user->logged_in) {
            $this->return_text = "Error|not_logged_in";
            return;
        }

        if(!isset($_POST["post_id"]) || !is_numeric($_POST["post_id"])) {
            $this->return_text = "Error|post_id";
            return;
        }

        $post = new Post($_POST["post_id"]);
        if(!$post->post_id) {
            $this->return_text = "Error|no_post";
            return;
        }

        if($post->vars()->user_id != $this->user->user_id) {
            $this->return_text = "Error|invalid_user";
            return;
        }

        $post->unhide();
        $this->return_text = "OK";
        return;
    }
}
?>