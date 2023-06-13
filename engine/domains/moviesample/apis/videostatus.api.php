<?php
    require_once(INCLUDE_PATH . "domains/moviesample/apis/default.api.php");
    require_once(OBJECTS_PATH . "video.class.php");

class VideoStatusApi extends DefaultApi
{
    protected $return_text = "Error|video_status";

    public function process()
    {
        if(!$this->user->logged_in) {
            $this->return_text = "Error|not_logged_in";
            return;
        }

        if(!isset($_POST["video_id"])) {
            $this->return_text = "Error|video_id";
            return;
        }

        if(!is_numeric($_POST["video_id"])) {
            $this->return_text = "Error|video_id";
            return;
        }

        $video = new Video($_POST["video_id"]);
        if(!$video->video_id) {
            $this->return_text = "Error|no_video";
            return;
        }

        if($video->vars()->user_id != $this->user->user_id) {
            $this->return_text = "Error|invalid_user";
            return;
        }

        $process_state = $video->vars()->process_state;
        $video_type = $video->vars()->transcoding;
        $progress = $video->vars()->progress;
        $this->return_text = $process_state . "|" . $progress . "|" . $video_type;
        if($process_state == "processing" || $process_state == "transcoding") {
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
            $this->return_text .= "|" . $seconds_left . "|" . $seconds_gone;
        }
    }
}
?>