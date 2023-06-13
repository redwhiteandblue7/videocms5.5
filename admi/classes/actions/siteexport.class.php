<?php
    require_once(HOME_DIR . 'admi/classes/actions/editaction.class.php');
    require_once(INCLUDE_PATH . "objects/site.class.php");

class SiteExportAction extends EditAction
{
    public $name = "Export Sites";

    public function render() : void
    {
        $site = new Site();
        $site_list = $site->sitesWithoutPosts();
        include('templates/actions/siteexport_template.php');
    }

    public function prerender() : void
    {
        include "templates/sites_template.php";
    }
}