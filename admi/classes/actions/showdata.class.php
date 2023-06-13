<?php
    require_once(DB_PATH .'pagedata.db.class.php');
    require_once(HOME_DIR . 'admi/classes/actions/displayaction.class.php');
	require_once(OBJECTS_PATH . 'domain.class.php');

class ShowDataAction extends DisplayAction
{
	public $name = "Stats";

    public function __construct()
    {
        $this->dbo = new PageDataDB();
        $this->dbo->setPrefix();

        $this->daterange = $this->dateRange();
        $this->range_val = $this->rangeVal($this->daterange);
		$this->dbo->dateRange($this->range_val);

        parent::__construct();

        $this->dbo->sortby($this->sort_by);
    }

    protected function sortby()
    {
		if(isset($this->get_object->sortBy)) {
			$_SESSION["showdata_sortby"] = $this->get_object->sortBy;
		}
        if(isset($_SESSION["showdata_sortby"])) {
            $this->sort_by = $_SESSION["showdata_sortby"];
        }
    }

    public function prerender() : void
    {
        $dmo = new Domain();
        $update_time = $dmo->varsArray()["time_last_stat_update"];
		$this->tracked = $dmo->varsArray()["se_tracking"];

        include "templates/stats_template.php";
    }

    public function render() : void
    {
        include "templates/actions/showdata_template.php";
    }

    protected function dateRange() : string
    {
		if(isset($_POST['daterange'])) {
			$_SESSION["daterange"] = $_POST['daterange'];
        }

		if(isset($_SESSION["daterange"])) {
			return $_SESSION["daterange"];
		} else {
            return "today";
        }
    }

	protected function rangeVal($daterange) : int
	{
		$gmt_td = (intval(date("O")) / 100) * 3600;                    //get difference between time zone and GMT
		$t = time();

		$datearray = getdate($t - $gmt_td);
		$day = $datearray["mday"];
		$month = $datearray["mon"];
		$weekday = $datearray["wday"];
		$year = $datearray["year"];
		$hour = $datearray["hours"];
		$minute = $datearray["minutes"];

		switch($daterange)
		{
			case "today":
				return gmmktime(0, 0, 0, $month, $day, $year);
			case "last24":
				return $t - 86400;
			case "yesterday":
				return gmmktime(0, 0, 0, $month, $day - 1, $year);
			case "last48":
				return $t - 172800;
			case "twodaysago":
				return gmmktime(0, 0, 0, $month, $day - 2, $year);
			case "last72":
				return $t - 259200;
			case "thisweek":
				if($weekday == 0) return gmmktime(0, 0, 0, $month, $day - 6, $year);
				else return gmmktime(0, 0, 0, $month, $day - $weekday + 1, $year);
			case "last7":
				return gmmktime(0, 0, 0, $month, $day - 7, $year);
			case "all":
				return 0;
			default:
				return gmmktime(0, 0, 0, $month, $day, $year);
		}
	}
}
?>