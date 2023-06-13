<?php
    require_once(HOME_DIR . 'admi/classes/actions/showdata.class.php');

class ShowPageloadsAction extends ShowDataAction
{
    public $name = "Page Data";

    protected function sortby()
    {
        if(isset($this->get_object->sortBy)) {
            $_SESSION["showpageloads_sortby"] = $this->get_object->sortBy;
        }

        if(isset($_SESSION["showpageloads_sortby"])) {
            $this->sort_by = $_SESSION["showpageloads_sortby"];
        }
    }

    public function process() : bool
    {
		$start = $this->page * ROWS_PER_PAGE;
		$end = ROWS_PER_PAGE;
		$this->num_of_rows = $this->dbo->fetchPageloads($this->type, $start, $end, $this->id);
		$this->pages = floor(($this->num_of_rows + ROWS_PER_PAGE - 1) / ROWS_PER_PAGE);
        $this->results = $this->dbo->results();
        return false;
    }

    public function prerender() : void
    {
        $dmo = new Domain();
        $update_time = $dmo->varsArray()["time_last_stat_update"];

        include "templates/stats_template.php";
    }

    public function render() : void
    {
        $action = "ShowPageloads";

        include "templates/actions/showpageloads_template.php";
    }
}
