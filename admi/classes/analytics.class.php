<?php

class AnalyticsPage
{
	/** 
	 *  Controller function for analytics API.
	 */
	public function exec() : void
	{
		if(isset($_GET["a"])) {
			$action = $_GET["a"];
		} else {
			$action = "null";
		}

		$this->action($action);
	}

	private function action(string $action) : void
	{
		$file_name = strtolower($action) . ".class.php";
		if(file_exists("classes/analytics/" . $file_name)) {
			include("classes/analytics/" . $file_name);
			$class_name = ucfirst($action) . "Action";
			$page = new $class_name();
		} else {
			include('actions/defaultaction.class.php');
			$page = new DefaultAction();
		}

        $page->process();
		$page->prerender();
		$page->render();
	}
}
?>