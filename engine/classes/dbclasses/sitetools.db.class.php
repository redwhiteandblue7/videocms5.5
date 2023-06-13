<?php

require_once(DB_PATH . "manage.db.class.php");

class SitetoolsDB extends ManageDatabase
{
	/** Fetch the sites from the database into table_rows and return the total number of rows
	 * Results are filtered by tag and sponsor
	 * @param type "all" or "enabled"
	 * @param sort_by "sortBySite", "sortByName", "sortByPriority", "sortBySponsor"
	 * @param start starting row
	 * @param limit number of rows to fetch
	 * @return int number of rows
	 */
	public function fetchSites(int $start, int $limit, int $tag_filter, int $sponsor_filter, string $sort_by) : int
	{
	    $prefix = $this->table_prefix;

		$type = "all"; // *temp* default to all sites for now

		$tagq = "";
		$tagjoinq = "";
		$sponsor_q = "";

		if($tag_filter) {
			$tagq = " and tag_id=$tag_filter";
			$tagjoinq = "left join {$prefix}_site_tag_rel on {$prefix}_site_tag_rel.site_id={$prefix}_sites.site_id";
		}

		if($sponsor_filter) {
			$sponsor_q = " and sponsors.sponsor_id={$sponsor_filter}";
		}

		$q = "select SQL_CALC_FOUND_ROWS *
				from {$prefix}_sites
				$tagjoinq
				left join sponsors on sponsors.sponsor_id={$prefix}_sites.sponsor_id
				";

		if($type == "all") {
			$q .= " where 1";
		} else {
			$q .= " where {$prefix}_sites.enabled='enabled'";
		}

		$q .= $tagq;
		$q .= $sponsor_q;

		switch($sort_by) {
			case "sortBySite":
				$order = "site_id desc";
				break;
			case "sortByName":
				$order = "site_name";
				break;
			case "sortByPriority":
				$order = "pref desc";
				break;
			case "sortBySponsor":
			    $order = "sponsor_name,site_name";
			    break;
			default:
				$order = "site_name";
				break;
		}

		$q .= " order by $order limit $start, $limit";
		$r = $this->dbc->query($q) or die($this->dbc->error . " query was $q in " . __FILE__ . " at line " . __LINE__);

		$q = "select found_rows()";
		$result = $this->dbc->query($q) or die($this->dbc->error . " query was $q in " . __FILE__ . " at line " . __LINE__);
		$num_of_rows = $result->fetch_row()[0];
		$result->close();

		$i = $start;
		while($row = $r->fetch_object()) {
            $row->rownum = $i++;
            $this->table_rows[] = $row;
		}
		$r->close();
		return $num_of_rows;
	}

	public function fetchSitesWithoutPosts() : array
	{
	    $prefix = $this->table_prefix;

		$q = "select {$prefix}_sites.site_id,
				{$prefix}_sites.site_name,
				{$prefix}_sites.site_ref
				from {$prefix}_sites
				left join {$prefix}_posts on {$prefix}_sites.site_name={$prefix}_posts.title
				where {$prefix}_sites.enabled='enabled'
				and {$prefix}_posts.post_id is null
				order by site_name
				";
		$r = $this->dbc->query($q) or die($this->dbc->error . " query was $q in " . __FILE__ . " at line " . __LINE__);

		while($row = $r->fetch_object()) {
            $this->table_rows[] = $row;
		}
		$r->close();
		return $this->table_rows;
	}
}