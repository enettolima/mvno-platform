<?php
/**
 * List items
 */
function subscribers_list($row_id = NULL, $search = NULL, $sort = NULL, $page = 1) {
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
    $search_fields = array('id', 'mvno_id', 'first_name');
    $exceptions = array();
    $search_query = build_search_query($search, $search_fields, $exceptions);
    
    $subscriberss = $db->subscribers()
    ->where($row_id)
    ->and($search_query)
    ->order($sort)
    ->limit($limit, $offset);
  } else {
    $subscriberss = $db->subscribers()
    ->where($row_id)
    ->order($sort)
    ->limit($limit, $offset);
  }
  $total_records = $db->subscribers()->count("*");
  
  $i = 0;
  if (count($subscriberss)) {
    // Building the header with sorter
    $headers[] = array('display' => 'Id', 'field' => 'id');
    $headers[] = array('display' => 'Mvno Id', 'field' => 'mvno_id');
    $headers[] = array('display' => 'First Name', 'field' => 'first_name');
    $headers[] = array('display' => 'Edit', 'field' => NULL);
    $headers[] = array('display' => 'Delete', 'field' => NULL);
    $headers = build_sort_header('subscribers_list', 'subscribers', $headers, $sort);

    foreach( $subscriberss as $subscribers ){
      $j = $i + 1;
      //This is important for the row update/delete
      $rows[$j]['row_id']   = $subscribers['id'];
      /////////////////////////////////////////////
      $rows[$j]['id']       = $subscribers['id'];
      $rows[$j]['mvno_id']   = $subscribers['mvno_id'];
      $rows[$j]['first_name'] = $subscribers['first_name'];
      $rows[$j]['edit']   = theme_link_process_information('',
          'subscribers_edit_form',
          'subscribers_edit_form',
          'subscribers',
          array('extra_value' => 'id|' . $subscribers['id'],
              'response_type' => 'modal',
              'icon' => NATURAL_EDIT_ICON));
      $rows[$j]['delete'] = theme_link_process_information('',
          'subscribers_delete_form',
          'subscribers_delete_form',
          'subscribers', array('extra_value' => 'id|' . $subscribers['id'],
              'response_type' => 'modal',
              'icon' => NATURAL_REMOVE_ICON));
      $i++;
    }
  }
  
  $options = array(
    'show_headers' => TRUE,
    'page_title' => translate('Subscriberss List'),
    'page_subtitle' => translate('Manage Subscriberss'),
    'empty_message' => translate('No subscribers found!'),
    'table_prefix' => theme_link_process_information(translate('Create New Subscribers'),
      'subscribers_create_form',
      'subscribers_create_form',
      'subscribers',
      array('response_type' => 'modal')),
    'pager_items' => build_pager('subscribers_list', 'subscribers', $total_records, $limit, $page),
    'page' => $page,
    'sort' => $sort,
    'search' => $search,
    'show_search' => TRUE,
    'function' => 'subscribers_list',
    'module' => 'subscribers',
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
function subscribers_create_form() {
    $frm = new DbForm();
    return $frm->build("subscribers_create_form");
}

/*
 * Insert on table
 */
function subscribers_create_form_submit($data) {
  $error    = subscribers_validate($data);
  if (!empty($error)) {
    return FALSE;
  }
  $subscribers = new Subscribers();
  $response = $subscribers->create($data);
  if ( $response['id'] > 0 ) {
    return subscribers_list($response['id']);
  } else {
    return false;
  }
}

/*
 * show edit form
 */
function subscribers_edit_form($data) {
  $subscribers = new Subscribers();
  $subscribers->byID($data['id']);
  $frm = new DbForm();
  $frm->build('subscribers_edit_form', $subscribers, $_SESSION['log_access_level']);
}

/*
 * Update table
 */
function subscribers_edit_form_submit($data) {
  $error = subscribers_validate($data);
  if (!empty($error)) {
    return FALSE;
  } else {
    $subscribers = new Subscribers();
    $update = $subscribers->update($data);
    if ($update['code']==200) {
      return subscribers_list($data['id']);
    }
  }
}

/*
 * show edit form
 */
function subscribers_delete_form($data) {
  $subscribers = new Subscribers();
  $subscribers->byID($data['id']);
  //$subscribers->loadSingle('id='.$data['subscribers_id']);
  if($subscribers->affected>0){
    $frm = new DbForm();
    $frm->build('subscribers_delete_form', $subscribers, $_SESSION['log_access_level']);
  }else{
    return FALSE;   
  }
}

/*
 * Remove from table
 */
function subscribers_delete_form_submit($data) {
  $subscribers = new Subscribers();
  $delete = $subscribers->delete($data['id']);
  if ($delete['code']==200) {
    return $data['id'];
  } else {
    return FALSE;
  }
}

/*
 * Validate data
 */
function subscribers_validate($data) {
  $subscribers = new Subscribers();
  if (strpos($data['fn'], "edit")) {
    $type = "edit";
  }
  if (strpos($data['fn'], "delete")) {
    $type = "delete";
  }
  if (strpos($data['fn'], "create")) {
    $type = "create";
  }
  return $subscribers->_validate($data, $type, false);
}

?>
