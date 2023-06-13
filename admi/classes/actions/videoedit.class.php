<?php
    require_once(HOME_DIR . 'admi/classes/actions/editaction.class.php');
    require_once(INCLUDE_PATH . "objects/video.class.php");

class VideoEditAction extends EditAction
{
    public $name = "Edit Video";

    public function process() : bool
    {
        //Get the video data for the video id if it exists
        $id = $this->id;
        $video = new Video($id);
        //If the form hasn't been submitted then just set up the video array and return
        if(!(isset($_POST["video_id"]))) {
            $this->action_status = "vars_empty";
            if($id) {
                $vars = $video->vars();
                if(isset($vars->video_id)) {
                    $this->post_object = $vars;
                    return false;
                }
                //If we get here then the video id was invalid
                $this->action_status = "not_found";
            }
            //If there's no id then we're creating a new video
            $this->post_object->video_id = 0;
            $this->post_object->id=0;
            $this->post_object->duration = 0;
            $this->post_object->orig_height = 0;
            $this->post_object->orig_width = 0;
            $this->post_object->fps = 0;
            $this->post_object->progress = 0;
            $this->post_object->process_state = "unknown";
            $this->post_object->transcoding = "none";
            return false;
        }

        //If we get here then the form has been submitted
        $video->save($this->post_object);

        //If there was an error saving the video then set the action status and return
        if($video->error_type) {
            $this->action_status = $video->error_type;
            return false;
        }

        //If we get here then the video was saved successfully
        $this->action_status = "video_saved";
        return true;
    }

    public function prerender() : void
    {
        include "templates/videos_template.php";
    }

    public function render() : void
    {
        $video = new Video($this->id);
        include "templates/actions/videoedit_template.php";
    }
}