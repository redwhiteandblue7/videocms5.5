<?php

require_once("dbclasses/submittools.db.class.php");
require_once('classes/admin.class.php');

class SubmittoolsPage extends AdminPage
{
	protected $dbo;

	public function __construct()
	{
		$this->dbo = new SubmittoolsDB();

		parent::__construct();
	}

	public function initPage()
	{
		parent::initPage();
		$this->getFilterVars();

		if(!$this->action) $this->setAction("show_submits");
	}

	protected function processAction()
	{
		$result = false;
		switch($this->action)
		{
			case "show_new":
				$result = $this->showSubmits("new");
				break;
			case "show_pending":
				$result = $this->showSubmits("pending");
				break;
			case "import_site":
				$result = $this->importAsSite();
				break;
			default:
				$result = $this->showSubmits("all");
				break;
		}
		return $result;
	}

	private function showSubmits($type)
	{
		$this->rememberMe();
		$this->setAction("show_submits");

		$start = $this->page * GALS_PER_PAGE;
		$end = GALS_PER_PAGE;
		$this->num_of_rows = $this->dbo->fetchSubmits($type, $this->sort_by, $start, $end);
		$this->pages = floor(($this->num_of_rows + GALS_PER_PAGE - 1) / GALS_PER_PAGE);
		return false;

	}

	private function importAsSite()
	{
		if(!isset($_POST["submit_id"])) return false;

		extract($this->post_array);

		if(!$user_name)
		{
			$this->error_messages[] = "Username appears to be blank somehow";
			return false;
		}

		if(!$submit_content)
		{
			$this->error_messages[] = "No description. We need a description of some sort";
			return false;
		}

		if(!$submit_title)
		{
			$this->error_messages[] = "Title cannot be blank";
			return false;
		}

		if(!$alt_title)
		{
			$this->error_messages[] = "Alt title cannot be blank";
			return false;
		}

		$submit_content = addslashes($submit_content);
		$submit_title = addslashes($submit_title);
		$alt_title = addslashes($alt_title);

		$vars_array = [];

		$vars_array["description"] = $submit_content;
		$vars_array["title"] = $submit_title;
		$vars_array["alt_title"] = $alt_title;
		$vars_array["pagename"] = $pagename;
		$vars_array["site_url"] = $submit_url;
		$vars_array["post_type"] = "blog";
		$vars_array["display_state"] = "hide";
		$vars_array["post_id"] = 0;

		if($error = $this->dbo->insertPost($vars_array))
		{
			$this->error_messages[] = $error;
			return false;
		}

		if(!$user_id)
		{
			$user_id = $this->dbo->insertUser($user_name, "password", $email_addr);
		}
		$this->dbo->updateSubmit($submit_id, "processed");
		$this->status_messages[] = "Imported, check posts tab";
		return true;
	}
}

?>
