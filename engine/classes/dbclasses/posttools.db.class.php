<?php

require_once(DB_PATH . "manage.db.class.php");

class PosttoolsDB extends ManageDatabase
{
	public $post_id = 0;

	public function fetchPost(int $post_id)
	{
		$prefix = $this->table_prefix;

		$q = "select {$prefix}_posts.*, {$prefix}_sites.site_name
				from
				{$prefix}_posts
				left join {$prefix}_sites on {$prefix}_posts.site_id={$prefix}_sites.site_id
				where {$prefix}_posts.post_id=$post_id
				";
		$r = $this->dbc->query($q) or die($this->dbc->error . " query was $q in " . __FILE__ . " at line " . __LINE__);
		if($row = $r->fetch_object())
		{
			$r->close();
			return $row;
		}
		else
		{
			return "";
		}
	}

	//Same as fetchPost but using the pagename (slug) to identify the row
	public function fetchPostByPagename(string $pagename)
	{
		$prefix = $this->table_prefix;

		$q = "select {$prefix}_posts.*, {$prefix}_sites.site_name
				from
				{$prefix}_posts
				left join {$prefix}_sites on {$prefix}_posts.site_id={$prefix}_sites.site_id
				where {$prefix}_posts.pagename='$pagename'
				";
		$r = $this->dbc->query($q) or die($this->dbc->error . " query was $q in " . __FILE__ . " at line " . __LINE__);
		if($row = $r->fetch_object())
		{
			$r->close();
			return $row;
		}
		else
		{
			return "";
		}
	}

	/** Fetch posts of post_type 'video'
	 * @param start - start index
	 * @param limit - number of rows to fetch
	 * @param sort_by - sort by
	 * @param list - list of post IDs to fetch (optional), if provided, start, limit, and sort_by are ignored
	 * @return number of rows
	 */
	public function fetchVideoPosts(int $start, int $limit, string $sort_by = "", string $list = "", int $channel = 0, int $tag = 0, int $user_id = 0) : int
	{
		$prefix = $this->table_prefix;

		if($list) {
			$in_list = "and {$prefix}_posts.post_id in ($list)";
			$order = "order by field({$prefix}_posts.post_id, $list)";
		}
		else {
			$in_list = "";
			switch($sort_by) {
				case "sortByAdded":
					$order = "order by {$prefix}_posts.time_added desc";
					break;
				case "sortByVisible":
					$order = "order by {$prefix}_posts.time_visible desc";
					break;
				case "sortByUpdated":
					$order = "order by {$prefix}_posts.time_updated desc";
					break;
				case "sortByDaily":
					$order = "order by daily_clicks desc, time_visible desc";
					break;
				case "sortByMonthly":
					$order = "order by monthly_clicks desc, time_visible desc";
					break;
				case "sortByViews":
					$order = "order by total_clicks desc, time_visible desc";
					break;
				case "sortByID":
					$order = "order by {$prefix}_posts.post_id";
					break;
				case "sortByIDR":
					$order = "order by {$prefix}_posts.post_id desc";
					break;
				case "sortByTrend":
					$t = time();
					$order = "order by SQRT(total_clicks * 10000) / ($t - {$prefix}_posts.time_added) desc";
					break;
				default:
					$order = "";
					break;
			}
		}

		if($tag) {
			$tag_q = "and {$prefix}_post_tag_rel.tag_id=$tag";
			$tagjoinq = "left join {$prefix}_post_tag_rel on {$prefix}_post_tag_rel.post_id={$prefix}_posts.post_id";
		} else {
			$tag_q = "";
			$tagjoinq = "";
		}

		if($channel) {
			$channel_q = "and {$prefix}_channels.channel_id=$channel";
		} else {
			$channel_q = "";
		}

		if($user_id) {
			$user_q = "and {$prefix}_posts.user_id=$user_id";
		} else {
			$user_q = "and {$prefix}_posts.display_state='display'";
		}

		$q = "select count(1) as cnt from
			{$prefix}_posts
			left join {$prefix}_videos on {$prefix}_videos.video_id={$prefix}_posts.video_id
			left join {$prefix}_channels on {$prefix}_channels.channel_id={$prefix}_videos.channel_id
			$tagjoinq
			where 1
			and post_type='video'
			$user_q
			and {$prefix}_posts.post_id!=$this->post_id
			$in_list
			$tag_q
			$channel_q
			";
		$r = $this->dbc->query($q) or die($this->dbc->error . ", query was $q");
		$num_of_rows = $r->fetch_row()[0];
		$r->free();

		$q = "select {$prefix}_posts.*, {$prefix}_channels.*, {$prefix}_videos.*, {$prefix}_posts.time_added as post_time
			from
			{$prefix}_posts
			left join {$prefix}_videos on {$prefix}_videos.video_id={$prefix}_posts.video_id
			left join {$prefix}_channels on {$prefix}_channels.channel_id={$prefix}_videos.channel_id
			$tagjoinq
			where 1
			and post_type='video'
			$user_q
			and {$prefix}_posts.post_id!=$this->post_id
			$in_list
			$tag_q
			$channel_q
			$order
			limit $start, $limit
			";
		$r = $this->dbc->query($q) or die($this->dbc->error . " query was $q in " . __FILE__ . " at line " . __LINE__);
		$i = $start;

		$this->table_rows = [];
		while($row = $r->fetch_object()) {
			$row->rownum = $i++;
			$this->table_rows[] = $row;
		}
		$r->close();
		return $num_of_rows;
	}

	public function fetchPosts(int $start, int $limit, int $site_filter = 0, int $sponsor_filter = 0, $tag_filter = 0, string $sort_by = "", string $list = "") : int
	{
		$prefix = $this->table_prefix;

		$tag_q = "";
		$tagjoinq = "";
		$sponsor_q = "";
		$sponsorjoinq = "";
//		$display_type = $this->display_type;
		$display_type = "full";
		$list_q = "";

		$filter_q = ($site_filter == 0) ? "" : "and {$prefix}_posts.site_id=$site_filter ";
		if($tag_filter) {
			if($tag_filter == "None") {
				$tag_q = "and {$prefix}_post_tag_rel.id IS NULL";
			} else {
				$tag_q = "and tag_id=$tag_filter";
			}
			$tagjoinq = "left join {$prefix}_post_tag_rel on {$prefix}_post_tag_rel.post_id={$prefix}_posts.post_id";
		}

		if($sponsor_filter) {
			$sponsor_q = "and sponsors.sponsor_id=$sponsor_filter";
			$sponsorjoinq = "left join sponsors on sponsors.sponsor_id={$prefix}_sites.sponsor_id";
		}

		if($display_type == "full") {
			$site_q = "";
			$display_q = "";
		} else {
			$site_q = "({$prefix}_sites.enabled='enabled' or {$prefix}_posts.site_id=0)";
			$display_q = " and display_state!='delete'";
		}

		if($list) {
			$list_q = " and {$prefix}_posts.post_id in ($list)";
		}

		$q = "select count(1) as cnt from
			{$prefix}_posts
			left join {$prefix}_sites on {$prefix}_sites.site_id={$prefix}_posts.site_id
			$sponsorjoinq
			$tagjoinq
			where {$prefix}_posts.post_id!=$this->post_id
			$site_q
			$sponsor_q
			$tag_q
			$filter_q
			$display_q
			$list_q
			";
		$r = $this->dbc->query($q) or die($this->dbc->error . ", query was $q");
		$row = $r->fetch_assoc();
		$num_of_rows = $row["cnt"];
		$r->free();

		switch($sort_by) {
			case "sortBySite":
				$order = "order by site_id";
				break;
			case "sortByTitle":
				$order = "order by title";
				break;
			case "sortByPriority":
				$order = "order by priority desc, {$prefix}_posts.post_id desc";
				break;
			case "sortByRank":
				$order = "order by ranking desc, priority desc, {$prefix}_posts.post_id desc";
				break;
			case "sortByAdded":
				$order = "order by time_added desc";
				break;
			case "sortByVisible":
				$order = "order by time_visible desc";
				break;
			case "sortByUpdated":
				$order = "order by time_updated desc";
				break;
			case "sortByDaily":
				$order = "order by daily_clicks desc, time_visible desc";
				break;
			case "sortByMonthly":
				$order = "order by monthly_clicks desc, time_visible desc";
				break;
			case "sortByID":
				$order = "order by {$prefix}_posts.post_id";
				break;
			case "sortByIDR":
				$order = "order by {$prefix}_posts.post_id desc";
				break;
			default:
				$order = "order by {$prefix}_posts.post_id desc";
				break;
		}

		$q = "select {$prefix}_posts.*, domainstring, site_name
			from
			{$prefix}_posts
			left join {$prefix}_sites on {$prefix}_posts.site_id={$prefix}_sites.site_id
			$sponsorjoinq
			$tagjoinq
			left join {$prefix}_hardlinks on {$prefix}_hardlinks.hardlink_id={$prefix}_posts.trade_id
			where {$prefix}_posts.post_id!=$this->post_id
			$site_q
			$sponsor_q
			$tag_q
			$filter_q
			$display_q
			$list_q
			$order
			limit $start, $limit
			";
		$r = $this->dbc->query($q) or die($this->dbc->error . " query was $q in " . __FILE__ . " at line " . __LINE__);
		$i = $start;

		$this->table_rows = [];
		while($row = $r->fetch_object()) {
			$row->rownum = $i++;
			$this->table_rows[] = $row;
		}
		$r->close();

		return $num_of_rows;
	}

	public function fetchSearchResults($search, $limit)
	{
		$prefix = $this->table_prefix;
		$search = $this->sanitize($search);
		$q = "SELECT post_id FROM {$prefix}_posts WHERE MATCH(title,description) AGAINST('$search' IN NATURAL LANGUAGE MODE) LIMIT $limit";
		$r = $this->dbc->query($q) or die($this->dbc->error . " query was $q in " . __FILE__ . " at line " . __LINE__);
		//build a comma separated list of post ids
		$list = "";
		while($row = $r->fetch_object()) {
			$list .= $row->post_id . ",";
        }
		//remove the last comma
		if($list) $list = substr($list, 0, -1);
		$r->close();
        return $list;
	}

    /** Given a post_id and joining the posts table with the tags table via the post_tag_rel table, get a list of all the posts
     *  that have the same tags as the given post, ordered by the number of tags they have in common
     * @param post_id - id of post to get related posts for
     * @param limit - number of posts to return
     * @return: array of post objects
     */
    public function fetchRelatedPosts(int $post_id, int $limit = 5) : string
    {
        $prefix = $this->table_prefix;
        $q = "select {$prefix}_posts.post_id from {$prefix}_posts
                left join {$prefix}_post_tag_rel on {$prefix}_post_tag_rel.post_id={$prefix}_posts.post_id
                left join {$prefix}_tags on {$prefix}_tags.tag_id={$prefix}_post_tag_rel.tag_id
                where {$prefix}_posts.post_id!=$post_id and {$prefix}_tags.tag_id in
                (select {$prefix}_tags.tag_id from {$prefix}_tags
                left join {$prefix}_post_tag_rel on {$prefix}_post_tag_rel.tag_id={$prefix}_tags.tag_id
                where {$prefix}_post_tag_rel.post_id=$post_id)
                group by {$prefix}_posts.post_id
                order by count({$prefix}_posts.post_id) desc
                limit $limit
                ";
        $r = $this->dbc->query($q) or die($this->dbc->error . " query was $q in " . __FILE__ . " at line " . __LINE__);
		//build a comma separated list of post ids
		$list = "";
		while($row = $r->fetch_object()) {
			$list .= $row->post_id . ",";
        }
		//remove the last comma
		if($list) $list = substr($list, 0, -1);
		$r->close();
        return $list;
    }

	/** Gets a list of channels with counts of the videos in each together with the row of the latest video post in each
	 * @return: array of channel objects
	 */
	public function fetchChannelsWithPosts() : array
	{
		$prefix = $this->table_prefix;
		$q = "select distinct post_id, {$prefix}_posts.*, c2.* from {$prefix}_posts
			left join {$prefix}_videos on {$prefix}_videos.video_id={$prefix}_posts.video_id
			left join
			(select c1.channelid, c1.channelname, max({$prefix}_posts.time_added) as max_time, video_count from {$prefix}_posts
			left join {$prefix}_videos on {$prefix}_videos.video_id={$prefix}_posts.video_id
			left join
			(select {$prefix}_channels.channel_id as channelid, {$prefix}_channels.channel_name as channelname, count({$prefix}_videos.video_id) as video_count from {$prefix}_channels
			left join {$prefix}_videos on {$prefix}_videos.channel_id={$prefix}_channels.channel_id
			group by channelid, channelname) as c1 on c1.channelid={$prefix}_videos.channel_id
			group by c1.channelid, c1.channelname, video_count) as c2 on c2.channelid={$prefix}_videos.channel_id
			where {$prefix}_posts.time_added=c2.max_time
			order by video_count desc";
		$r = $this->dbc->query($q) or die($this->dbc->error . " query was $q in " . __FILE__ . " at line " . __LINE__);

		$channels = array();
		while($row = $r->fetch_object()) {
			$channels[] = $row;
		}
		$r->close();
		return $channels;
	}

    public function savePost(stdClass $vars) : bool
	{
		$pagename = $vars->pagename ?? "";
		//if we don't have a pagename, we need to get it from the title
		if(!($pagename)) $pagename = $this->getUniquePagename($vars->title, $vars->post_id ?? 0);
		$vars->pagename = $pagename;

		$post_id = $vars->post_id ?? 0;
		$id = $vars->id ?? 0;
		$description = $vars->description ?? "";

		if(!$id) {
			//no id so this is a new post so we can ignore the post_id
			$vars->time_added = time();
			$vars->daily_prod = 1000;
			$vars->monthly_prod = 1000;
			$this->insertRow("posts", $vars, "", true);
			$id = $this->getInsertID();
			$post_id = $this->fetchColumn("posts", "id", $id, "post_id", true);
		} else {
			//we have an id so this is an update
			unset($vars->time_added);
			$this->updateRow("posts", $vars, true);
		}

		//find any hashtags in the description
		$hashtags = $this->getTagsFromString($description);
		//now we need to update the tags. First we need to delete any tags for this post
		$this->deleteRow("post_tag_rel", "post_id", $post_id, true);
		//now we need to insert the new tags
		foreach($hashtags as $hashtag) {
			//get the tag id from the tags table if it exists
			if(!$tag_id = $this->fetchColumn("tags", "tag_name", $hashtag, "tag_id", true)) {
				//it doesn't exist so insert it
				$tag = new stdClass;
				$tag->tag_name = $hashtag;
				$tag->landing_page = "/";
				$tag->invisible = "false";
				$this->insertRow("tags", $tag, "", true);
				$tag_id = $this->getInsertID();
			}
			//now add the post tag relationship
			$tagr = new stdClass;
			$tagr->post_id = $post_id;
			$tagr->tag_id = $tag_id;
			$this->insertRow("post_tag_rel", $tagr, "", true);
		}

		$vars->post_id = $post_id;
		return true;
	}

	public function getUniquePagename(string $title, $post_id = 0)
	{
		$words = explode(" ", str_replace("-", " ", $title));
		$title = "";
		foreach($words as $word) {
			$word = trim($word);
			if(!$word) continue;
			if($title) $word = "-" . $word;
			if(strlen($title) > 50) {
				break;
			}
			$title .= $word;
		}

		$pagename = $this->getTagFromTitle($title);
		$pagename = str_replace(" - ", "-", $pagename);
		$pagename = str_replace("--", "-", $pagename);

		//we need to check if this pagename already exists
		$i = 1;
		while($this->rowExistsInTable("posts", "pagename", $pagename, "post_id", $post_id, true)) {
			//this slug already exists so we need to add a number to the end of it with at least 2 digits
			$pagename .= "-" . sprintf("%02d", $i++);
		}
		return $pagename;
	}

	/** increment the view count for the post in post_id */
	public function updatePostStats($post_id)
	{
		$prefix = $this->table_prefix;
		$t = time();
		$q = "UPDATE {$prefix}_posts SET
			daily_clicks=daily_clicks+1,
			monthly_clicks=monthly_clicks+1,
			total_clicks=total_clicks+1,
			time_last_viewed=$t
			WHERE post_id=$post_id";
		$r = $this->dbc->query($q) or die($this->dbc->error . " query was $q in " . __FILE__ . " at line " . __LINE__);
	}

	/** Inserts the trade domain id into the post from the hardlinks table if there is one
	 * 
	 * @param int $post_id
	 * 
	 * ! Needs rewriting to use the new database class
	 */
/*
	public function insertTrade($post_id)
	{
		$prefix = $this->table_prefix;
		if(!$row = $this->fetchRow("posts", "post_id", $post_id, true)) {
			return false;
		}
		$ds = parse_url($row->site_url);
		$domainstring = $ds["host"];
		if(substr($domainstring, 0, 4) == "the.") $domainstring = substr($domainstring, 4);
		if(substr($domainstring, 0, 6) == "tour1.") $domainstring = substr($domainstring, 6);
		if(substr($domainstring, 0, 6) == "tour2.") $domainstring = substr($domainstring, 6);
		if(substr($domainstring, 0, 6) == "tour3.") $domainstring = substr($domainstring, 6);
		if(substr($domainstring, 0, 6) == "tour4.") $domainstring = substr($domainstring, 6);
		if(substr($domainstring, 0, 6) == "tour5.") $domainstring = substr($domainstring, 6);
		if(substr($domainstring, 0, 8) == "landing.") $domainstring = substr($domainstring, 8);
		if(substr($domainstring, 0, 4) == "www.") $domainstring = substr($domainstring, 4);

		$q = "select max(ref_code) as rc, max(link_id) as lid from {$prefix}_hardlinks";
		$r = $this->dbc->query($q) or die($this->dbc->error . " query was $q in " . __FILE__ . " at line " . __LINE__);
		if($r->num_rows)
		{
			$row = $r->fetch_assoc();
			extract($row);
			$ref_code = $row["rc"] + 10;
			$link_id = $row["lid"] + 1;
			$r->close();
		}
		else
		{
			$ref_code = 10;
			$link_id = 1;
		}
		$time_visible = time();

		$dom_id = $this->getDomainIndex($domainstring);
		$q = "insert into {$prefix}_hardlinks set
			status=2,
			last_status=2,
			request_status=1,
			dom_id=$dom_id,
			ref_code=$ref_code,
			link_id=$link_id,
			domainstring='$domainstring',
			landing_page='$row->site_url',
			anchor='$row->title',
			time_visible=$time_visible
			";
		$r = $this->dbc->query($q) or die($this->dbc->error . " query was $q in " . __FILE__ . " at line " . __LINE__);
		$id = $this->dbc->insert_id;

		$q = "update {$prefix}_posts set trade_id=$link_id where post_id=$post_id";
		$r = $this->dbc->query($q) or die($this->dbc->error . " query was $q in " . __FILE__ . " at line " . __LINE__);
		return true;
	}
*/
}