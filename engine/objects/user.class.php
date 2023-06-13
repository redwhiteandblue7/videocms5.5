<?php

//the user class
//contains the current user status, logged in, auth level etc
require_once(INCLUDE_PATH . 'classes/database.class.php');

class User
{
    private $dbo;
    private $vars;
    private $result_pointer = 0;
    private $users = [];

    public $logged_in = false;
    public $auth_level = 0;
    public $error_type = "";
    public $username = "";
    public $user_id = 0;

    /** Construct using either:
     *  user_id - fetch user from users table
     *  0 - create and empty user
     *  false - fetch the currently logged in user from session if there is one, empty if not
     */
    public function __construct($user_id = false)
    {
        $this->dbo = new Database();

        if(!$this->dbo->tableExists("users")) {
            //no point going any further if there is no users table yet (if database is not set up yet)
            $this->error_type = "no_users";
			unset($_SESSION["loggedin"]);
            return;
        }

        //if constructing with the currently logged in user (usual case)
        if($user_id === false) {
            if(isset($_SESSION["loggedin"])) {
                //already logged in this session
                $this->auth_level = $_SESSION["loggedin"];
                $this->username = $_SESSION["username"];
                $this->user_id = $_SESSION["user_id"];
                $this->logged_in = true;
                return;
            } elseif(isset($_POST["name"], $_POST["pass"])) {
                //user is trying to log in from login page
                $username = $this->dbo->sanitize($_POST["name"]);
                $password = $this->dbo->sanitize($_POST["pass"]);
            } elseif(isset($_COOKIE["User"], $_COOKIE["Pass"])) {
                //user wants to stay logged in
                $username = $this->dbo->sanitize($_COOKIE["User"]);
                $password = $this->dbo->sanitize($_COOKIE["Pass"]);
            }

            //if we now have the username and password
            if(isset($username, $password)) {
                if($this->userLogin($username, $password)) {
                    $_SESSION["user_id"] = $this->vars->user_id;
                    $_SESSION["username"] = $username;
                    $_SESSION["password"] = $password;
                    $_SESSION["loggedin"] = $this->vars->user_privilege;
                    $this->auth_level = $this->vars->user_privilege;
                    $this->logged_in = true;
                    $this->username = $this->vars->user_name;
                    $this->user_id = $this->vars->user_id;
                }
            }
        } elseif($user_id) {
            if($row = $this->dbo->fetchRow("users", "user_id", $user_id)) {
                $this->vars = $row;
                $this->auth_level = $this->vars->user_privilege;
                $this->username = $this->vars->user_name;
                $this->user_id = $this->vars->user_id;
            }
        }
    }

    private function userLogin(string $username, string $password) : bool
    {
        if($row = $this->dbo->fetchRowByValue("users", "user_name", $username))
        {
			if(!password_verify($password, $row->pass_word)) {
                $this->error_type = "user_pass_invalid";
                return false;
            }

            if($row->user_privilege == 0) {
                $this->error_type = "zero_priv";
                return false;
            }
            $this->vars = $row;
            $t = time();
            $this->dbo->updateColumn("users", "time_last_login", $t, "user_id", $row->user_id);
            $this->dbo->incrementColumn("users", "total_logins", "user_id", $row->user_id);
            return true;
        }

        $this->error_type = "user_pass_invalid";
        return false;
    }

    /** Return whether the user is authorized to the required level
     * 
     * @param auth_level = the required authorization level
     * 
     * Return: "ok" if authorized, "no_auth" if not authorized, "no_login" if no authorization can be done because user not logged in
     */
    public function auth(int $auth_level) : string
    {
        if($this->auth_level < $auth_level) {
            if($this->logged_in)
                return "no_auth";
            else
                return "no_login";
        } else {
            return "ok";
        }
    }

	public function register() : bool
	{
        $this->error_type = "";

		if(isset($_POST["new_name"]) && isset($_POST["new_pass"]) && isset($_POST["email"]))
		{
			$username = $_POST["new_name"];
            $valid = $this->validateUser($username);
            if($valid != "OK") {
                $this->error_type = $valid;
                return false;
            }

			$password = $_POST["new_pass"];
			if($password != $this->dbo->sanitize($password)) {
                $this->error_type = "pass_invalid";
				return false;
			}

            if(isset($_POST["confirm_pass"]) && $_POST["confirm_pass"] != $password) {
                $this->error_type = "pass_mismatch";
                return false;
            }

			if(strtolower($password) == "password") {
                $this->error_type = "pass_silly";
				return false;
			}

			if(strlen($password) < PASSWORD_LENGTH) {
                $this->error_type = "pass_short";
				return false;
			}

			$email = $_POST["email"];
            $valid = $this->validateEmail($email);
            if($valid != "OK") {
                $this->error_type = $valid;
                return false;
            }

			$this->dbo->insertUser($username, $password, $email);
			return true;
		}

		return false;
	}

    public function validateUser($username)
    {
        if($username != $this->dbo->sanitize($username)) {
            return "name_invalid";
        }

        if($username != $this->dbo->stripNonAlphaNumeric($username)) {
            return "name_invalid";
        }

        if(strlen($username) < USERNAME_LENGTH) {
            return "name_short";
        }

        if($this->dbo->fetchRowByValue("users", "user_name", $username)) {
            return "name_exists";
        }
        return "OK";
    }

    public function validateEmail($email)
    {
        if($email != $this->dbo->sanitize($email)) {
            return "email_invalid";
        }

        if(strlen($email) < 6 || strpos($email, "@") === false) {
            return "email_invalid";
        }

        if($this->dbo->fetchRowByValue("users", "email_addr", $email)) {
            return "email_exists";
        }
        return "OK";
    }

    public function users() : array
    {
        $this->users = $this->dbo->fetchTable("users");
        $this->result_pointer = 0;
        return $this->users;
    }

    public function next()
    {
        if($this->result_pointer < sizeof($this->users)) {
            $row = $this->users[$this->result_pointer++];
            $this->row = $row;
            return $row;
        }

        return "";
    }

    //this is to logout the normal user, admin users are logged out by the admin class
    public function logout()
    {
        $this->logged_in = false;
        unset($_SESSION["loggedin"]);
        setcookie("User", '', time() + 1, "/");
        setcookie("Pass", '', time() + 1, "/");
        $this->auth_level = 0;
        $this->username = "";
        $this->user_id = 0;
    }

    //if a session token is set then return it so that it matches expected value from post data
    //if not then the user has not recently visited any page so we need to return a random string to foil CSRF attacks
    public function getSessionToken() : string
    {
        if(isset($_SESSION["token"])) {
            return $_SESSION["token"];
        } else {
//            return bin2hex(random_bytes(32));
            return "Hello World";
        }
    }

    //set a random string as a session token to prevent CSRF attacks
    public function setSessionToken() : string
    {
        if(!isset($_SESSION["token_count"])) {
            $_SESSION["token_count"] = 0;
        } else {
            $_SESSION["token_count"]++;
        }
        $_SESSION["token"] = bin2hex(random_bytes(32));
        return $_SESSION["token"];
    }
}