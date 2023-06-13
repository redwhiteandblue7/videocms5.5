<?php
    require_once(HOME_DIR . 'admi/classes/actions/editaction.class.php');
    require_once(INCLUDE_PATH . "objects/site.class.php");

class SiteEditAction extends EditAction
{
    public $name = "Edit Site";

    public function process() : bool
    {
        // Get the site data for the site id if it exists
        $id = $this->id;
        $site = new Site($id);

        // If the form hasn't been submitted then just set up the post array and return
        if(!(isset($_POST["site_name"]))) {
            $this->action_status = "vars_empty";
            if($id) {
                if($this->post_object = $site->vars()) return false;
                // If we get here then the site id was invalid
                $this->action_status = "not_found";
            }
            // If there's no id then we're creating a new site
            $this->post_object->sponsor_id = 0;
            $this->post_object->site_id = 0;
            $this->post_object->enabled = "enabled";
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
        unset($this->post_object->sponsor_name);

        // If we get here then the form has been submitted so we need to save the site
        $site->save($this->post_object);

        // If there was an error saving the site then set the action status and return
        if($site->error_type) {
            $this->action_status = $site->error_type;
            return false;
        }

        $this->action_status = "ok";
        return true;
    }

    public function prerender() : void
    {
        include "templates/sites_template.php";
    }

    public function render() : void
    {
        $sites = new Site();
        include "templates/actions/siteedit_template.php";
    }
}