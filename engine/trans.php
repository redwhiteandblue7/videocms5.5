<?php
    session_start();

	ini_set('display_errors', '1');
	error_reporting(E_ALL);
    
    $t = date("Y-m-d H:i:s");
    $rd = __DIR__;
    //open a log file in the /home/ folder
    $log = fopen("$rd/../transcode.log", "a");
    fwrite($log, "Starting transcode $t\n");

    require_once($rd . '/../admi/ndefines.php');
    require_once(OBJECTS_PATH . 'video.class.php');

    stream_set_blocking(STDIN, false);
    stream_set_blocking(STDOUT, false);
    stream_set_blocking(STDERR, false);

    if($argc < 3) {
        echo "Error - not enough arguments";
        //log the error
        fwrite($log, "Error - not enough arguments\n");
        fclose($log);
        exit();
    }

    if(!empty($argv[1])) {
        parse_str($argv[1], $_GET);
    }

    if(!empty($argv[2])) {
        parse_str($argv[2], $_SESSION);
    }
    
    if(!empty($_GET["id"]) && is_numeric($_GET["id"])) {
        $id = $_GET["id"];
    } else {
        echo "Error - no video ID";
        //log the error
        fwrite($log, "Error - no video ID\n");
        fclose($log);
        exit();
    }

    fwrite($log, "Creating video object with video id $id, domain prefix " . $_SESSION["domain_prefix"] . "\n");
    $video = new Video($id);
    if(!$video->video_id) {
        //video id was not found
        echo "Error - video not found";
        //log the error
        fwrite($log, "Error - video not found\n");
        fclose($log);
        exit();
    }

    if($video->vars()->transcoding == "180p") {
        //if we are transcoding the 180p preview then we are at the processing stage
        $video->setState("processing");
    } else {
        //otherwise we are at the transcoding stage
        $video->setState("transcoding");
    }

    //log the start of the transcode
    fwrite($log, "Setting transcode state for video " . $video->video_id . " to " . $video->vars()->process_state . " at " . $video->vars()->transcoding . "\n");
    fclose($log);

    //lastinputtime is used to check if the transcode has stalled (if there is no input for 5 seconds)
    $lastInputTime = time();
    //if duration parameter is set then use that, otherwise use the duration from the video object
    if(isset($_GET["dur"]) && is_numeric($_GET["dur"])) {
        $duration = $_GET["dur"];
    } else {
        $duration = $video->vars()->duration;
    }
    $fps = $video->vars()->r_fps;
    $num_of_frames = $duration * $fps;

    while($lastInputTime > time() - 5)
    {
        $line = fgets(STDIN);

        if(feof(STDIN)) {
            break;
        }
        
        if($line !== false) {
            $lastInputTime = time();

            if((strpos($line, "video:")) === 0) {
                //this only appears when the video is done transcoding
                $video->setProgress(100);
            } elseif (($fr = strpos($line, "frame=")) !== false) {
                $fr_line = substr($line, $fr);
                $fr_parts = explode("f", $fr_line);
                $fr_bits = explode("=", $fr_parts[1]);
                $frame = trim($fr_bits[1]);
                $progress = ($frame * 100) / $num_of_frames;
                $video->setProgress($progress);
            }
        }
    }

    $video->transcodeFinished();
    //if we were making a preview, then we are done
    if($video->vars()->process_state == "processing") {
        $video->setState("processed");
        exit();
    }

    //If there is another resolution to transcode, start it
    if(!$video->transcode()) {
        //If not then we have finished transcoding all resolutions
        if($video->error_type == "no_transcode") {
            $video->setState("transcoded");
        }
    }
    exit();
?>