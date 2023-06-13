<?php
    require_once(HOME_DIR . 'admi/classes/apis/apiaction.class.php');
    require_once(OBJECTS_PATH . 'video.class.php');

//class to get the row from the database for the video id passed in and return as a JSON object
class VideoUploads extends ApiAction
{
    public function process() : bool
    {
        $video = new Video();

        $uploads = $video->uploads();
        if(!$uploads) {
            $this->return_text = "Error - no uploads found";
            return false;
        }

        $this->return_text = "OK";
        foreach($uploads as $upload) {
            $this->return_text .= "|" . $upload["filename"];
        }
        return false;
    }
}