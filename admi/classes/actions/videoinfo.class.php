<?php
    require_once(HOME_DIR . 'admi/classes/actions/editaction.class.php');
    require_once(OBJECTS_PATH . "video.class.php");
    require_once(OBJECTS_PATH . "domain.class.php");

class VideoInfoAction extends EditAction
{
    public $name = "Edit Video";

    public function process() : bool
    {
        return false;
    }

    public function prerender() : void
    {
        include "templates/videos_template.php";
    }

    public function render() : void
    {
        $video = new Video($this->id);
        $domain = new Domain();
        include "templates/actions/videoinfo_template.php";
    }
}