<?php
/**
 * NATURAL - Copyright Open Source Mind, LLC
 * Last Modified: Date: 05-21-2013 19:15:01 -0500
 * @package Natural Framework
 */
session_start();
require_once('../../bootstrap.php');
require_once('dashboard_widgets.controller.php');
require_once('dashboard_widgets_blocks.php');

$islogged = array_key_exists('logged', $_SESSION) ? $_SESSION['logged'] : false;
if (!$islogged) {
    //Checing session to force logout
    //Processed by process_information on lib/js/controller.js
    echo "LOGOUT";
    exit(0);
}

//Getting function from the jquery call
if($_GET['fn'])  {
  $fn = $_GET['fn'];
}
else {
  $fn = $_POST['fn'];
}

/*
 * Sending calls to the view
 */
switch ($fn) {
    case 'dashboard_widgets_list':
        echo dashboard_widgets_list($_GET['row_id']);
        break;
    case 'dashboard_widgets_list_pager':
        print dashboard_widgets_list(NULL, $_GET['search'], $_GET['sort'], $_GET['page']);
        break;
    case 'dashboard_widgets_list_sort':
        print dashboard_widgets_list(NULL, $_GET['search'], $_GET['sort'], 1);
        break;
    case 'dashboard_widgets_list_search':
        print dashboard_widgets_list(NULL, $_GET['search']);
        break;
    case 'dashboard_widgets_graph_line_template':
        print dashboard_widgets_graph_line_template();
        break;
    case 'dashboard_widgets_graph_area_template':
        print dashboard_widgets_graph_area_template();
        break;
    case 'dashboard_widgets_graph_bar_template':
        print dashboard_widgets_graph_bar_template();
        break;
    case 'dashboard_widgets_graph_donut_template':
        print dashboard_widgets_graph_donut_template();
        break;
    case 'dashboard_widgets_graph_temp_template':
        print dashboard_widgets_graph_temp_template();
        break;
    case 'dashboard_widgets_create_form_submit':
        print dashboard_widgets_create_form_submit($_POST);
        break;
    case 'dashboard_widgets_edit_form':
        print dashboard_widgets_edit_form($_GET);
        break;
    case 'dashboard_widgets_edit_form_submit':
        print dashboard_widgets_edit_form_submit($_GET);
        break;
    case 'dashboard_widgets_delete_form':
        print dashboard_widgets_delete_form($_GET);
        break;
    case 'dashboard_widgets_delete_form_submit':
        print dashboard_widgets_delete_form_submit($_GET);
        break;
    case 'dashboard_widgets_load_droplets_wrapper':
        print dashboard_widgets_load_droplets_wrapper();
        break;
    case 'dashboard_widgets_load_droplets':
        print dashboard_widgets_load_droplets();
        break;
    case 'dashboard_update_list':
        dashboard_update_list($_GET);
        break;
    case 'dashboard_setup':
        print dashboard_setup($_GET);
        break;
    case 'dashboard_user_update':
        print dashboard_user_update($_POST);
        break;
    case 'dashboard_user_widget_add':
        print dashboard_user_widget_add($_GET);
        break;

    /*
     *Calling functions at dashboard_widgets_blocks.php
     */
     case 'render_widget_graph':
         print render_widget_graph($_GET);
         break;
     case 'custom_graph_example':
         print custom_graph_example($_GET);
         break;
}
?>
