<?php
    require_once(HOME_DIR . 'admi/classes/actions/displayaction.class.php');
	require_once(OBJECTS_PATH . 'video.class.php');

class ShowVideosAction extends DisplayAction
{
    private $num_of_videos = 0;
    public $remember_me = true;
    public $name = "Videos";
    
    protected function sortby()
    {
        if(isset($this->get_object->sortBy)) {
            $_SESSION["showvideos_sortby"] = $this->get_object->sortBy;
        }

        if(isset($_SESSION["showvideos_sortby"])) {
            $this->sort_by = $_SESSION["showvideos_sortby"];
        }
    }

    public function prerender() : void
    {
        include "templates/videos_template.php";
    }

    public function render() : void
    {
        $video = new Video();
        $this->num_of_videos = $video->videos(0, 100);
        include("templates/actions/showvideos_template.php");
    }
}