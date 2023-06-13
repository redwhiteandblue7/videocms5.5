<?php
	  require_once(INCLUDE_PATH . "domains/moviesample/classes/mvs.class.php");

    class ErrorPage extends MovieSamplePage
    {
        protected $template = "error";

        public function init()
        {
            parent::init();
        }
    }
?>
