<?php

require_once("dbclasses/stats.db.class.php");
require_once('classes/admin.class.php');

class StatsPage
{
	protected $dbo;

	public function __construct()
	{
		$this->dbo = new StatsDB();

		parent::__construct();
	}

	public function initPage()
	{
		parent::initPage();

		if(isset($_GET["clear"]))
		{
			unset($_SESSION["sort"]);
			unset($_SESSION["page"]);
			unset($_SESSION["type"]);
		}

//		$this->rangetype = $this->getRangetypeVar(($this->listtype == "") && (!isset($_GET["clear"])));
		$this->daterange = $this->getDateRange($this->rangetype);
		$this->getColumnType();
		$this->group_filter = $this->getGroupFilter();
		$this->did = 0;
		if(isset($_GET["did"]) && is_numeric($_GET["did"])) $this->did = $_GET["did"];

		if(!$this->action) $this->setAction("show_data");
	}

	public function initAjax()
	{
		parent::initAjax();
//		$this->rangetype = $this->getRangetypeVar(($this->listtype == "") && (!isset($_GET["clear"])));
		$this->daterange = $this->getDateRange($this->rangetype);
		$this->getColumnType();
		$this->group_filter = $this->getGroupFilter();
		$this->did = 0;
		if(isset($_GET["did"]) && is_numeric($_GET["did"])) $this->did = $_GET["did"];
	}

	protected function processAction()
	{
		switch($this->action)
		{
			case "show_new_visits":
				$this->displayPageloads("new");
				break;
			case "show_return_visits":
				$this->displayPageloads("returning");
				break;
			case "show_raw_data":
				$this->displayRawStats();
				break;
			case "show_pageloads":
				$this->displayPageloads("all");
				break;
			case "show_real_pageloads":
				$this->displayPageloads("real");
				break;
			case "show_clicks":
				$this->displayPageloads("clicks");
				break;
			case "dompageloads":
				$this->displayPageloads("domain");
				break;
			case "dompageloadsall":
				$this->displayPageloads("alldomain");
				break;
			case "show_visitor_pages":
				$this->displayPageloads("visitor");
				break;
			case "show_all_visitor_pages":
				$this->displayPageloads("visitor");
				break;
			case "ppageloads":
				$this->displayPageloads("pages");
				break;
			case "epageloads":
				$this->displayPageloads("epages");
				break;
			case "sclicks":
				$this->displayPageloads("sclicks");
				break;
			case "show_bot_traffic":
				$this->displayPageloads("bad");
				break;
			case "show_visitors":
				$this->displayVisitors("visitors");
				break;
			case "show_visitor":
				$this->displayVisitors("visitor");
				break;
			case "remove_visitor":
				$this->removeVisitor($this->did);
				break;
//			case "reset_test":
//				$this->resetTestGroup();
//				break;
			default:
//				$this->dbo->storeDailyStats();
				break;
		}
	}

	//Functions to read and display traffic stats
	public function displayPageloads($type)
	{
		$date_range = ($this->action == "show_all_visitor_pages") ? 0 : $this->daterange;
		$this->action_template = "show_pageloads";
		$start = $this->page * ROWS_PER_PAGE;
		$end = ROWS_PER_PAGE;
		$this->num_of_rows = $this->dbo->fetchPageloads($type, $date_range, $start, $end, $this->sort_by);
		$this->pages = floor(($this->num_of_rows + ROWS_PER_PAGE - 1) / ROWS_PER_PAGE);
		return false;
	}

	public function displayRawStats()
	{
		$start = $this->page * ROWS_PER_PAGE;
		$end = ROWS_PER_PAGE;
		$search = $_GET["string"] ?? "";
		$this->num_of_rows = $this->dbo->fetchRawData($this->daterange, $start, $end, $this->sort_by, $search);
		$this->pages = floor(($this->num_of_rows + ROWS_PER_PAGE - 1) / ROWS_PER_PAGE);
		return false;
	}

	public function displayVisitors($type)
	{
		$this->action_template = "show_visitors";
		$start = $this->page * ROWS_PER_PAGE;
		$end = ROWS_PER_PAGE;
		$visitor_id = ($type == "visitor") ? $_GET["did"] : 0;
		$this->num_of_rows = $this->dbo->fetchVisitors($visitor_id, $this->daterange, $this->sort_by, $start, $end);
		$this->pages = floor(($this->num_of_rows + ROWS_PER_PAGE - 1) / ROWS_PER_PAGE);
		return false;
	}
/*
	private function getRangetypeVar($checkboxes = false)
	{
		if(isset($_POST['daterange']))
		{
			if($checkboxes)
			{
				if(isset($_POST['gv'])) $_SESSION['visits_graph'] = true;
				else unset($_SESSION['visits_graph']);
				if(isset($_POST['gp'])) $_SESSION['pages_graph'] = true;
				else unset($_SESSION['pages_graph']);
				if(isset($_POST['gg'])) $_SESSION['global_graph'] = true;
				else unset($_SESSION['global_graph']);
			}
			$_SESSION["range"] = $_POST['daterange'];
			if(isset($_POST['shtest']))
			{
				if(!$_SESSION["show_test"])
				{
					$prefix = $this->table_prefix;
					$q = "select max(testgroup) as mx from {$prefix}_pageloads";
					$r = $this->dbc->query($q) or die($this->dbc->error);

					$mx = $r->fetch_row()[0];
					$_SESSION["group_filter"] = $mx;
				}
				$_SESSION['show_test'] = true;
			}
			else
			{
				unset($_SESSION['show_test']);
				$_SESSION["group_filter"] = 0;
				$this->group_filter = 0;
			}
		}
		if(isset($_SESSION["range"]))
		{
			return $_SESSION["range"];
		}
		return "today";
	}
*/
	public function getColumnType()
	{
		$column_type = 1;
		if(isset($_GET["ctype"])) $_SESSION["column_type"] = $_GET["ctype"];
		if(isset($_SESSION["column_type"])) $column_type = $_SESSION["column_type"];
		$this->columntype = $column_type;
		$start_days = 0;
		if(isset($_SESSION["start_days"])) $start_days = $_SESSION["start_days"];
		if(isset($_GET["adddays"])) $start_days += $_GET["adddays"];
		if(isset($_GET["subdays"])) $start_days += $_GET["subdays"];
		if(isset($_GET["zerodays"])) $start_days = 0;
		$this->startdays = $start_days;
		$_SESSION["start_days"] = $start_days;
	}

	public function getGroupFilter()
	{
		$testgroup = $this->group_filter;
		if(isset($_POST["testgroup"]))
		{
			$testgroup = $_POST["testgroup"];
			$_SESSION["group_filter"] = $testgroup;
			return $testgroup;
		}
		if(isset($_SESSION["group_filter"]))
		{
			return $_SESSION["group_filter"];
		}
		return $testgroup;
	}
/*
	public function resetTestGroup()
	{
		if(isset($_GET["sure"]))
		{
			$_SESSION["group_filter"] =  0;
			$prefix = $this->table_prefix;
			$q = "update {$prefix}_stats set testgroup=0";
			$r = $this->dbc->query($q) or die($this->dbc->error);
			$q = "update {$prefix}_pageloads set testgroup=0";
			$r = $this->dbc->query($q) or die($this->dbc->error);
			$q = "update {$prefix}_clickthrus set testgroup=0";
			$r = $this->dbc->query($q) or die($this->dbc->error);
			$q = "update {$prefix}_badclicks set testgroup=0";
			$r = $this->dbc->query($q) or die($this->dbc->error);
			$q = "update {$prefix}_visitors set testgroup=0";
			$r = $this->dbc->query($q) or die($this->dbc->error);
			echo "<br /><br />Done";
			return true;
		}
		else
		{
			echo "<br /><br />Clear the test group data, are you sure?";
			echo " <a href=\"admin.php?a=resettest&sure=true&subpage=stats\">Yes I'm sure</a>";
			return false;
		}
	}
*/
	private function writeShowGroupLinks($table_type)
	{
		$groups = $_SESSION["group_filter"];
		if(($groups == "") || ($groups == 0)) return;
		for($i = 1; $i <= $groups; $i++)
		{
			echo "<a href=\"#\" onclick=\"showGroupTable('$table_type', $i); return false;\">[$i]</a> ";
		}
	}

	public function showGraph()
	{
		$gg = "";
		if(isset($_SESSION['global_graph'])) $gg = "&g=1";
		$t = floor(time() / 300);
?>
<script>
    var columntype = 1;
    var startdays = 0;

    function setGraphType(ctype)
    {
	columntype = ctype;
	showGraphs();
    }

    function addStartDays(amount)
    {
	startdays += amount;
	showGraphs();
    }

    function subStartDays(amount)
    {
	startdays -= amount;
	showGraphs();
    }

    function zeroStartDays()
    {
	startdays = 0;
	showGraphs();
    }

    function showGraphs()
    {
	var gg = '<?=$gg; ?>';
	var t = Math.floor(Date.now() / 1000);
	var gdiv = document.getElementById("graphs");
	var gcontent = '<img src="graph.php?t=visits' + gg + '&c=' + columntype + '&d=' + <?=$this->domain_id; ?> + '&st=' + startdays + '&time=' + t + '" width="1800" height="320" alt="" />';
	var gcontent = gcontent + '<img src="graph.php?t=pages' + gg + '&c=' + columntype + '&d=' + <?=$this->domain_id; ?> + '&st=' + startdays + '&time=' + t + '" width="1800" height="320" alt="" />';
	gdiv.innerHTML = gcontent;
    }
</script>
<?php
		echo "<div style=\"text-align:right;font-size:10px;overflow:auto;margin:0px 40px;\">";
		echo "<a href=\"#\" onclick=\"setGraphType(1); return false;\">[Days]</a> ";
		echo "<a href=\"#\" onclick=\"setGraphType(2); return false;\">[Weeks]</a> ";
		echo "<a href=\"#\" onclick=\"setGraphType(3); return false;\">[Months]</a> ";
		echo " - <a href=\"#\" onclick=\"subStartDays(180); return false;\">[-180 Days]</a> ";
		echo "<a href=\"#\" onclick=\"subStartDays(60); return false;\">[-60 Days]</a> ";
		echo "<a href=\"#\" onclick=\"zeroStartDays(); return false;\">[0 Days]</a> ";
		echo "<a href=\"#\" onclick=\"addStartDays(60); return false;\">[+60 Days]</a> ";
		echo "<a href=\"#\" onclick=\"addStartDays(180); return false;\">[+180 Days]</a> ";
		echo "<div id=\"graphs\">";
		if(isset($_SESSION['visits_graph'])) echo "<img src=\"graph.php?t=visits$gg&c=$this->columntype&d=$this->domain_id&st=0&time=$t\" width=\"1800\" height=\"320\" alt=\"\" />\n";
		if(isset($_SESSION['pages_graph'])) echo "<img src=\"graph.php?t=pages$gg&c=$this->columntype&d=$this->domain_id&st=0&time=$t\" width=\"1800\" height=\"320\" alt=\"\" />\n";
		echo "</div></div><br />\n";
	}

	private function removeVisitor($visitor_id)
	{
		if(!is_numeric($visitor_id)) return;
		$this->dbo->removeVisitorData($visitor_id);
		return false;
	}

}

?>
