<?php
    require_once(HOME_DIR . 'admi/classes/apis/apiaction.class.php');
    require_once(OBJECTS_PATH . 'video.class.php');

class TranscodeStart extends ApiAction
{
    public function process() : bool
    {
        if(!$video_id = $this->id) {
            $this->return_text = "Error - video ID not set";
            return false;
        }

        $video = new Video($video_id);
        if(!$video->video_id) {
            $this->return_text = "Error - video not found";
            return false;
        }

        $video->transcode();
        if($video->error_type) {
            switch($video->error_type) {
                case "already_transcoding":
                    $this->return_text = "Error - transcoding is already in progress";
                    break;
                case "no_transcode":
                    $this->return_text = "Error - no transcode needed";
                    break;
                case "error":
                    $this->return_text = "Error - " . $video->error_message;
                    break;
                default:
                    $this->return_text = "Error - unknown error";
                    break;
            }
            return false;
        }

        $this->return_text = "OK";
        return false;
    }
}