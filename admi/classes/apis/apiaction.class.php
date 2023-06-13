<?php
    require_once(HOME_DIR . 'admi/classes/action.class.php');
    require_once(DB_PATH . 'manage.db.class.php');
    require_once(OBJECTS_PATH . 'domain.class.php');

class ApiAction extends Action
{
    protected $return_text = "Error - undefined error";
    protected $redirectedURL = "";
    protected $httpCode = 200;
    protected $curlErr = "";
    protected $files = [];
    protected $dbo;
    protected $domain;

	public $mod;

    public function __construct()
    {
        $this->dbo = new ManageDatabase();
        $this->dbo->setPrefix();

        $this->id();
        $this->type();
        
        $this->domain = new Domain();
		$d = $this->domain->prefix();

        //Dependency injection of the module class to provide certain post text process functions specific to each domain
		$mod_class_name = ucfirst($d) . "Functions";
		$module = 'modules/' . $d . '_mod.class.php';
		if(file_exists($module)) {
			include($module);
			$this->mod = new $mod_class_name();
		} else {
			include('modules/default_mod.class.php');
			$this->mod = new ModuleFunctions();
		}
        //Now inject the database object we are already using into the module class as it doesn't have its own
        $this->mod->dbo = $this->dbo;
    }

	protected function id()
	{
		$this->id = 0;
		if(isset($_GET["id"]) && is_numeric($_GET["id"])) {
			$this->id = $_GET["id"];
		}
	}

	protected function type()
	{
		$this->type = "";
		if(isset($_GET["type"])) {
			$this->type = $_GET["type"];
		}
	}

    public function process() : bool
    {
        return false;
    }

    public function prerender() : void
    {
        return;
    }

    public function render() : void
    {
        include "templates/apis/default_template.php";
    }
}