<?php
	require_once(INCLUDE_PATH . "domains/moviesample/classes/mvs.class.php");
    require_once(OBJECTS_PATH . "post.class.php");
    require_once(OBJECTS_PATH . "channel.class.php");

class ChannelPage extends MovieSamplePage
{
    protected $template = "error";
    protected $label = "Latest Uploads in Channel ";
    protected $title;

    public function init()
    {
        parent::init();
        $this->page->initDescription();
        $this->canonical_url = $this->canonical_base;
    }

    public function process()
    {
        $channel_id = $this->uri[1];

        //channel id must be a number, if it's not then show an error
        if(!is_numeric($channel_id)) {
            return;
        }

        //we need to check that the channel exists by trying to instantiate it
        $this->channel = new Channel($channel_id);
        if(!$this->channel->channel_name) {
            return;
        }


        $this->label .= $this->channel->channel_name;
        $this->template = "results";
		$this->canonical_url = $this->canonical_base . "channel/$channel_id";

        $this->post = new Post();
        $this->num_of_videos = $this->post->videoPosts(0, 40, "sortByVisible", $channel_id, 0);

		//now we can set up the vars for the page
		$description = new stdClass();
		$description->title = $this->channel->channel_name . " Latest Videos - MovieSample.net";
        //if there is only one video or no results at all then we don't want search engines to index this page
        if($this->num_of_videos < 2) {
            $description->robots = true;
        }
		$this->page->description($description);
        $this->show_tags = 30;
        if($this->num_of_videos > 16) {
            $this->show_trending = 10;
        }
    }
}
?>
