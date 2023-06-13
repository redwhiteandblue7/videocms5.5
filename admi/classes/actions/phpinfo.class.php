 <?php
    require_once(HOME_DIR . 'admi/classes/action.class.php');

class PhpInfoAction extends Action
{
    public $name = "PHP Info";

    public function process(): bool
    {
        return false;
    }

    public function prerender() : void
    {
        include "templates/tools_template.php";
    }

    public function render() : void
    {
        include "templates/actions/phpinfo_template.php";
    }
}
