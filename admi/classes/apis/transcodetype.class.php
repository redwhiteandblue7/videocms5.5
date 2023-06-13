<?php
    require_once(HOME_DIR . 'admi/classes/apis/apiaction.class.php');
    require_once(OBJECTS_PATH . 'video.class.php');

class TranscodeType extends ApiAction
{
    public function process() : bool
    {
        if(!$video_id = $this->id) {
            $this->return_text = "Error - videoID not set";
            return false;
        }

        $video = new Video($video_id);
        if(!$video->video_id) {
            $this->return_text = "Error - video not found";
            return false;
        }

        $type = $video->getNextTranscodeSize();
        if(!$type) {
            $video->setState("transcoded");
        }
        $this->return_text = "OK|" . $type;
        return false;
    }
}