<?php
    require_once(HOME_DIR . 'admi/classes/apis/apiaction.class.php');

class TranscodeProgress extends ApiAction
{
    public function process() : bool
    {
		$url_obj = json_decode($_POST["x"], false);
		$site_url = $url_obj->siteURL;
		$post_id = $url_obj->postID;
        $pagename = $url_obj->pageName;

        $this->return_text = $this->mod->getReviewData($site_url, $post_id, $pagename);
        return false;
    }
}