<?php
    require_once(INCLUDE_PATH . "domains/moviesample/apis/default.api.php");

class LoginApi extends DefaultApi
{
    protected $return_text = "Error logging in user";

    public function process()
    {
        if($this->user->logged_in) {
            $this->template = "lrbuttons";
        } else {
            $this->return_text = "Error|" . $this->user->error_type;
        }
    }
}
?>