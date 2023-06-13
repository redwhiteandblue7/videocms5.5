<?php
    require_once(HOME_DIR . 'admi/classes/apis/apiaction.class.php');

//Class to upload the image to use as the screenshot
class ScreenshotUpload extends ApiAction
{
    public function process() : bool
    {
		if($_FILES["screenshot_upload"]["error"] != 0) {
			switch($_FILES["screenshot_upload"]["error"]) {
				case 1:
				    $this->return_text = "File exceeded upload_max_filesize";
				    break;
				case 2:
				    $this->return_text = "File exceeded max_file_size";
				    break;
				case 3:
				    $this->return_text = "Incomplete file upload";
				    break;
				case 4:
				    $this->return_text = "No file uploaded";
				    break;
				default:
                    $this->return_text = "Unknown error type " . $_FILES["screenshot_upload"]["error"];
				    break;
			}
            return false;
		}

		if(substr($_FILES["screenshot_upload"]["type"], 0, 5) != "image") {
			$this->return_text = "File not an image: mime type " . $_FILES["screenshot_upload"]["type"];
            return false;
		}

        $filename = "images/screenshots/" . basename($_FILES["screenshot_upload"]["name"]);
        $filepath = $this->dbo->domain_vars->public_path . $filename;
        $filename = "/" . $filename;

		if(is_uploaded_file($_FILES["screenshot_upload"]["tmp_name"])) {
			if(!move_uploaded_file($_FILES["screenshot_upload"]["tmp_name"], $filepath)) {
				$this->return_text = "Could not save uploaded file to $filepath";
                return false;
			}
		} else {
			$this->return_text = "Uploaded file does not match form post data: " . $_FILES["screenshot_upload"]["tmp_name"];
            return false;
		}
        $this->return_text = $filename;
        return false;
    }
}