<?php
    require_once(HOME_DIR . 'admi/classes/analytics/analyseaction.class.php');

class ReferrersAction extends AnalyseAction
{
    public function process() : bool
    {
        if(isset($_GET["addsrch"]) && is_numeric($_GET["addsrch"])) {
            $this->dbo->addSearchDomain($_GET["addsrch"]);
        } elseif(isset($_GET["delsrch"]) && is_numeric($_GET["delsrch"])) {
            $this->dbo->deleteSearchDomain($_GET["delsrch"]);
        }
        $this->num_of_rows = $this->dbo->fetchReferrerData();
        $this->results = $this->dbo->results();
        return false;
    }

    public function render() : void
    {
        include('templates/analytics/referrers_template.php');
    }
}