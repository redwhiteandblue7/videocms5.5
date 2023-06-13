<?php
    require_once(DB_PATH . 'tools.db.class.php');
    require_once(HOME_DIR . 'admi/classes/actions/showtools.class.php');

class StoreGooglebotsAction extends ShowToolsAction
{
    public $name = "Googlebots";

    public function process(): bool
    {
        $dbo = new ToolsDB();
        $dbo->setPrefix();
        
		$dbo->updateGooglebotStats();
		$this->status_messages[] = "Updated. Now you can view the latest Googlebot stats.";
        return false;
    }
}
