<?php
    require_once(INCLUDE_PATH . "domains/moviesample/apis/default.api.php");
    require_once(OBJECTS_PATH . "channel.class.php");

class ChannelNameApi extends DefaultApi
{
    protected $return_text = "Error|channel_name";

    public function process()
    {
        if(!isset($_POST["channel_id"]) || !is_numeric($_POST["channel_id"])) {
            $this->return_text = "Error|channel_id";
            return;
        }
        if(!isset($_POST["channel_name"]) || !$_POST["channel_name"]) {
            $this->return_text = "Error|channel_name";
            return;
        }
        $channel = new Channel();
        if($channel->getChannelByName(trim($_POST["channel_name"]))) {
            if($channel->vars()->channel_id != $_POST["channel_id"]) {
                $this->return_text = "NotOK";
                return;
            }
        }

        $this->return_text = "OK";
    }
}
?>