<?php
    require_once(INCLUDE_PATH . "domains/moviesample/apis/default.api.php");
    require_once(OBJECTS_PATH . "post.class.php");
    require_once(OBJECTS_PATH . "video.class.php");
    require_once(OBJECTS_PATH . "channel.class.php");

class UserContentApi extends DefaultApi
{
    protected $return_text = "No template provided";

    public function process()
    {
        if(isset($_POST["template"]))
        {
            //we simply return the template provided by the calling script
            $this->template = $_POST["template"];
        }
        return;
    }
}
?>