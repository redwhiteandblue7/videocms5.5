<?php
    require_once(HOME_DIR . 'admi/classes/apis/apiaction.class.php');
    require_once(OBJECTS_PATH . 'video.class.php');

//class to get the row from the database for the video id passed in and return as a JSON object
class VideoImport extends ApiAction
{
    public function process() : bool
    {
        $video = new Video();

        if(!isset($_GET['filename']) || $_GET['filename'] == "") {
            $this->return_text = "Error - filename not set";
            return false;
        }
        if(isset($_GET['allow_skip']) && $_GET['allow_skip'] == "true") {
            $allow_skip = true;
        }

        $filename = $_GET['filename'];
        $upload = $video->probe($filename);
        if($video->error_type) {
            $this->return_text = "Error - " . $video->error_message;
            return false;
        }
        $upload->allow_skip = $allow_skip ?? false;
        $video->import($upload);
        if($video->error_type) {
            $this->return_text = "Error - " . $video->error_type;
            return false;
        }

        $this->return_text = "OK";
        return false;
    }
}