<?php

require_once("dbclasses/linktrades.db.class.php");
require_once('classes/admin.class.php');

class LinktradesPage extends AdminPage
{
	protected $dbo;

	public function __construct()
	{
		$this->dbo = new LinktradesDB();

		parent::__construct();
	}

	public function initPage()
	{
		parent::initPage();
		if(!$this->action) $this->setAction("show_links");
	}

	public function processAction()
	{
		$result = false;
		switch($this->action)
		{
			case "approve_link":
				$result = $this->approveLink();
				break;
			case "delete_link":
				$result = $this->deleteLink();
				break;
			case "suspend_link":
				$result = $this->suspendLink();
				break;
			case "edit_link":
				$result = $this->editLink();
				break;
			case "show_links":
				$result = $this->showLinks();
				break;
			case "add_tag":
				$result = $this->addTags("hardlinks", "link_id");
				break;
			case "delete_tag":
				$result = $this->deleteTags("hardlinks");
				break;
			default:
				$result = $this->showLinks();
				break;
		}
		return $result;
	}

	private function showLinks()
	{
		$this->rememberMe();

		$start = $this->page * ROWS_PER_PAGE;
		$end = ROWS_PER_PAGE;

		$this->num_of_rows = $this->dbo->fetchLinks($this->sort_by, $start, $end);
		$this->pages = floor(($this->num_of_rows + ROWS_PER_PAGE - 1) / ROWS_PER_PAGE);
		return false;
	}

	private function editLink()
	{
		if(!isset($_POST["link_id"])) return false;

		if(!$this->post_array["domainstring"])
		{
			$this->error_messages[] =  "Please enter the domain the hits will come from e.g example.com";
			return false;
		}

		if(strpos($this->post_array["domainstring"], "/") !== false)
		{
			$this->error_messages[] = "Hostname only. Do not enter subdirectories, filenames or http scheme in the domain";
			return false;
		}

		if(!$this->post_array["landing_page"])
		{
			$this->error_messages[] = "Please enter the landing page to send hits to";
			return false;
		}

		$this->dbo->insertLink($this->post_array);
		$this->status_messages[] = "Link added or updated, whatever.";
		return true;
	}

	private function deleteLink()
	{
		if(!$this->dbo->deleteLink($this->did))
		{
			$this->error_messages[] = "This link still has tags. Delete the tags first.";
			return true;
		}
		$this->status_messages[] = "Link deleted, gone, finito.";
		return true;
	}

	private function suspendLink()
	{
		if(!$this->dbo->setLinkStatus($this->did, 0))
		{
			$this->error_messages[] = "Err...nothing happened.";
			return true;
		}
		$this->status_messages[] = "Link suspended.";
		return true;
	}

	private function approveLink()
	{
		if(!$this->dbo->setLinkStatus($this->did, 2))
		{
			$this->error_messages[] = "Err...nothing happened.";
			return true;
		}
		$this->status_messages[] = "Link activated.";
		return true;
	}

}

?>
