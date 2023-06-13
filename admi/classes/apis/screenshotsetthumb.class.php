<?php
    require_once(HOME_DIR . 'admi/classes/apis/apiaction.class.php');

class ScreenshotSetThumb extends ApiAction
{
    public function process() : bool
    {
        $img_obj = json_decode($_POST["x"], false);
        if($this->dbo->updateColumn("posts", "thumb_url", $img_obj->thumbnail, "post_id", $img_obj->post_id, true )) {
            $this->return_text = "OK";
        } else {
            $this->return_text = "Error - table row not updated, post_id was " . $img_obj->post_id;
        }
        return false;
    }
}