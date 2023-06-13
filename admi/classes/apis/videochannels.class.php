<?php
    require_once(HOME_DIR . 'admi/classes/apis/apiaction.class.php');
    require_once(OBJECTS_PATH . 'video.class.php');

//class to get the row from the database for the video id passed in and return as a JSON object
class VideoChannels extends ApiAction
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

        $channels = $video->getChannels();
        if(!$channels) {
            $this->return_text = "Error - no channels found";
            return false;
        }

        $this->return_text = "OK";
        foreach($channels as $channel) {
            $this->return_text .= "|" . $channel->channel_id . "," . $channel->channel_name;
        }
        return false;
    }
}