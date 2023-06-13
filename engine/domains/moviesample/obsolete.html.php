<?php
	  require_once(INCLUDE_PATH . "domains/moviesample/classes/mvs.class.php");

	  class ObsoletePage extends MovieSamplePage
	  {
		  protected $template = "obsolete";

		  public function init()
		  {
			  parent::init();
		  }
	  }
  ?>
