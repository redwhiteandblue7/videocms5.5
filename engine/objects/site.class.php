<?php

//the site class for fetching and storing data for the sites table
    require_once(DB_PATH . "sitetools.db.class.php");

class Site
{
    private $dbo;
    private $row;
    private $results = [];
    private $result_pointer = 0;

    public $error_type = "";

    public function __construct(int $site_id = 0)
    {
        $this->dbo = new SitetoolsDB();
        $this->dbo->setPrefix();

        if($site_id)
        {
            $this->row = $this->dbo->fetchRow("sites", "site_id", $site_id, true);
        }
    }

    public function sites(int $start, int $limit, int $sponsor_filter = 0, int $tag_filter = 0, string $sort_by = "") : int
    {
        $num_of_rows = $this->dbo->fetchSites($start, $limit, $sponsor_filter, $tag_filter, $sort_by);
        $this->result_pointer = 0;
        $this->results = $this->dbo->results();

        return $num_of_rows;
    }

    public function next() : mixed
    {
        if($this->result_pointer < sizeof($this->results)) {
            $this->row = $this->results[$this->result_pointer++];
            return $this->row;
        }

        return "";
    }

    public function vars() : mixed
    {
        if(isset($this->row->site_id)) {
            return $this->row;
        }
        return "";
    }

    /** save the site data to the database
     * @param stdClass object containing site data
     * @return bool true if saved successfully, false if not
     */
    public function save(stdClass $vars) : bool
    {
        //site must have a name
        if(!isset($vars->site_name) || $vars->site_name == "") {
            $this->error_type = "no_site_name";
            return false;
        }

        //site must have a sponsor
        if(!isset($vars->sponsor_id) || $vars->sponsor_id == "") {
            $this->error_type = "no_sponsor";
            return false;
        }

        //site must have a referral link code
        if(!isset($vars->site_ref) || $vars->site_ref == "") {
            $this->error_type = "no_site_ref";
            return false;
        }

        //if no priority is set, set it to 0
        if(!isset($vars->pref) || $vars->pref == "") {
            $vars->pref = 0;
        }

        //remove ".com" from the end of the site name
        if(substr($vars->site_name, -4) == ".com") {
            $vars->site_name = substr($vars->site_name, 0, -4);
        }

        //site domain is actually used as the slug for the cloaked link so set a url safe version of the site name
        if(!isset($vars->site_domain) || $vars->site_domain == "") {
            $ps = array(" ", "!", "?", ",", ".", "&", ":", ";", "'", "\"", "(", ")", "-");
            $vars->site_domain = strtolower(str_replace($ps, "", $vars->site_name));
        }

        $this->row = $vars;
        if(isset($vars->site_id) && $vars->site_id > 0) {
            $this->dbo->updateRow("sites", $vars, true);
            return true;
        } else {
            if($this->dbo->insertRow("sites", $vars, "site_name", true)) {
                $this->row->site_id = $this->dbo->getInsertID();
                return true;
            }
        }

        $this->error_type = "site_exists";
        return false;
    }

    //Checks for a sponsor with a similar name and returns the sponsor id if it exists, otherwise adds the sponsor and returns the new id
    public function sponsor(string $sponsor_name) : int
    {
        $sponsors = $this->getSponsors();
        //get a lower case version of the sponsor name without spaces
        $sponsor_name_lc = strtolower(str_replace(" ", "", $sponsor_name));
        foreach($sponsors as $sponsor) {
            if($sponsor_name_lc == strtolower(str_replace(" ", "", $sponsor->sponsor_name))) {
                return $sponsor->sponsor_id;
            }
        }
        //if we get here, the sponsor doesn't exist, so add it
        $vars = new stdClass;
        $vars->sponsor_name = $sponsor_name;
        if(!$this->dbo->insertRow("sponsors", $vars)) {
            $this->error_type = "sponsor_exists";
            return 0;
        }
        return $this->dbo->getInsertID();
    }

    //Get a list of sponsors
    public function getSponsors() : array
    {
        return $this->dbo->fetchTable("sponsors", "sponsor_name");
    }

    //Get a raw list of tags
    public function getTags() : array
    {
        return $this->dbo->fetchTable("tags", "tag_name", true);
    }

    //Get a raw list of sites without any filters
    public function getSites() : array
    {
        return $this->dbo->fetchTable("sites", "site_name", true);
    }

    //delete the site and all associated tags
    public function delete() : bool
    {
        $this->dbo->deleteTags("site", $this->row->site_id);
        return $this->dbo->deleteRow("sites", "site_id", $this->row->site_id, true);
    }

    //Get an array of sites that don't have any posts associated with them
    public function sitesWithoutPosts() : array
    {
        return $this->dbo->fetchSitesWithoutPosts();
    }

    //Get tags for the page into the $this->tags array
    public function tags() : array
    {
        return $this->dbo->fetchTags("site", $this->row->site_id);
    }
}
