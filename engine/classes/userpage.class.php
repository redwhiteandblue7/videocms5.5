<?php
	require_once(INCLUDE_PATH . 'classes/visitor.class.php');
	require_once(INCLUDE_PATH . 'traits/text.trait.php');
	require_once(INCLUDE_PATH . 'classes/database.class.php');
	require_once(OBJECTS_PATH . 'user.class.php');

class UserPage
{
	use TextFuncs;

	protected $dbo;
	protected $user;
	protected $protocol = "http";
	protected $template = "";
	protected $visitor;
	protected $error;
	protected $pageload_stat_id;
	protected $session_token;
	protected $blurb_text;

	protected $a_target = "";
	protected $s_target = "";
	protected $cache_file = "";
	protected $BBHandler;

	protected $page_num = 0;
	protected $num_of_pages = 1;
	protected $num_of_rows = 0;

	protected $tag_names;

	protected $canonical_url;
	protected $canonical_base;

	public $domain;
	public $page;
	public $uri = array();

//	abstract public function process();
//	abstract public function render();

	public function __construct()
	{
//		$this->dbo = new Database();
		$this->visitor = new Visitor();
		$this->user = new User();
	}

	public function init()
	{
		$this->protocol = $this->domain->vars()->http_scheme;
//		$page = str_replace("index.html", "", $this->page->vars()->page_name);
		$this->canonical_base = $this->protocol . "://" . $this->domain->vars()->sub_domain . $this->domain->vars()->domain_name . "/";
		$this->visitor->copyDomainVars($this->domain->vars());
		$this->visitor->blockRobots();
		$this->visitor->getBrowserType();
//		$this->visitor->setTestGroup();
	}

/*
	public function getInvisiblePageTagnames()
	{
		$this->dbo->fetchInvisiblePageTags($this->page_vars->page_id);
		while($row = $this->dbo->getNextResultsRow())
		{
			$this->tag_names[] = $row;
		}
		$this->dbo->resetResults();
	}

	public function getInvisiblePostTagnames()
	{
		$this->dbo->fetchInvisiblePostTags($this->post_vars->post_id);
		while($row = $this->dbo->getNextResultsRow())
		{
			$this->tag_names[] = $row;
		}
		$this->dbo->resetResults();
	}
*/
	protected function getPage()
	{
		$matches = array();
		if(preg_match("/^page([0-9]+)\\.html$/", $this->page_name, $matches))
		{
			$this->page_num = $matches[1] - 1;
			if($this->page_num >= $this->page_vars->max_gallery_pages)
			{
				$this->error = "error";
			}
			if($this->canonical_url) $this->canonical_url .= "page" . ($this->page_num + 1) . ".html";
		}
	}

	public function getPageloadStat()
	{
		if($this->pageload_stat_id) return $this->pageload_stat_id;

		return $this->visitor->addVisitorPage();
	}
}

?>