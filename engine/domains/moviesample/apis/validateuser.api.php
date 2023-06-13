<?php
    require_once(INCLUDE_PATH . "domains/moviesample/apis/default.api.php");

class ValidateUserApi extends DefaultApi
{
    protected $return_text = "Error|username";

    public function process()
    {
        if(!isset($_POST["name"])) {
            return;
        }

        $this->return_text = $this->user->validateUser($_POST["name"]);
    }
}
?>