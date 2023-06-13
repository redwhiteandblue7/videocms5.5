<?php
    require_once(HOME_DIR . 'admi/classes/actions/editaction.class.php');
    require_once(INCLUDE_PATH . "objects/page.class.php");

class PageDeleteAction extends EditAction
{
    public $name = "Delete Page";

    public function process() : bool
    {
        $id = $this->id;
        $page = new Page($id);

        if($page->delete()) {
            $this->action_status = "deleted";
            return true;
        } else {
            $this->action_status = "error";
            return false;
        }
    }

    public function prerender() : void
    {
        include "templates/pages_template.php";
    }

    public function render() : void
    {
        include "templates/actions/showpages_template.php";
    }
}
