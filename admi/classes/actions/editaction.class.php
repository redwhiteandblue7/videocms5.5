<?php
    require_once(HOME_DIR . 'admi/classes/action.class.php');

class EditAction extends Action
{
    protected $dbo;
    protected $post_array = [];         //array containing sanitised fields from $_POST
    protected $post_object;             //object containing sanitised fields from $_POST

    public function __construct()
    {
        $this->getSantizedGetObject();
		$this->id();
		$this->type();
        $this->getSanitizedPostArray();
        $this->getSanitizedPostObject();
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
        return;
    }

    public function status(string $status = "") : string
    {
        return $this->action_status;
    }

    // Get a sanitized version of the post vars ready for SQL insertion but turn encoded html chars back into real chars
    private function getSanitizedPostArray() : void
    {
        $this->post_array = [];

        foreach($_POST as $key => $value) {
            if(is_array($value)) {
                $this->post_array[$key] = [];
                foreach($value as $subkey => $subvalue) {
                    $this->post_array[$key][$subkey] = htmlspecialchars_decode($subvalue);
                }
            } else {
                $this->post_array[$key] = htmlspecialchars_decode($value);
            }
        }
    }

    // Get a sanitized version of the post vars ready for SQL insertion
    private function getSanitizedPostObject() : void
    {
        $this->post_object = new stdClass();

        foreach($_POST as $key => $value) {
            if(is_array($value)) {
                $this->post_object->$key = [];
                foreach($value as $subkey => $subvalue) {
                    $this->post_object->$key[$subkey] = htmlspecialchars_decode($subvalue);
                }
            } else {
                $this->post_object->$key = htmlspecialchars_decode($value);
            }
        }
    }
}