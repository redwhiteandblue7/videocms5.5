<?php
    require_once(HOME_DIR . 'admi/classes/actions/displayaction.class.php');
	require_once(OBJECTS_PATH . 'channel.class.php');

class ShowChannelsAction extends DisplayAction
{
    private $num_of_channels = 0;
    public $remember_me = true;
    public $name = "Channels";
    
    protected function sortby()
    {
        if(isset($this->get_object->sortBy)) {
            $_SESSION["showchannels_sortby"] = $this->get_object->sortBy;
        }

        if(isset($_SESSION["showchannels_sortby"])) {
            $this->sort_by = $_SESSION["showchannels_sortby"];
        }
    }

    public function prerender() : void
    {
        include "templates/videos_template.php";
    }

    public function render() : void
    {
        $channel = new Channel();
        $this->num_of_channels = $channel->channels(0, 100);
        echo "</nav></header>";
        include("templates/actions/showchannels_template.php");
        echo "</body></html>";
    }
}
