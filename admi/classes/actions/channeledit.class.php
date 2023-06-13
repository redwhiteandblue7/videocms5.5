<?php
    require_once(HOME_DIR . 'admi/classes/actions/editaction.class.php');
    require_once(OBJECTS_PATH . "channel.class.php");
    require_once(OBJECTS_PATH . "user.class.php");

class ChannelEditAction extends EditAction
{
    public $name = "Edit Channel";

    public function process() : bool
    {
        // Get the channel data for the channel id if it exists
        $id = $this->id;
        $channel = new Channel($id);
        // If the form hasn't been submitted then just set up the post array and return
        if(!(isset($_POST["channel_name"]))) {
            $this->action_status = "vars_empty";
            if($id) {
                if($this->post_object = $channel->vars()) return false;
                // If we get here then the channel id was invalid
                $this->action_status = "not_found";
            }
            // If there's no id then we're creating a new channel
            $this->post_object->channel_id = 0;
            $this->post_object->site_id = 0;
            $this->post_object->id = 0;
            return false;
        }

        // If we get here then the form has been submitted so we need to save the channel
        $channel->save($this->post_object);

        // If there was an error saving the channel then set the action status and return
        if($channel->error_type) {
            $this->action_status = $channel->error_type;
        } else {
            $this->action_status = "channel_saved";
            return true;
        }

        return false;
    }

    public function prerender() : void
    {
        include "templates/videos_template.php";
    }

    public function render() : void
    {
        $channels = new Channel();
        $channels->channels();
        $users = new User();
        $users->users();

        include("templates/actions/channeledit_template.php");
    }
}