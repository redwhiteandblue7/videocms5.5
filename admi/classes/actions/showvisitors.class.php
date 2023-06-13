<?php
    require_once(HOME_DIR . 'admi/classes/actions/showdata.class.php');

class ShowVisitorsAction extends ShowDataAction
{
    public $name = "Visitor Data";

    protected function sortby()
    {
        if(isset($this->get_object->sortBy)) {
            $_SESSION["showvisitors_sortby"] = $this->get_object->sortBy;
        }

        if(isset($_SESSION["showvisitors_sortby"])) {
            $this->sort_by = $_SESSION["showvisitors_sortby"];
        }
    }

    public function process() : bool
    {
 		$start = $this->page * ROWS_PER_PAGE;
		$end = ROWS_PER_PAGE;
		$this->num_of_rows = $this->dbo->fetchVisitors($this->id, $start, $end);
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
        include "templates/actions/showvisitors_template.php";
    }
}
