<?php
    require_once(HOME_DIR . 'admi/classes/apis/apiaction.class.php');

//Class to make a square thumbnail from an image
class ScreenshotThumb extends ApiAction
{
    public function process() : bool
    {
        $img_obj = json_decode($_POST["x"], false);
		$filepath = $this->dbo->domain_vars->public_path . $img_obj->filename;
        $name = basename($img_obj->filename);
        if(($p = strrpos($name, "_")) === false)                    //find the filename up to the bit we just added in the last operation
        {
            $p = strrpos($name, ".");                               //or if it doesn't exist just get the name up to the filename extension
        }
        $fname = strtolower(substr($name, 0, $p));
        $new_width = (integer)($this->dbo->domain_obj->thumbnail_width);
        $new_height = (integer)($this->dbo->domain_obj->thumbnail_height);
        $new_filename = "{$this->dbo->domain_obj->thumbnail_folder}{$fname}_{$new_width}x{$new_height}.jpg"; //get filename for output image by adding the size to the end and make it a jpg
        $new_filepath = $this->dbo->domain_vars->public_path . $new_filename;
        $new_filename = "/" . $new_filename;

        $imagick = new Imagick($filepath);
        $imagick->resizeImage($new_width, $new_height, Imagick::FILTER_CATROM, 0.9);
        $imagick->setImageCompression(Imagick::COMPRESSION_JPEG);
        $imagick->setImageCompressionQuality(70);
        $imagick->writeimage($new_filepath);
        $this->return_text = $new_filename;
        return true;
    }
}