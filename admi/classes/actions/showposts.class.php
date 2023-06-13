<?php
    require_once(HOME_DIR . 'admi/classes/actions/displayaction.class.php');
	require_once(OBJECTS_PATH . 'post.class.php');
	require_once(OBJECTS_PATH . 'domain.class.php');

class ShowPostsAction extends DisplayAction
{
    private $tag_filter = 0;
    private $cat_filter = "";
    private $site_filter = 0;
    private $sponsor_filter = 0;
    private $autotag = 0;

    private $post;

    public $remember_me = true;
    public $name = "Posts";

    public function __construct()
    {
        parent::__construct();

        $this->setCategoriesFilter();
        $this->setSiteFilter();
        $this->setSponsorFilter();
        $this->setTagFilter();
    }

    protected function sortby()
    {
        if(isset($this->get_object->sortBy)) {
            $_SESSION["showposts_sortby"] = $this->get_object->sortBy;
        }

        if(isset($_SESSION["showposts_sortby"])) {
            $this->sort_by = $_SESSION["showposts_sortby"];
        }
    }

    private function setSiteFilter()
    {
        if(isset($_POST["site_filter"])) $_SESSION["posts_site_filter"] = $_POST["site_filter"];
        if(isset($_SESSION["posts_site_filter"])) $this->site_filter = $_SESSION["posts_site_filter"];
    }

    private function setSponsorFilter()
    {
        if(isset($_POST["sponsor_filter"])) $_SESSION["posts_sponsor_filter"] = $_POST["sponsor_filter"];
        if(isset($_SESSION["posts_sponsor_filter"])) $this->sponsor_filter = $_SESSION["posts_sponsor_filter"];
    }

    private function setTagFilter()
    {
        if(isset($_POST["tag_filter"])) $_SESSION["posts_tag_filter"] = $_POST["tag_filter"];
        if(isset($_SESSION["posts_tag_filter"])) $this->tag_filter = $_SESSION["posts_tag_filter"];
    }

    private function setCategoriesFilter()
    {
        if(isset($_POST["category_filter"])) $_SESSION["posts_category_filter"] = $_POST["category_filter"];
        if(isset($_SESSION["posts_category_filter"])) $this->cat_filter = $_SESSION["posts_category_filter"];
    }

    public function prerender() : void
    {
        include "templates/posts_template.php";
    }

    public function render() : void
    {
        $posts = new Post();
        $domain = new Domain();

		$start = $this->page * GALS_PER_PAGE;
		$limit = GALS_PER_PAGE;
		$num_of_rows = $posts->posts($start, $limit, $this->site_filter, $this->sponsor_filter, $this->tag_filter, $this->sort_by);
		$this->pages = floor(($num_of_rows + GALS_PER_PAGE - 1) / GALS_PER_PAGE);

        include "templates/actions/showposts_template.php";
    }
}
?>