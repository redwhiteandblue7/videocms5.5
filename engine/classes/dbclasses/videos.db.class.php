<?php
    require_once(DB_PATH . "manage.db.class.php");

class VideosDB extends ManageDatabase
{
	/** fetch videos from database into the table_rows array and return the total number of rows in the table
	 * @param start - the row number to start fetching from (when using pagination)
	 * @param limit - the maximum number of rows to fetch
	 * @param user_id - the user id of the user who uploaded the video
	 * @param channel_id - the channel id of the channel the video belongs to
	 * @param process_state - the process state of the video (pending, transcoded, transcoding, ready, posted etc)
	 * @return int - the total number of rows in the table
	 */
    public function fetchVideos(int $start = 0, int $limit = 99999, int $user_id = 0, int $channel_id = 0, string $process_state = "")
    {
		$prefix = $this->table_prefix;

		$whereclause = "where 1";
		if($user_id > 0) {
			$whereclause .= " and {$prefix}_videos.user_id=$user_id";
		}
		if($channel_id > 0) {
			$whereclause .= " and {$prefix}_videos.channel_id=$channel_id";
		}
		if($process_state != "") {
			$whereclause .= " and {$prefix}_videos.process_state='$process_state'";
		}
		$q = "select count(1) as cnt from
			{$prefix}_videos
			$whereclause
            ";
        $r = $this->dbc->query($q) or die($this->dbc->error . ", query was $q");
        $num_of_rows = $r->fetch_row()[0];
        $r->free();

		$q = "select {$prefix}_videos.*, channel_name, user_name from
			{$prefix}_videos
			left join {$prefix}_channels on {$prefix}_videos.channel_id={$prefix}_channels.channel_id
			left join users on users.user_id={$prefix}_videos.user_id
			$whereclause
			order by {$prefix}_videos.time_added desc
			limit $start, $limit
            ";
        $r = $this->dbc->query($q) or die($this->dbc->error . " query was $q in " . __FILE__ . " at line " . __LINE__);

        $i = $start;
        while($row = $r->fetch_object()) {
            $row->rownum = ++$i;
            $this->table_rows[] = $row;
        }
        $r->close();

        return $num_of_rows;
    }

	/** Fetch the video row from the videos table including channel name and post title and username
	 * @param id - the id of the video to fetch
	 * @return object - the video row
	 */
	public function fetchVideo(int $video_id)
	{
		$prefix = $this->table_prefix;
		$q = "select {$prefix}_videos.*, channel_name, user_name from
			{$prefix}_videos
			left join {$prefix}_channels on {$prefix}_videos.channel_id={$prefix}_channels.channel_id
			left join users on users.user_id={$prefix}_videos.user_id
			where {$prefix}_videos.video_id=$video_id
			";
		$r = $this->dbc->query($q) or die($this->dbc->error . " query was $q in " . __FILE__ . " at line " . __LINE__);

		if($row = $r->fetch_object()) {
			$r->close();
			return $row;
		}

		return "";
	}

	/** Import a video into the videos table by moving it from the uploads folder to the imported folder and set up the video urls where appropriate
	 * @param vars - an object containing the following properties:
	 * 	$vars->import_filename - the name of the file to import
	 * 	$vars->duration - the duration of the video in seconds
	 * 	$vars->fps - the frames per second of the video
	 * 	$vars->orig_width - the width of the video in pixels
	 * 	$vars->orig_height - the height of the video in pixels
	 * 	$vars->username - the username of the user who uploaded the video
	 * 	$vars->user_id - the user id of the user who uploaded the video
	 * 	$vars->asset_path - the path to the assets folder
	 * 	$vars->asset_url - the url to the assets folder
	 * @return string - an error message if there was an error, or an empty string if there was no error
	 */
	public function importVideo(stdClass $vars) : string
	{
		$source_filename = $vars->import_filename;
		$orig_width = $vars->orig_width;
		$orig_height = $vars->orig_height;
		$username = $vars->username;

        $source_folder = $vars->asset_path . $username . "/uploads/";
        $dest_folder = $vars->asset_path . $username . "/imported/";
		//the full path to the source file
		$source_path = $source_folder . $source_filename;

		$orig_url = $vars->asset_url . $username . "/uploads/";
		$orig_filename = $source_filename;
		$base_url = $vars->asset_url . $username . "/imported/";

		$url_1080p = "";
		$url_720p = "";
		$url_480p = "";
		$url_low = "";

		//make sure the destination folder exists
        if(!is_dir($dest_folder)) {
			$old = umask(0);
            mkdir($dest_folder, 0777);
			umask($old);
        }

		//get a new name for the imported file. This will be a hex timestamp followed by the original filename
		$base_filename = $this->newFilename($source_filename);
		//get the extension of the file which may be .mp4, .mov, .webm, or something else
		$ext = substr($base_filename, strrpos($base_filename, ".") + 1);
		//now get the filename without the extension
		$base_filename = substr($base_filename, 0, -1 * strlen($ext));
		//now put a timestamp in front
		$time_added = time();
		$base_filename = dechex($time_added) . "_" . $base_filename;

		//are we allowing skipping of transcoding if the file is mp4 and already the right resolution?
		$allow_skip = $vars->allow_skip ?? false;
		$dest_filename = $base_filename;
		//if the imported file is an mp4 file and it's in one of the resolutions we support, then we can use it without transcoding
		if($ext == ".mp4" && $allow_skip) {
			if($orig_height == 1080) {
				$url_1080p = $base_url . $dest_filename . $ext;
			} elseif($orig_height == 720) {
				$url_720p = $base_url . $dest_filename . $ext;
			} elseif($orig_height == 480) {
				$url_480p = $base_url . $dest_filename . $ext;
			} elseif($orig_height < 480) {
				$url_low = $base_url . $dest_filename . $ext;
			}
		}

		$dest_filename .= $ext;
		$base_filename .= $ext;

		//now move the file to the imported folder
		$imported_path = $dest_folder . $dest_filename;
		if(!@rename($source_path, $imported_path)) {
			return "Could not move $source_path to $imported_path, aborting import";
		}

		$vars->id = 0;
		$vars->video_id = 0;
		$vars->orig_url = $orig_url;
		$vars->orig_filename = $orig_filename;
		$vars->base_url = $base_url;
		$vars->base_filename = $base_filename;
		$vars->orig_width = $orig_width;
		$vars->orig_height = $orig_height;
		$vars->url_1080p = $url_1080p;
		$vars->url_720p = $url_720p;
		$vars->url_480p = $url_480p;
		$vars->url_low = $url_low;
		$vars->time_added = $time_added;
		//unset the properties we don't want to insert into the videos table
		unset($vars->username);
		unset($vars->asset_path);
		unset($vars->asset_url);
		unset($vars->import_filename);
		unset($vars->video_url);
		unset($vars->allow_skip);
		$this->insertRow("videos", $vars, "", true);
		return "";
	}

    //make a new filename for an uploaded video
    private function newFilename(string $filename) : string
    {
        $newFilename = strtolower($filename);
        //get the extension
        $extension = substr($newFilename, strrpos($newFilename, "."));
        //remove the extension
        $newFilename = str_replace($extension, "", $newFilename);
        //remove any reference to the original resolution
        $newFilename = str_replace("_1080p", "", $newFilename);
        $newFilename = str_replace("_720p", "", $newFilename);
        $newFilename = str_replace("_480p", "", $newFilename);
        $newFilename = str_replace("_2160p", "", $newFilename);
        $newFilename = str_replace("_360p", "", $newFilename);
        $newFilename = str_replace("_240p", "", $newFilename);
        //now convert underscores and dashes to spaces
        $newFilename = str_replace("_", " ", $newFilename);
        $newFilename = str_replace("-", " ", $newFilename);
        //remove any double spaces
        $newFilename = str_replace("  ", " ", $newFilename);
        //now split into words
        $words = explode(" ", $newFilename);
        //now make a new filename with the first 5 words
        $newFilename = "";
        for($i = 0; $i < 5; $i++) {
            if(isset($words[$i])) {
                $newFilename .= trim($words[$i]) . "-";
            }
        }
        //remove the last dash
        $newFilename = substr($newFilename, 0, -1);
        //add the extension back in
        $newFilename .= $extension;
        return $newFilename;
    }
}
