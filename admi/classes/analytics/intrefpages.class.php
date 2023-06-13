<?php
    require_once(HOME_DIR . 'admi/classes/analytics/analyseaction.class.php');
	require_once(OBJECTS_PATH . 'domain.class.php');

class IntrefpagesAction extends AnalyseAction
{
    public function process() : bool
    {
        $dmo = new Domain();
        $this->num_of_rows = $this->dbo->fetchInternalPagesData($dmo->domain_name);
        $this->results = $this->dbo->results();
        return false;
    }

    public function render() : void
    {
        include('templates/analytics/intrefpages_template.php');
    }
}