<?php

require_once(DB_PATH . "manage.db.class.php");

class ToolsDB extends ManageDatabase
{
    public function countPages() : int
    {
		$prefix = $this->table_prefix;
		if(!in_array("{$prefix}_pages", $this->tables_array)) return 0;
		$q = "select count(1) as cnt from {$prefix}_pages";
		$r = $this->dbc->query($q) or die($this->dbc->error);
		$pages = $r->fetch_row()[0];
		$r->free();
        return $pages;
    }

    public function countReferstrings() : int
    {
		$prefix = $this->table_prefix;
		if(!in_array("{$prefix}_referstrings", $this->tables_array)) return 0;
		$q = "select count(1) as cnt from {$prefix}_referstrings";
		$r = $this->dbc->query($q) or die($this->dbc->error);
		$referstrings = $r->fetch_row()[0];
		$r->free();
        return $referstrings;
    }

	public function importFlvs()
	{
		$prefix = $this->table_prefix;

		$q = "select * from {$prefix}_flvs";
		$r = $this->dbc->query($q) or die($this->dbc->error . " query was $q in " . __FILE__ . " at line " . __LINE__);
        while($row = $r->fetch_assoc()) {
			extract($row);
			$pagename .= "-" . $flv_id;

			$post_id = 0;
			$description = "";
			$post_type = "video";
			$display_state = "display";
			$site_url = "";
			$link_type = "dofollow";
			$priority = 0;
			$trade_id = 0;
			$sub_title = "";
			$time_updated = 0;

            $vars = compact("post_id", "thumb_url", "video_url", "alt_title", "title", "pagename", "site_id", "duration", "orig_height", "orig_width", "orig_thumb", 
							"time_added", "time_visible", "time_updated", "categories", "site_url", "post_type", "link_type", "display_state", "description");
            $post_result = $this->insertPost($vars);
			if($post_result) {
				$this->messages[] = $post_result;
			} else {
				$this->messages[] = "Failed to insert post $pagename";
			}
		}
	}

    public function importDynamicPages()
    {
		$prefix = $this->table_prefix;
		$domain_id = $this->domain_id;
        $q = "select
                domain_page_rel.page_id,
                domain_page_rel.page_name,
                redir_page.page_dest
                from
                domain_page_rel
                left join redir_page on domain_page_rel.page_id=redir_page.page_id
                where page_type='redir' and domain_id=$domain_id";
		$r = $this->dbc->query($q) or die($this->dbc->error . " query was $q in " . __FILE__ . " at line " . __LINE__);
        while($row = $r->fetch_assoc()) {
            extract($row);
            $query = "insert into {$prefix}_dynamic_page set dest_url='$page_dest', page_name='$page_name', page_id=$page_id";
            $result = $this->dbc->query($query) or die($this->dbc->error . " query was $query in " . __FILE__ . " at line " . __LINE__);
        }

        $q = "select
                domain_page_rel.page_id,
                domain_page_rel.page_name,
                dynamic_page.page_filename,
                description
                from
                domain_page_rel
                left join dynamic_page on domain_page_rel.page_id=dynamic_page.page_id
                left join {$prefix}_page_description_rel on domain_page_rel.page_id={$prefix}_page_description_rel.page_id
                left join {$prefix}_descriptions on {$prefix}_descriptions.desc_id={$prefix}_page_description_rel.desc_id
                where page_type='dynamic' and domain_id=$domain_id";
        $r = $this->dbc->query($q) or die($this->dbc->error . " query was $q in " . __FILE__ . " at line " . __LINE__);
        while($row = $r->fetch_assoc()) {
            extract($row);
			if($description) $description = addslashes($description);
            $query = "insert into {$prefix}_dynamic_page set page_filename='$page_filename', page_name='$page_name', page_id=$page_id, description='$description'";
            $result = $this->dbc->query($query) or die($this->dbc->error . " query was $query in " . __FILE__ . " at line " . __LINE__);
        }
    }

    public function importDynamicPages5_1()
    {
		$prefix = $this->table_prefix;
        $q = "select
				page_id,
                description
                from
                {$prefix}_page_descriptions";
        $r = $this->dbc->query($q) or die($this->dbc->error . " query was $q in " . __FILE__ . " at line " . __LINE__);
        while($row = $r->fetch_assoc())
        {
            extract($row);
			$description = addslashes($description);
            $query = "update {$prefix}_dynamic_page set description='$description' where page_id=$page_id";
            $result = $this->dbc->query($query) or die($this->dbc->error . " query was $query in " . __FILE__ . " at line " . __LINE__);
        }
    }

	public function importLinkDescriptions()
	{
		$prefix = $this->table_prefix;
		$q = "select {$prefix}_hardlinks.link_id, description from {$prefix}_hardlinks
				left join {$prefix}_hardlinks_description_rel on {$prefix}_hardlinks.link_id={$prefix}_hardlinks_description_rel.link_id
				left join {$prefix}_descriptions on {$prefix}_descriptions.desc_id={$prefix}_hardlinks_description_rel.desc_id";
		$r = $this->dbc->query($q) or die($this->dbc->error . ", query was $q in " . __FILE__ . " at " . __LINE__);
		while($row = $r->fetch_assoc())
		{
			extract($row);
			if($description)
			{
				$description = addslashes($description);
				$query = "update {$prefix}_hardlinks set description='$description' where link_id=$link_id";
				$result = $this->dbc->query($query) or die($this->dbc->error);
			}
		}
	}

	/**
	 *  TODO: these two functions need to be merged to update the posts from the old 4.x version to the current database design
	 */
	public function importPostDescriptions()
	{
		$prefix = $this->table_prefix;
		$q = "select {$prefix}_posts.post_id, description from {$prefix}_posts
				left join {$prefix}_post_description_rel on {$prefix}_posts.post_id={$prefix}_post_description_rel.post_id
				left join {$prefix}_descriptions on {$prefix}_descriptions.desc_id={$prefix}_post_description_rel.desc_id";
		$r = $this->dbc->query($q) or die($this->dbc->error . ", query was $q in " . __FILE__ . " at " . __LINE__);
		while($row = $r->fetch_assoc())
		{
			extract($row);
			if($description)
			{
				$description = addslashes($description);
				$query = "insert into {$prefix}_post_descriptions set description='$description', post_id=$post_id";
				$result = $this->dbc->query($query) or die($this->dbc->error);
			}
		}
	}

	public function importPostsTemp()
	{
		$prefix = $this->table_prefix;
		$q = "select {$prefix}_posts.*, {$prefix}_post_stats.*, {$prefix}_post_descriptions.description
			from
			{$prefix}_posts
			left join {$prefix}_post_stats on {$prefix}_post_stats.post_id={$prefix}_posts.post_id
			left join {$prefix}_post_descriptions on {$prefix}_post_descriptions.post_id={$prefix}_posts.post_id
			";
		$r = $this->dbc->query($q) or die($this->dbc->error . " query was $q in " . __FILE__ . " at line " . __LINE__);
		while($row = $r->fetch_object())
		{
			$this->updateRow("posts", $row, true);
		}
	}

	public function updateGooglebotStats()
	{
		$prefix = $this->table_prefix;
		$q = "select max(stime) as st from {$prefix}_googlebot_stats";
		$r = $this->dbc->query($q) or die($this->dbc->error . " query was $q in " . __FILE__ . " at line " . __LINE__);
		$st = 0;
		if($r->num_rows)
		{
			$st = $r->fetch_row()[0];
			if(!is_numeric($st)) $st = 0;
			$r->free();
		}

		$q = "select * from {$prefix}_stats where useragent like '%googlebot%' and stime>$st";
		$r = $this->dbc->query($q) or die($this->dbc->error . " query was $q in " . __FILE__ . " at line " . __LINE__);
		while($row = $r->fetch_assoc())
		{
			extract($row);
			$u = explode("/", $pagename);
			if(is_numeric($u[1]))
			{
				$flv_id = $u[1];
			}
			else
			{
				$page = $u[sizeof($u) - 1];
				$p = explode(".", $page);
				$p = $p[0];
				$pa = explode("-", $p);
				$n = $pa[sizeof($pa) - 1];
				$flv_id = (is_numeric($n)) ? $n : 0;
			}
			$pagename = addslashes($pagename);

			$q2 = "insert into {$prefix}_googlebot_stats set
				stat_id=$stat_id,
				stime=$stime,
				pagename='$pagename',
				gallery_id=$flv_id,
				ip_address=$ip_address,
				useragent='$useragent'
				";
			$r2 = $this->dbc->query($q2) or die($this->dbc->error . " query was $q2 in " . __FILE__ . " at line " . __LINE__);
			}
		$r->free();
	}

	public function fetchGooglebotStats($order)
	{
		$prefix = $this->table_prefix;

		$post_stats = array();
		$q = "select post_id, monthly_clicks from {$prefix}_post_stats";
		$r = $this->dbc->query($q) or die($this->dbc->error . " query was $q in " . __FILE__ . " at line " . __LINE__);
		while($row = $r->fetch_assoc()) {
			extract($row);
			$post_stats[$post_id] = $monthly_clicks;
		}

		switch($order) {
			case "page":
				$q = "select pagename, ANY_VALUE(gallery_id), count(1) as co from {$prefix}_googlebot_stats group by pagename order by pagename, co desc";
				break;
			case "count":
				$q = "select pagename, ANY_VALUE(gallery_id), count(1) as co from {$prefix}_googlebot_stats group by pagename order by co desc, pagename";
				break;
			default:
				break;
		}

		$r = $this->dbc->query($q) or die($this->dbc->error . " query was $q in " . __FILE__ . " at line " . __LINE__);
		while($row = $r->fetch_assoc()) {
			extract($row);
			if(substr($pagename, 0, 5) != "[403]") {
				$u = explode("/", $pagename);
				if(is_numeric($u[1])) {
					$post_id = $u[1];
				} else {
					$page = $u[sizeof($u) - 1];
					$p = explode(".", $page);
					$p = $p[0];
					$pa = explode("-", $p);
					$n = $pa[sizeof($pa) - 1];
					$post_id = (is_numeric($n)) ? $n : 0;
				}
				$row["gallery_id"] = $post_id;
				if($post_id) {
					$row["views"] = $post_stats[$post_id] ?? 0;
				} else {
					$row["views"] = "-";
				}

				$this->results_rows[] = $row;
			}
		}
		return sizeof($this->results_rows);
	}

	public function resetStats($stime)
	{
		$prefix = $this->table_prefix;

		$q = "select min(stime) as st from {$prefix}_stats";
		$r = $this->dbc->query($q) or die($this->dbc->error . " query was $q in " . __FILE__ . " at line " . __LINE__);
		$min_stime = $r->fetch_row()[0];

		$stime = max($stime, $min_stime);

		$q = "delete from {$prefix}_pageloads where stime>$stime";
		$r = $this->dbc->query($q) or die($this->dbc->error . " query was $q in " . __FILE__ . " at line " . __LINE__);

		$q = "delete from {$prefix}_badclicks where stime>$stime";
		$r = $this->dbc->query($q) or die($this->dbc->error . " query was $q in " . __FILE__ . " at line " . __LINE__);

		$q = "delete from {$prefix}_clickthrus where stime>$stime";
		$r = $this->dbc->query($q) or die($this->dbc->error . " query was $q in " . __FILE__ . " at line " . __LINE__);

		$q = "delete from {$prefix}_visitors where first_time>$stime";
		$r = $this->dbc->query($q) or die($this->dbc->error . " query was $q in " . __FILE__ . " at line " . __LINE__);

		$q = "update domains set
			time_last_stat_update=$stime
			where
			domain_id=$this->domain_id
			";
		$r = $this->dbc->query($q) or die($this->dbc->error . " query was $q in " . __FILE__ . " at line " . __LINE__);
	}
}