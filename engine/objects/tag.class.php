<?php

//the tag class
//for fetching and saving tag data to and from the database
    require_once(DB_PATH . 'manage.db.class.php');

class Tag
{
    private $dbo;
    private $row;
    private $results = [];
    private $result_pointer = 0;

    public $error_type = "";

    public function __construct(int $id = 0)
    {
        $this->dbo = new ManageDatabase();
        $this->dbo->setPrefix();

        if($id) {
            $this->row = $this->dbo->fetchRow("tags", "tag_id", $id, true);
        }
    }

    public function vars()
    {
        if(isset($this->row->tag_id)) {
            return $this->row;
        }
        return "";
    }

    public function tags(string $sort_by = "") : array
    {
        return $this->dbo->fetchTable("tags", $sort_by, true);
    }

    //Get the list of tags ordered by the number of posts they are attached to
    public function sortedTags(int $limit = 99999) : array
    {
        return $this->dbo->fetchTagsByPopularity($limit);
    }

    //Get the list of tags ordered alphabetically
    public function atozTags() : array
    {
        return $this->dbo->fetchTagsByName();
    }

    public function getTagByPagename(string $pagename) : bool
    {
        $this->row = $this->dbo->fetchRow("tags", "tag_name", $pagename, true);

        if($this->row) {
            return true;
        }
        return false;
    }

}
