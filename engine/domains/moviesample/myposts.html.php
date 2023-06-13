<?php
	require_once(INCLUDE_PATH . "domains/moviesample/classes/mvs.class.php");
    require_once(OBJECTS_PATH . "post.class.php");

class MyPostsPage extends MovieSamplePage
{
    protected $template = "account";
    protected $account_template = "acc_posts";
    protected $label = "My Videos";

    public function init()
    {
        parent::init();
        $this->page->initDescription();
        $this->canonical_url = $this->canonical_base . "myposts.html";
    }

    public function process()
    {
        return;
    }
}
?>
