<?php

//the page class
//for interacting with page data
    require_once(INCLUDE_PATH . 'classes/database.class.php');

class Page
{
    private $dbo;
    private $row;
    private $start = 0;
    private $end = 1;
    private $results = [];
    private $result_pointer = 0;
    private $autotag = "";
    private $description = "";

    public $error_type = "";
    public $page_id = 0;

    public function __construct(int $id = 0)
    {
        $this->dbo = new Database();
        $this->dbo->setPrefix();

        if($id) {
            if($this->row = $this->dbo->fetchRow("pages", "id", $id, true)) {
                $this->page_id = $this->row->page_id;
            }
        }
    }

    /** Set up parameters and filters for the pages() call */
    public function prepare(int $start, int $end) : void
    {
        $this->start = $start;
        $this->end = $end;
    }

    /** Fetch the pages data from the database into the $this->results array 
     * @return int The number of pages found
     */
    public function pages() : int
    {
		$this->results = $this->dbo->fetchTable("pages", "page_name", true);
        return sizeof($this->results);
    }

    public function next()
    {
        if($this->result_pointer < sizeof($this->results)) {
            $this->row = $this->results[$this->result_pointer++];
            return $this->row;
        }

        return "";
    }

    /* Return the current vars object */
    public function vars()
    {
        if(isset($this->row->page_id)) return $this->row;
        return "";
    }

    /**  Insert new or update an existing page in the pages database table
    * @return bool True if the insert or update was successful
    */
    public function save(stdClass $row) : bool
    {
        if(!isset($row->page_name) || !$row->page_name) {
            $this->error_type = "no_name";
            return false;
        }

        if((!isset($row->page_filename) || !$row->page_filename) && (!isset($row->dest_url) || !$row->dest_url)) {
            $this->error_type = "no_filename";
            return false;
        }

        $this->row = $row;
        if($row->id) {
            $this->dbo->updateRow("pages", $row, true);
            return true;
        }
        if($this->dbo->insertRow("pages", $row, "page_name", true)) {
            $this->row->id = $this->dbo->getInsertID();
            return true;
        }

        $this->error_type = "page_exists";
        return false;
    }

    /**
     * Delete the page from the database
     */
    public function delete() : bool
    {
        $this->dbo->deleteTags("page", $this->row->page_id);
        return $this->dbo->deleteRow("pages", "page_id", $this->row->page_id, true);
    }

    /**
     * Get tags for the page into the $this->tags array
     */
    public function tags() : array
    {
        return $this->dbo->fetchTags("page", $this->row->page_id);
    }

    public function getPage(string $page_name)
    {
        $this->row = $this->dbo->fetchRow("pages", "page_name", $page_name, true);
        if($this->row) {
            $this->page_id = $this->row->page_id;
        }
    }

    public function initDescription()
    {
		if($xml = $this->row->description)
		{
			$this->description = simplexml_load_string($xml, "SimpleXMLElement", LIBXML_NOCDATA) or die("Error: Cannot create object from page description");
			$this->description->title = htmlentities($this->description->title, ENT_QUOTES, "UTF-8");
			$this->description->heading = htmlentities($this->description->heading, ENT_QUOTES, "UTF-8");
            if(property_exists($this->description, 'content')) $this->description->content = htmlentities($this->description->content, ENT_QUOTES, "UTF-8");
			if(property_exists($this->description, 'meta')) $this->description->meta = htmlentities($this->description->meta, ENT_QUOTES, "UTF-8");
			$max_galleries = 0;
			if(property_exists($this->description, 'maxgalleries')) $max_galleries = (integer)($this->description->maxgalleries);
			$this->description->maxgalleries = $max_galleries;
            if(property_exists($this->description, "noindex")) $this->description->robots = true;
		}
    }

    public function description(stdClass $description = null)
    {
        if($description) {
            $this->description = $description;
        }
        return $this->description;
    }
}
?>