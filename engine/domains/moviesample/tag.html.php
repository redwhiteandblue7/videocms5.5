<?php
	require_once(INCLUDE_PATH . "domains/moviesample/classes/mvs.class.php");
    require_once(OBJECTS_PATH . "post.class.php");

class TagPage extends MovieSamplePage
{
    protected $template = "error";
    protected $label = "Top Videos Tagged with ";
    protected $title;

    public function init()
    {
        parent::init();
        $this->page->initDescription();
        $this->canonical_url = $this->canonical_base;
    }

    public function process()
    {
        $pagename = $this->uri[1];

        //we need to check that the tag exists and that the pagename is valid
        //now see if there is a tag with this pagename
        if(!$this->tags->getTagByPagename($pagename)) {
            return;
        }


        $this->title = ucfirst($this->tags->vars()->tag_name);
        $this->label .= "#$this->title";
        $this->template = "results";
		$this->canonical_url = $this->canonical_base . "tag/" . $this->tags->vars()->tag_name;

        $this->post = new Post();
        $this->num_of_videos = $this->post->videoPosts(0, 40, "sortByVisible", 0, $this->tags->vars()->tag_id);

		//now we can set up the vars for the page
		$description = new stdClass();
		$description->title = $this->title . " videos - MovieSample.net";
        //if there is only one video or no results at all then we don't want search engines to index this page
        if($this->num_of_videos < 2) {
            $description->robots = true;
        }
		$this->page->description($description);
        $this->show_trending = 10;
    }
}
?>
