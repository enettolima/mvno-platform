<?php
/**
 * NATURAL - Copyright Open Source Mind, LLC
 * Last Modified: Date: 05-21-2013 19:15:01 -0500
 * @package Natural Framework
 */
session_start();
require_once('../../bootstrap.php');
require_once('template_book.controller.php');
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
    case 'book_list':
        echo book_list($_GET['row_id']);
        break;
    case 'book_list_pager':
        print book_list(NULL, $_GET['search'], $_GET['sort'], $_GET['page']);
        break;
    case 'book_list_sort':
        print book_list(NULL, $_GET['search'], $_GET['sort'], 1);
        break;
    case 'book_list_search':
        print book_list(NULL, $_GET['search']);
        break;
    case 'book_create_form':
        print book_create_form();
        break;
    case 'book_create_form_submit':
        print book_create_form_submit($_GET);
        break;
    case 'book_edit_form':
        print book_edit_form($_GET);
        break;
    case 'book_edit_form_submit':
        print book_edit_form_submit($_GET);
        break;
    case 'book_delete_form':
        print book_delete_form($_GET);
        break;
    case 'book_delete_form_submit':
        print book_delete_form_submit($_GET);
        break;
}
?>
