<?php

require_once(DB_PATH . "manage.db.class.php");

class LinktradesDB extends ManageDatabase
{
	public $domain_vars;
	
	public function fetchLinks($sort_by, $start, $end)
	{
		$prefix = $this->table_prefix;

		$q = "select count(1) as cnt from {$prefix}_hardlinks";
		$r = $this->dbc->query($q) or die($this->dbc->error);
		$num_of_rows = $r->fetch_row()[0];
		$r->close();

		switch($sort_by)
		{
			case "sortByC7":
				$order = "clicks_7_days desc";
				break;
			case "sortByC1":
				$order = "clicks_24_hours desc";
				break;
			case "sortByIn7":
				$order = "ins_7_days desc";
				break;
			case "sortByIn1":
				$order = "ins_24_hours desc";
				break;
			default:
		        $order = "request_status desc, status desc, link_id desc";
		        break;
		}

		$q = "select
				{$prefix}_hardlinks.link_id,
				ref_code,
				domainstring,
				anchor,
				landing_page,
				status,
				request_status,
				outs_24_hours,
				ins_24_hours,
				clicks_24_hours,
				outs_7_days,
				ins_7_days,
				clicks_7_days,
				time_visible,
				description
				from {$prefix}_hardlinks
				order by $order
				limit $start, $end
				";
		$r = $this->dbc->query($q) or die($this->dbc->error . " query was $q in " . __FILE__ . " at line " . __LINE__);
		$i = $start;
		while($row = $r->fetch_assoc())
		{
            $row["rownum"] = $i++;
            $this->results_rows[] = $row;
		}
		$r->close();

		return $num_of_rows;
	}

	public function insertLink($vars_array)
	{
		$prefix = $this->table_prefix;
		extract($vars_array);

		if(substr($domainstring, 0, 7) == "http://") $domainstring = substr($domainstring, 7);
		if(substr($domainstring, 0, 8) == "https://") $domainstring = substr($domainstring, 8);
		if(substr($domainstring, 0, 4) == "www.") $domainstring = substr($domainstring, 4);
		if(substr($domainstring, -1) == "/") $domainstring = substr($domainstring, 0, -1);
		$landing_page = strip_tags($landing_page);

		$dom_id = $this->getDomainIndex($domainstring);

		$description = addslashes($description);
		$anchor = addslashes($anchor);

		if($link_id == 0)
		{
			$time_added = time();
			$row = $this->getLinkCodes();
			$ref_code = $row["rc"] + 10;
			$link_id = $row["lid"] + 1;
			$q = "insert into {$prefix}_hardlinks set
				status=$status,
				request_status=$request_status,
				dom_id=$dom_id,
				ref_code=$ref_code,
				link_id=$link_id,
				domainstring='$domainstring',
				landing_page='$landing_page',
				anchor='$anchor',
				description='$description',
				time_visible=$time_added
				";
			$r = $this->dbc->query($q) or die($this->dbc->error);
		}
		else
		{
			$q = "update {$prefix}_hardlinks set
				status=$status,
				request_status=$request_status,
				dom_id=$dom_id,
				ref_code=$ref_code,
				domainstring='$domainstring',
				landing_page='$landing_page',
				description='$description',
				anchor='$anchor'
				where link_id=$link_id limit 1";
			$r = $this->dbc->query($q) or die($this->dbc->error);
		}
	}

	public function getArrayFromLinksTable($link_id)
	{
		$prefix = $this->table_prefix;
		$q = "select
			{$prefix}_hardlinks.link_id,
			{$prefix}_hardlinks.anchor,
			{$prefix}_hardlinks.landing_page,
			{$prefix}_hardlinks.domainstring,
			{$prefix}_hardlinks.dom_id,
			{$prefix}_hardlinks.ref_code,
			{$prefix}_hardlinks.status,
			{$prefix}_hardlinks.request_status,
			description
			from {$prefix}_hardlinks
			where {$prefix}_hardlinks.link_id=$link_id";
		$r = $this->dbc->query($q) or die($this->dbc->error . " query was $q in " . __FILE__ . " at line " . __LINE__);

		if($row = $r->fetch_assoc())
		{
			$r->close();
			return $row;
		}
		else
		{
			return "";
		}
	}

	protected function getDomainIndex($domainstring)
	{
		$q = "select dom_id from referdomains where domainstring='$domainstring'";
		$r = $this->dbc->query($q) or die($this->dbc->error . " query was $q in " . __FILE__ . " at line " . __LINE__);
		if($r->num_rows)
		{
			$d = $r->fetch_row()[0];
			$r->close();
			return $d;
		}
		else
		{
			$q = "insert into referdomains set domainstring='$domainstring'";
			$r = $this->dbc->query($q) or die($this->dbc->error . " query was $q in " . __FILE__ . " at line " . __LINE__);
			return $this->dbc->insert_id;
		}
	}

	private function getLinkCodes()
	{
		$prefix = $this->table_prefix;
        $q = "select max(ref_code) as rc, max(link_id) as lid from {$prefix}_hardlinks";
		$r = $this->dbc->query($q) or die($this->dbc->error . ", query was $q in " . __FILE__ . " at " . __LINE__);
		if($row = $r->fetch_assoc())
		{
			return $row;
		}
		else
		{
			return array("rc"=>"0", "lid"=>"0");
		}
	}

	public function setLinkStatus($link_id, $status)
	{
		$prefix = $this->table_prefix;
		$q = "update {$prefix}_hardlinks set status=$status where id=$link_id";
		$r = $this->dbc->query($q) or die($this->dbc->error);
		return $this->dbc->affected_rows;
	}

	public function deleteLink($link_id)
	{
		$prefix = $this->table_prefix;

		$q = "delete from {$prefix}_hardlinks_tag_rel where link_id=$link_id";
		$r = $this->dbc->query($q) or die($this->dbc->error);

		$q = "delete from {$prefix}_hardlinks where link_id=$link_id";
		$r = $this->dbc->query($q) or die($this->dbc->error);
		return true;
	}
}