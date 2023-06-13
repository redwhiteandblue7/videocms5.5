<?php
    require_once(INCLUDE_PATH . "domains/moviesample/apis/default.api.php");

class RegisterApi extends DefaultApi
{
    protected $return_text = "Error registering user";

    public function process()
    {
        if($this->user->logged_in) {
            $this->return_text = "Error|logged_in";
            return;
        }

        if($this->user->register()) {
            $this->return_text = "OK";
        } else {
            $this->return_text = "Error|" . $this->user->error_type;
        }
    }
}
?>