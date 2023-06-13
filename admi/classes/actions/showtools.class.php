<?php
    require_once(HOME_DIR . 'admi/classes/action.class.php');

class ShowToolsAction extends Action
{
    public $name = "Tools";

    public function process() : bool
    {
        return false;
    }

    public function prerender() : void
    {
        include "templates/tools_template.php";
    }

    public function render() : void
    {
        include "templates/actions/showtools_template.php";
    }
}