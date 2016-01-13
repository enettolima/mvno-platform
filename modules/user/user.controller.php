<?php
/**
 * User List.
 */
function user_list($row_id = NULL, $search = NULL, $sort = NULL, $page = 1) {
	$view = new ListView();

	// Row Id for update only row
	if (!empty($row_id)) {
		$row_id = 'id = ' . $row_id;
	} else {
		$row_id = 'id != 0';
	}

	// Sort
	if (empty($sort)) {
		$sort = 'first_name ASC';
	}

	$limit = PAGER_LIMIT; // PAGER_LIMIT
	$offset = ($page * $limit) - $limit;
	$db = DataConnection::readOnly();
	$total_records = 0;
	
	// Search
	if (!empty($search)) {
		$search_fields = array('id', 'first_name', 'last_name', 'username');
		$exceptions = array();
		$search_query = build_search_query($search, $search_fields, $exceptions);

		$users = $db->user()
		->where($row_id)
		->and($search_query)
		->order($sort)
		->limit($limit, $offset);
	} else {
		$users = $db->user()
		->where($row_id)
		->order($sort)
		->limit($limit, $offset);
	}

	$total_records = $db->user()->count("*");
	if (count($users) > 0) {
		// Building the header with sorter
		$headers[] = array('display' => 'Id', 'field' => 'id');
		$headers[] = array('display' => 'First Name', 'field' => 'first_name');
		$headers[] = array('display' => 'Last Name', 'field' => 'last_name');
		$headers[] = array('display' => 'Username', 'field' => 'username');
		$headers[] = array('display' => 'Edit', 'field' => NULL);
		$headers[] = array('display' => 'Delete', 'field' => NULL);
		$headers = build_sort_header('user_list', 'user', $headers, $sort);

		$i = 0;
		foreach( $users as $user ){
			$class = "";
			if($user['username'] == "admin"){
				$class = "disabled";
			}
			//This is important for the row update
			$rows[$i]['row_id'] 		= $user['id'];
			$rows[$i]['id'] 				= $user['id'];
			$rows[$i]['first_name']	= $user['first_name'];
			$rows[$i]['last_name'] 	= $user['last_name'];
			$rows[$i]['username'] 	= $user['username'];
			$rows[$i]['edit'] 			= theme_link_process_information('',
				'user_edit_form',
				'user_edit_form',
				'user',
				array('extra_value' => 'user_id|' . $user['id'],
					'response_type' => 'modal',
					'icon' => constant("NATURAL_EDIT_ICON")));
			$rows[$i]['delete'] 		= theme_link_process_information('',
				'user_delete_form',
				'user_delete_form',
				'user',
				array('extra_value' => 'user_id|' . $user['id'],
					'response_type' => 'modal',
					'icon' => constant("NATURAL_REMOVE_ICON"),
					'class' => $class));
			$i++;	
		}
	}

	//count($users)
	$options = array(
		'show_headers' => TRUE,
		'page_title' => translate('Users List'),
		'page_subtitle' => translate('Manage Users'),
		'empty_message' => translate('No user found!'),
		'table_prefix' => theme_link_process_information(translate('Create New User'),
			'user_create_form',
			'user_create_form',
			'user',
			array('response_type' => 'modal')),
		'pager_items' => build_pager('user_list', 'user', $total_records, $limit, $page),
		'page' => $page,
		'sort' => $sort,
		'search' => $search,
		'show_search' => TRUE,
		'function' => 'user_list',
		'module' => 'user',
		'update_row_id' => '',
	  'table_form_id' => '',
		'table_form_process' => '',
	);

	$listview = $view->build($rows, $headers, $options);

  return $listview;
}

/**
 * User Create Form.
 */
function user_create_form() {
	$frm = new DbForm();

  // Select the proper levels
	$db = DataConnection::readOnly();
	$access_levels = $db->acl_levels()
		->select('description, level')
		->where('level <= ? ',  $_SESSION['log_access_level']);

	if (count($access_levels) > 0) {
		$items = array();
		foreach ($access_levels as $access_level) {
			$items[] = ucwords($access_level['description']) . '=' . $access_level['level'];
		}
		$frm->access_level_options = implode(';', $items);
	}
  $frm->build('user_create_form', $frm, $_SESSION['log_access_level']);
}

/**
 * User Create Form Submit.
 */
function user_create_form_submit($data) {
  $user = new User();
	// Validate User Fields
	$error = user_validate_fields($data);
  if (!empty($error)) {
		foreach($error as $msg) {
		  natural_set_message($msg, 'error');
		}
    return FALSE;
  }
	else {
		// Verify Username
		$user->byUsername($data['username']);
    if ($user->affected) {
		  natural_set_message('Username "' . $data['username'] . '" already taken.', 'error');
      return FALSE;
    }

		// Adding values
		if($data['password']){
			$user->password 	= $data['password'];
			$gen_pass = fasle;;
		}else{
			$gen_pass = true;
		}
		
		$res = $user->create(false, $gen_pass, $data);
		if ($res) {
	    natural_set_message('User ' . $data['first_name'] . ' ' . $data['last_name'] . ' was created successfully!', 'success');
	  }
	  return user_list($res->id);
	}
}

/**
 * User Edit Form Builder.
 */
function user_edit_form($user_id) {
  $user = new User();
  $user->byID($user_id);
  if ($user->affected > 0) {
    $frm = new DbForm();
    // Select the properly levels
  	$db = DataConnection::readOnly();
		$access_levels = $db->acl_levels()
			->select('description, level')
			->where('level <= ? ',  $_SESSION['log_access_level']);

		if (count($access_levels) > 0) {
			$items = array();
			foreach ($access_levels as $access_level) {
				$items[] = ucwords($access_level['description']) . '=' . $access_level['level'];
			}
			
			$user->access_level_options = implode(';', $items);
    }
		// Testing chekboxes
		$user->user_race = array('caucasian', 'asian', 'indian');
		// Testing radio buttons
		//$user->user_race = 'asian';
		// Testing uploader - avatar field with fids
		$user->avatar = array($user->file_id);
    $frm->build('user_edit_form', $user, $_SESSION['log_access_level']);
  }
  else {
		natural_set_message('Problems loading user ' . $user_id, 'error');
	  return FALSE;
  }
}

/**
 * User Edit Form Submit.
 */
function user_edit_form_submit($data) {
	$user = new User();
	$user->byID($data['id']);
  // Validate User Fields
	$error = user_validate_fields($data);
  if (!empty($error)) {
		foreach($error as $msg) {
		  natural_set_message($msg, 'error');
		}
    return FALSE;
  }
	else {
		foreach ($user as $field => $value) {
			if($field != 'dashboard_1' && $field != 'dashboard_2' && $field != 'id') {
				$user->$field = $data[$field];
			}
		}
		$user->dashboard_1 = $user->dashboard_1;
		$user->dashboard_2 = $user->dashboard_2;
		$update = $user->update($data['id']);
		if ($update['code']==200) {
		  natural_set_message('User ' . $data['first_name'] . ' ' . $data['last_name'] . ' was updated successfully!', 'success');
			return user_list($data['id']);
		}else{
			natural_set_message($update['message'], 'error');
		}
	}
}

/**
 * User Validate Fields.
 */
function user_validate_fields($fields) {
	$error = array();
	foreach ($fields as $key => $value) {
	  $field_name = ucwords(str_replace('_', ' ', $key));
    switch ($key) {
      case 'first_name':
      case 'last_name':
			case 'username':
        if (trim($value) == '') {
          $error[] = 'Field ' . $field_name . ' is required!';
        }
        break;
      case 'email':
        if (!(filter_var($value, FILTER_VALIDATE_EMAIL))) {
          $error[] = 'Invalid format for ' . $field_name . ', please insert a valid email!';
        }
        break;
    }
	}
	return $error;
}

/**
 * User Delete Form Builder.
 */
function user_delete_form($user_id) {
	$user = new User();
  $user->byID($user_id);
  if ($user->affected > 0) {
    $frm = new DbForm();
		$user->first_last_name = $user->first_name . ' ' . $user->last_name;
    $frm->build('user_delete_form', $user, $_SESSION['log_access_level']);
  }
  else {
		natural_set_message('Problems loading user ' . $user_id, 'error');
	  return FALSE;
  }
}

/**
 * User Delete Form Submit.
 */
function user_delete_form_submit($data) {
  //$user = new User();
  //$user->loadSingle('id = ' . $data['id']);
	$user = new User();
  $user->byID($data['id']);
  if ($user->affected > 0) {
    // Remove user
    $user->delete($data['id']);
    natural_set_message('User ' . $user->first_name . ' ' . $user->last_name . ' was removed successfully!', 'success');
    return $data['id'];
  }
	else {
		natural_set_message('Problems removing user ' . $user->first_name . ' ' . $user->last_name . '!', 'error');
    return FALSE;
  }
}

?>
