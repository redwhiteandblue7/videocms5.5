<?php
    require_once(INCLUDE_PATH . "classes/userpage.class.php");

    class DefaultPage extends UserPage
    {
        public function process()
        {
            return;
        }

        public function render()
        {
            echo "No class defined for page " . $this->page->vars()->page_name;
        }
    }
?>