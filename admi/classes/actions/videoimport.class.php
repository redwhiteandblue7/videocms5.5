<?php
    require_once(HOME_DIR . 'admi/classes/actions/editaction.class.php');
    require_once(OBJECTS_PATH . '/video.class.php');

class VideoImportAction extends EditAction
{
    private $error;

    public $name = "Import Video";

    public function process() : bool
    {
        $video = new Video();

        if(isset($_POST["import_filename"])) {
            $video->import($this->post_object);
            if($video->error_type) {
                $this->action_status = $video->error_type;
                $this->error = $video->error_message;
                return false;
            }
            $this->action_status = "imported";
            return true;
        }

        $this->action_status = "vars_empty";

        $this->post_object->import_filename = "";
        $this->post_object->video_url = "";

        if(isset($_GET["name"])) {
            $this->post_object = $video->probe($_GET["name"]);
            if(!$video->error_type) {
                return false;
            }
            $this->action_status = $video->error_type;
            $this->error = $video->error_message;
        }
        $this->post_object->duration = 0;
        $this->post_object->orig_height = 0;
        $this->post_object->orig_width = 0;
        $this->post_object->fps = 0;
        $this->post_object->r_fps = 0;
        $this->post_object->orientation = "landscape";
        return false;
    }

    public function prerender() : void
    {
        include "templates/videos_template.php";
    }

    public function render() : void
    {
        include "templates/actions/videoimport_template.php";
    }
}
?>