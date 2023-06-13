<?php
	require_once(INCLUDE_PATH . "domains/moviesample/classes/mvs.class.php");
    require_once(OBJECTS_PATH . "post.class.php");

class ChannelsPage extends MovieSamplePage
{
    protected $template = "channels";
    protected $label = "All Channels";

    public function init()
    {
        parent::init();
        $this->page->initDescription();
        $this->canonical_url = $this->canonical_base . "channels.html";
    }

    public function process()
    {
        $this->post = new Post();
        $this->channels = $this->post->channelsWithPosts();
        $this->show_tags = 30;
    }
}
?>
