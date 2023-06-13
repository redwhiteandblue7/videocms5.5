<?php
    require_once(HOME_DIR . 'admi/classes/apis/apiaction.class.php');

class PostFromData extends ApiAction
{
    public function process() : bool
    {
        $img_obj = json_decode($_POST["x"], false);
        $title = $img_obj->siteName;
        $site_url = $img_obj->siteURL;
        $categories = $img_obj->categories;

        $this->return_text = $this->mod->createPostFromHTML($title, $site_url, $categories);
        return false;
    }
}