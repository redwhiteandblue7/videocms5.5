<?php
//Output from the ffmpeg tiling process is piped to this script which will update the database when the tiling is finished
    session_start();

	ini_set('display_errors', '1');
	error_reporting(E_ALL);
    
    $t = date("Y-m-d H:i:s");
    $rd = __DIR__;
    //open a log file in the /home/ folder
    $log = fopen("$rd/../transcode.log", "a");
    fwrite($log, "Starting sprite tile $t\n");

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

    //set the state of the video to tiling
    $video->setState("tiling");

    //log the start of the transcode
    fwrite($log, "Setting tiling state for video " . $video->video_id . "\n");
    fclose($log);

    //lastinputtime is used to check if the tiling has stalled (if there is no input for 5 seconds)
    $lastInputTime = time();

    while($lastInputTime > time() - 5)
    {
        $line = fgets(STDIN);

        if(feof(STDIN)) {
            break;
        }
        
        if($line !== false) {
            $lastInputTime = time();
        }
    }

    //Tiling is finished so update the database
    $video->setState("ready");
    exit();
?>