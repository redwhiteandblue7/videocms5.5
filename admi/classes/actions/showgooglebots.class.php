<?php
    require_once(DB_PATH . 'tools.db.class.php');
    require_once(HOME_DIR . 'admi/classes/actions/displayaction.class.php');

class ShowGooglebotsAction extends DisplayAction
{
    public $name = "Googlebots";

    public function sortby()
    {
        if(isset($this->get_object->sortBy)) {
            $_SESSION["showgooglebots_sortby"] = $this->get_object->sortBy;
        }
        if(isset($_SESSION["showgooglebots_sortby"])) {
            $this->sort_by = $_SESSION["showgooglebots_sortby"];
        } else {
            $this->sort_by = "page";
        }
    }

    public function process(): bool
    {
        $this->dbo = new ToolsDB();
        $this->dbo->setPrefix();

		$this->num_of_rows = $this->dbo->fetchGooglebotStats($this->sort_by);
        $this->results = $this->dbo->results();
        return false;
    }

    public function prerender() : void
    {
        include "templates/tools_template.php";
    }

    public function render() : void
    {
        include "templates/actions/showgooglebots_template.php";
    }
}
