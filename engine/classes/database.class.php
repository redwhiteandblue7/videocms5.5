<?php

//the database class
//this is an abstraction layer between the objects and the database itself, all database specific code goes in here and provides functions to interrogate the db
//and post / retrieve data

    require_once(INCLUDE_PATH . 'defines.php');
	require_once(INCLUDE_PATH . 'traits/text.trait.php');

class Database
{
    use TextFuncs;

	protected $dbc;
	protected $mysqldrvr;
    protected $table_rows = [];
    protected $last_insert_id = 0;

	public $table_prefix;

    //by default this will construct using the hostname to find the table index
    //admin functions should use a child class which overrides this behaviour
    public function __construct()
    {
		$this->mysqldrvr = new mysqli_driver();
		$this->mysqldrvr->report_mode = MYSQLI_REPORT_ERROR;
		$this->dbc = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

        $this->setPrefix();
    }

	public function __destruct()
	{
		$this->dbc->close();
	}

    private function getDomainPrefix(string $domainstring)
    {
        $domainstring = str_replace(".", "_", $domainstring);
        $domainstring = str_replace("-", "_", $domainstring);
        $domainstring = strtolower($domainstring);

        return $domainstring;
    }

    public function getPrefix(string $hostname)
    {
        if(substr($hostname, 0, 4) == "www.") {
            $hostname = substr($hostname, 4);
        } elseif(substr($hostname, 0, 5) == "test.") {
            $hostname = substr($hostname, 5);
        }

        $dom = explode(".", $hostname);
        $domain = $dom[0];
        return $this->getDomainPrefix($domain);
    }

    /** Set domain prefix to use for table names
     * 
     * ! Must only be called with a hostname if we are changing the current selected domain
     * @param hostname - the domain name we are setting as the current selected domain
     * From user side no need to pass anything in, it will get domain from server host
     */
    public function setPrefix(string $hostname = "")
    {
        //if we have neither a hostname passed in or a session variable set
        if(!$hostname && !isset($_SESSION["domain_prefix"])) {
            //then get the current host from the server
            $hostname = $_SERVER["HTTP_HOST"];
            if(substr($hostname, 0, 3) == "192") {
                //if we're running on a local machine then pretend we're on the defined domain
                $hostname = FALLBACK_DOMAIN;
            }
        }

        //if we now have a hostname then either one was passed in or we're using the current host
        if($hostname) {
            $this->table_prefix = $this->getPrefix($hostname);
            //set the session variable.
            $_SESSION["domain_prefix"] = $this->table_prefix;
        } else {
            //no hostname so there must be a session variable
            $this->table_prefix = $_SESSION["domain_prefix"];
        }
    }

    public function currentPrefix() : string
    {
        return $this->table_prefix;
    }

	public function sanitize(string $unsafe_string) : string
	{
        if($unsafe_string === "") return "";
		return $this->dbc->real_escape_string($unsafe_string);
	}

    /** Get the array of rows for a table */
    public function results() : array
    {
        return $this->table_rows;
    }

    /** Update a row in a database table from the object passed in, matching the object properties to the table columns
    * If the object has a property called id then it will use that to match the row to update
    * If the object has a property called time_updated then it will update that to the current time
    * If the object has a property the same name as the table with "_id" appended it will not update that column
    * @param table - the table to update
    * @param object - the object to update from
    * @param use_prefix - whether to use the table prefix or not
    * @param id - the id of the row to update if the object doesn't have an id property
    */
    public function updateRow(string $table, stdClass $object, bool $use_prefix = false)
    {
        //if $table has an 's' on the end then remove it
        $foreign_key = ((substr($table, -1) == "s") ? substr($table, 0, -1) : $table) . "_id";
        if($use_prefix) {
            $table = $this->table_prefix . "_" . $table;
        }

        $q = "update `$table` set ";
        $comma = "";
        foreach($object as $key => $value) {
            if($key == "id") {
                continue;
            }
            if($key == "time_updated") {
                $value = time();
            }
            if($key == $foreign_key) {
                continue;
            }
            if(is_string($value)) {
                $value = $this->sanitize($value);
            }
            $q .= "$comma `$key`='$value'";
            $comma = ", ";
        }
        if(isset($object->id)) {
            $q .= " where id=$object->id";
        } else {
            $q .= " where $foreign_key={$object->$foreign_key}";
        }
        $this->dbc->query($q) or die($this->dbc->error . " query was $q in " . __FILE__ . " at line " . __LINE__);
    }

    /** Insert a row into a database table from the object passed in, matching the object properties to the table columns
    * If @param check_exists is passed in then it will check that the column with that name does not already exist with that value
    * If the object has a property called time_created then it will update that to the current time
    * If the object has a property called time_updated then it will update that to the current time
    * If the object has a property the same name as the table with "_id" appended then if there is no id property it will use that as the primary key otherwise
    * it will set it to a unique value being the max value of the column + 1
    * @param table - the table to insert into
    * @param object - the object to insert from
    * @param check_exists - the column name to check for an existing value
    * @param use_prefix - whether to use the table prefix or not
    * @return true if inserted, false if not
    */
    public function insertRow(string $table, stdClass $object, $check_exists = "", bool $use_prefix = false) : bool
    {
        //if $table has an 's' on the end then remove it
        $foreign_key = ((substr($table, -1) == "s") ? substr($table, 0, -1) : $table) . "_id";
        if($use_prefix) {
            $table = $this->table_prefix . "_" . $table;
        }

        if($check_exists) {
            $q = "select * from `$table` where `$check_exists`='{$object->$check_exists}'";
            $result = $this->dbc->query($q) or die($this->dbc->error . " query was $q in " . __FILE__ . " at line " . __LINE__);
            if($result->num_rows > 0) {
                return false;
            }
        }
        $q = "insert into `$table` set ";
        $comma = "";
        foreach($object as $key => $value) {
            if($key == "id") {
                continue;
            }
            if($key == "time_created") {
                $value = time();
            }
            if($key == "time_updated") {
                $value = time();
            }
            if(is_string($value)) {
                $value = $this->sanitize($value);
            }
            if($key == $foreign_key && !$value && isset($object->id)) {
                $q .= "$comma `$key`=(select coalesce(max(`$key`), 0) + 1 from `$table` as `temptable`)";
            } else {
                $q .= "$comma `$key`='$value'";
            }
            $comma = ", ";
        }
        $this->dbc->query($q) or die($this->dbc->error . " query was $q in " . __FILE__ . " at line " . __LINE__);
        $this->last_insert_id = $this->dbc->insert_id;
        return true;
    }

    /** Delete a row from a table
     * @param table - the table to delete from
     * @param id_column - the name of the id column
     * @param id - the id of the row to delete
     * @param use_prefix - whether to use the table prefix or not
     * @return true if deleted, false if not
     */
    public function deleteRow(string $table, string $id_column, int $id, bool $use_prefix = false) : bool
    {
        if($use_prefix) {
            $table = $this->table_prefix . "_" . $table;
        }
        $q = "delete from `$table` where `$id_column`=$id";
        $this->dbc->query($q) or die($this->dbc->error . " query was $q in " . __FILE__ . " at line " . __LINE__);
        return ($this->dbc->affected_rows > 0);
    }

	//Insert the user and if it's the first user give him admin privilege otherwise no-one would ever be able to access the admin
	public function insertUser(string $username, string $password, string $email) : int
	{
		$q = "select 1 from `users`";
		$r = $this->dbc->query($q) or die($this->dbc->error . " query was $q in " . __FILE__ . " at line " . __LINE__);
		$n = $r->num_rows;
		$r->close();

		$key = $username . $password;
		$key_hash = sha1($key);
		$password_hash = password_hash($password, PASSWORD_DEFAULT);
		$t = time();

        $priv = ($n) ? 1 : 255;
		$q = "insert into `users` set user_name='$username', pass_word='$password_hash', email_addr='$email', time_registered=$t, activate_key='$key_hash', user_privilege=$priv";
		$r = $this->dbc->query($q) or die($this->dbc->error . " query was $q in " . __FILE__ . " at line " . __LINE__);
		return $this->dbc->insert_id;
	}

    /** Fetch a row from a table using column id
     * 
     * @param table = name of table without prefix
     * @param column = name of column to test (usually an id column)
     * @param column_value = id to test for in column
     * @param use_prefix = true if domain prefix needs to be added to start of table name
     * 
     * @return: row as object or empty string
     */
    public function fetchRow(string $table, string $column, $row_id, bool $use_prefix = false)
    {
        if($use_prefix) $table = $this->table_prefix . "_" . $table;
        $q = "select * from $table where $column='$row_id'";
        $r = $this->dbc->query($q) or die($this->dbc->error . " query was $q in " . __FILE__ . " at line " . __LINE__);
        if($row = $r->fetch_object()) {
            $r->close();
            return $row;
        } else {
            return "";
        }
    }

    /** Fetch rows from a table using column id
     * 
     * @param table = name of table without prefix
     * @param column = name of column to test (usually an id column)
     * @param column_value = id to test for in column
     * @param use_prefix = true if domain prefix needs to be added to start of table name
     * 
     * @return: rows as array of objects
     */
    public function fetchRows(string $table, string $column, $column_value, bool $use_prefix = false) : array
    {
        if($use_prefix) $table = $this->table_prefix . "_" . $table;
        $q = "select * from $table where $column='$column_value'";
        $r = $this->dbc->query($q) or die($this->dbc->error . " query was $q in " . __FILE__ . " at line " . __LINE__);
        $rows = [];
        while($row = $r->fetch_object()) {
            $rows[] = $row;
        }
        $r->close();
        return $rows;
    }

    /** Fetch a row from a table using column value
     * 
     * @param table = name of table without prefix
     * @param column = name of column to test (can be a text column or enum)
     * @param column_value = string value of column to test
     * @param use_prefix = true if domain prefix needs to be added to start of table name
     * 
     * @return: row as object or empty string
     */
    public function fetchRowByValue(string $table, string $column, string $column_value, bool $use_prefix = false)
    {
        if($use_prefix) $table = $this->table_prefix . "_" . $table;
        $q = "select * from $table where $column='$column_value'";
        $r = $this->dbc->query($q) or die($this->dbc->error . " query was $q in " . __FILE__ . " at line " . __LINE__);
        if($row = $r->fetch_object()) {
            $r->close();
            return $row;
        } else {
            return "";
        }
    }

    /** Fetch first row from table ordered by the order_by parameter
     * 
     * @param table = name of table without prefix
     * @param order_by = name of column to order by
     * @param use_prefix = true if domain prefix needs to be added to start of table name
     * @return: row as object or empty string
     */
    public function fetchTopRow(string $table, string $order_by, bool $use_prefix = false)
    {
        if($use_prefix) $table = $this->table_prefix . "_" . $table;
        $q = "select * from $table order by $order_by limit 1";
        $r = $this->dbc->query($q) or die($this->dbc->error . " query was $q in " . __FILE__ . " at line " . __LINE__);
        if($row = $r->fetch_object()) {
            $r->close();
            return $row;
        } else {
            return "";
        }
    }

    /** 
     * Fetch all rows from a table
     * @param table = name of table without prefix
     * @param order_by = name of column to order by
     * @param use_prefix = true if domain prefix needs to be added to start of table name
     * @return: array of rows as objects
     */
    public function fetchTable(string $table, string $order_by = "", bool $use_prefix = false)
    {
        if($use_prefix) $table = $this->table_prefix . "_" . $table;

        $q = "select * from $table";
        if($order_by) $q .= " order by $table.$order_by";
        $r = $this->dbc->query($q) or die($this->dbc->error . " query was $q in " . __FILE__ . " at line " . __LINE__);
        $rows = [];
        while($row = $r->fetch_object()) {
            $rows[] = $row;
        }
        $r->close();
        return $rows;
    }

    /** Check if a table exists in the database 
     * 
     * @param table Name of the table as a string
     * @return: true or false
    */
    public function tableExists(string $table) : bool
    {
        $q = "show tables like '$table'";
		$r = $this->dbc->query($q) or die($this->dbc->error . " query was $q in " . __FILE__ . " at line " . __LINE__);
        if($r->num_rows) {
            return true;
        } else {
            return false;
        }
    }

    public function getInsertID()
    {
        return $this->last_insert_id;
    }

    //general purpose functions to get table data

    /** Raw count of rows in table 
     * @param table = name of table
     * @param use_prefix = true if domain prefix needs to be prepended to table name
    */
	public function countRows(string $table, bool $use_prefix = false)
	{
        if($use_prefix) $table = $this->table_prefix . "_" . $table;
		$q = "select count(1) as cnt from $table";
        $r = $this->dbc->query($q) or die($this->dbc->error . ", query was $q");
        $num_of_rows = $r->fetch_row()[0];
        $r->free();
		return $num_of_rows;
	}

    /** Sum of a column in a table
     * @param table = name of table
     * @param column = name of column
     * @param use_prefix = true if domain prefix needs to be prepended to table name
     */
    public function sumColumn(string $table, string $column, bool $use_prefix = false)
    {
        if($use_prefix) $table = $this->table_prefix . "_" . $table;
        $q = "select sum($column) as cnt from $table where 1";
		$r = $this->dbc->query($q) or die($this->dbc->error . " query was $q in " . __FILE__ . " at line " . __LINE__);
        $sum = $r->fetch_row()[0];
        $r->free();
		return $sum;
    }

    /** Max value of a column in a table
     * @param table = name of table
     * @param column = name of column
     * @param use_prefix = true if domain prefix needs to be prepended to table name
     */
    public function maxColumn(string $table, string $column, bool $use_prefix = false)
    {
        if($use_prefix) $table = $this->table_prefix . "_" . $table;
        $q = "select max($column) as cnt from $table where 1";
        $r = $this->dbc->query($q) or die($this->dbc->error . " query was $q in " . __FILE__ . " at line " . __LINE__);
        $max = $r->fetch_row()[0];
        $r->free();
        return $max;
    }

    /** Sum column in table of matching rows  
     * @param table = name of table
     * @param id_column - name of id column
     * @param row_id - id value to select row by
     * @param column = name of column
     * @param use_prefix = true if domain prefix needs to be prepended to table name
     */
    public function sumValuesFromTableRowsById(string $table, string $id_column, int $row_id, string $column, bool $use_prefix = false)
    {
        if($use_prefix) $table = $this->table_prefix . "_" . $table;
        $q = "select sum($column) as cnt from $table where $id_column=$row_id";
		$r = $this->dbc->query($q) or die($this->dbc->error . " query was $q in " . __FILE__ . " at line " . __LINE__);
        $sum = $r->fetch_row()[0];
        $r->free();
		return $sum;
    }

    /** Count rows where column = value
     * @param table = name of table
     * @param column = name of column to test value
     * @param value = value to test for
     * @param use_prefix = true if domain prefix needs to be prepended to table name
     */
	public function countMatchingRows(string $table, string $column, int $value, bool $use_prefix = false)
	{
        if($use_prefix) $table = $this->table_prefix . "_" . $table;
		$q = "select count(1) as cnt from $table where $column=$value";
		$r = $this->dbc->query($q) or die($this->dbc->error . " query was $q in " . __FILE__ . " at line " . __LINE__);
        $num_of_rows = $r->fetch_row()[0];
        $r->free();
		return $num_of_rows;
	}

    /** Get the values from an enum column into an array. Should work for set columns too
     * @param table = name of table
     * @return array of values
     */
    public function getEnumValues(string $table, string $column, bool $use_prefix = false) : array
    {
        if($use_prefix) $table = $this->table_prefix . "_" . $table;
        $q = "show columns from $table like '$column'";
        $r = $this->dbc->query($q) or die($this->dbc->error . " query was $q in " . __FILE__ . " at line " . __LINE__);
        $row = $r->fetch_assoc();
        $r->free();
		if(array_key_exists("Type", $row)) {
			$enum = explode("','",preg_replace("/(enum|set)\('(.+?)'\)/","\\2",$row["Type"]));
		} else {
			$enum = explode("','",preg_replace("/(enum|set)\('(.+?)'\)/","\\2",$row[1]));
		}
        return $enum;
    }

    /** Check if a row exists in a table with the same column value, excluding the row we're trying to insert
     * @param table = name of table
     * @param column = name of column to test value
     * @param value = value to test for
     * @param id_column = name of id column
     * @param id_value = id value to exclude from test
     * @param use_prefix = true if domain prefix needs to be prepended to table name
     * @return true if row exists
     */
    public function rowExistsInTable(string $table, string $column, string $value, string $id_column, int $id_value, bool $use_prefix = false) : bool
    {
        if($use_prefix) $table = $this->table_prefix . "_" . $table;
        $q = "select count(1) as cnt from $table where `$column`='$value' and `$id_column`!=$id_value";
        $r = $this->dbc->query($q) or die($this->dbc->error . " query was $q in " . __FILE__ . " at line " . __LINE__);
        $num_of_rows = $r->fetch_row()[0];
        $r->free();
        return $num_of_rows > 0;
    }

    /** Fetch a value from one column in a table row selected by the id column
     * 
     * @param table - name of table without prefix
     * @param id_column - name of id column
     * @param row_id - id value to select row by
     * @param column - name of column to select value from
     * @param use_prefix - true if domain prefix needs to be prepended to table name
     * @return: value from column
     */
    public function fetchColumn(string $table, string $id_column, $row_id, string $column, bool $use_prefix = false)
    {
        if($use_prefix) $table = $this->table_prefix . "_" . $table;
        $q = "select $column from $table where $id_column='$row_id'";
        $r = $this->dbc->query($q) or die($this->dbc->error . " query was $q in " . __FILE__ . " at line " . __LINE__);

        if($r->num_rows) {
            return $r->fetch_row()[0];
        } else {
            return "";
        }
    }

    public function deleteDomain($domain_id)
    {
		$q = "delete from domains where domain_id=$domain_id limit 1";
		$r = $this->dbc->query($q) or die($this->dbc->error);
    }
    
    public function deleteDomainFromDailyStats($domain_id)
    {
		$q = "delete from daily_stats where domain_id=$domain_id";
		$r = $this->dbc->query($q) or die($this->dbc->error);
    }

    /** Update a column in a table row selected by the id column
     * @param table - name of table without prefix
     * @param column - name of column to update
     * @param value - value to set column to
     * @param id_column - name of id column
     * @param id_value - id value to select row by
     * @param use_prefix - true if domain prefix needs to be prepended to table name
     * @return: number of rows affected
     */
    public function updateColumn(string $table, string $column, $value, string $id_column, int $id_value, bool $use_prefix = false)
    {
        if($use_prefix) $table = $this->table_prefix . "_" . $table;
        if(is_string($value)) {
            $value = $this->sanitize($value);
        }
        $q = "update $table set $column='$value' where $id_column=$id_value limit 1";
        $this->dbc->query($q) or die($this->dbc->error . " query was $q in " . __FILE__ . " at line " . __LINE__);
        return $this->dbc->affected_rows;
    }

    /** Insert a single value into a column in row in a table along with an id value
     * @param table - name of table without prefix
     * @param column - name of column to insert value into
     * @param value - value to insert
     * @param id_column - name of id column
     * @param id_value - id value
     * @param use_prefix - true if domain prefix needs to be prepended to table name
     * @return: number of rows affected
     */
    public function insertColumn(string $table, string $column, $value, string $id_column, int $id_value, bool $use_prefix = false)
    {
        if($use_prefix) $table = $this->table_prefix . "_" . $table;
        if(is_string($value)) {
            $value = $this->sanitize($value);
        }
        $q = "insert into $table set $column='$value', $id_column=$id_value";
        $this->dbc->query($q) or die($this->dbc->error . " query was $q in " . __FILE__ . " at line " . __LINE__);
        return $this->dbc->affected_rows;
    }

    //Increment the value of given column where id column in row equals given id
    public function incrementColumn(string $table, string $column, string $id_column, int $id_value, bool $use_prefix = false)
    {
        if($use_prefix) $table = $this->table_prefix . "_" . $table;
        $q = "update $table set $column=$column+1 where $id_column=$id_value limit 1";
        $r = $this->dbc->query($q) or die($this->dbc->error . " query was $q in " . __FILE__ . " at line " . __LINE__);
        return $this->dbc->affected_rows;
    }

	public function fetchTags(string $table, int $id) : array
	{
        $column = str_replace("related", "", $table) . "_id";
		$prefix = $this->table_prefix;
        $q = "select {$prefix}_tags.tag_name, {$prefix}_tags.tag_id, {$prefix}_{$table}_tag_rel.id from {$prefix}_tags
                left join {$prefix}_{$table}_tag_rel on {$prefix}_{$table}_tag_rel.tag_id={$prefix}_tags.tag_id
                where {$prefix}_{$table}_tag_rel.$column=$id
                order by {$prefix}_tags.tag_name
                ";
		$r = $this->dbc->query($q) or die($this->dbc->error . " query was $q in " . __FILE__ . " at line " . __LINE__);
        $rows = [];
		while($row = $r->fetch_object()) {
            $rows[] = $row;
        }
		$r->close();
        return $rows;
	}

	/** Get a list of the tags sorted by number of posts they are tagged with
	 * @return array
	 */
	public function fetchTagsByPopularity(int $limit)
	{
		$prefix = $this->table_prefix;
		$q = "SELECT t.tag_id, t.tag_name, t.landing_page, t.invisible, COUNT(ptr.post_id) AS num_posts
			FROM {$prefix}_tags t
			LEFT JOIN {$prefix}_post_tag_rel ptr ON t.tag_id = ptr.tag_id
            WHERE t.invisible='false'
			GROUP BY t.tag_id
			ORDER BY num_posts DESC
            LIMIT $limit";
		$r = $this->dbc->query($q) or die($this->dbc->error . " query was $q in " . __FILE__ . " at line " . __LINE__);
		$tags = array();
		while($row = $r->fetch_object()) {
			$tags[] = $row;
		}
		return $tags;
	}

    /** Get a list of the tags sorted by tag name with a count of the number of posts they are tagged with
     * @return array
     */
    public function fetchTagsByName()
    {
        $prefix = $this->table_prefix;
        $q = "SELECT t.tag_id, t.tag_name, t.landing_page, t.invisible, COUNT(ptr.post_id) AS num_posts
            FROM {$prefix}_tags t
            LEFT JOIN {$prefix}_post_tag_rel ptr ON t.tag_id = ptr.tag_id
            WHERE t.invisible='false'
            GROUP BY t.tag_id
            ORDER BY t.tag_name ASC";
        $r = $this->dbc->query($q) or die($this->dbc->error . " query was $q in " . __FILE__ . " at line " . __LINE__);
        $tags = array();
        while($row = $r->fetch_object()) {
            $tags[] = $row;
        }
        return $tags;
    }

    public function deleteTags(string $table, int $id) : bool
    {
        $column = $table . "_id";
		$prefix = $this->table_prefix;
        $q = "delete from {$prefix}_{$table}_tag_rel where $column=$id";
        $this->dbc->query($q) or die($this->dbc->error . " query was $q in " . __FILE__ . " at line " . __LINE__);
        return ($this->dbc->affected_rows > 0);
    }
}

?>