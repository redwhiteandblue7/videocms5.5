<?php
    require_once(HOME_DIR . 'admi/classes/actions/displayaction.class.php');
	require_once(OBJECTS_PATH . 'site.class.php');

class ShowSitesAction extends DisplayAction
{
    private $tag_filter = 0;
    private $sponsor_filter = 0;
    
    private $site;

    public $remember_me = true;
    public $name = "Sites";

    public function __construct()
    {
        parent::__construct();

        $this->setSponsorFilter();
        $this->setTagFilter();
    }

    protected function sortby()
    {
        if(isset($this->get_object->sortBy)) {
            $_SESSION["showsites_sortby"] = $this->get_object->sortBy;
        }

        if(isset($_SESSION["showsites_sortby"])) {
            $this->sort_by = $_SESSION["showsites_sortby"];
        }
    }

    private function setSponsorFilter()
    {
        if(isset($_POST["sponsor_filter"])) $_SESSION["sites_sponsor_filter"] = $_POST["sponsor_filter"];
        if(isset($_SESSION["sites_sponsor_filter"])) $this->sponsor_filter = $_SESSION["sites_sponsor_filter"];
    }

    private function setTagFilter()
    {
        if(isset($_POST["tag_filter"])) $_SESSION["sites_tag_filter"] = $_POST["tag_filter"];
        if(isset($_SESSION["sites_tag_filter"])) $this->tag_filter = $_SESSION["sites_tag_filter"];
    }

    public function prerender() : void
    {
        include("templates/sites_template.php");
    }

    public function render() : void
    {
        $sites = new Site();

        $start = $this->page * GALS_PER_PAGE;
		$limit = GALS_PER_PAGE;

        $this->num_of_rows = $sites->sites($start, $limit, $this->tag_filter, $this->sponsor_filter, $this->sort_by);
		$this->pages = floor(($this->num_of_rows + GALS_PER_PAGE - 1) / GALS_PER_PAGE);

        include("templates/actions/showsites_template.php");
    }
}
?>