<?php
    require_once(DB_PATH . 'tools.db.class.php');
    require_once(HOME_DIR . 'admi/classes/actions/showtools.class.php');

class UpgradePostsAction extends ShowToolsAction
{
    public $name = "Upgrade Posts";

    public function process(): bool
    {
        $dbo = new ToolsDB();
        $dbo->setPrefix();
        
		$dbo->importPostsTemp();
		$this->status_messages[] = "Posts upgraded, I think";
		return true;
    }
}
