<?php
    require_once(INCLUDE_PATH . "domains/moviesample/apis/default.api.php");

class LogoutApi extends DefaultApi
{
    protected $return_text = "Error logging out";

    public function process()
    {
        //simply log out the user
        $this->user->logout();
        //now we return the login/register buttons template so the calling script can put them into the nav bar and menu
        $this->template = "lrbuttons";
    }
}
?>