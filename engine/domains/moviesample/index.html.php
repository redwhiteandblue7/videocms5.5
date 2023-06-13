<?php
	require_once(INCLUDE_PATH . "domains/moviesample/classes/mvs.class.php");
    require_once(OBJECTS_PATH . "post.class.php");

class IndexPage extends MovieSamplePage
{
    protected $template = "index";
    protected $label = "Latest Uploads";

    public function init()
    {
        parent::init();
        $this->page->initDescription();
        $this->canonical_url = $this->canonical_base . "index.html";
    }

    public function process()
    {
        $this->post = new Post();
        $this->num_of_videos = $this->post->videoPosts(0, 40, "sortByAdded");

        $this->show_trending = 10;
        $this->show_history = true;
        $this->show_tags = 30;
    }
}
?>
