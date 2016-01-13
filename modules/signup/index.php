<?php
require_once('../../bootstrap.php');
require_once('signup.controller.php');

/*
 * Sending calls to the view
 * Call functions on {yourmodule}.controller.php
 */

 if($_GET['fn'])  {
   $fn = $_GET['fn'];
 }
 else {
   $fn = $_POST['fn'];
 }

switch ($fn) {
    case 'signup_form':
        echo signup_form($_POST);
        break;
    default:
        echo signup_form($_POST);
        break;
}
?>
