<?php
    require_once(HOME_DIR . 'admi/classes/actions/editaction.class.php');
    require_once(INCLUDE_PATH . "objects/site.class.php");

class SiteDeleteAction extends EditAction
{
    public $name = "Delete Site";

    public function process() : bool
    {
        $id = $this->id;
        $site = new Site($id);

        if($site->delete()) {
            $this->action_status = "deleted";
            return true;
        } else {
            $this->action_status = "error";
            return false;
        }
    }

    public function prerender() : void
    {
        include "templates/sites_template.php";
    }

    public function render() : void
    {
        include "templates/actions/showsites_template.php";
    }
}
