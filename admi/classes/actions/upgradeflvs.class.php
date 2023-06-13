<?php
    require_once(DB_PATH . 'tools.db.class.php');
    require_once(HOME_DIR . 'admi/classes/actions/showtools.class.php');

class UpgradeFlvsAction extends ShowToolsAction
{
    public $name = "Upgrade FLVs";

    public function process(): bool
    {
        $dbo = new ToolsDB();
        $dbo->setPrefix();
        
		$dbo->importFlvs();
		$this->status_messages[] = "FLVs converted to posts okay, I think";
		return true;
    }
}
