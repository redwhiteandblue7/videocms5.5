<?php
    require_once(HOME_DIR . 'admi/classes/actions/showtools.class.php');
    require_once(DB_PATH . 'update.db.class.php');

class UpdateTablesAction extends ShowToolsAction
{
    public $action_status = "none";
    public $name = "Update Tables";

    public function process() : bool
    {
        $dbo = new UpdateDB();
        $dbo->setPrefix();

		$execute = (isset($_GET["setup"]) && $_GET["setup"] == "yes");

		$dbo->addTableSchemaPointers(ADMIN_MODULES);
		$done = $dbo->initTablesInQueue($execute, false);
		if($execute) {
			if($done) {
				$this->action_status = "done";
			} else {
				$this->action_status = "error";
			}
		} else {
			if($done) {
				$this->action_status = "none";
			} else {
				$this->action_status = "ready";
			}
		}

		$this->status_messages = array_merge($this->status_messages, $dbo->messages);
        return false;
    }

    public function prerender() : void
    {
        return;
    }

    public function render() : void
    {
        include "templates/actions/updatetables_template.php";
    }
}
?>