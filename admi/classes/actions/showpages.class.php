<?php
    require_once(HOME_DIR . 'admi/classes/actions/displayaction.class.php');
	require_once(OBJECTS_PATH . 'page.class.php');

class ShowPagesAction extends DisplayAction
{
    public $remember_me = true;
    public $name = "Pages";
    
    protected function sortby()
    {
        if(isset($this->get_object->sortBy)) {
            $_SESSION["showpages_sortby"] = $this->get_object->sortBy;
        }

        if(isset($_SESSION["showpages_sortby"])) {
            $this->sort_by = $_SESSION["showpages_sortby"];
        }
    }

    public function prerender() : void
    {
        include "templates/pages_template.php";
    }

    public function render() : void
    {
        $page = new Page();

		$start = $this->page * GALS_PER_PAGE;
		$end = GALS_PER_PAGE;
        $page->prepare($start, $end);
		$this->num_of_rows = $page->pages();
		$this->pages = floor(($this->num_of_rows + GALS_PER_PAGE - 1) / GALS_PER_PAGE);

        include "templates/actions/showpages_template.php";
    }
}
?>