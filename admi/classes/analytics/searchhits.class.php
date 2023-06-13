<?php
    require_once(HOME_DIR . 'admi/classes/analytics/analyseaction.class.php');

class SearchhitsAction extends AnalyseAction
{
    public function process() : bool
    {
        $this->num_of_rows = $this->dbo->fetchSearchHitsData();
        $this->results = $this->dbo->results();
        return false;
    }

    public function render() : void
    {
        include('templates/analytics/searchhits_template.php');
    }
}