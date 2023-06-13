<?php

class ApisPage
{
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
        $class_name = $action;
		$file_name = strtolower($action) . ".class.php";
		if(file_exists("classes/apis/" . $file_name)) {
			include("classes/apis/" . $file_name);
			$page = new $class_name();
        } elseif(file_exists("classes/actions/" . $file_name)) {
            include("classes/analytics/" . $file_name);
            $page = new $class_name();
        } elseif(file_exists("classes/actions/" . $file_name)) {
			include("classes/actions/" . $file_name);
			$page = new $class_name();			 
		} else {
			include('actions/defaultaction.class.php');
			$page = new DefaultAction();
		}

        $page->in_modal = true;
        $page->process();
		$page->render();
	}
}
