<?php

require_once("dbclasses/tools.db.class.php");
require_once('classes/admin.class.php');

class ToolsPage extends AdminPage
{
	public $state = "ready";
	protected $dbo;

	public function __construct()
	{
		$this->dbo = new ToolsDB();

		parent::__construct();
	}

	public function processAction()
	{
		switch($this->action)
		{
			case "upgradepages":
				$result = $this->upgradePages();
				break;
			case "upgradeflvs":
				$result = $this->upgradeFlvs();
				break;
			case "upgrade5pages":
				$result = $this->upgradePages5_1();
				break;
			case "upgradepostdesc":
				$result = $this->upgradePostDescriptions();
				break;
			case "upgradelinkdesc":
				$result = $this->upgradeLinkDescriptions();
				break;
			case "storegooglebots":
				$result = $this->storeGooglebots();
				break;
			case "googlebots":
				$result = $this->showGooglebots("page");
				break;
			case "googlebotc":
				$result = $this->showGooglebots("count");
				break;
			case "googlebothits":
				$result = $this->showGooglebots("hits");
				break;
			case "resettables":
				$result = $this->resetTables();
				break;
			case "pingsitemap":
				$result = $this->pingSitemap();
				break;
			case "convertpostsxml":
				$result = $this->convertPostsToXML();
				break;
			case "addtrades":
				$result = $this->addTradesToPosts();
				break;
			case "analyseposts":
				$result = $this->analysePosts();
				break;
			case "exportsimilarweb":
				$result = $this->exportSimilarwebURLs();
				break;
			case "importsimilarweb":
				$result = $this->importSimilarwebData();
				break;
			case "rankposts":
				$result = $this->rankPosts();
				break;
			default:
				$result = $this->showDefaults();
				break;
		}
		return $result;
	}

	private function showDefaults()
	{
		$this->setAction("show_defauls");
		$this->rememberMe();

		$pages = $this->dbo->countPages();
		$referstrings = $this->dbo->countReferstrings();

		$this->status_messages[] = "$pages rows in the pages table.<br />$referstrings rows in the referrer table.";
		return false;
	}

	private function upgradeFlvs()
	{
		$this->dbo->importFlvs();
		$this->status_messages[] = "Okay, I think";
		return true;
	}

	private function upgradePages()
	{
		$this->dbo->importDynamicPages();
		$this->status_messages[] = "Okay, I think";
		return true;
	}

	private function upgradePages5_1()
	{
		$this->dbo->importDynamicPages5_1();
		$this->status_messages[] = "Okay, I think";
		return true;
	}

	private function upgradePostDescriptions()
	{
		$this->dbo->importPostDescriptions();
		$this->status_messages[] = "Okay, I think";
		return true;
	}

	private function upgradeLinkDescriptions()
	{
		$this->dbo->importLinkDescriptions();
		$this->status_messages[] = "Okay, I think";
		return true;
	}

	private function addTradesToPosts()
	{
		$this->dbo->setCatFilter("");
		$this->dbo->setTagFilter(0);
		$this->dbo->setSiteFilter(0);

		$this->dbo->fetchPosts("full", "sortByID", 0, 99999);
		$i = 0;
		while($row = $this->dbo->getNextResultsRow())
		{
			extract($row);
			if(!$site_id && !$trade_id)
			{
				$this->dbo->insertTrade($post_id);
				$i++;
			}
		}
		$this->status_messages[] = "$i posts updated, or something like that.";
		return true;
	}

	private function convertPostsToXML()
	{
		$this->dbo->setCatFilter("");
		$this->dbo->setTagFilter(0);
		$this->dbo->setSiteFilter(0);

		$this->dbo->fetchPosts("full", "sortByID", 0, 99999);

		return false;
	}

	private function analysePosts()
	{
		$this->dbo->setCatFilter("");
		$this->dbo->setTagFilter(0);
		$this->dbo->setSiteFilter(0);

		$this->dbo->fetchPosts("full", "sortByID", 0, 99999);

		return false;
	}

	private function rankPosts()
	{
		$this->dbo->setCatFilter("");
		$this->dbo->setTagFilter(0);
		$this->dbo->setSiteFilter(0);

		$post_count = $this->mod->rerankPosts();
		$this->status_messages[] = $post_count . " posts re-ranked";
		return true;
	}

	private function exportSimilarwebURLs()
	{
		$this->dbo->setCatFilter("");
		$this->dbo->setTagFilter(0);
		$this->dbo->setSiteFilter(0);

		$this->dbo->fetchPosts("full", "sortByID", 0, 99999);

		return false;
	}

	private function importSimilarwebData()
	{
		$this->dbo->setCatFilter("");
		$this->dbo->setTagFilter(0);
		$this->dbo->setSiteFilter(0);

		if(!isset($_POST["data_dump"])) return false;
		extract($this->post_array);
		if(!trim($data_dump))
		{
			$this->error_messages[] = "There does not seem to be anything there";
			return false;
		}
		$data_dump = str_replace("- -", "", $data_dump);

		$msg = $this->mod->processSimilarwebData($data_dump);

		if(sizeof($this->mod->error_messages))
		{
			$this->error_messages = $this->mod->error_messages;
			$this->error_messages[] = $msg;
			return false;
		}

		$this->status_messages[] = $msg;
		return false;
	}

	private function storeGooglebots()
	{
		$this->dbo->updateGooglebotStats();
		$this->status_messages[] = "Updated. Now you can view the latest Googlebot stats.";
		return true;
	}

	private function showGooglebots($order = "page")
	{
		$this->action_template = "show_googlebots";
		$this->num_of_rows = $this->dbo->fetchGooglebotStats($order);
		return false;
	}

	private function pingSitemap()
	{

	}
/*
	private function emptyStats()
	{
		$prefix = $this->table_prefix;

		if(isset($_GET["continue"]))
		{
			$q = "truncate table {$prefix}_pages";
			$r = $this->dbc->query($q) or die($this->dbc->error);
			$q = "truncate table {$prefix}_referstrings";
			$r = $this->dbc->query($q) or die($this->dbc->error);
			echo "<br /><br />Done them.";
		}
		else
		{
			echo "<a href=\"admin.php?subpage=tools&a=emptystats&continue=true\">Are you sure you want to clear the tables?</a>";
		}
	}
*/
	private function resetTables()
	{
		$this->action_template = "reset_tables";

		if(isset($_POST["daterange"]))
		{
			$rangetype = $_POST["daterange"];
			$stime = $this->getDateRange($rangetype);

			$this->dbo->resetStats($stime);

			$this->status_messages[] = "Reset stats from " . gmdate("H:i:s l F j Y", $stime);
			return true;
		}

		return false;
	}
/*
	private function optimiseStats()
	{
		$prefix = $this->table_prefix;

		if(isset($_GET["continue"]))
		{
			$q = "optimize table {$prefix}_stats";
			$r = $this->dbc->query($q) or die($this->dbc->error);
			$row = $r->fetch_assoc();
			extract($row);
			echo "<br />Table $Table $Op, $Msg_type: $Msg_text";

			$q = "optimize table {$prefix}_pageloads";
			$r = $this->dbc->query($q) or die($this->dbc->error);
			$row = $r->fetch_assoc();
			extract($row);
			echo "<br />Table $Table $Op, $Msg_type: $Msg_text";

			$q = "optimize table {$prefix}_visitors";
			$r = $this->dbc->query($q) or die($this->dbc->error);
			$row = $r->fetch_assoc();
			extract($row);
			echo "<br />Table $Table $Op, $Msg_type: $Msg_text";

			$q = "optimize table {$prefix}_clickthrus";
			$r = $this->dbc->query($q) or die($this->dbc->error);
			$row = $r->fetch_assoc();
			extract($row);
			echo "<br />Table $Table $Op, $Msg_type: $Msg_text";

			$q = "optimize table {$prefix}_badclicks";
			$r = $this->dbc->query($q) or die($this->dbc->error);
			$row = $r->fetch_assoc();
			extract($row);
			echo "<br />Table $Table $Op, $Msg_type: $Msg_text";

			echo "<br /><br />Done them.";
		}
		else
		{
			echo "<a href=\"admin.php?subpage=tools&a=optimise&continue=true\">Are you sure you want to optimise the tables?</a>";
		}
	}
*/
}

?>
