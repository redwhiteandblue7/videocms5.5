<?php
    require_once(HOME_DIR . 'admi/classes/actions/editaction.class.php');
    require_once(DB_PATH . "update.db.class.php");

class DomainEditAction extends EditAction
{
	public $name = "Edit Domain";

    public function process() : bool
    {
		if(isset($this->type) && $this->type == "new") {
			$domain_id = 0;
		} elseif(isset($_POST["domain_id"]) && is_numeric($_POST["domain_id"])) {
			$domain_id = $_POST["domain_id"];
		} else {
			$domain_id = $_SESSION["domain_id"] ?? 0;
		}

		$domain = new Domain($domain_id);

		if(!isset($_POST["domain_id"])) {
			$this->post_array = $domain->varsArray();
			return false;
		}

		if($domain->save($this->post_object) && !$domain_id) {
			if($domain->init()) {
				$domain->switch();
				if(!$this->initDomainTables()) {
					$this->action_status = "tables_err";
					return false;
				}
			}
		}

		$this->action_status = ($domain->error_type) ? $domain->error_type : "ok";
		return false;
    }

    public function prerender() : void
    {
        include "templates/domains_template.php";
    }

    public function render() : void
    {
        include "templates/actions/domainedit_template.php";
    }

    public function pagination() : string
    {
        return "";
    }

	private function initDomainTables() : bool
	{
		$dbo = new UpdateDB();
		$dbo->setPrefix();
		$dbo->addTableSchemaPointers(ADMIN_MODULES);
		$e = $dbo->initTablesInQueue(true);
		$this->status_messages = array_merge($this->status_messages, $dbo->messages);
		return $e;
	}

}
?>