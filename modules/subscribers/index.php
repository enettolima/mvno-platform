<?php
/**
 * NATURAL - Copyright Open Source Mind, LLC
 * Last Modified: Date: 05-21-2013 19:15:01 -0500
 * @package Natural Framework
 */
session_start();
require_once('../../bootstrap.php');
require_once('subscribers.controller.php');
if (!$_SESSION['logged']) {
    //Checing session to force logout
    //Processed by process_information on lib/js/controller.js
    echo "LOGOUT";
    exit(0);
}

/*
 * Sending calls to the view
 * Call functions on {yourmodule}.controller.php
 */
switch ($_GET['fn']) {
    case 'subscribers_list':
        echo subscribers_list($_GET['row_id']);
        break;
    case 'subscribers_list_pager':
        print subscribers_list(NULL, $_GET['search'], $_GET['sort'], $_GET['page']);
        break;
    case 'subscribers_list_sort':
        print subscribers_list(NULL, $_GET['search'], $_GET['sort'], 1);
        break;
    case 'subscribers_list_search':
        print subscribers_list(NULL, $_GET['search']);
        break;
    case 'subscribers_create_form':
        print subscribers_create_form();
        break;
    case 'subscribers_create_form_submit':
        print subscribers_create_form_submit($_GET);
        break;
    case 'subscribers_edit_form':
        print subscribers_edit_form($_GET);
        break;
    case 'subscribers_edit_form_submit':
        print subscribers_edit_form_submit($_GET);
        break;
    case 'subscribers_delete_form':
        print subscribers_delete_form($_GET);
        break;
    case 'subscribers_delete_form_submit':
        print subscribers_delete_form_submit($_GET);
        break;
}
?>
