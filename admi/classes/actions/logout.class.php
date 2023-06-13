<?php
    require_once(HOME_DIR . 'admi/classes/actions/editaction.class.php');

class LogoutAction extends EditAction
{
    public function __construct()
    {
        unset($_SESSION["loggedin"]);
        unset($_SESSION["domain_id"]);
        unset($_SESSION["old_domain_id"]);
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