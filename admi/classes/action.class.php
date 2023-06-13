<?php
abstract class Action
{
    public $action_status = "";
    public $status_messages = [];
    public $in_modal = false;
	public $remember_me = false;
	public $page = 0;
	public $action = "";
	public $domain_id = 0;
	public $name = "Unnamed";
	protected $id = 0;
	protected $type = "";
	protected $get_object;

    abstract public function process() : bool;
    abstract public function prerender() : void;
    abstract public function render() : void;

	protected function id()
	{
		$this->id = 0;
		if(isset($this->get_object->id)) {
			$this->id = $this->get_object->id;
		}
	}

	protected function type()
	{
		$this->type = "";
		if(isset($this->get_object->type)) {
			$this->type = $this->get_object->type;
		}
	}

    public function status(string $status = "") : string
    {
		if($status != "") {
			$this->action_status = $status;
		}
        return $this->action_status;
    }

	/** Function to strip non alpha numeric characters from a string */
	protected function stripNonAlphaNumeric(string $string) : string
	{
		return preg_replace("/[^a-zA-Z0-9]/", "", $string);
	}
	
	protected function getSantizedGetObject() : void
	{
		$this->get_object = new stdClass();

		foreach($_GET as $key => $value) {
			$this->get_object->$key = $this->stripNonAlphaNumeric($value);
		}
	}
}
?>