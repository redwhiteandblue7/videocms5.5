<?php
    require_once(HOME_DIR . 'admi/classes/action.class.php');
    require_once(DB_PATH . 'update.db.class.php');

class SetupAction extends Action
{
    public $action_status = "none";
    public $name = "Setup";

    public function __construct()
    {
        unset($_SESSION["loggedin"]);
        unset($_SESSION["domain_id"]);
        unset($_SESSION["old_domain_id"]);
        setcookie("User", '', time() + 1, "/");
        setcookie("Pass", '', time() + 1, "/");    
    }
    
    public function process() : bool
    {
        $setup = (isset($_GET["setup"]) && $_GET["setup"] == "yes");
        if($this->initDatabase($setup)) {
            $this->action_status = "ok";
        } else {
            if($setup) $this->action_status = "err";
        }

        return false;
    }

    public function prerender() : void
    {
        return;
    }

    public function render() : void
    {
        include "templates/actions/setup_template.php";
    }

	private function initDatabase($execute) : bool
	{
        $dbo = new UpdateDB();
		$done = $dbo->initTables($execute);
		$this->status_messages = array_merge($this->status_messages, $dbo->messages);
		return $done;
	}

}
?>