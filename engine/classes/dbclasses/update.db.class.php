<?php
require_once(DB_PATH . 'tabledata/common_tables_schema.php');
require_once(DB_PATH . 'tabledata/post_tables_schema.php');
require_once(DB_PATH . 'tabledata/video_tables_schema.php');
require_once(DB_PATH . 'tabledata/banner_tables_schema.php');
require_once(DB_PATH . 'tabledata/submit_tables_schema.php');
require_once(DB_PATH . "manage.db.class.php");

class UpdateDB extends ManageDatabase
{
    private $initial_table_ptr = INITIAL_TABLE_SCHEMA;
    private $common_table_ptr = COMMON_TABLE_SCHEMA;
    private $domain_table_ptr = DOMAIN_TABLE_SCHEMA;
    private $video_table_ptr = VIDEO_TABLE_SCHEMA;
    private $banner_table_ptr = BANNER_TABLE_SCHEMA;
    private $post_table_ptr = POST_TABLE_SCHEMA;
    private $submit_table_ptr = SUBMIT_TABLE_SCHEMA;
    private $table_pointers = [];
    private $tables_status = [];
    private $tables_sql = [];

	public function __construct()
    {
        parent::__construct();
        $this->initTableSchemaPointers();
    }

    //Functions to initialise the database

    //Set up pointers to the initial tables arrays
    protected function initTableSchemaPointers()
    {
        $this->table_pointers[] = $this->initial_table_ptr;
        $this->table_pointers[] = $this->common_table_ptr;
    }

    //Add pointers to the tables required per module so that the update tool knows which schema to check against
    public function addTableSchemaPointers($modules)
    {
        $this->table_pointers[] = $this->domain_table_ptr;

        if(strpos($modules, "videos") !== false) {
            $this->table_pointers[] = $this->video_table_ptr;
        }

        if(strpos($modules, "banners") !== false) {
            $this->table_pointers[] = $this->banner_table_ptr;
        }

        if(strpos($modules, "posts") !== false) {
            $this->table_pointers[] = $this->post_table_ptr;
        }

        if(strpos($modules, "submits") !== false) {
            $this->table_pointers[] = $this->submit_table_ptr;
        }
    }

    //Builds the SQL for the initial tables and either reports it or executes it
    //Quiet = true to not reveal any SQL
    public function initTables($execute = false, $quiet = true) : bool
    {
        $this->getTablesUpdateSQL($this->initial_table_ptr);
        $this->getTablesUpdateSQL($this->common_table_ptr);
        if($execute) {
            return $this->runTablesSQL($quiet);
        } else {
            return $this->listTablesSQL($quiet);
        }
    }

    //Builds the SQL for all the tables required by current modules definition and either reports it or executes it
    //Quiet = true to not reveal any SQL
    public function initTablesInQueue($execute = false, $quiet = true) : bool
    {
        foreach($this->table_pointers as $ptr) {
            $this->getTablesUpdateSQL($ptr);
        }

        if($execute) {
            return $this->runTablesSQL($quiet);
        } else {
            return $this->listTablesSQL($quiet);
        }
    }

    //Run the SQL queries already built by getTablesUpdateSQL();
    //Quiet = true to not reveal any SQL
    private function runTablesSQL($quiet = true) : bool
    {
        foreach($this->tables_sql as $q) {
            if(!$quiet) $this->messages[] = "Running query $q";
/*
            try{
                $this->dbc->query($q);
            }
            catch (mysqli_sql_exception $e) {
                $this->messages[] = "";
                $this->messages[] = "Something went wrong. MySQLi said:<br />" . $e;
                return false;
            }
*/
            if(!@$this->dbc->query($q)) {
                $this->messages[] = "";
                $this->messages[] = "Something went wrong. MySQLi said:<br />" . $this->dbc->error;
                return false;
            }

        }
        $this->tables_sql = [];
        $this->tables_status = [];
        return true;
    }

    //List the changes and SQL required built by getTablesUpdateSQL() into the message queue
    //Quiet = true to not reveal any SQL
    private function listTablesSQL($quiet = true) : bool
    {
        if(sizeof($this->tables_sql)) {
            foreach($this->tables_status as $t) {
                $this->messages[] = $t;
            }
            if(!$quiet) {
                $this->messages[] = "";
                $this->messages[] = "To remedy the above the following SQL is needed:";
                foreach($this->tables_sql as $q) {
                    $this->messages[] = $q;
                }
            }
            return false;
        }
        return true;
    }

    //This function compares the existing table structures to what is defined in the schema data, and builds sql queries to create or alter tables as required
    //Queries stored in tables_sql to be run by the calling func.
    private function getTablesUpdateSQL($table_schema)
    {
        foreach($table_schema as $table) {
            $table_name = $table["name"];
            if($table_name[0] == "_") $table_name = $this->table_prefix . $table_name;
            $c = [];
            if(in_array($table_name, $this->tables_array)) {
                $q = "show columns from $table_name";
                $r = $this->dbc->query($q) or die($this->dbc->error . ", query was $q in " . __FILE__ . " at " . __LINE__);
                while($row = $r->fetch_assoc()) {
                    //Get $Field, $Type, $Null, $Default, $Key and $Extra vars that describe the column
                    extract($row);
                    //'Set' type columns cannot be modified here as they may be dynamically altered in the CMS
                    if(substr($Type, 0, 3) == "set") $Type = "set('default')";

                    //Display width attribute for integer data types is deprecated in MySQL 8 so we need to remove them if present
                    if(strpos($Type, "int") !== false) {
                        $Type = preg_replace("/\([^)]+\)/", "", $Type);
                    }
                    $null_c = ($Null == "NO") ? " NOT NULL" : "";
                    $default_c = ($Default == "") ? "" : " default '$Default'";
                    $extra_c = ($Extra == "") ? "" : " $Extra";
                    $key_c = ($Key == "PRI") ? " primary key" : "";

                    //build an SQL statement segment that would create the same column
                    $sql = "$Field $Type" . $null_c . $default_c . $extra_c . $key_c;
                    $c[$Field] = $sql;
                }
                $r->close();

                $columns_found = [];

                foreach($c as $column_name=>$column_sql) {
                    if(array_key_exists($column_name, $table["columns"])) {
                        $sql = $column_name . " " . $table["columns"][$column_name];
                        $columns_found[] = $column_name;
                        if($column_sql != $sql) {
                            $this->tables_sql[] = "ALTER TABLE $table_name MODIFY COLUMN `$column_name` " . $table["columns"][$column_name];
                            $this->tables_status[] = "Table `$table_name` has non-matching column $column_name ($column_sql)";
                        }
                    } else {
                        $this->tables_sql[] = "ALTER TABLE $table_name DROP COLUMN `$column_name`";
                        $this->tables_status[] = "Table `$table_name` has unused column $column_name ";
                    }
                }
                $last_col = "FIRST";
                foreach($table["columns"] as $key=>$value) {
                    if(!in_array($key, $columns_found)) {
                        $this->tables_sql[] = "ALTER TABLE $table_name ADD COLUMN `$key` $value $last_col";
                        $this->tables_status[] = "Table `$table_name` is missing column $key ";
                    }
                    $last_col = "AFTER $key";
                }
            } else {
                $this->tables_status[] = "Table `$table_name` does not exist";
                $q = "CREATE TABLE $table_name (";
                $sql = "";
                foreach($table["columns"] as $key=>$value) {
                    if($sql) $sql .= ", ";
                    $sql .= "`$key` $value";
                }
                $q .= $sql;
                $q .= ");";
                $this->tables_sql[] = $q;
            }
        }
    }

    public function createStopWordsTable()
    {
        if(!in_array("post_stopwords", $this->tables_array)) {
            $q = "CREATE TABLE post_stopwords(value VARCHAR(30)) ENGINE=INNODB";
            $this->dbc->query($q) or die($this->dbc->error . ", query was $q in " . __FILE__ . " at " . __LINE__);
            $q = "INSERT INTO post_stopwords(value) VALUES('a'),('about'),('an'),('and'),('are'),('as'),('at'),
                                                            ('be'),('by'),('com'),('for'),('from'),('fulltext'),
                                                            ('how'),('i'),('in'),('is'),('it'),('of'),('on'),('or'),
                                                            ('post'),('snippet'),('tags'),('that'),('the'),('this'),('to'),
                                                            ('was'),('what'),('when'),('where'),('who'),('will'),('with'),('www'),('xml')";
            $this->dbc->query($q) or die($this->dbc->error . ", query was $q in " . __FILE__ . " at " . __LINE__);
            $q = "SET GLOBAL innodb_ft_server_stopword_table = '" . DB_NAME . "/post_stopwords'";
            $this->dbc->query($q) or die($this->dbc->error . ", query was $q in " . __FILE__ . " at " . __LINE__);
        }
    }
}