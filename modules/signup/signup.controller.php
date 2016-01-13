<?php
function signup_form($data) {
  $user = new User();
  if($data){
    foreach ($data as $key => $value) {
      $field_name = ucwords(str_replace('_', ' ', $key));
      switch ($key) {
        case 'first_name':
        case 'last_name':
        case 'username':
          if (trim($value) == '') {
            $error[] = 'Field ' . $field_name . ' is required!';
          }
          break;
        case 'email':
          if (!(filter_var($value, FILTER_VALIDATE_EMAIL))) {
            $error[] = 'Invalid format for ' . $field_name . ', please insert a valid email!';
          }
          break;
      }
    }

    // Verify Username
    $user->byUsername($data['username']);
    if ($data['username'] = $user->username) {
      $error[] = 'Username already taken. Please select another one.';
    }

    if ($data['password'] != $data['password2']) {
      $error[] = 'Password does not match.';
    }

    // Adding values
    if($data['password']){
      $user->password 	= $data['password'];
      $gen_pass = false;
    }else{
      $gen_pass = true;
    }
    if(!$error){
      $res = $user->create(false, $gen_pass, $data);
      if ($res) {
        session_start();
        $ACL = new ACL();
        $ACL->username = $res->username;
        $ACL->password = $res->temp_password;
        $ACL->login();
        header("Location: /dashboard.php");
      }else{
        $error[] = 'Ops, We could not create the user at this time. Try again later.';
      }
    }
  }
  global $twig;
  // Twig Base
  $template = $twig->loadTemplate('signup-content.html');
  $template->display(array(
    'project_title' => TITLE,
    'path_to_theme' => '../../'.THEME_PATH,
    'company' => NATURAL_COMPANY,
    'page' => 'signup',
    'data' => $data,
    'errors' => $error
  ));
}
?>
