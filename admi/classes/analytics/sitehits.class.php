<?php
    require_once(HOME_DIR . 'admi/classes/analytics/analyseaction.class.php');

class SitehitsAction extends AnalyseAction
{
    public function process() : bool
    {
        $this->num_of_rows = $this->dbo->fetchSiteHitsData();
        $this->results = $this->dbo->results();
        return false;
    }

    public function render() : void
    {
        include('templates/analytics/sitehits_template.php');
    }
}