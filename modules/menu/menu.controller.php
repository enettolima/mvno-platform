<?php

/**
 * List items
 */
function menu_list($row_id = NULL, $search = NULL, $sort = NULL, $page = 1) {
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
      $search_fields = array('id', 'label', 'func', 'module');
      $exceptions = array();
      $search_query = build_search_query($search, $search_fields, $exceptions);
      
      $menus = $db->menu()
      ->where($row_id)
      ->and($search_query)
      ->order($sort)
      ->limit($limit, $offset);
    } else {
      $menus = $db->menu()
      ->where($row_id)
      ->order($sort)
      ->limit($limit, $offset);
    }
		
		$total_records = $db->menu()->count("*");
		$i = 0;
    if (count($menus)) {
			// Building the header with sorter
			$headers[] = array('display' => 'Id', 'field' => 'id');
			$headers[] = array('display' => 'Label', 'field' => 'label');
			$headers[] = array('display' => 'Function', 'field' => 'func');
			$headers[] = array('display' => 'Module', 'field' => 'module');
			$headers[] = array('display' => 'Edit', 'field' => NULL);
			$headers[] = array('display' => 'Delete', 'field' => NULL);
			$headers = build_sort_header('menu_list', 'menu', $headers, $sort);
			
			foreach( $menus as $menu ){
				$j = $i + 1;
				//This is important for the row update/delete
				$rows[$j]['row_id'] = $menu['id'];
				/////////////////////////////////////////////
				$rows[$j]['id']     = $menu['id'];
				$rows[$j]['label']  = $menu['label'];
				$rows[$j]['func']   = $menu['func'];
				$rows[$j]['module'] = $menu['module'];
				
				if($menu['system']==1){
						$disabled = 'disabled';
				}else{
						$disabled = '';
				}
				$rows[$j]['edit']   = theme_link_process_information('', 'menu_edit_form',
					'menu_edit_form',
					'menu',
					array('extra_value' => 'id|' . $menu['id'],
						'response_type' => 'modal',
						'icon' => NATURAL_EDIT_ICON,
						'class' => $disabled));
				$rows[$j]['delete'] = theme_link_process_information('',
					'menu_delete_form',
					'menu_delete_form',
					'menu',
					array('extra_value' => 'id|' . $menu['id'],
						'response_type' => 'modal',
						'icon' => NATURAL_REMOVE_ICON,
						'class' => $disabled));
        $i++;
			}
    }
    
    $options = array(
        'show_headers' => TRUE,
        'page_title' => translate('Users List'),
        'page_subtitle' => translate('Manage Menus'),
        'empty_message' => translate('No menu found!'),
        'table_prefix' => theme_link_process_information(translate('Create New Menu'),
					'menu_create_form',
					'menu_create_form',
					'menu',
					array('response_type' => 'modal')),
        'pager_items' => build_pager('menu_list', 'menu', $total_records, $limit, $page),
        'page' => $page,
        'sort' => $sort,
        'search' => $search,
        'show_search' => TRUE,
        'function' => 'menu_list',
        'module' => 'menu',
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
function menu_create_form() {
    $frm = new DbForm();
    $menu = new Menu();
    $menus = $menu->fetchAll();
		if(count($menus)>0){
      $items = array();
      $items[] = 'Main Menu=0';
      foreach ($menus as $pid_opt) {
        $items[] = ucwords($pid_opt['label']) . '=' . $pid_opt['id'];
      }
      $frm->pid_options = implode(';', $items);
      return $frm->build("menu_create_form", $frm);  
    }else{
      natural_set_message('Could load Menu at this time', 'error');
    }
}

/*
 * Insert on table
 */
function menu_create_form_submit($data) {
	$error = menu_validate($data);
	
	//print_debug($error);
	if(count($error)>0){
		return FALSE;
	}
	
	$menu = new Menu();
	foreach ($data as $field => $value) {
		$submit[$field] = $value;
	}
	$response = $menu->create($submit);
	if ( $response['id'] > 0 ) {
		return menu_list($response['id']);
	} else {
		return false;
	}
		
		/*
    $menu = new Menu();
    foreach ($data as $field => $value) {
        if ($field != 'affected' && $field != 'errorcode' && $field != 'data' && $field != 'dbid' && $field != 'id' && $field != 'fn') {
            $menu->$field = $value;
        }
    }
    $menu->insert();
    if ($menu->affected > 0 ) {
        natural_set_message('Menu has been created!', 'success');
        return menu_list($menu->id);
    } else {
        natural_set_message('Could not save this Menu at this time', 'error');
        return false;
    }*/
}

/*
 * show edit form
 */
function menu_edit_form($data) {
	$menu = new Menu();
	$menu->byID($data['id']);
	$mn = new Menu();
	$menus = $mn->fetchAll();
	if(count($menus)>0){
		$items = array();
		$items[] = 'Main Menu=0';
		foreach ($menus as $pid_opt) {
			$items[] = ucwords($pid_opt['label']) . '=' . $pid_opt['id'];
		}
		$menu->pid_options = implode(';', $items);
		$frm = new DbForm();
		$frm->build('menu_edit_form', $menu, $_SESSION['log_access_level']);
	}else{
		natural_set_message('Could load Menu at this time', 'error');
	}
}

/*
 * Update table
 */
function menu_edit_form_submit($data) {
	$error = menu_validate($data);
	if (!empty($error)) {
		return FALSE;
	} 
	$menu = new Menu();
	foreach ($data as $field => $value) {
		if ($field != 'fn') {
			$menu->$field = $value;
			$submit[$field] = $value;
		}
	}
	$update = $menu->update($submit);
	if ($update['code']==200) {
		return menu_list($data['id']);
	}
}

/*
 * show edit form
 */
function menu_delete_form($data) {
    $menu = new Menu();
    $menu->byID($data['id']);
		if($menu->affected>0){
			$frm = new DbForm();
			$frm->build('menu_delete_form', $menu, $_SESSION['log_access_level']);
    }else{
			natural_set_message('Problems loading menu ' . $data['id'], 'error');
			return FALSE;   
    }
}

/*
 * Remove from table
 */
function menu_delete_form_submit($data) {
	$menu = new Menu();
	$delete = $menu->delete($data['id']);
	if ($delete['code']==200) {
		natural_set_message('Menu has been removed successfully!', 'success');
		return $data['id'];
	} else {
		natural_set_message('Problems loading menu ' . $data['id'], 'error');
		return FALSE;
	}
}

/*
 * Validate data
 */
function menu_validate($data) {
	$menu = new Menu();
	if (strpos($data['fn'], "edit")) {
		$type = "edit";
	}
	if (strpos($data['fn'], "delete")) {
		$type = "delete";
	}
	if (strpos($data['fn'], "create")) {
		$type = "create";
	}
	return $menu->_validate($data, $type, false);
}

?>