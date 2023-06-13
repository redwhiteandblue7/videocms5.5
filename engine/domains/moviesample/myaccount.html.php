<?php
	require_once(INCLUDE_PATH . "domains/moviesample/classes/mvs.class.php");
    require_once(OBJECTS_PATH . "post.class.php");
    require_once(OBJECTS_PATH . "video.class.php");
    require_once(OBJECTS_PATH . "channel.class.php");

class MyAccountPage extends MovieSamplePage
{
    protected $template = "account";
    protected $account_template = "account";
    protected $label = "My Account";

    public function init()
    {
        parent::init();
        $this->page->initDescription();
        $this->canonical_url = $this->canonical_base . "myaccount.html";
    }

    public function process()
    {
        return;
    }
}
?>
