<?php
    require_once(DB_PATH . 'update.db.class.php');
    require_once(HOME_DIR . 'admi/classes/actions/showtools.class.php');

class CreateStopWordsAction extends ShowToolsAction
{
    public $name = "Stop Words Table";

    public function process(): bool
    {
        $dbo = new UpdateDB();
        $dbo->setPrefix();

        $dbo->createStopWordsTable();
		$this->status_messages[] = "Table created, I think";
		return true;
    }
}
