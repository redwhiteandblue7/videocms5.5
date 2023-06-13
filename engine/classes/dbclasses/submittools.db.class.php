<?php

require_once("dbclasses/manage.db.class.php");
require_once('dbclasses/traits/posts.trait.php');

class SubmittoolsDB extends ManageDB
{
	use PostsFuncs;
	
	public function fetchSubmits($type, $sort_by, $start, $end)
	{
		switch($type)
		{
			case "all":
				$description = "All submissions";
				$whereclause = "where 1";
				$q = "update user_submits set progress='notified' where progress='new' and submit_domain={$this->domain_vars->domain_id}";
				$r = $this->dbc->query($q) or die($this->dbc->error . " query was $q in " . __FILE__ . " at line " . __LINE__);
				break;
			case "new":
				$description = "New submissions";
				$whereclause = "where (progress='new' OR progress='notified')";
				break;
			case "pending":
				$description = "Pending submissions";
				$whereclause = "where progress='pending'";
				break;
			default:
				$description = "All submissions";
				$whereclause = "where 1";
				break;
		}

		$this->messages[] =  $description;

		$q = "select SQL_CALC_FOUND_ROWS
				submit_id,
				submit_time,
				progress,
				ip_address,
				useragent,
				user_name,
				email_addr,
				submit_title,
				submit_url,
				submit_tags,
				submit_category,
				submit_thumb,
				submit_content
				from
				user_submits
				left join domains on user_submits.submit_domain=domains.domain_id
				$whereclause
				and domains.domain_id={$this->domain_vars->domain_id}
				order by submit_id desc
				limit $start, $end";
		$r = $this->dbc->query($q) or die($this->dbc->error . " query was $q in " . __FILE__ . " at line " . __LINE__);
		$q = "select found_rows()";
		$r2 = $this->dbc->query($q) or die($this->dbc->error);
		$num_of_rows = $r2->fetch_row()[0];
		$r2->free();

		$i = $start;

		while($row = $r->fetch_assoc())
		{
			$row["rownum"] = $i++;
			$this->results_rows[] = $row;
		}
		$r->close();
		return $num_of_rows;
	}

	public function getPendingSubmitArray($submit_id)
	{
		$q = "select * from user_submits where submit_id=$submit_id limit 1";
		$r = $this->dbc->query($q) or die($this->dbc->error . " query was $q in " . __FILE__ . " at line " . __LINE__);
		if($row = $r->fetch_assoc())
		{
			$r->free();
			$this->updateSubmit($submit_id, "pending");
		}
		return $row;
	}

	public function updateSubmit($submit_id, $progress)
	{
		$q = "update user_submits set progress='$progress' where submit_id=$submit_id limit 1";
		$r = $this->dbc->query($q) or die($this->dbc->error . " query was $q in " . __FILE__ . " at line " . __LINE__);
	}

	public function findIDFromUsername($username)
	{
		$q = "select user_id from users where user_name='$username'";
		$r = $this->dbc->query($q) or die($this->dbc->error);
		if($row = $r->fetch_assoc())
		{
			return $row["user_id"];
		}
		else
		{
			return "";
		}
	}

	public function findIDFromEmail($email)
	{
		$q = "select user_id from users where email_addr='$email'";
		$r = $this->dbc->query($q) or die($this->dbc->error);
		if($row = $r->fetch_assoc())
		{
			return $row["user_id"];
		}
		else
		{
			return "";
		}
	}
}