<?php
    require_once(HOME_DIR . 'admi/classes/action.class.php');
    require_once(DB_PATH . 'analytics.db.class.php');

class AnalyseAction extends Action
{
    protected $dbo;
    protected $num_of_rows = 0;

    public function __construct()
    {
        $this->dbo = new AnalyticsDB();
        $this->dbo->setPrefix();

        if(isset($_GET["daterange"]) && is_numeric($_GET["daterange"])) {
            $this->dbo->dateRange($_GET["daterange"]);
        }
    }

    public function process() : bool
    {
        return false;
    }

    public function prerender() : void
    {
        return;
    }

    public function render() : void
    {
        return;
    }

    public function name() : string
    {
        return "Analyse Data";
    }

    public function pagination() : string
    {
        return "";
    }
}