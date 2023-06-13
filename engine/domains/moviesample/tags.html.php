<?php
	require_once(INCLUDE_PATH . "domains/moviesample/classes/mvs.class.php");
    require_once(OBJECTS_PATH . "post.class.php");

class TagsPage extends MovieSamplePage
{
    protected $template = "tags";

    public function init()
    {
        parent::init();
        $this->page->initDescription();
        $this->canonical_url = $this->canonical_base . "tags.html";
    }

    public function process()
    {
       $this->tag_names = $this->tags->atozTags();
       $this->show_history = true;
       
    }
}
?>