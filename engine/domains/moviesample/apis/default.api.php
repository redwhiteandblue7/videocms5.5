<?php
	require_once(INCLUDE_PATH . "domains/moviesample/classes/mvs.class.php");

class DefaultApi extends MovieSamplePage
{
    protected $return_text = "No class defined for this API request";

    public function init()
    {
		$this->visitor->blockRobots();
		$this->visitor->getBrowserType();
        $this->session_token = $this->user->getSessionToken();
    }

    public function process()
    {
        return;
    }

    public function render()
    {
        if($this->template) {
            require_once(INCLUDE_PATH . "domains/moviesample/templates/" . $this->template . "_tpl.php");
        } else {
            echo $this->return_text;
        }
    }
}
?>