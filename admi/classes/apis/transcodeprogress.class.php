<?php
    require_once(HOME_DIR . 'admi/classes/apis/apiaction.class.php');
    require_once(OBJECTS_PATH . 'video.class.php');

class TranscodeProgress extends ApiAction
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


        if($video->vars()->process_state == "transcoded") {
            $this->return_text = "Done";
            return false;
        }

        if($video->vars()->process_state == "processed") {
            $this->return_text = "DoneClip";
            return false;
        }

        $video_type = $video->vars()->transcoding;
        $progress = $video->vars()->progress;
        $start_time = $video->vars()->transcode_start;
        $time_elapsed = time() - $start_time;
        $seconds_left = 0;
        if($time_elapsed) {
            //get a rough estimate of the time remaining
            $speed = $progress / $time_elapsed;
            if($speed) {
                $seconds_left = (100 - $progress) / $speed;
                if($seconds_left < 0) {
                    $seconds_left = 0;
                }
                //round to the nearest second
                $seconds_left = round($seconds_left);
            }
        }
        $seconds_gone = round($time_elapsed);
        $this->return_text = $progress . "|" . $video_type . "|" . $seconds_left . "|" . $seconds_gone;
        return false;
    }
}