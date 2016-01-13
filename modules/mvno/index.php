<?php
/**
 * NATURAL - Copyright Open Source Mind, LLC
 * Last Modified: Date: 05-21-2013 19:15:01 -0500
 * @package Natural Framework
 */
session_start();
require_once('../../bootstrap.php');
require_once('mvno.controller.php');
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
    case 'mvno_list':
        echo mvno_list($_GET['row_id']);
        break;
    case 'mvno_list_pager':
        print mvno_list(NULL, $_GET['search'], $_GET['sort'], $_GET['page']);
        break;
    case 'mvno_list_sort':
        print mvno_list(NULL, $_GET['search'], $_GET['sort'], 1);
        break;
    case 'mvno_list_search':
        print mvno_list(NULL, $_GET['search']);
        break;
    case 'mvno_create_form':
        print mvno_create_form();
        break;
    case 'mvno_create_form_submit':
        print mvno_create_form_submit($_GET);
        break;
    case 'mvno_edit_form':
        print mvno_edit_form($_GET);
        break;
    case 'mvno_edit_form_submit':
        print mvno_edit_form_submit($_GET);
        break;
    case 'mvno_delete_form':
        print mvno_delete_form($_GET);
        break;
    case 'mvno_delete_form_submit':
        print mvno_delete_form_submit($_GET);
        break;
}
?>
