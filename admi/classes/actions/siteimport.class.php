<?php
    require_once(HOME_DIR . 'admi/classes/actions/editaction.class.php');
    require_once(INCLUDE_PATH . "objects/site.class.php");

class SiteImportAction extends EditAction
{
    public $name = "Import Sites";

    public function process() : bool
    {
        // Create a new site object
        $site = new Site();
        // If the form hasn't been submitted then just set up the post array and return
        if(!(isset($_POST["link_dump"]))) {
            $this->action_status = "vars_empty";
            $this->post_object->sponsor_id = 0;
            return false;
        }

        // Check the form data before passing it to the site object
		if($this->post_object->sponsor_name) {
			if($this->post_object->sponsor_id) {
                $this->action_status = "ambiguous_sponsor";
				return false;
			}
            // Get the sponsor id from the sponsor name whether it's a new sponsor or an existing one
            $this->post_object->sponsor_id = $site->sponsor($this->post_object->sponsor_name);
		} elseif(!$this->post_object->sponsor_id) {
            $this->action_status = "no_sponsor";
			return false;
		}

		if(!trim($this->post_object->link_dump)) {
            $this->action_status = "no_links";
			return false;
		}

        // Check the link dump for errors
		$lines = explode("\n", $this->post_object->link_dump);
		foreach($lines as $line) {
			$parts = explode("|", $line);
			if(sizeof($parts) < 2) {
                $this->action_status = "not_enough_data";
				return false;
			}

			if(!trim($parts[0])) {
                $this->action_status = "empty_site_name";
				return false;
			}

			if(!trim($parts[1])) {
                $this->action_status = "empty_link";
				return false;
			}
		}

        $vars = new stdClass;
        //now go through the link dump again and add each site to the database
		foreach($lines as $line) {
			$parts = explode("|", $line);
			$vars->site_name = $parts[0];
			$site_ref = $parts[1];
			$site_ref = str_replace("\n", "", $site_ref);
			$vars->site_ref = str_replace("\r", "", $site_ref);
            $vars->site_id = 0;
            $vars->sponsor_id = $this->post_object->sponsor_id;
            $vars->site_domain = "";

			$vars->pref = 1;
			$vars->enabled = "enabled";

            $site->save($vars);
		}

        // If there was an error saving the site then set the action status and return
        if($site->error_type) {
            $this->action_status = $site->error_type;
        } else {
            $this->action_status = "sites_imported";
            return true;
        }

        return false;
    }

    public function prerender() : void
    {
        include "templates/sites_template.php";
    }

    public function render() : void
    {
        $site = new Site();
        include("templates/actions/siteimport_template.php");
    }
}