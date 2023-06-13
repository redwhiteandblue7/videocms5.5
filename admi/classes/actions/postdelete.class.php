<?php
    require_once(HOME_DIR . 'admi/classes/actions/editaction.class.php');
    require_once(INCLUDE_PATH . "objects/post.class.php");

class PostDeleteAction extends EditAction
{
    public $name = "Delete Post";

    public function process() : bool
    {
        $id = $this->id;
        $post = new Post($id);

        if($post->delete()) {
            $this->action_status = "deleted";
            return true;
        } else {
            $this->action_status = "error";
            return false;
        }
    }

    public function prerender() : void
    {
        include "templates/posts_template.php";
    }

    public function render() : void
    {
        include "templates/actions/showposts_template.php";
    }
}
?>