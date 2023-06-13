<?php
    require_once(HOME_DIR . 'admi/classes/actions/editaction.class.php');
    require_once(INCLUDE_PATH . "objects/page.class.php");

class PageEditAction extends EditAction
{
    public $name = "Edit Page";

    public function process() : bool
    {
        // If we're copying a page, set the post_array to the page we're copying
        if(isset($_POST["copy_id"]) && is_numeric($_POST["copy_id"])) {
            $copy_id = $_POST["copy_id"];
            $copy_page = new Page($copy_id);
            $this->post_object = $copy_page->vars();
            $this->post_object->page_id = $_POST["page_id"] ?? 0;
            $this->post_object->copy_id = $copy_id;
            $this->post_object->id = $_POST["id"] ?? 0;
            $this->action_status = "copy";
            return false;
        }

        // Get the page data for the page id if it exists
        $id = $this->id;
		$page = new Page($id);
        // If the form hasn't been submitted then just set up the post array and return
		if(!(isset($_POST["page_name"]))) {
            $this->action_status = "vars_empty";
            if($id) {
                if($this->post_object = $page->vars()) return false;
                // If we get here then the page id was invalid
                $this->action_status = "not_found";
            }
            // If there's no id then we're creating a new page
            $this->post_object->page_id = 0;
            $this->post_object->id=0;
            return false;
        }

        // If we get here then the form has been submitted so we need to save the page
		$page->save($this->post_object);

        // If there was an error saving the page then set the action status and return
		if($page->error_type) {
			$this->action_status = $page->error_type;
		} else {
			$this->action_status = "ok";
            return true;
        }

		return false;
    }

    public function prerender() : void
    {
        include "templates/pages_template.php";
    }

    public function render() : void
    {
        $pages = new Page();
        $pages->pages();
        include "templates/actions/pageedit_template.php";
    }
}
?>