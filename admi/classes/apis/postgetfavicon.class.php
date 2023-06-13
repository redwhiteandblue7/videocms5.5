<?php
    require_once(HOME_DIR . 'admi/classes/apis/apiaction.class.php');

class PostGetFavicon extends ApiAction
{
    public function process() : bool
    {
		$img_obj = json_decode($_POST["x"], false);
		$site_url = $img_obj->siteURL;
		$post_id = $img_obj->postID;
		$title = $img_obj->siteName;

		$this->return_text = $this->mod->getFavicon($title, $site_url, $post_id);
		return false;
    }
}