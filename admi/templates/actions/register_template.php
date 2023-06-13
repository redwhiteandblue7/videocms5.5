<?php
    if(is_array($this->post_array)) extract($this->post_array);
    $user_err = false;
    $pass_err = false;
    $email_err = false;
?>
</header>
<section class="thin"><div>
<?php
    echo "<p>Register an admin account</p>\n<br />\n";
    if($this->action_status) {
        switch($this->action_status) {
            case "name_invalid":
                echo "<p class=\"error centre\">Username contains invalid characters. Please try another.</p>\n";
                $user_err = true;
                break;
            case "name_short":
                echo "<p class=\"error centre\">Username is too short. Must be at least " . USERNAME_LENGTH . " characters.</p>\n";
                $user_err = true;
                break;
            case "pass_invalid":
                echo "<p class=\"error centre\">Password contains invalid characters. Please try another.</p>\n";
                $pass_err = true;
                break;
            case "pass_silly":
                echo "<p class=\"error centre\">You can&#039;t just have 'password' as your password you fool.</p>\n";
                $pass_err = true;
                break;
            case "pass_short":
                echo "<p class=\"error centre\">Password is too short. Must be at least " . PASSWORD_LENGTH . " characters.</p>\n";
                $pass_err = true;
                break;
            case "email_invalid":
                echo "<p class=\"error centre\">Email address does not seem to be valid.</p>\n";
                $email_err = true;
                break;
            case "name_exists":
                echo "<p class=\"error centre\">Some git is already using that username.</p>\n";
                $user_err = true;
                break;
            case "email_exists":
                echo "<p class=\"error centre\">Some git is already using that email address.</p>\n";
                $email_err = true;
                break;
            case "ok":
                echo "<p class=\"success centre\">Everything seems to be okay. In theory you can now log in with the details you just registered.</p>\n";
                break;
            default:
                echo "<p class=\"error centre\">Something went wrong but I&#039;m not sure what. Error code " . $this->action_status . "</p>\n";                
                break;
        }
    } else {
        echo "<p>Enter a username password and email address to register an admin account. If the database has just been set up this will automatically be upgraded to admin auth level.</p>\n";
    }
?>
<br /><form method="post" action="?a=Register">
Username:<br />
<input <?=($user_err) ? "class=\"errorf\" " : "";?>type="text" name="new_name" value="<?=$new_name ?? "";?>" />
<br /><br />
Password:<br />
<input <?=($pass_err) ? "class=\"errorf\" " : "";?>type="password" name="new_pass" value="<?=$new_pass ?? "";?>" />
<br /><br />
Email address:<br />
<input <?=($email_err) ? "class=\"errorf\" " : "";?>type="text" name="email" value="<?=$email ?? "";?>" />
<br />
<input type="submit" value="Register" />
</form></div></section>
</body></html>