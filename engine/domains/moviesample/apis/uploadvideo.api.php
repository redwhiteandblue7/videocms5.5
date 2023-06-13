<?php
    require_once(INCLUDE_PATH . "domains/moviesample/apis/default.api.php");
    require_once(OBJECTS_PATH . "video.class.php");

class UploadVideoApi extends DefaultApi
{
    protected $return_text = "Error uploading video";

    public function process()
    {
        if(!$this->user->logged_in) {
            $this->return_text = "Error|not_logged_in";
            return;
        }
        //if files array is empty then the file was probably too big
        if(!isset($_FILES["video_file"])) {
            if($_SERVER['CONTENT_LENGTH'] > 0)
                $this->return_text = "Error|max_file_size";
            else
                $this->return_text = "Error|no_file";
            return;
        }

		if($_FILES["video_file"]["error"] != 0) {
			switch($_FILES["video_file"]["error"]) {
				case 1:
				    $this->return_text = "Error|max_file_size";
				    break;
				case 2:
				    $this->return_text = "Error|max_file_size";
				    break;
				case 3:
				    $this->return_text = "Error|incomplete";
				    break;
				case 4:
				    $this->return_text = "Error|no_file";
				    break;
				default:
                    $this->return_text = "Error|" . $_FILES["video_file"]["error"];
				    break;
			}
            return false;
		}

		if(substr($_FILES["video_file"]["type"], 0, 5) != "video") {
			$this->return_text = "Error|mimetype";
            return false;
		}

        $video = new Video();
        //replace any spaces with underscores as spaces in filenames can cause problems
        $dest_filename = str_replace(" ", "_", basename($_FILES["video_file"]["name"]));
        $dest_path = $video->uploadsFolder() . $dest_filename;
        if(is_uploaded_file($_FILES["video_file"]["tmp_name"])) {
            if(!move_uploaded_file($_FILES["video_file"]["tmp_name"], $dest_path)) {
                $this->return_text = "Error|move_uploaded_file";
                return false;
            }
        } else {
            $this->return_text = "Error|tmp_name";
            return false;
        }

        $vars = $video->probe($dest_filename);
        if($video->error_type) {
            $this->return_text = "Error|corrupt";
            return false;
        }

        $vars->allow_skip = false;
        //try to import the video, this will tell us if it has any errors
        $video->import($vars);
        if($video->error_type) {
            if($video->error_type == "error") {
                $this->return_text = "Error|import";
            } else {
                $this->return_text = "Error|probe";
            }
            return false;
        }
        //gentlemen, start your engines!
        if(!$video->transcode()) {
            $this->return_text = "Error|$video->error_type";
            return false;
        }
        $this->return_text = "OK|" . $video->video_id;
    }
}
?>