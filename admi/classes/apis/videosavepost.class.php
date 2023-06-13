<?php
    require_once(HOME_DIR . 'admi/classes/apis/apiaction.class.php');
    require_once(OBJECTS_PATH . 'video.class.php');
    require_once(OBJECTS_PATH . 'post.class.php');

class VideoSavePost extends ApiAction
{
    public function process() : bool
    {
        $videoObj = json_decode($_POST["x"], false);

        $video = new Video($videoObj->id);
        if(!$video->video_id) {
            $this->return_text = "Error - video not found";
            return false;
        }

        $post = new Post();
        $vars = new stdClass();
        $vars->video_id = $video->vars()->video_id;
        $vars->title = $videoObj->title;
        $vars->alt_title = $this->mod->processAltText($videoObj->title, $videoObj->description);
        $vars->description = $this->mod->processPostDescription($videoObj->description);
        $vars->id = 0;
        $vars->post_id = 0;
        $vars->user_id = $video->vars()->user_id;
        $vars->site_id = 0;
        $vars->display_state = "display";
        $vars->link_type = "dofollow";
        $vars->time_visible = time();
        $vars->time_added = time();
        $vars->priority = 1;
        $vars->ranking = 1;

        $vars->thumb_url = "/" . $video->vars()->url_thumbnail;
        $vars->video_url = "/" . $video->getHighestResolution();
        $vars->duration = $video->vars()->duration;
        $vars->orig_thumb = "/" . $video->vars()->url_poster;
        $vars->orig_width = $video->vars()->orig_width;
        $vars->orig_height = $video->vars()->orig_height;
        $vars->post_type = "video";

        $post->save($vars);
        if($post->error_type) {
            $this->return_text = "Error - " . $post->error_type;
            return false;
        }

        // Post was saved, so update the video record
        $video->vars()->channel_id = $videoObj->channel;
        $video->vars()->process_state = "posted";
        $video->save();
        $this->return_text = "OK";
        return false;
    }
}