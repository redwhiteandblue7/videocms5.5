<?php
    require_once(INCLUDE_PATH . "domains/moviesample/apis/default.api.php");
    require_once(OBJECTS_PATH . "channel.class.php");

class EditChannelApi extends DefaultApi
{
    protected $return_text = "Error|video_channels";

    public function process()
    {
        if(!$this->user->logged_in) {
            $this->return_text = "Error|not_logged_in";
            return;
        }

        if(!isset($_POST["channel_name"]) || !$_POST["channel_name"]) {
            $this->return_text = "Error|channel_name";
            return;
        }
        if(!isset($_POST["channel_id"]) || !is_numeric($_POST["channel_id"])) {
            $this->return_text = "Error|channel_id";
            return;
        }

        $channel_id = $_POST["channel_id"];
        $channel = new Channel($channel_id);
        if($channel_id) {
            if(!$channel->channel_name) {
                $this->return_text = "Error|no_channel";
                return;
            }
            $vars = $channel->vars();
            if($vars->user_id != $this->user->user_id) {
                $this->return_text = "Error|invalid_user";
                return;
            }
        } else {
            $vars = new stdClass();
            $vars->user_id = $this->user->user_id;
            $vars->id = 0;
            $vars->channel_id = 0;
            $vars->display_state = 'display';
            $vars->link_url = '';
        }
        $vars->channel_name = $_POST["channel_name"];
        if(!$channel->save($vars)) {
            $this->return_text = "Error|channel_exists";
            return;
        }

        $this->return_text = "OK|" . $channel->vars()->channel_id;
    }
}
?>