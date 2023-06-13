<?php
    if($this->user->logged_in && $this->user->auth_level >= 1) {
?>
<li><button type="button" onclick="logoutUser();return false;">Log Out</button></li>
<li><a href="/myaccount.html"><button type="button">My Account</button></a></li>
<?php
    } else {
?>
<li><button type="button" onclick="loginFormInit();return false;">Login</button></li>
<li><button type="button" onclick="registerFormInit();return false;">Register</button></li>
<?php
    }
?>