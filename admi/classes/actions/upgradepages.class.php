<?php
    require_once(DB_PATH . 'tools.db.class.php');
    require_once(HOME_DIR . 'admi/classes/actions/showtools.class.php');

class UpgradePagesAction extends ShowToolsAction
{
    public $name = "Upgrade Pages";

    public function process(): bool
    {
        $dbo = new ToolsDB();
        $dbo->setPrefix();
        
		$this->dbo->importDynamicPages();
		$this->status_messages[] = "Okay, I think";
		return true;
    }
}
