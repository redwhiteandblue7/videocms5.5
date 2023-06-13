<?php

	require_once(INCLUDE_PATH . 'defines.php');

class Db
{
	protected $dbc;
	public $table_prefix;
	protected $tables = array();
    public $hostname;
    public $base_url;
    public $http_scheme;

	public function __construct()
	{
		$this->dbc = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
        $h = $_SERVER["HTTP_HOST"];
        $s = $_SERVER["REQUEST_SCHEME"];
        if(substr($h, 0, 3) == "192")
        {
            $h = FALLBACK_DOMAIN;
            $s = "http";
        }
        $this->hostname = $h;
        $this->http_scheme = $s;
        $this->base_url = $s . "://" . $_SERVER["HTTP_HOST"] . "/";
		$this->table_prefix = $this->domainPrefix();
	}

	public function __destruct()
	{
		$this->dbc->close();
	}

	private function getDomainPrefix($domainstring)
	{
		$domainstring = str_replace(".", "_", $domainstring);
		$domainstring = str_replace("-", "_", $domainstring);
		$domainstring = strtolower($domainstring);

		return $domainstring;
	}

	protected function getTablesPrefix($hostname)
	{
		if(substr($hostname, 0, 4) == "www.")
		{
			$hostname = substr($hostname, 4);
		}
		elseif(substr($hostname, 0, 5) == "test.")
		{
			$hostname = substr($hostname, 5);
		}
		$dom = explode(".", $hostname);
		$domain = $dom[0];
		return $this->getDomainPrefix($domain);
	}

	protected function domainPrefix()
	{
		return $this->getTablesPrefix($this->hostname);
	}

	public function getArrayFromTableRowById($table, $column, $row_id)
	{
		$q = "select * from $table where $column=$row_id";
		$r = $this->dbc->query($q) or die($this->dbc->error);

		if($row = $r->fetch_assoc()) $r->close();
		return $row;
	}

	protected function getTablesArray()
	{
		if(!sizeof($this->tables))
		{
			$q = "show tables";
			$r = $this->dbc->query($q) or die($this->dbc->error);
			while($row = $r->fetch_row())
			{
				$this->tables[] = $row[0];
			}
			$r->free();
		}
	}

}

?>
