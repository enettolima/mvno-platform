<?php

class ACL {

    public $username;
    public $password;
    public $email;

    public function login() {
        $user = new User();
        $user->authenticate($this->username, $this->password);
        if ($user->granted) {
            $_SESSION['affected'] = $user->affected;
            $_SESSION['logged'] = true;
            $_SESSION['log_id'] = $user->id;
            $_SESSION['log_email'] = $user->email;
            $_SESSION['log_file_id'] = $user->file_id;
            $_SESSION['log_first_name'] = $user->first_name;
            $_SESSION['log_last_name'] = $user->last_name;
            $_SESSION['log_username'] = $user->username;
            $_SESSION['log_access_level'] = $user->access_level;
            $_SESSION['log_status'] = $user->status;
            $_SESSION['log_language'] = $user->language;
            $_SESSION['log_dash1'] = $user->dashboard_1;
            $_SESSION['sid'] = session_id();
            $_SESSION['dash_type'] = 1;
        } else {
            $_SESSION['logged'] = false;
        }
    }

    public function logout() {
        session_start();
        if (isset($_SESSION)) {
            session_destroy();
        }
    }
}

?>
