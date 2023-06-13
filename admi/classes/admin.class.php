<?php
    require_once(DB_PATH . "manage.db.class.php");
	require_once(OBJECTS_PATH . 'domain.class.php');
	require_once(OBJECTS_PATH . 'user.class.php');

class AdminPage
{
	public $title;
	public $base_url;
	public $pagename;
	public $page = 0;			//page number in results displays
	public $domain_list = [];
	public $num_of_domains = 0;
	public $status_messages = [];
	public $action_status = "";	//result code of the last action
	public $username;

	protected $in_modal;		//true if actions are being performed from within the modal window
	protected $columntype;
	protected $startdays;
	protected $browser;
	protected $domain_id;

	private $dbo;
	private $authorized = false;

	public function __construct()
	{
		$this->pagename = basename($_SERVER['PHP_SELF']);

		$user = new User();
		if($user->error_type) {
			//if there's an error already then no point continuing we need to just quit back to the index page
			$this->action_status = $user->error_type;
			return;
		}

		$dmo = new Domain();
		if($dmo->error_type) {
			//if there's an error already then no point continuing we need to just quit back to the index page
			$this->action_status = $dmo->error_type;
			return;
		}

		//if user is not logged in or doesn't have auth level
		if(!$user->logged_in) {
			//then if page is displaying register form
			if(isset($_GET["a"]) && $_GET["a"] == "Register") {
				//then if user successfully registered
				if($user->register()) {
					//Make the template display the login form not the register form
					$this->action_status = "ok";
				} else {
					$this->action_status = $user->error_type;
				}
			}
			return;
		}

		$this->username = $user->username;
		if(isset($_GET["a"]) && $_GET["a"] == "Login") {
			//Prevent the login form from appearing if user has just successfully logged in (default action will be run instead)
			unset($_GET["a"]);
		}

		if(!$dmo->domain_id) {
			//if there's no domain id then there's no domains in the database
			$this->action_status = "no_domain";
		}
		$this->domain_id = $dmo->domain_id;

		if(isset($_GET["strict_mode"]))
		{
			if($_GET["strict_mode"] == "fast") $_SESSION["mode_strict"] = "fast";
			if($_GET["strict_mode"] == "strict") $_SESSION["mode_strict"] = "strict";
		}
		if(!isset($_SESSION["mode_strict"])) $_SESSION["mode_strict"] = "strict";

		if(isset($_GET["list_mode"]))
		{
			if($_GET["list_mode"] == "full") $_SESSION["mode_list"] = "full";
			if($_GET["list_mode"] == "short") $_SESSION["mode_list"] = "short";
		}
		if(!isset($_SESSION["mode_list"])) $_SESSION["mode_list"] = "short";

		if(isset($_POST["autotag"])) $_SESSION["autotag"] = $_POST["autotag"];
		if(isset($_SESSION["autotag"])) $this->autotag = $_SESSION["autotag"];
	}

	/** 
	 *  The main admin controller function
	 *  Decides on whether to display setup or auth pages, or run the current action
	 */
	public function exec()
	{
		if($this->action_status == "no_users" || $this->action_status == "no_domains") {
			//these errors mean there is no users table or there is no domains table so the database needs to be set up
			$this->action("Setup");
			return;
		}

		if(isset($_GET["a"])) {
			$action = $_GET["a"];
		} else {
			$action = DEFAULT_ADMIN_ACTION;
		}

		if(!$this->authorize(200)) {
			//user is not logged in with the required auth level so show the login or registration form
			if($action == "Register" && $this->action_status != "ok") {
				//if user is on the register page and has not successfully registered
				$this->action("Register");
				return;
			}

			//otherwise take him to the login page
			$this->action("Login");
			return;
		}

		//if logging out then just do that and do not display any admin template
		if($action == "Logout") {
			$this->action("Logout");
			return;
		}

		//user is authorized so crack on
		setcookie("User", $_SESSION["username"], time() + 7862400, "/");
		setcookie("Pass", $_SESSION["password"], time() + 7862400, "/");
		$this->authorized = true;

		//if no domain exists in database the only action the user can perform is to add a new one
		//otherwise if an action is requested via get then run that
		//otherwise use default action (usually display stats)
		if($this->action_status == "no_domain") {
			$action = "EditDomain";
		}
		$this->action($action);
	}

	private function authorize(int $auth_level) : bool
	{
		if(!isset($_SESSION["loggedin"])) return false;
		if($_SESSION["loggedin"] >= $auth_level) return true;

		//set this here so the login template can display an error message
		$this->action_status = "no_auth";
		return false;
	}

	private function action(string $action = "")
	{
		$file_name = "classes/actions/" . strtolower($action) . ".class.php";
		if(file_exists($file_name)) {
			include($file_name);
			$class_name = $action . "Action";
			$page = new $class_name();
		} else {
			include('actions/defaultaction.class.php');
			$page = new DefaultAction();
		}

		//mostly this changes nothing except for the auth actions where we've already got the auth status and need to pass it in
		$page->status($this->action_status);
		$page->domain_id = $this->domain_id;

		if($page->remember_me) {
			//if the action wants to be remembered then set the session vars
			$_SESSION["remember_action"] = $action;
			$_SESSION["remember_page"] = $page->page;
		}

		//run the action process, returns true if process completed and any remembered action now needs to be run
		if($page->process() && isset($_SESSION["remember_action"])) {
			//if there is an action set in session var then run that action also, 
			$action = $_SESSION["remember_action"];
			$status = $page->status();
			$class_name = $action . "Action";
			$file_name = "actions/" . strtolower($action) . ".class.php";
			include($file_name);
			$page = new $class_name();
			//restore the page number we were on before the action was run
			$page->page = $_SESSION["remember_page"];
			$page->action = $action;
			$page->status($status);
			$page->process();
		}

		if(!$sub_page = $page->name) $sub_page = $action;

		//initialize the current session domain or the first available domain if there is one
		$dmo = new Domain();
		$this->base_url = $dmo->baseURL();
		if($dmo->selected()) {
			//user has just changed the domain so reset all filters and settings
			$this->resetFilters();
		}

		//if the admin panel is going to be rendered get the list of domains for the domain selection dropdown
		if($this->authorized) {
			$dbo = new ManageDatabase();
			$this->domain_list = $dbo->getDomains();
			$this->num_of_domains = sizeof($this->domain_list);
		}

		include('templates/admin_template.php');
		$page->prerender();
		$page->render();
		$dmo->final();
	}

	/** Reset session variables when the selected domain has changed */
	protected function resetFilters()
	{
		unset($_SESSION["remember_action"]);
		unset($_SESSION["remember_page"]);
		unset($_SESSION["searchstring"]);
		unset($_SESSION["sort"]);
		unset($_SESSION["type"]);
	}
}

?>
