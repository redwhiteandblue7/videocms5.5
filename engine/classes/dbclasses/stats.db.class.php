<?php

require_once(DB_PATH . "manage.db.class.php");

//class intended to be inherited by the page data and analytics classes
class StatsDB extends ManageDatabase
{
    protected $daterange;
    protected $group_by;
    protected $groupclause = "";
    protected $pgroupclause = "";
    protected $dateclause = "";
    protected $group_filter = 0;

    public function setGroup($group)
    {
        if(!is_numeric($group)) return;
        $this->groupclause = " and testgroup=$group";
        $this->pgroupclause = " and {$this->table_prefix}_pageloads.testgroup=$group";
    }

    public function dateRange($daterange)
    {
        $this->daterange = $daterange;
    }

    public function setDates($start, $end)
    {
		if(!$end) {
			$this->dateclause = ">=$start";
		} else {
			$this->dateclause = " between $start and $end";
		}
    }

    public function deleteSearchDomain($srchdom)
    {
        $q = "delete from searches where dom_id=$srchdom";
		$r = $this->dbc->query($q) or die($this->dbc->error . " query was $q in " . __FILE__ . " at line " . __LINE__);
    }

    public function addSearchDomain($srchdom)
    {
        $q = "select 1 from searches where dom_id=$srchdom";
		$r = $this->dbc->query($q) or die($this->dbc->error . " query was $q in " . __FILE__ . " at line " . __LINE__);
        if($r->num_rows == 0)
        {
            $r->close();
            $q = "insert into searches set dom_id=$srchdom";
            $r = $this->dbc->query($q) or die($this->dbc->error . " query was $q in " . __FILE__ . " at line " . __LINE__);
        }
    }
}