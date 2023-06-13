<?php
    require_once(DB_PATH . "manage.db.class.php");

class ChannelsDB extends ManageDatabase
{
	/** fetch all channels from the database and store them in the table_rows array and return the total number of rows in the table
	 * @param start - the first row to fetch
	 * @param limit - the maximum number of rows to fetch
	 * @return int - the total number of rows in the table
	 */
    public function fetchChannels(int $start = 0, int $limit = 99999, int $user_id = 0)
    {
		$prefix = $this->table_prefix;

		if($user_id) {
			$user_q = "and {$prefix}_channels.user_id=$user_id";
		} else {
			$user_q = "";
		}

		$q = "select count(1) as cnt from
			{$prefix}_channels
			where 1
			$user_q
            ";
        $r = $this->dbc->query($q) or die($this->dbc->error . ", query was $q");
        $num_of_rows = $r->fetch_row()[0];
        $r->free();

		$q = "select {$prefix}_channels.*, site_name, user_name from
			{$prefix}_channels
			left join {$prefix}_sites on {$prefix}_channels.site_id={$prefix}_sites.site_id
			left join users on users.user_id={$prefix}_channels.user_id
			where 1
			$user_q
			order by {$prefix}_channels.time_added desc
			limit $start, $limit
            ";
        $r = $this->dbc->query($q) or die($this->dbc->error . " query was $q in " . __FILE__ . " at line " . __LINE__);
        $i = $start;

        while($row = $r->fetch_object()) {
            $row->rownum = $i++;
            $this->table_rows[] = $row;
        }
        $r->close();

        return $num_of_rows;
    }

	/** fetch a channel from the database
	 * @param channel_id - the id of the channel to fetch
	 * @return mixed - an object containing the channel data, or an empty string if the channel was not found
	 */
	public function fetchChannel(int $channel_id)
	{
		$prefix = $this->table_prefix;
		$q = "select {$prefix}_channels.*, channel_name, site_name, user_name from
			{$prefix}_channels
			left join {$prefix}_sites on {$prefix}_channels.site_id={$prefix}_sites.site_id
			left join users on users.user_id={$prefix}_channels.user_id
			where {$prefix}_channels.channel_id=$channel_id
			";
		$r = $this->dbc->query($q) or die($this->dbc->error . ", query was $q");
		if($row = $r->fetch_object()) {
			$r->free();
			return $row;
		} else {
			return "";
		}
	}

	public function deleteChannel(int $channel_id)
	{
		$prefix = $this->table_prefix;

		$q = "delete from {$prefix}_channel_tag_rel where channel_id=$channel_id";
		$r = $this->dbc->query($q) or die($this->dbc->error . " query was $q in " . __FILE__ . " at line " . __LINE__);
		$q = "delete from {$prefix}_channels where channel_id=$channel_id";
		$r = $this->dbc->query($q) or die($this->dbc->error . " query was $q in " . __FILE__ . " at line " . __LINE__);
		return true;
	}
}