<?php
    require_once(HOME_DIR . 'admi/classes/actions/displayaction.class.php');
	require_once(OBJECTS_PATH . 'video.class.php');

class ShowUploadsAction extends DisplayAction
{
    private $video_uploads = [];
    public $remember_me = true;
    public $name = "Uploads";
    
    protected function sortby()
    {
        if(isset($this->get_object->sortBy)) {
            $_SESSION["showuploads_sortby"] = $this->get_object->sortBy;
        }

        if(isset($_SESSION["showuploads_sortby"])) {
            $this->sort_by = $_SESSION["showuploads_sortby"];
        }
    }

    public function prerender() : void
    {
        include "templates/videos_template.php";
    }

    public function render() : void
    {
        $video = new Video();
        $this->video_uploads = $video->uploads(100);
        include("templates/actions/showuploads_template.php");
    }
}