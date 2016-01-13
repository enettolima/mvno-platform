<?php
/**
 * List items
 */
function dashboard_widgets_list($row_id = NULL, $search = NULL, $sort = NULL, $page = 1) {
    $view = new ListView();
    // Row Id for update only row
    if (!empty($row_id)) {
      $row_id = 'id = ' . $row_id;
    } else {
      $row_id = 'id != 0';
    }

		// Sort
    if (empty($sort)) {
        $sort = 'id ASC';
    }

    $limit = PAGER_LIMIT;
    $offset = ($page * $limit) - $limit;

		$db = DataConnection::readOnly();
		$total_records = 0;

    // Search
    if (!empty($search)) {
        $search_fields = array('id', 'title', 'description');
        $exceptions = array();
        $search_query = build_search_query($search, $search_fields, $exceptions);

				$dashboard_widgets = $db->dashboard_widgets()
									->where($row_id)
									->and($search_query)
									->order($sort)
									->limit($limit, $offset);
		} else {
				$dashboard_widgets = $db->dashboard_widgets()
									->where($row_id)
									->order($sort)
									->limit($limit, $offset);
		}

		$total_records = $db->dashboard_widgets()->count("*");

    if (count($dashboard_widgets) > 0) {
        // Building the header with sorter
        $headers[] = array('display' => 'Id', 'field' => 'id');
        $headers[] = array('display' => 'Title', 'field' => 'title');
        $headers[] = array('display' => 'Description', 'field' => 'description');
        $headers[] = array('display' => 'Edit', 'field' => NULL);
        $headers[] = array('display' => 'Delete', 'field' => NULL);
        $headers = build_sort_header('dashboard_widgets_list', 'dashboard_widgets', $headers, $sort);

				$i = 0;
        foreach($dashboard_widgets as $widget) {
            $rows[$i]['row_id'] = $widget['id'];
            $rows[$i]['id']     = $widget['id'];
            $rows[$i]['title']   = $widget['title'];
            if(strlen($widget['description'])>50){
                $rows[$i]['description'] = substr($widget['description'], 0, 50).'...';
            }else{
                $rows[$i]['description'] = $widget['description'];
            }
            $rows[$i]['edit']   = theme_link_process_information('',
							'dashboard_widgets_edit_form',
							'dashboard_widgets_edit_form',
							'dashboard_widgets',
							array('extra_value' => 'id|' . $widget['id'],
								'response_type' => 'modal',
								'icon' => NATURAL_EDIT_ICON,
								'class' => $disabled));
            $rows[$i]['delete'] = theme_link_process_information('',
							'dashboard_widgets_delete_form',
							'dashboard_widgets_delete_form',
							'dashboard_widgets',
							array('extra_value' => 'id|' . $widget['id'],
								'response_type' => 'modal',
								'icon' => NATURAL_REMOVE_ICON,
								'class' => $disabled));
					$i++;
				}
    }

    $options = array(
        'show_headers' => TRUE,
        'page_title' => translate('Users List'),
        'page_subtitle' => translate('Manage Dashboard Widgetss'),
        'empty_message' => translate('No dashboard widgets found!'),
        'table_prefix' => theme_link_process_information(translate('Create New Dashboard Widget'),
					'dashboard_widgets_graph_line_template',
					'dashboard_widgets_graph_line_template',
					'dashboard_widgets',
					array('response_type' => 'in_modal')),
        'pager_items' => build_pager('dashboard_widgets_list', 'dashboard_widgets', $total_records, $limit, $page),
        'page' => $page,
        'sort' => $sort,
        'search' => $search,
        'show_search' => TRUE,
        'function' => 'dashboard_widgets_list',
        'module' => 'dashboard_widgets',
        'update_row_id' => '',
        'table_form_id' => '',
        'table_form_process' => '',
    );
    $listview = $view->build($rows, $headers, $options);

    return $listview;
}

/*
 * show add form
 */
function dashboard_widgets_graph_line_template() {
  global $twig;
	// Twig Base
	$template = $twig->loadTemplate('widget-create-line.html');
	$template->display(array(
		// Dashboard - Passing default variables to content.html
		'page_title' => 'Dashboard',
		'page_subtitle' => 'Widgets'
	));
}

function dashboard_widgets_graph_area_template() {
  global $twig;
	// Twig Base
	$template = $twig->loadTemplate('widget-create-area.html');
	$template->display(array(
		// Dashboard - Passing default variables to content.html
		'page_title' => 'Dashboard',
		'page_subtitle' => 'Widgets'
	));
}

function dashboard_widgets_graph_bar_template() {
  global $twig;
	// Twig Base
	$template = $twig->loadTemplate('widget-create-bar.html');
	$template->display(array(
		// Dashboard - Passing default variables to content.html
		'page_title' => 'Dashboard',
		'page_subtitle' => 'Widgets'
	));
}

function dashboard_widgets_graph_donut_template() {
  global $twig;
	// Twig Base
	$template = $twig->loadTemplate('widget-create-donut.html');
	$template->display(array(
		// Dashboard - Passing default variables to content.html
		'page_title' => 'Dashboard',
		'page_subtitle' => 'Widgets'
	));
}

function dashboard_widgets_graph_temp_template() {
  global $twig;
	// Twig Base
	$template = $twig->loadTemplate('widget-create-template.html');
	$template->display(array(
		// Dashboard - Passing default variables to content.html
		'page_title' => 'Dashboard',
		'page_subtitle' => 'Widgets',
    'widget_templates' => array_diff(scandir(NATURAL_WIDGET_TEMPLATE_PATH, 1), array('.', '..'))
	));
}

/*
 * Insert on table
 */
function dashboard_widgets_create_form_submit($data) {
  $error = dashboard_widgets_validate($data);
  if (!empty($error)) {
    return FALSE;
  }
  $dash = new DashboardWidgets();
  foreach ($data as $field => $value) {
    if ($field != 'fn') {
      $dash->$field = $value;
      $submit[$field] = $value;
    }
  }
  $response = $dash->create($submit);
  if ( $response['id'] > 0 ) {
    return dashboard_widgets_list();
  } else {
    return false;
  }
}

/*
 * show edit form
 */
function dashboard_widgets_edit_form($data) {
    $dashboard_widgets = new DashboardWidgets();
    $dashboard_widgets->byID($data['id']);
    $frm = new DbForm();
    $frm->build('dashboard_widgets_edit_form', $dashboard_widgets, $_SESSION['log_access_level']);
}

/*
 * Update table
 */
function dashboard_widgets_edit_form_submit($data) {
	$error = dashboard_widgets_validate($data);
  if (!empty($error)) {
    return FALSE;
  } else {
    $dash = new DashboardWidgets();
    $update = $dash->update($data);
    if ($update['code']==200) {
      return dashboard_widgets_list($data['id']);
    }
  }
}

/*
 * show edit form
 */
function dashboard_widgets_delete_form($data) {
    $dashboard_widgets = new DashboardWidgets();
    $dashboard_widgets->byID($data['id']);
    $frm = new DbForm();
    return $frm->build('dashboard_widgets_delete_form', $dashboard_widgets, $_SESSION['log_access_level']);
}

/*
 * Remove from table
 */
function dashboard_widgets_delete_form_submit($data) {

	$dash = new DashboardWidgets();
  $delete = $dash->delete($data['id']);
  if ($delete['code']==200) {
    return $data['id'];
  } else {
    return FALSE;
  }

		/*$dashboard_widgets = new DashboardWidgets();
    $dashboard_widgets->remove('id=' . $data['id']);
    if ($dashboard_widgets->affected > 0) {
        //return "ERROR||Could not remove!";
        $dashboard_widgets->remove('id=' . $data['id']);
        natural_set_message('Dashboard Widget has been removed successfully!', 'success');
        return $data['id'];
    } else {
        natural_set_message('Problems loading user ' . $user_id, 'error');
        return FALSE;
    }*/
}

/*
 * Validate data
 */
function dashboard_widgets_validate($data) {
  $dashboard_widgets = new DashboardWidgets();
  if (strpos($data['fn'], "edit")) {
    $type = "edit";
  }
  if (strpos($data['fn'], "delete")) {
    $type = "delete";
  }
  if (strpos($data['fn'], "create")) {
    $type = "create";
  }
  return $dashboard_widgets->_validate($data, $type, false);
}


function dashboard_widgets_load_droplets_wrapper(){
	global $twig;
	// Twig Base
	$template = $twig->loadTemplate('content.html');
	$template->display(array(
		// Dashboard - Passing default variables to content.html
		'page_title' => 'Dashboard',
		'page_subtitle' => 'Widgets',
    'content' => dashboard_widgets_load_droplets() //Loading dashboard widgets
	));
}

function dashboard_widgets_load_droplets(){
	// Dashboard Configuration according logged user personal preferences
	global $twig;

	$camp_button = '';

	$content = $twig->render('dashboard-content.html',
		array(
			'setup_form' => dashboard_setup_form(),
			'camp_button' => $camp_button,
			'widgets' => dashboard_widgets($_SESSION['dash_type'])
		)
	);
	return $content;
}

function dashboard_content(){
  //dashboard is the first function to render the dashboard
	global $twig;
	$content = $twig->render('dashboard-content.html',
		array(
			'setup_form' => dashboard_setup_form(),
			'widgets' => dashboard_widgets($_SESSION['dash_type'])
		)
	);
	return $content;
}

function dashboard_setup($data) {
  print_debug($data);
  exit;
    $user = new User();
    $user->byID($_SESSION['log_id']);
    $dash_type = 'dashboard_' . $data['dashboard_type'];
    $user_widgets = $user->$dash_type;
    $nlist = array();
    $new_list = array();
    if ($user_widgets && $data['widget']) {
        // Remove widgets that were not selected now
        foreach ($data['widget'] as $widget) {
            $wgt[] = $widget;
        }
        for ($i = 0; $i < count($user_widgets); $i++) {
            for ($x = 0; $x < count($user_widgets[$i]); $x++) {
                if (in_array($user_widgets[$i][$x], $wgt)) {
                    $nlist[$i][] = $user_widgets[$i][$x];
                } else {
                    $nlist[$i][] = null;
                }
            }
        }
        foreach ($wgt as $v) {
            if (in_array($v, $nlist[0]) || in_array($v, $nlist[1])) {
                //skipp setting this widget to the array cause it already exists
            } else {
                if (!$nlist[0][0]) {
                    $nlist[0][0] = $v;
                } else {
                    if (!$nlist[1][0]) {
                        $nlist[1][0] = $v;
                    } else {
                        /*if (!$nlist[2][0]) {
                            $nlist[2][0] = $v;
                        } else {
                            $nlist[0][] = $v;
                        }*/
                        $nlist[0][] = $v;
                    }
                }
            }
        }
        $new_list[0] = array();
        $new_list[1] = array();
        //$new_list[2] = array();
        for ($i = 0; $i < 2; $i++) {
            $ct = 0;
            for ($x = 0; $x < count($nlist[$i]); $x++) {
                if ($nlist[$i][$x] != null) {
                    $new_list[$i][$ct] = $nlist[$i][$x];
                    $ct++;
                }
            }
        }
    } else {
        foreach ($data['widget'] as $key => $value) {
            if ($value) {
                $new_list[0][0] = $value;
            }
        }
    }
    //array_unshift($new_list, $widget);
    $user->$dash_type = $new_list;
    $user->update($_SESSION['log_id']);
    return dashboard_widgets();
}


function dashboard_widgets($data) {
	  $dash = array();
    $user = new User();
    $user->byID($_SESSION['log_id']);
		global $twig;
    if ($user->dashboard) {
        // Build the dashboard accordingly the dashboard type and if there is something recorded in his desktop
        $user_widgets = $user->dashboard;
        //print_debug($user->dashboard);
        //exit;
				if ($user_widgets) {
						$db = DataConnection::readOnly();
						$widgets = $db->dashboard_widgets();
            foreach ($user_widgets as $user){
              foreach($widgets as $widget){
                   if($user['id'] == $widget['id'] && $widget['enabled'] == 1){
                     $dash[0] .= $twig->render('dashboard-widget.html',
                                                 array(
                                                     'icon' => $widget['icon'],
                                                     'widget_id' => $widget['id'],
                                                     'widget_title' => $widget['title'],
                                                     'widget_function' => $widget['widget_function'],
                                                     'x'       => $user['x'],
                                                     'y'       => $user['y'],
                                                     'width'   => $user['width'],
                                                     'height'  => $user['height']
                                                 ));
                   }
              }
            }
        }
    } else {
        // Return the message to configure his/her dashboard
        //  $content = 'Maybe you are new here, don\'t forget to Setup your Dashboard<br/>Click on the link on the right link "Dashboard Setup" and choose which items you want to see on your dashboard.';
    }
    return $dash[0];
}

/**
 * Function for the user to Setup the Dashboard
 */
function dashboard_setup_form() {
    // Get the Dashboard Type
    $dashboard_type = $_SESSION['dash_type'];

		$db = DataConnection::readOnly();
		$widgets = $db->dashboard_widgets()
									->where('enabled',1);

    if (count($widgets) > 0) {
        // Retrieve the widgets already selected by the user
        $user = new User();
        $user->byID($_SESSION['log_id']);
        if ($user->dashboard) {
            $user_widgets = $user->dashboard;
        }
        $checked = '';
        foreach ($widgets as $widget){
          foreach($user_widgets as $user){
              if($user['id'] == $widget['id']){
                 $checked = 'checked="checked"';
                 break;
              }else{
                $checked = '';
              }
          }
          $inputs[$widget['id']]['id']   = $widget['id'];
          $inputs[$widget['id']]['title']= $widget['title'];
          $inputs[$widget['id']]['check']= $checked;
          $inputs[$widget['id']]['fn']   = $widget['widget_function'];
        }
    }
    if ($inputs) {
        global $twig;
        $form = $twig->render(
            'dashboard-setup.html',
            array(
              'title'   => 'Dashboard Setup',
              'inputs'  => $inputs
            )
        );
    }
    return $form;
}

/**
 * Update Dashboard List
 */
function dashboard_update_list($data) {
  if ($data['positions']) {
    $po = explode("-",$data['positions']);
    for($i=0; $i<count($po); $i++){
      $pos = '';
      $pos = str_replace('widget_', '', $po[$i]);
      $positions[$i] = explode(',', $pos);
    }
  }
  $user = new User();
	$user->byID($_SESSION['log_id']);
  $dashboard_type = 'dashboard_' . $data['dashboard_type'];
  $user->$dashboard_type = $positions;
  $user->update($_SESSION['log_id']);
}

function dashboard_user_update($data) {
    //print_debug(json_decode(file_get_contents("php://input")));
    $user = new User();
    $user->byID($_SESSION['log_id']);
    $user->dashboard = file_get_contents("php://input");
    $user->update($_SESSION['log_id']);
}

?>
