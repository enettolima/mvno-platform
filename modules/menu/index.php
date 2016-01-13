<?php
/**
 * NATURAL - Copyright Open Source Mind, LLC
 * Last Modified: Date: 05-21-2013 19:15:01 -0500
 * @package Natural Framework
 */
session_start();
require_once('../../bootstrap.php');
//require_once('menu.view.php');
require_once('menu.controller.php');

if (!$_SESSION['logged']) {
    //Checing session to force logout
    //Processed by process_information on lib/js/controller.js
    echo "LOGOUT";
    exit(0);
}

//Getting function from the jquery call
$fn = $_GET['fn'];

/*
 * Sending calls to the view
 */
switch ($fn) {
    case 'menu_list':
        echo menu_list($_GET['row_id']);
        break;
    case 'menu_list_pager':
        print menu_list(NULL, $_GET['search'], $_GET['sort'], $_GET['page']);
        break;
    case 'menu_list_sort':
        print menu_list(NULL, $_GET['search'], $_GET['sort'], 1);
        break;
    case 'menu_list_search':
        print menu_list(NULL, $_GET['search']);
        break;
    case 'menu_create_form':
        print menu_create_form();
        break;
    case 'menu_create_form_submit':
        print menu_create_form_submit($_GET);
        break;
    case 'menu_edit_form':
        print menu_edit_form($_GET);
        break;
    case 'menu_edit_form_submit':
        print menu_edit_form_submit($_GET);
        break;
    case 'menu_delete_form':
        print menu_delete_form($_GET);
        break;
    case 'menu_delete_form_submit':
        print menu_delete_form_submit($_GET);
        break;
}
?>
