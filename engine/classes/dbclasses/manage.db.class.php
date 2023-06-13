<?php

	require_once(INCLUDE_PATH . 'classes/database.class.php');

class ManageDatabase extends Database
{
	public $messages = [];
    protected $sort_by;

	private $domains = [];
	protected $tables_array = [];
	protected $results_rows = [];
	protected $results_row_pointer = 0;
    protected $message_pointer = 0;

	public function __construct()
    {
		$this->mysqldrvr = new mysqli_driver();
		$this->mysqldrvr->report_mode = MYSQLI_REPORT_ERROR;
		$this->dbc = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
		$this->getTables();
    }

	//set the sort_by property for use in any display results functions
	public function sortby(string $sort)
	{
		$this->sort_by = $sort;
	}

	//get the names of all the existing tables in the database into tables_array
    private function getTables()
    {
        $q = "show tables";
        $result = $this->dbc->query($q) or die($this->dbc->error);
        while($r = $result->fetch_row())
        {
            $this->tables_array[] = $r[0];
        }
    }

	/** Delete tables that start with @param name
	 * 
	 * ! Use carefully! Only intended for use in deleting domain tables
	 */
	public function deleteTables(string $name = "")
	{
		//if name were an empty string it would drop all the tables!
		if(!$name) $name = $this->table_prefix;
		foreach($this->tables_array as $table) {
			if(strpos($table, $name) === 0) {
				$q = "drop table $table";
				$r = $this->dbc->query($q) or die($this->dbc->error);	
			}
		}

	}

	public function getTagFromTitle($title, $replace = true)
	{
		$pagename = strtolower($title);
		$ps = array(" ", "!", "?", ",", ".", "&", ":", ";", "'", "\"", "(", ")", "+", "=", "/", "\\", "[", "]", "{", "}", "<", ">", "|", "`", "~", "@", "#", "$", "%", "^", "*");
		$pr = array("-", "");
		$pagename = str_replace($ps, $pr, $pagename);

		if($replace)
		{
			$pagename = str_replace("-a-", "-", $pagename);
			$pagename = str_replace("-at-", "-", $pagename);
			$pagename = str_replace("-an-", "-", $pagename);
			$pagename = str_replace("-in-", "-", $pagename);
			$pagename = str_replace("-on-", "-", $pagename);
			$pagename = str_replace("-to-", "-", $pagename);
			$pagename = str_replace("-is-", "-", $pagename);
			$pagename = str_replace("-and-", "-", $pagename);
			$pagename = str_replace("-the-", "-", $pagename);
		}
		return $pagename;
	}


	protected function addTags($rel_table, $column)
	{
		$vars = [];
		$tag_id = 0;
		if($this->autotag != 0) {
			$tag_id = $this->autotag;
		} else {
			if(isset($_GET["tid"])) $tag_id = $this->get_vars->tid;
		}

		if($tag_id == 0) {
			return false;
		} else {
			$vars["tag_id"] = $tag_id;
			$vars["$column"] = $this->did;
			if($this->dbo->getArrayFromTableRowByValues("{$rel_table}_tag_rel", $vars, true)) {
				return false;
			}
			$this->dbo->insertTableColumns("{$rel_table}_tag_rel", $vars, true);
			return true;
		}
	}

	public function getDomains()
	{
		if(!sizeof($this->domains)) {
			if(in_array("domains", $this->tables_array)) {
				$q = "select domain_id, domain_name from domains order by domain_id";
				$r = $this->dbc->query($q) or die($this->dbc->error);
				while($row = $r->fetch_assoc()) {
					$this->domains[] = $row;
				}
				$r->close();
			}
		}
		return $this->domains;
	}
}
?>