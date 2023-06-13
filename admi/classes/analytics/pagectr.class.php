<?php
    require_once(HOME_DIR . 'admi/classes/analytics/analyseaction.class.php');

class PagectrAction extends AnalyseAction
{
    public function process() : bool
    {
        $this->num_of_rows = $this->dbo->fetchPageCTRData();
        $this->results = $this->dbo->results();
        return false;
    }

    public function render() : void
    {
        include('templates/analytics/pagectr_template.php');
    }
}