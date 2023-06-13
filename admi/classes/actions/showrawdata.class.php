<?php
    require_once(HOME_DIR . 'admi/classes/actions/displayaction.class.php');

class ShowRawDataAction extends ShowDataAction
{
    public $name = "Raw Data";

    public function process() : bool
    {
		$search = $_GET["string"] ?? "";
		$start = $this->page * ROWS_PER_PAGE;
		$end = ROWS_PER_PAGE;
		$this->num_of_rows = $this->dbo->fetchRawData($start, $end, $search);
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
        $action = "ShowRawData";

        include "templates/actions/showrawdata_template.php";
    }
}
