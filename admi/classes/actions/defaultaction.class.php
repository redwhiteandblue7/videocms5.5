<?php
    require_once(HOME_DIR . 'admi/classes/action.class.php');

class DefaultAction extends Action
{
    public $name = "";

    public function process() : bool
    {
        return false;
    }

    public function prerender() : void
    {
        return;
    }

    public function render() : void
    {
        echo "<p>This action does not exist</p>";
    }

    public function pagination() : string
    {
        return "";
    }
}
?>