<?php
    require_once(HOME_DIR . 'admi/classes/apis/apiaction.class.php');

//Class to handle the creation of a smaller poster image from the screenshot for display on mobile devices
class ScreenshotMobile extends ApiAction
{
    public function process() : bool
    {
        $img_obj = json_decode($_POST["x"], false);
		$filepath = $this->dbo->domain_vars->public_path . $img_obj->filename;
        $name = basename($img_obj->filename);
        //find the filename up to the bit we just added in the last operation
        if(($p = strrpos($name, "_")) === false) {
            //or if it doesn't exist just get the name up to the filename extension
            $p = strrpos($name, ".");
        }
        $fname = strtolower(substr($name, 0, $p));

        $imagick = new Imagick($filepath);

        $mbposter_width = (integer)($this->dbo->domain_obj->mbposter_width);
        $mbposter_height = (integer)($this->dbo->domain_obj->mbposter_height);
        $new_height = round(($img_obj->width * $mbposter_height) / $mbposter_width);
        $imagick->cropImage($img_obj->width, $new_height, 0, $img_obj->crop_y);

        $new_width = $mbposter_width;
        $new_height = $mbposter_height;
        //get filename for output image by adding the size to the end and make it a jpg
        $new_filename = "{$this->dbo->domain_obj->poster_folder}{$fname}_{$new_width}x{$new_height}.jpg";
        $new_filepath = $this->dbo->domain_vars->public_path . $new_filename;
        $new_filename = "/" . $new_filename;

        $imagick->resizeImage($new_width, $new_height, Imagick::FILTER_CATROM, 0.9);
        $imagick->setImageCompression(Imagick::COMPRESSION_JPEG);
        $imagick->setImageCompressionQuality(70);
        $imagick->writeimage($new_filepath);

        $this->return_text = $new_filename;
        return true;
    }
}