<?php
    if(is_array($this->post_array)) extract($this->post_array);
    $user_err = false;
    $pass_err = false;
?>
</header>
<section class="thin"><div>
<?php
    echo "<p>Admin Login</p>\n<br />\n";
    if($this->action_status) {
        switch($this->action_status) {
            case "user_pass_invalid":
                echo "<p class=\"error centre\">Username or password did not match but just to be spiteful I&#039;m not telling you which one it was.</p>\n";
                $user_err = true;
                $pass_err = true;
                break;
            case "no_auth":
                echo "<p class=\"error centre\">You do not have authorization to view this page.</p>\n";
                break;
            case "zero_auth":
                echo "<p class=\"error centre\">Your account is not approved. Please contact admin for help.</p>\n";
                break;
            case "loggedout":
                echo "<p class=\"success centre\">You have logged out.</p>\n";
                break;
            case "ok":
                echo "<p class=\"success centre\">Everything seems to be okay. In theory you can now log in with the details you just registered.</p>\n";
                break;
            default:
                echo "<p class=\"error centre\">Something went wrong but I&#039;m not sure what. Error code " . $this->action_status . "</p>\n";                
                break;
        }
    } else {
        echo "<p>Username and password required.</p>\n";
    }
?>
<br /><form method="post" action="<?=($this->action_status == "loggedout") ? "?a=ShowData" : "" ?>">
Username:<br />
<input <?=($user_err) ? "class=\"errorf\" " : "";?>type="text" name="name" value="<?=$name ?? "";?>" />
<br /><br />
Password:<br />
<input <?=($pass_err) ? "class=\"errorf\" " : "";?>type="password" name="pass" value="<?=$pass ?? "";?>"/>
<br />
<input type="submit" value="Login" />
</form></div></section>
<br /><br /><p><a href="?a=Register">Register</a></p>
</body></html>