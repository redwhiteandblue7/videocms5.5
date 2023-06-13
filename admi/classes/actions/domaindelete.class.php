<?php
    require_once(HOME_DIR . 'admi/classes/actions/editaction.class.php');
    require_once(DB_PATH . "manage.db.class.php");

class DomainDeleteAction extends EditAction
{
    private $dmo;

    public $name = "Delete Domain";

    public function process() : bool
    {
        $this->dmo = new Domain();

		if(!isset($_GET["delete"]) || $_GET["delete"] != "yes") {
            $this->action_status = "none";
            return false;
        }

        $dbo = new ManageDatabase();
        $dbo->setPrefix();
		$dbo->deleteRow("daily_stats", "domain_id", $this->dmo->domain_id);
		$dbo->deleteTables();
		$dbo->deleteRow("domains", "domain_id", $this->dmo->domain_id);
        $this->dmo->free();

		$this->action_status = "ok";
		return false;
    }

    public function prerender() : void
    {
        include "templates/domains_template.php";
    }

    public function render() : void
    {
        include "templates/actions/domaindelete_template.php";
    }
}
?>