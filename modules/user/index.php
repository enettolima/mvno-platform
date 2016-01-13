<?php
/**
 * NATURAL - Copyright Open Source Mind, LLC
 * Last Modified: Date: 02-15-2015 19:15:01 -0500
 * @package Natural Framework
 */
session_start();
require_once('../../bootstrap.php');
require_once('user.controller.php');

if (!$_SESSION['logged']) {
  echo "LOGOUT";
  exit(0);
}

/*
 * Sending calls to the view
 * Call functions on {yourmodule}.controller.php
 */
switch ($_GET['fn']) {
  case 'user_list':
    print user_list($_GET['row_id']);
    break;
  case 'user_list_pager':
    print user_list(NULL, $_GET['search'], $_GET['sort'], $_GET['page']);
    break;
  case 'user_list_sort':
    print user_list(NULL, $_GET['search'], $_GET['sort'], 1);
    break;
  case 'user_list_search':
    print user_list(NULL, $_GET['search']);
    break;
  case 'user_edit_form':
    print user_edit_form($_GET['user_id']);
    break;
  case 'user_edit_form_submit':
    print user_edit_form_submit($_GET);
    break;
  case 'user_delete_form':
    print user_delete_form($_GET['user_id']);
    break;
  case 'user_delete_form_submit':
    print user_delete_form_submit($_GET);
    break;
  case 'user_create_form':
    print user_create_form();
    break;
  case 'user_create_form_submit':
    print user_create_form_submit($_GET);
    break;
}
?>
