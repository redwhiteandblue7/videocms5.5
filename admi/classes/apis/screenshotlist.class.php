<?php
    require_once(HOME_DIR . 'admi/classes/apis/apiaction.class.php');

//Class to get a list of images in the screenshots folder
class ScreenshotList extends ApiAction
{
    public function process() : bool
    {
		$filepath = $this->dbo->domain_vars->public_path . "images/screenshots/";
		$handle = opendir($filepath);
		while(($file = readdir($handle)) !== false) {
            if($file != "." && $file != "..") $this->files[] = $file;
		}
        return false;
    }

    public function render() : void
    {
        include "templates/apis/getimagelist_template.php";
    }
}