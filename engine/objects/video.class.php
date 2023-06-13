<?php

//the video class
//for performing operations with the video table data
    require_once(DB_PATH . 'videos.db.class.php');
    require_once(OBJECTS_PATH . 'domain.class.php');
    require_once(OBJECTS_PATH . 'user.class.php');

class Video
{
    private $dbo;
    private $row = stdClass::class;
    private $videos = [];
    private $result_pointer = 0;

    public $orig_filename;
    public $video_id = 0;
    public $error_message;
    public $error_type;

    //construct with a video id to populate video data from database or 0 to create empty video object
    public function __construct(int $video_id = 0)
    {
        $this->dbo = new VideosDB();
        $this->dbo->setPrefix();

        if($video_id) {
            if($this->row = $this->dbo->fetchVideo($video_id)) {
                $this->orig_filename = $this->row->orig_filename;
                $this->video_id = $video_id;
            }
        }
    }

    public function videos(int $start = 0, int $limit = 99999, int $user_id = 0, int $channel_id = 0, string $process_state = "") : int
    {
        $num_of_videos = $this->dbo->fetchVideos($start, $limit, $user_id, $channel_id, $process_state);
        $this->videos = $this->dbo->results();
        $this->result_pointer = 0;
        return $num_of_videos;
    }

    public function next()
    {
        if($this->result_pointer < sizeof($this->videos)) {
            $row = $this->videos[$this->result_pointer++];
            $this->row = $row;
            $this->orig_filename = $this->row->orig_filename;
            $this->video_id = $this->row->video_id;
            $row->aspect_ratio = $this->aspectRatio($row->orig_width, $row->orig_height);
            return $row;
        }

        return "";
    }

    public function delete()
    {
        if(!$this->video_id) return;
        if($this->row->process_state == "processing") return;
        if($this->row->url_1080p) {
            $this->deleteFile($this->row->url_1080p);
        }
        if($this->row->url_720p) {
            $this->deleteFile($this->row->url_720p);
        }
        if($this->row->url_480p) {
            $this->deleteFile($this->row->url_480p);
        }
        if($this->row->url_180p) {
            $this->deleteFile($this->row->url_180p);
        }
        if($this->row->url_low) {
            $this->deleteFile($this->row->url_low);
        }
        if($this->row->url_poster) {
            $this->deleteFile($this->row->url_poster);
        }
        if($this->row->url_thumbnail) {
            $this->deleteFile($this->row->url_thumbnail);
        }
        if($this->row->url_vtt) {
            $this->deleteFile($this->row->url_vtt);
            $sprite = str_replace("sprite.vtt", "sprite.jpg", $this->row->url_vtt);
            $this->deleteFile($sprite);
        }
        $orig = $this->row->base_url . $this->row->base_filename;
        $this->deleteFile($orig);
        $this->dbo->deleteRow("videos", "id", $this->row->id, true);
        $this->video_id = 0;
        $this->row = stdClass::class;
    }

    private function deleteFile(string $filename)
    {
		$domain = new Domain();
        $path = $domain->vars()->public_path . $filename;
        if(file_exists($path)) {
            unlink($path);
        }
    }

    //return the path to the uploads folder for the current user and create it if it doesn't exist
    public function uploadsFolder()
    {
		$domain = new Domain();
        $user = new User();
        $folder = $user->username . "/";
        $domain->checkFolderExists($folder);
        $folder .= "uploads/";
        $domain->checkFolderExists($folder);
        return $domain->assetPath() . $folder;
    }

    //return the path to the video folder for the current user and create it if it doesn't exist
    public function videoFolder()
    {
        $domain = new Domain();
        $user = new User();
        $folder = $user->username . "/";
        $domain->checkFolderExists($folder);
        $folder .= "imported/";
        $domain->checkFolderExists($folder);
        return $domain->assetPath() . $folder;
    }

    public function uploads(int $limit = 99) : array
    {
        $video_uploads = [];

        $path = $this->uploadsFolder();
        $handle = opendir($path);
        $files = array();
        while(($file = readdir($handle)) !== false) {
            if((fnmatch("*.mp4", $file) || 
                (fnmatch("*.webm", $file)) || 
                (fnmatch("*.mov", $file)) || 
                (fnmatch("*.wmv", $file)) || 
                (fnmatch("*.avi", $file)) || 
                (fnmatch("*.mkv", $file)))) {
                $t = filemtime($path . $file);
                $s = filesize($path . $file);
                $files[] = ["filename"=>$file, "time"=>$t, "size"=>$s];
            }
        }

        usort($files, fn($a, $b) => $b["time"] <=> $a["time"]);

        $i = $limit;
        foreach($files as $f) {
            $video_uploads[] = $f;
            if(--$i == 0) break;
        }

        return $video_uploads;
    }

    /** Get all the information we can about the video before importing it, using ffprobe
     * @param filename name of the video file - it should be found in the user's uploads folder
     * @return stdClass object with the following properties:
     * import_filename - the name of the file to import
     * duration - the duration of the video in seconds
     * orig_width - the width of the video in pixels
     * orig_height - the height of the video in pixels
     * fps - the average frame rate of the video
     * r_fps - the real frame rate of the video
     * orientation - the orientation of the video, either landscape or portrait
     * video_url - the url of the video (temporary until the video is imported)
     */
    public function probe(string $filename) : stdClass
    {
        $vars = new stdClass();
        $domain = new Domain();
        $user = new User();

        $vars->import_filename = $filename;
        $vars->video_url = "/" . $domain->assetFolder() . $user->username . "/uploads/" . $vars->import_filename;
        $import_path = $domain->assetPath() . $user->username . "/uploads/" . $vars->import_filename;

        //first we will check the file for errors
        //create a temporary copy of the file
        //get the file extension
        $ext = substr($filename, strrpos($filename, ".") + 1);
        $output_path = str_replace("." . $ext, "_temp." . $ext, $import_path);
        $ff = "ffmpeg -hide_banner -err_detect explode -i $import_path -y -c:v copy -c:a copy $output_path";
        $res = shell_exec("$ff 2>&1");
        //now delete the temporary file
        unlink($output_path);

        if(strpos($res, "Error while decoding stream") !== false || 
            strpos($res, "Invalid data found when processing input") !== false ||
            strpos($res, "corrupt decoded frame in stream") !== false ||
            strpos($res, "corrupt input packet in stream") !== false ||
            strpos($res, "Invalid argument") !== false) {
            $this->error_type = "error";
            $this->error_message = "The video file is corrupt and cannot be imported.";
            return $vars;
        }

        $vars->duration = 0;
        $ff = "ffprobe -v error -select_streams v:0 -show_entries format=duration -of default=noprint_wrappers=1:nokey=1 $import_path";
        $dur = trim(shell_exec("$ff 2>&1"));
        if(is_numeric($dur)) $vars->duration = round($dur);
        $vars->orig_width = 0;
        $vars->orig_height = 0;
        $ff = "ffprobe -v error -select_streams v:0 -show_entries stream=width,height -of csv=p=0:s=x $import_path";
        $res = shell_exec("$ff 2>&1");
        $r = explode("x", $res);
        if(count($r) == 2) {
            $vars->orig_height = trim($r[1]);
            $vars->orig_width = trim($r[0]);
        }

        //get the frame rate, we need both the average and the real frame rate
        $vars->fps = 0;
        $ff = "ffprobe -v error -of csv=p=0 -select_streams v:0 -show_entries stream=avg_frame_rate $import_path";
        $fr = shell_exec("$ff 2>&1");
        $f = explode("/", $fr);
        if(is_numeric(trim($f[0]))) $vars->fps = trim($f[0]) / trim($f[1]);
        $vars->r_fps = 0;
        $ff = "ffprobe -v error -of csv=p=0 -select_streams v:0 -show_entries stream=r_frame_rate $import_path";
        $fr = shell_exec("$ff 2>&1");
        $f = explode("/", $fr);
        if(is_numeric(trim($f[0]))) $vars->r_fps = trim($f[0]) / trim($f[1]);

        //we can't tell the orientation by looking at the width and height so we'll use ffprobe again to get this
        $ff = "ffprobe -v error -of csv=p=0 -select_streams v:0 -show_entries stream_tags=rotate $import_path";
        $rotate = trim(shell_exec("$ff 2>&1"));
        if($rotate == "0") {
            $vars->orientation = "landscape";
        } else if($rotate == "90") {
            $vars->orientation = "portrait";
        } else if($rotate == "180") {
            $vars->orientation = "landscape";
        } else if($rotate == "270") {
            $vars->orientation = "portrait";
        } else {
            $vars->orientation = "landscape";
        }

        return $vars;
    }

    public function import(stdClass $vars)
    {
        $domain = new Domain();
        $user = new User();

        $vars->user_id = $user->user_id;
        $vars->username = $user->username;
        $vars->asset_path = $domain->assetPath();
        $vars->asset_url = $domain->assetFolder();

		if(!isset($vars->import_filename) || !$vars->import_filename) {
			$this->error_type = "no_name";
            return;
        }
        if(!is_numeric($vars->duration) || $vars->duration < 1) {
            $this->error_type = "no_duration";
            return;
        }
        if(!is_numeric($vars->orig_width) || $vars->orig_width < 1 || !is_numeric($vars->orig_height) || $vars->orig_height < 1) {
            $this->error_type = "no_dims";
            return;
        }
        if(!is_numeric($vars->fps) || $vars->fps < 1) {
            $this->error_type = "no_fps";
            return;
        }
        if(!is_numeric($vars->r_fps) || $vars->r_fps < 1) {
            $this->error_type = "no_rfps";
            return;
        }

        if($this->error_message = $this->dbo->importVideo($vars)) {
            $this->error_type = "error";
        }

        //the video import was successful, but the vars object will not have complete information and we need this to start a transcode
        //so we will get the id and then fetch the full video object
        $id = $this->dbo->getInsertId();
        $this->row = $this->dbo->fetchRow("videos", "id", $id, true);
        $this->video_id = $this->row->video_id;
        //now let's get a thumbnail image so users can tell one video from another
        $this->thumbnail();
    }

    public function save(stdClass $vars = null) : bool
    {
        if(!$vars) $vars = $this->row;

        if(!isset($vars->orig_filename) || !$vars->orig_filename) {
            $this->error_type = "no_name";
            return false;
        }
        if(!isset($vars->base_filename) || !$vars->base_filename) {
            $this->error_type = "no_base";
            return false;
        }
        if(!isset($vars->base_url) || !$vars->base_url) {
            $this->error_type = "no_url";
            return false;
        }
        if(!is_numeric($vars->duration) || $vars->duration < 1) {
            $this->error_type = "no_duration";
            return false;
        }
        if(!is_numeric($vars->orig_width) || $vars->orig_width < 1 || !is_numeric($vars->orig_height) || $vars->orig_height < 1) {
            $this->error_type = "no_dims";
            return false;
        }
        if(!is_numeric($vars->fps) || $vars->fps < 1) {
            $this->error_type = "no_fps";
            return false;
        }

        $this->row = $vars;
        unset($vars->channel_name);
        unset($vars->user_name);

        if(isset($vars->id) && $vars->id > 0) {
            $this->dbo->updateRow("videos", $vars, true);
            return true;
        } else {
            if($this->dbo->insertRow("videos", $vars, "orig_filename", true)) {
                $this->row->id = $this->dbo->getInsertId();
                return true;
            }
        }

        $this->error_type = "video_exists";
        return false;
    }

    public function vars()
    {
        if(isset($this->row->video_id)) {
            return $this->row;
        }
        return "";
    }

    public function numRows() : int
    {
        return sizeof($this->videos);
    }

    /** This will start a transcode into the next resolution that does not yet exist if there is one 
    * @return bool true if a transcode was started, false if not
    */
    public function transcode() : bool
    {
        if($this->row->transcoding != "none") {
            $this->error_type = "already_transcoding";
            return false;
        }

        if(!$resolution = $this->getNextTranscodeSize()) {
            $this->error_type = "no_transcode";
            return false;
        }

        $this->dbo->updateColumn("videos", "transcoding", $resolution, "id", $this->row->id, true);
        $this->row->transcoding = $resolution;
        $this->dbo->updateColumn("videos", "transcode_start", time(), "id", $this->row->id, true);
        $this->row->transcode_start = time();

        $domain = new Domain();
        $public_path = $domain->vars()->public_path;
        $source_path = $public_path . $this->row->base_url . $this->row->base_filename;
        //Get the filename without the extension
        $filename = substr($this->row->base_filename, 0, strrpos($this->row->base_filename, "."));
        $target_path = $public_path . $this->row->base_url . $filename . "_" . $resolution . ".mp4";

        //now we need to figure out the dimensions of the video
        $scale = $this->scale($resolution);

        // FFMpeg options are:
        // -i - input file
        // -s - scale
        // -c:v - video codec
        // -crf - constant rate factor
        // -c:a - audio codec
        // -b:a - audio bitrate
        // -movflags - faststart
        // -y - overwrite output files

        $script = INCLUDE_PATH . "trans.php \"id=" . $this->row->video_id . "\" \"domain_prefix=" . $domain->prefix() . "\" > /dev/null &";
//        $cmd = "nohup ffmpeg -nostdin -hide_banner -i $source_path -s 1694x720 -c:v libx264 -preset slow -crf 26 -c:a aac -b:a 128k -movflags +faststart -y $target_path 2>&1 | php $script";
        $cmd = "nohup ffmpeg -nostdin -hide_banner -i $source_path $scale -c:v libx264 -preset slow -crf 26 -c:a aac -b:a 128k -movflags +faststart -y $target_path 2>&1 | php $script";
        $output = shell_exec($cmd);
        return true;
    }

    /** This is similar to the transcode function but transcodes to a fixed size preview video
     * @param startTime the time in seconds to start the preview clip from
     * @return bool true if a preview was started, false if not
     */
    public function preview(float $startTime) : bool
    {
        if($this->row->transcoding != "none") {
            $this->error_type = "already_transcoding";
            return false;
        }

        $this->dbo->updateColumn("videos", "transcoding", "180p", "id", $this->row->id, true);
        $this->row->transcoding = "180p";
        $this->dbo->updateColumn("videos", "transcode_start", time(), "id", $this->row->id, true);
        $this->row->transcode_start = time();

        $domain = new Domain();
        $public_path = $domain->vars()->public_path;
        $source_path = $public_path . $this->row->base_url . $this->row->base_filename;
        //Get the filename without the extension
        $filename = substr($this->row->base_filename, 0, strrpos($this->row->base_filename, "."));
        $target_path = $public_path . $this->row->base_url . $filename . "_180p.mp4";

        //now work out where in the video to start and end the preview
        $duration = $this->row->duration;
        $endTime = $startTime + 60;
        //we don't want the preview clip to include credits or the ending of the video so we'll cut it off 20 seconds before the end
        if($endTime > $duration - 20) {
            $endTime = $duration - 20;
        }
        //if that would make the clip less than 20 seconds long (or negative) then we'll take the whole clip from earlier in the video instead
        if($endTime < $startTime + 20) {
            //if the start time was too far into the video then set it back to 25% in
            if($startTime > $duration * 0.25) {
                $startTime = $duration * 0.25;
            }
            //get the smaller of either 50 seconds or 66% of the remaining video duration
            $endTime = min($startTime + 60, ($duration - $startTime) * 0.66 + $startTime);
        }

        $duration = $endTime - $startTime;
        
        $seek = "-ss " . $startTime;
        $d = "-t " . $duration;

        $pts = 0.25;    //record at 4x speed
        $dur = $duration * $pts;

        // FFMpeg options are:
        // -i - input file
        // -c:v - video codec
        // -crf - constant rate factor
        // -an - no audio
        // -movflags - faststart
        // -y - overwrite output files

        //the ffmpeg filter parameter will be different depending on what the resolution of the input video is
        //if it's 4:3 aspect ratio then we will crop the top and bottom of the video
        //if it's widescreen (greater than 16:9) then we will crop the sides of the video
        //if it's 16:9 then we will just scale it down to 384x216
        //if it's in portrait mode then ffmpeg will rotate it when transcoding so we just treat it as if it's 9:16 and pad left and right
        $aspect = $this->aspectRatio($this->row->orig_width, $this->row->orig_height);
        $ptsf = "";
        //if we're changing the playback speed then add this to the filter
        if($pts != 1) {
            $ptsf = ",setpts=$pts*PTS";
        }

        if($this->row->orientation == "portrait" || $aspect == "Portrait") {
            $filter = "-filter:v \"scale=384:216:force_original_aspect_ratio=decrease,pad=384:216:-1:-1:color=black$ptsf\"";
        } elseif($aspect == "4:3" || $aspect == "Cinema") {
            $filter = "-filter:v \"scale=384:216:force_original_aspect_ratio=increase,crop=384:216$ptsf\"";
        } else {
            $filter = "-filter:v \"scale=384:216$ptsf\"";
        }

        $script = INCLUDE_PATH . "trans.php \"id=" . $this->row->video_id . "&dur=$dur\" \"domain_prefix=" . $domain->prefix() . "\" > /dev/null &";
        $cmd = "nohup ffmpeg -nostdin -hide_banner $seek $d -i $source_path $filter -c:v libx264 -preset slow -crf 25 -an -movflags +faststart -y $target_path 2>&1 | php $script";
        $output = shell_exec($cmd);
        return true;
    }

    /** This function will make the sprite tile image and the vtt file for the videojs plugin to display thumbnails over the timeline
     * @return bool true if the vtt was created, false if not. Sprite file will be created in the background
     */
    public function sprite() : bool
    {
        if($this->row->transcoding != "none") {
            //don't want to be doing this while a transcode is taking place
            $this->error_type = "already_transcoding";
            return false;
        }

        $domain = new Domain();
        $public_path = $domain->vars()->public_path;
        $source_path = $public_path . $this->row->base_url . $this->row->base_filename;
        //Get the filename without the extension
        $filename = $this->row->base_url . substr($this->row->base_filename, 0, strrpos($this->row->base_filename, ".")) . "_sprite.jpg";
        $target_path = $public_path . $filename;

        //now we need to work out how many frames are going to be in the sprite and how many rows and columns it will have
        $duration = $this->row->duration;
        //it doessn't make sense to do more than about 2 frames per second for short clips
        //but we also want to limit the total number of frames to about 120
        $fps = round(min(2, 120 / $duration), 2);

        //now we can work out how many frames we'll need
        $frames = ceil($duration * $fps);

        //now we need to know the dimensions of each thumbnail
        $aspect = $this->aspectRatio($this->row->orig_width, $this->row->orig_height);
        if($this->row->orientation == "portrait" || $aspect == "Portrait") {
            $width = 90;
            $height = 160;
        } elseif($aspect == "Cinema") {
            $width = 215;
            $height = 90;
        } elseif($aspect == "4:3") {
            $width = 120;
            $height = 90;
        } else {
            $width = 160;
            $height = 90;
        }

        //now if we use 8 columns then we can have 8 frames per row
        $columns = 8;
        //so we need to work out how many rows we'll need
        $rows = ceil($frames / $columns);

        //now we can create the sprite
        $script = INCLUDE_PATH . "sprite.php \"id={$this->row->video_id}\" \"domain_prefix=" . $domain->prefix() . "\" > /dev/null &";
//        $cmd = "ffmpeg -nostdin -hide_banner -i $source_path -vf \"fps=$fps,scale=$width:$height,tile={$columns}x{$rows}\" -q:v 2 -y $target_path";
        $cmd = "nohup ffmpeg -nostdin -hide_banner -i $source_path -vf \"fps=$fps,scale=$width:$height,tile={$columns}x{$rows}\" -q:v 8 -y $target_path 2>&1 | php $script";
        $output = shell_exec($cmd);
/*
        //now we need to compress the sprite and brighten it a bit
        $imagick = new Imagick($target_path);
        $imagick->modulateImage(100, 125, 110);
        $imagick->setImageCompression(Imagick::COMPRESSION_JPEG);
        $imagick->setImageCompressionQuality(40);
        $imagick->writeimage($target_path);
*/
        $fqfn = "/" . $filename;    //fully qualified filename (actually it's not fully qualified because it doesn't include the domain)
        //now we need to create the vtt file
        $vtt = "WEBVTT\n\n";
        $interval = 1 / $fps;
        for($i = 0; $i < $frames; $i++) {
            $start = $i * $interval;
            $end = $start + $interval;
            $startCue = $this->vttTimestamp($start);
            $endCue = $this->vttTimestamp($end);
            $vtt .= "$startCue --> $endCue\n$fqfn#xywh=" . ($i % $columns) * $width . "," . floor($i / $columns) * $height . ",$width,$height\n\n";
        }

        //now we can save the vtt file
        $vtt_filename = substr($filename, 0, strrpos($filename, ".")) . ".vtt";
        $vtt_path = $public_path . $vtt_filename;
        file_put_contents($vtt_path, $vtt);
        //update the database with the path to the vtt file
        $this->dbo->updateColumn("videos", "url_vtt", $vtt_filename, "id", $this->row->id, true);
//        $this->dbo->updateColumn("videos", "process_state", "ready", "id", $this->row->id, true);
        $this->row->url_vtt = $vtt_filename;
//        $this->row->process_state = "ready";
        return true;
    }

    /** Tidy up after transcode has finished */
    public function transcodeFinished() : void
    {
        //Get the column name we need to update
        $column = "url_" . $this->row->transcoding;
        //Get the filename without the extension
        $filename = substr($this->row->base_filename, 0, strrpos($this->row->base_filename, "."));
        //Add the resolution to the filename and add the extension
        $filename .= "_" . $this->row->transcoding . ".mp4";
        //Get the path to the file
        $path = $this->row->base_url . $filename;
        //Update the column with the path
        $this->dbo->updateColumn("videos", $column, $path, "id", $this->row->id, true);
        $this->row->$column = $path;
        //Set the transcoding column to none
        $this->dbo->updateColumn("videos", "transcoding", "none", "id", $this->row->id, true);
        $this->row->transcoding = "none";
        //Set progress back to 0
        $this->dbo->updateColumn("videos", "progress", 0, "id", $this->row->id, true);
        $this->row->progress = 0;
    }

    /** Make a thumbnail image from the original video. This is only to identify the video and will be replaced later */
    public function thumbnail() : void
    {
        $domain = new Domain();
        $public_path = $domain->vars()->public_path;
        $source_path = $public_path . $this->row->base_url . $this->row->base_filename;
        //Get the filename without the extension
        $filename = $this->row->base_url . substr($this->row->base_filename, 0, strrpos($this->row->base_filename, ".")) . "_thmb.jpg";
        $target_path = $public_path . $filename;

        //we'll just get it to seek to about 1/3 in and grab a frame
        $duration = $this->row->duration;
        $seek = $duration / 3;

        // FFMpeg options are:
        // -i - input file
        // -ss - seek to position
        // -frames:v - number of frames to output
        // -q:v - quality
        // -y - overwrite output files

        $cmd = "ffmpeg -nostdin -hide_banner -ss $seek -i $source_path -vf \"scale=320:180:force_original_aspect_ratio=decrease,pad=320:180:-1:-1:color=black\" -frames:v 1 -q:v 2 -y $target_path 2>&1 > /dev/null &";
//        $cmd = "ffmpeg -nostdin -hide_banner -i $source_path -vf \"thumbnail=300,scale=320:180:force_original_aspect_ratio=decrease,pad=320:180:-1:-1:color=black\" -frames:v 1 -q:v 2 -y $target_path 2>&1 > /dev/null &";
//        $cmd = "ffmpeg -hide_banner -i $source_path -vf \"thumbnail=300,scale=320:180:force_original_aspect_ratio=decrease,pad=320:180:-1:-1:color=black\" -frames:v 1 -q:v 2 -y $target_path";
        $output = shell_exec($cmd);
        $this->dbo->updateColumn("videos", "url_thumbnail", $filename, "id", $this->row->id, true);
    }

    /** Make the poster image from the frame at the time specified in the highest resolution video available using ffmpeg and return the url
     * @param time - the time in seconds to get the frame from
     * @return string - the url of the poster image
    */
    public function poster(string $time)
    {
        $domain = new Domain();
        $public_path = $domain->vars()->public_path;
        $source_path = $public_path . $this->getHighestResolution();
        //Get the filename without the extension
        $filename = $this->row->base_url . substr($this->row->base_filename, 0, strrpos($this->row->base_filename, ".")) . "_poster.jpg";
        $target_path = $public_path . $filename;

        //now to get the size to scale the poster to because without it ffmpeg may change the aspect ratio
        $size = $this->getHighestTranscodeSize();
        if($this->row->orientation == "portrait") {
            $scale = $this->scale($size, "Portrait");
        } else {
            $scale = $this->scale($size);
        }

        // FFMpeg options are:
        // -i - input file
        // -ss - seek to position
        // -frames:v - number of frames to output
        // -q:v - quality
        // -y - overwrite output files

        $cmd = "ffmpeg -hide_banner -ss $time -i $source_path $scale -frames:v 1 -q:v 2 -y $target_path";
        $output = shell_exec($cmd);
        $this->dbo->updateColumn("videos", "url_poster", $filename, "id", $this->row->id, true);
        $imagick = new Imagick($target_path);
        $imagick->setImageCompression(Imagick::COMPRESSION_JPEG);
        $imagick->setImageCompressionQuality(75);
        $imagick->writeimage($target_path);
        return $filename;
    }

    /** Make a thumbnail image from the poster image using Imagick. It should be 384x216.
     * If the poster image is in portrait orientation then we will add black bars on either side to pad it out to 384x216
     * If the poster is in 16:9 aspect ratio we can just resize it to 384x216
     * If the poster is in 4:3 aspect ratio we will crop it to 384x216
     * If the poster is in widescreen (greater than 16:9) we will crop the edges to 384x216
     * @return string - the relative url of the thumbnail image
     */
    public function thumbnailFromPoster() : string
    {
        $imagick = new Imagick();
        $domain = new Domain();
        $public_path = $domain->vars()->public_path;
        $poster_path = $public_path . $this->row->url_poster;
        //Get the target filename
        $filename = $this->row->base_url . substr($this->row->base_filename, 0, strrpos($this->row->base_filename, ".")) . "_thmb.jpg";
        $target_path = $public_path . $filename;
        $imagick->readImage($poster_path);
        $width = $imagick->getImageWidth();
        $height = $imagick->getImageHeight();
        $aspect_ratio = $this->aspectRatio($width, $height);
        if ($aspect_ratio == "Portrait") {
            //Portrait
            $imagick->scaleImage(384, 216, true);
            $imagick->setImageBackgroundColor('black');
            $width = $imagick->getImageWidth();
            $x = round((384 - $width) / 2);
            $imagick->extentImage(384, 216, -$x, 0);
        } elseif ($aspect_ratio == "Cinema") {
            //Widescreen
            $imagick->cropThumbnailImage(384, 216);
        } elseif ($aspect_ratio == "4:3") {
            //4:3
            $imagick->cropThumbnailImage(384, 216);
        } else {
            //16:9
            $imagick->scaleImage(384, 216, true);
        }
        $imagick->modulateImage(100, 125, 100);
        $imagick->setImageCompression(Imagick::COMPRESSION_JPEG);
        $imagick->setImageCompressionQuality(75);
        $imagick->writeimage($target_path);
        $this->dbo->updateColumn("videos", "url_thumbnail", $filename, "id", $this->row->id, true);
        return $filename;
    }

    /** Set the processing state of the video 
    * @param state - the state to set the video to
    */
    public function setState(string $state) : void
    {
        $this->dbo->updateColumn("videos", "process_state", $state, "id", $this->row->id, true);
        $this->row->process_state = $state;
    }

    /** Set the progress value for when a video is being transcoded
     * @param progress - the progress value to set (0 - 100)
    */
    public function setProgress($progress) : void
    {
        $this->dbo->updateColumn("videos", "progress", $progress, "id", $this->row->id, true);
        $this->row->progress = $progress;
    }

    /** Return a vtt cue style timestamp from a number of seconds
     * @param seconds - the number of seconds
     * @return string - the vtt cue style timestamp
     */
    public function vttTimestamp(float $seconds) : string
    {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds - ($hours * 3600)) / 60);
        $seconds = $seconds - ($hours * 3600) - ($minutes * 60);
        $milliseconds = round(fmod($seconds, 1) * 1000);
        $seconds = floor($seconds);
        $timestamp = sprintf("%02d:%02d:%02d.%03d", $hours, $minutes, $seconds, $milliseconds);
        return $timestamp;
    }

    /** Get the scale value for ffmpeg given the desired resolution 
     * @param resolution - the desired resolution
     * @param aspect - the aspect ratio of the video (use to force result to portrait)
     * @return string - the scale value for ffmpeg
    */
    public function scale(string $resolution, string $aspect = "") : string
    {
        if($resolution == "low") return "";
        if(!$aspect) $aspect = $this->aspectRatio($this->row->orig_width, $this->row->orig_height);
        if($aspect == "4:3") {
            if($resolution == "1080p") return "-s 1440x1080";
            if($resolution == "720p") return "-s 960x720";
            if($resolution == "480p") return "-s 640x480";
            if($resolution == "360p") return "-s 480x360";
            if($resolution == "240p") return "-s 320x240";
            if($resolution == "180p") return "-s 384x216";
        } elseif($aspect == "16:9") {
            if($resolution == "1080p") return "-s 1920x1080";
            if($resolution == "720p") return "-s 1280x720";
            if($resolution == "480p") return "-s 854x480";
            if($resolution == "360p") return "-s 640x360";
            if($resolution == "240p") return "-s 426x240";
            if($resolution == "180p") return "-s 384x216";
        } elseif($aspect == "Portrait") {
            if($resolution == "1080p") return "-s 1080x1920";
            if($resolution == "720p") return "-s 720x1280";
            if($resolution == "480p") return "-s 480x854";
            if($resolution == "360p") return "-s 360x640";
            if($resolution == "240p") return "-s 240x426";
            if($resolution == "180p") return "-s 384x216";
        } else {
            if($resolution == "1080p") return "-s 1920x800";
            if($resolution == "720p") return "-s 1280x536";
            if($resolution == "480p") return "-s 1152x480";
            if($resolution == "360p") return "-s 854x356";
            if($resolution == "240p") return "-s 426x178";
            if($resolution == "180p") return "-s 384x216";
        }
    }

    /** Get a string representation of the aspect ratio of a video
     * @param width - pixel width of the video
     * @param height - pixel height of the video
     * @return string
     */
    public function aspectRatio(int $width, int $height) : string
    {
        $ratio = ($height * 100) / $width;
        if($ratio > 100) {
            return "Portrait";
        } elseif($ratio > 60) {
            return "4:3";
        } elseif($ratio < 45) {
            return "Cinema";
        } else {
            return "16:9";
        }
    }

    /** Get the highest resolution that is available
     * @return string - the relative path to the highest resolution video available
     */
    public function getHighestResolution() : string
    {
        if($this->row->url_1080p) return $this->row->url_1080p;
        if($this->row->url_720p) return $this->row->url_720p;
        if($this->row->url_480p) return $this->row->url_480p;
        if($this->row->url_low) return $this->row->url_low;
        return "";
    }

    //Find the next highest resolution to transcode to that does not already exist
	public function getNextTranscodeSize() : string
	{
        //testing width here allows for cinema aspect videos to be transcoded to 1080p (1920 x 800)
        if($this->row->orig_width >= 1920 && !$this->row->url_1080p) return "1080p";
        //for the rest we test the height to allow 4:3 videos to be transcoded to 720p (unlikely to find a 4:3 video in 1080p)
        if($this->row->orig_height >= 720 && !$this->row->url_720p) return "720p";
        if($this->row->orig_height >= 480 && !$this->row->url_480p) return "480p";
        //anything lower than 480 will just be transcoded to its original resolution
        if($this->row->orig_height < 480 && !$this->row->url_low) return "low";
        return "";
	}

    /** Find the highest transcode size whether or not transcoding has already been done */
    public function getHighestTranscodeSize() : string
    {
        if($this->row->orig_width >= 1920) return "1080p";
        if($this->row->orig_height >= 720) return "720p";
        if($this->row->orig_height >= 480) return "480p";
        return "low";
    }

    public function getProcessStates() : array
    {
        return $this->dbo->getEnumValues("videos", "process_state", true);
    }

    public function getTranscodeTypes() : array
    {
        return $this->dbo->getEnumValues("videos", "transcoding", true);
    }

    public function getChannels() : array
    {
        return $this->dbo->fetchRows("channels", "user_id", $this->row->user_id, true);
    }
}
?>