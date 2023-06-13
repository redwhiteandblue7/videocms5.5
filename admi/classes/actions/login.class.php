<?php
    require_once(HOME_DIR . 'admi/classes/actions/editaction.class.php');

class LoginAction extends EditAction
{
    public function __construct()
    {
        unset($_SESSION["loggedin"]);
        setcookie("User", '', time() + 1, "/");
        setcookie("Pass", '', time() + 1, "/");

        parent::__construct();
    }

    public function render() : void
    {
        include("templates/actions/login_template.php");
    }
}
?>