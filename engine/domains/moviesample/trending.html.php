<?php
	require_once(INCLUDE_PATH . "domains/moviesample/classes/mvs.class.php");
    require_once(OBJECTS_PATH . "post.class.php");

class TrendingPage extends MovieSamplePage
{
    protected $template = "index";
    protected $label = "Trending Videos";

    public function init()
    {
        parent::init();
        $this->page->initDescription();
        $this->canonical_url = $this->canonical_base . "trending.html";
    }

    public function process()
    {
        $this->post = new Post();
        $this->num_of_videos = $this->post->videoPosts(0, 40, "sortByTrend");
        $this->show_tags = 30;
        $this->show_history = true;
    }
}
?>