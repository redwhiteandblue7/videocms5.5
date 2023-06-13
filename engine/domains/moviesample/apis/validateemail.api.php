<?php
    require_once(INCLUDE_PATH . "domains/moviesample/apis/default.api.php");

class ValidateEmailApi extends DefaultApi
{
    protected $return_text = "Error|email";

    public function process()
    {
        if(!isset($_POST["email"])) {
            return;
        }

        $this->return_text = $this->user->validateEmail($_POST["email"]);
    }
}
?>