<?php
	require_once(INCLUDE_PATH . "domains/moviesample/classes/mvs.class.php");
    require_once(OBJECTS_PATH . "post.class.php");

class HistoryPage extends MovieSamplePage
{
    protected $template = "history";
    protected $label = "Your recent viewing history";

    public function init()
    {
        parent::init();
        $this->page->initDescription();
        $this->canonical_url = $this->canonical_base . "history.html";
        $this->show_tags = 30;
        $this->show_trending = 10;
    }
}
?>