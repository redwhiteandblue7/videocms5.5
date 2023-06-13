<?php

//the domain class
//holds data about the current state of the domains table and manages interaction with currently selected domain if there is one
require_once(INCLUDE_PATH . 'classes/database.class.php');

class Domain
{
    private $dbo;
    private $vars;

    public $domain_id = 0;
	public $domain_name = "";
    public $error_type = "";

    /** Construct the domain object from either the passed domain id parameter or the session variable
     * 
     *  @param domain_id either a valid domain id, 0 to create empty, or false to find current from session
     * 
     **/
    public function __construct($domain_id = false)
    {
        $this->dbo = new Database();
        //if there is no domain table set up there's no point trying to init any domain
        if(!$this->dbo->tableExists("domains")) {
            $this->error_type = "no_domains";
            return;
        }

        if($domain_id === false) {
            $current_domain_id = $_SESSION["domain_id"] ?? "";
            $select_domain_id = "";
            //check if we're setting the domain id in the last page
            if(isset($_POST["select_domain_id"]) && is_numeric($_POST["select_domain_id"])) {
                $select_domain_id = $_POST["select_domain_id"];
            } elseif(isset($_POST["preserve_domain_id"]) && is_numeric($_POST["preserve_domain_id"])) {
                $select_domain_id = $_POST["preserve_domain_id"];
            }

            //if a different domain is being selected switch to it
            if(($select_domain_id) && ($current_domain_id != $select_domain_id)) {
                $this->domain_id = $select_domain_id;
                $this->switch();
                return;
            }

            //now check if there is a domain id set
            if(isset($_SESSION["domain_id"]) && is_numeric($_SESSION["domain_id"])) {
                //if there is then call the init function to initialize the domain_vars
                if($this->init($_SESSION["domain_id"])) {
                    $this->dbo->setPrefix($this->domain_name);
                    return;
                }
            }

            //if not then we just logged in or the session expired or we just deleted the domain so initialize with the first domain found in the database
            $this->domain_id = 0;
            $this->switch();
            return;
        }

        //if a domain id was passed in then init that otherwise this will be an empty object (used for creating new)
        if($domain_id) if($this->init($domain_id));
    }

    /** Switch the current session domain to this domain */
    public function switch()
    {
        if(!$this->init($this->domain_id)) return;
        $_SESSION["domain_id"] = $this->domain_id;
        unset($_SESSION["se_track_id"]);
        $this->dbo->setPrefix($this->domain_name);
        $this->initSearchTracking();
    }

    /** Return true if the current domain is not the same as the one the page started with
     *  i.e. the current domain has been changed - uses old_domain_id session var which must be updated
     *  using the final() method
     */
    public function selected() : bool
    {
        $current = $_SESSION["domain_id"] ?? 0;
        $old = $_SESSION["old_domain_id"] ?? 0;

        return ($current != $old);
    }

    /** Set the old domain id to be the same as current - this should be done after all processing is finished */
    public function final()
    {
        if(isset($_SESSION["domain_id"])) $_SESSION["old_domain_id"] = $_SESSION["domain_id"];
    }

    /** Use after deleting current domain so that admin object will select another if possible */
    public function free()
    {
		unset($_SESSION["domain_id"]);
		unset($_SESSION["old_domain_id"]);
    }

	/** Initialize the domain_vars from the domain id or from the first row found if none passed in
     * 
     *  @param domain_id either a valid domain id, 0 to create empty, or false to find current from session
     *  Return: the id of the domain initialized or 0 if none was 
     * 
    **/
	public function init($domain_id = false) : int
	{
        if($domain_id) {
            //we have a domain id set so find it in the domains table, it should always be there
            $row = $this->dbo->fetchRow("domains", "domain_id", $domain_id);
        } elseif($domain_id === false) {
            $row = $this->dbo->fetchRow("domains", "domain_id", $this->domain_id);
        } else {
			//no domain id so just get the first one we can find
            $row = $this->dbo->fetchTopRow("domains", "domain_id");
		}

		if($row)
		{
			$this->vars = $row;
			$this->domain_id = $this->vars->domain_id;
            $this->domain_name = $this->vars->domain_name;

			if($xml = $this->vars->description)
			{
				$this->obj = simplexml_load_string($xml) or die("Error: Cannot create object from page description");
			}
			return $this->domain_id;
		}

		//if no domain is set up return 0
		return 0;
	}

    public function switchToCurrent()
    {
        $hostname = $_SERVER["HTTP_HOST"];
        if(substr($hostname, 0, 3) == "192") {
            //if we're running on a local machine then pretend we're on the defined domain
            $hostname = FALLBACK_DOMAIN;
        }
        if($domain_id = $this->dbo->fetchColumn("domains", "domain_name", $hostname, "domain_id")) {
            if(!$this->init($domain_id)) return;
            $_SESSION["domain_id"] = $this->domain_id;
            $this->dbo->setPrefix($this->domain_name);
            }
    }

    /** Insert new or update existing domain into the domains table using values passed in vars array
     * 
     * @param vars = object containing properties corresponding with columns in table
     * Function will validate values and set error_type accordingly
     * Return: true if successful otherwise false
     */
    public function save($vars) : bool
    {
        if(!is_object($vars)) {
            $this->error_type = "vars_empty";
            return false;
        }

        if(!$vars->domain_name ?? "") {
            $this->error_type = "no_name";
            return false;
        }

        if(!$vars->site_name ?? "") {
            $this->error_type = "no_site";
            return false;
        }

        $this->vars = $vars;
        if($vars->domain_id ?? 0) {
            $this->dbo->updateRow("domains", $vars);
            $this->checkFolderExists();
            return true;
        }

        if(!$this->dbo->insertRow("domains", $vars, "domain_name")) {
            $this->error_type = "domain_exists";
            return false;
        }
        $this->domain_id = $this->dbo->getInsertID();
        $this->checkFolderExists();
        return true;
    }

    /** Check that a folder exists within the asset path of the domain and create it if not
     * 
     * @param folder = the folder to check for
     * Return: true if successful otherwise false
     */
	public function checkFolderExists(string $folder = "") : bool
	{
        if(!$this->vars->public_path || !$this->vars->asset_path) return false;
        $path = $this->vars->public_path . $this->vars->asset_path;
        if(!is_dir($path)) {
            $old = umask(0);
            mkdir($path, 0775);
            umask($old);}
        if(!$folder) return true;
        $path .= $folder;
		if(!is_dir($path)) {
			$old = umask(0);
            mkdir($path, 0775);
			umask($old);
		}
		return true;
	}

    /* Return a base URL constructed from the domain vars */
    public function baseURL() : string
    {
        if($this->vars) {
            return $this->vars->http_scheme . "://" . $this->vars->sub_domain . $this->vars->domain_name;
        } else {
            return "";
        }
    }

    public function fullURL(string $url) : string
    {
        $hostname = $_SERVER["HTTP_HOST"];
        //A quick and dirty fix for local development
        if(substr($hostname, 0, 3) == "192") return $url;
    
        //first check if the url is already a full url
        if(strpos($url, "://") !== false) return $url;
        //it's not so return the full url constructed from the domain vars
        return $this->baseURL() . $url;
    }

    /* Return the path given a url */
    public function urlToPath(string $url) : string
    {
        $base = $this->baseURL();
        //First if the url is not a full url then just add it to the public path
        if(strpos($url, "://") === false) {
            return $this->vars->public_path . $url;
        } 
        //otherwise check if it starts with the base url and if so convert it to a path
        if(strpos($url, $base) === 0) {
            return $this->vars->public_path . substr($url, strlen($base));
        }
        //otherwise return the url as is
        return $url;
    }

    /* Return a prefix derived from the domain name to be used for include files etc */
    public function prefix() : string
    {
        if($this->domain_name) {
            return $this->dbo->currentPrefix();
        } else {
            return "";
        }
    }

    /* Return the domain asset path */
    public function assetPath() : string
    {
        if($this->vars) {
            return $this->vars->public_path . $this->vars->asset_path;
        } else {
            return "";
        }
    }

    /* Return the domain asset folder */
    public function assetFolder() : string
    {
        if($this->vars) {
            return $this->vars->asset_path;
        } else {
            return "";
        }
    }

    public function varsArray() : array
    {
        if($this->vars) {
            return (array)$this->vars;
        } else {
            return array();
        }
    }

    /* Return the domain vars object */
    public function vars() : object
    {
        if($this->vars) {
            return $this->vars;
        } else {
            return (object)array();
        }
    }

    public function isVisible() : bool
    {
        if(!$this->vars) return false;
        return $this->vars->status == 1;
    }

    //sets up the session variables with the domain names to use in search engine tracking stats
	private function initSearchTracking()
	{
		if(!isset($_SESSION["se_track_id"])) {
			$trackname = $this->vars->se_tracking;
			if($trackname != "") {
                if($row = $this->dbo->fetchRowByValue("referdomains", "domainstring", $trackname)) {
					$_SESSION["se_track_id"] = $row->dom_id;
                }
			}
		}

		if(!isset($_SESSION["se_google_id"])) {
            if($row = $this->dbo->fetchRowByValue("referdomains", "domainstring", "google.com")) {
				$_SESSION["se_google_id"] = $row->dom_id;
			}
		}

		if(!isset($_SESSION["se_bing_id"])) {
            if($row = $this->dbo->fetchRowByValue("referdomains", "domainstring", "bing.com")) {
				$_SESSION["se_bing_id"] = $row->dom_id;
			}
		}
	}

}
?>