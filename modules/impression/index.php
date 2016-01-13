<?php
/**
 * NATURAL - Copyright Open Source Mind, LLC
 * Last Modified: Date: 05-21-2013 19:15:01 -0500
 * @package Natural Framework
 */
session_start();
require_once('../../bootstrap.php');
require_once('impression.controller.php');
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
    case 'impression_list':
        echo impression_list($_GET['row_id']);
        break;
    case 'impression_list_pager':
        print impression_list(NULL, $_GET['search'], $_GET['sort'], $_GET['page']);
        break;
    case 'impression_list_sort':
        print impression_list(NULL, $_GET['search'], $_GET['sort'], 1);
        break;
    case 'impression_list_search':
        print impression_list(NULL, $_GET['search']);
        break;
    case 'impression_create_form':
        print impression_create_form();
        break;
    case 'impression_create_form_submit':
        print impression_create_form_submit($_GET);
        break;
    case 'impression_edit_form':
        print impression_edit_form($_GET);
        break;
    case 'impression_edit_form_submit':
        print impression_edit_form_submit($_GET);
        break;
    case 'impression_delete_form':
        print impression_delete_form($_GET);
        break;
    case 'impression_delete_form_submit':
        print impression_delete_form_submit($_GET);
        break;
}
?>
