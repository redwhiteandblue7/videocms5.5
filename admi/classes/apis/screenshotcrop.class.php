<?php
    require_once(HOME_DIR . 'admi/classes/apis/apiaction.class.php');

//Class to crop the image from the screenshot to square
class ScreenshotCrop extends ApiAction
{
    public function process() : bool
    {
        $img_obj = json_decode($_POST["x"], false);
		$filepath = $this->dbo->domain_vars->public_path . $img_obj->filename;
        $p = strrpos($img_obj->filename, ".");
        $name = strtolower(substr($img_obj->filename, 0, $p));
        $new_filename = "{$name}_{$img_obj->width}x{$img_obj->width}.jpg";     //get filename for output image by adding the size to the end and make it a jpg
        $new_filepath = $this->dbo->domain_vars->public_path . $new_filename;
        $new_filename = "/" . $new_filename;

        $imagick = new Imagick($filepath);
        $imagick->cropImage($img_obj->width, $img_obj->width, 0, $img_obj->crop_y);
        $imagick->writeimage($new_filepath);
        $this->return_text = $new_filename;
        return false;
    }
}