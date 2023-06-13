<?php
    require_once(HOME_DIR . 'admi/classes/actions/editaction.class.php');
    require_once(INCLUDE_PATH . "objects/post.class.php");
    require_once(INCLUDE_PATH . "objects/video.class.php");
    require_once(INCLUDE_PATH . "objects/domain.class.php");

class PostEditAction extends EditAction
{
    public $name = "Edit Post";

    public function process() : bool
    {
        //Get the post data for the post id if it exists
        $id = $this->id;
        $post = new Post($id);
        if(isset($this->get_object->video_id)) {
            $video = new Video($this->get_object->video_id);    //video_id is the id of the video object NOT video_id
            $this->post_object->thumb_url = "/" . $video->vars()->url_thumbnail;
            $this->post_object->video_url = "/" . $video->getHighestResolution();
            $this->post_object->duration = $video->vars()->duration;
            $this->post_object->orig_thumb = "/" . $video->vars()->url_poster;
            $this->post_object->orig_width = $video->vars()->orig_width;
            $this->post_object->orig_height = $video->vars()->orig_height;
            $this->post_object->post_type = "video";
            $this->post_object->video_id = $video->vars()->video_id;
        }
        //If the form hasn't been submitted then just set up the post array and return
        if(!(isset($_POST["post_id"]))) {
            $this->action_status = "vars_empty";
            if($id) {
                $vars = $post->vars();
                if(isset($vars->post_id)) {
                    $this->post_object = $vars;
                    return false;
                }
                //If we get here then the post id was invalid
                $this->action_status = "not_found";
            }
            //If there's no id then we're creating a new post
            $this->post_object->post_id = 0;
            $this->post_object->id=0;
            $this->post_object->site_id = 0;
            $this->post_object->display_state = "display";
            $this->post_object->link_type = "dofollow";
            $this->post_object->time_added = time();
            return false;
        }

        //If we get here then the form has been submitted
        //Convert the posted time dropdowns into a time_visible timestamp and unset the dropdowns
		$this->post_object->time_visible = mktime($this->post_object->vf_hours, 0, 0, $this->post_object->vf_month, $this->post_object->vf_date, $this->post_object->vf_year);
        unset($this->post_object->vf_hours);
        unset($this->post_object->vf_month);
        unset($this->post_object->vf_date);
        unset($this->post_object->vf_year);
        //If the update time check box is checked then set the time_updated timestamp to now and unset the checkbox
		if(isset($this->post_object->update_time)) {
            $this->post_object->time_updated = time();
            unset($this->post_object->update_time);
        } else {
            unset($this->post_object->time_updated);
        }
        unset($this->post_object->preserve_domain_id);

        //now we are ready to save the post
        $post->save($this->post_object);

        //If there was an error saving the post then set the action status and return
        if($post->error_type) {
            $this->action_status = $post->error_type;
            return false;
        }

        $this->action_status = "ok";
        return true;
    }

    public function prerender() : void
    {
        include "templates/posts_template.php";
    }

    public function render() : void
    {
        $post = new Post();
        $domain = new Domain();
        include "templates/actions/postedit_template.php";
    }
}