<?php
/**
 * List items
 */
function mvno_list($row_id = NULL, $search = NULL, $sort = NULL, $page = 1) {
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
    $search_fields = array('id', 'name', 'address');
    $exceptions = array();
    $search_query = build_search_query($search, $search_fields, $exceptions);
    
    $mvnos = $db->mvno()
    ->where($row_id)
    ->and($search_query)
    ->order($sort)
    ->limit($limit, $offset);
  } else {
    $mvnos = $db->mvno()
    ->where($row_id)
    ->order($sort)
    ->limit($limit, $offset);
  }
  $total_records = $db->mvno()->count("*");
  
  $i = 0;
  if (count($mvnos)) {
    // Building the header with sorter
    $headers[] = array('display' => 'Id', 'field' => 'id');
    $headers[] = array('display' => 'Name', 'field' => 'name');
    $headers[] = array('display' => 'Address', 'field' => 'address');
    $headers[] = array('display' => 'Edit', 'field' => NULL);
    $headers[] = array('display' => 'Delete', 'field' => NULL);
    $headers = build_sort_header('mvno_list', 'mvno', $headers, $sort);

    foreach( $mvnos as $mvno ){
      $j = $i + 1;
      //This is important for the row update/delete
      $rows[$j]['row_id']   = $mvno['id'];
      /////////////////////////////////////////////
      $rows[$j]['id']       = $mvno['id'];
      $rows[$j]['name']   = $mvno['name'];
      $rows[$j]['address'] = $mvno['address'];
      $rows[$j]['edit']   = theme_link_process_information('',
          'mvno_edit_form',
          'mvno_edit_form',
          'mvno',
          array('extra_value' => 'id|' . $mvno['id'],
              'response_type' => 'modal',
              'icon' => NATURAL_EDIT_ICON));
      $rows[$j]['delete'] = theme_link_process_information('',
          'mvno_delete_form',
          'mvno_delete_form',
          'mvno', array('extra_value' => 'id|' . $mvno['id'],
              'response_type' => 'modal',
              'icon' => NATURAL_REMOVE_ICON));
      $i++;
    }
  }
  
  $options = array(
    'show_headers' => TRUE,
    'page_title' => translate('Mvnos List'),
    'page_subtitle' => translate('Manage Mvnos'),
    'empty_message' => translate('No mvno found!'),
    'table_prefix' => theme_link_process_information(translate('Create New Mvno'),
      'mvno_create_form',
      'mvno_create_form',
      'mvno',
      array('response_type' => 'modal')),
    'pager_items' => build_pager('mvno_list', 'mvno', $total_records, $limit, $page),
    'page' => $page,
    'sort' => $sort,
    'search' => $search,
    'show_search' => TRUE,
    'function' => 'mvno_list',
    'module' => 'mvno',
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
function mvno_create_form() {
    $frm = new DbForm();
    return $frm->build("mvno_create_form");
}

/*
 * Insert on table
 */
function mvno_create_form_submit($data) {
  $error    = mvno_validate($data);
  if (!empty($error)) {
    return FALSE;
  }
  $mvno = new Mvno();
  $response = $mvno->create($data);
  if ( $response['id'] > 0 ) {
    return mvno_list($response['id']);
  } else {
    return false;
  }
}

/*
 * show edit form
 */
function mvno_edit_form($data) {
  $mvno = new Mvno();
  $mvno->byID($data['id']);
  $frm = new DbForm();
  $frm->build('mvno_edit_form', $mvno, $_SESSION['log_access_level']);
}

/*
 * Update table
 */
function mvno_edit_form_submit($data) {
  $error = mvno_validate($data);
  if (!empty($error)) {
    return FALSE;
  } else {
    $mvno = new Mvno();
    $update = $mvno->update($data);
    if ($update['code']==200) {
      return mvno_list($data['id']);
    }
  }
}

/*
 * show edit form
 */
function mvno_delete_form($data) {
  $mvno = new Mvno();
  $mvno->byID($data['id']);
  //$mvno->loadSingle('id='.$data['mvno_id']);
  if($mvno->affected>0){
    $frm = new DbForm();
    $frm->build('mvno_delete_form', $mvno, $_SESSION['log_access_level']);
  }else{
    return FALSE;   
  }
}

/*
 * Remove from table
 */
function mvno_delete_form_submit($data) {
  $mvno = new Mvno();
  $delete = $mvno->delete($data['id']);
  if ($delete['code']==200) {
    return $data['id'];
  } else {
    return FALSE;
  }
}

/*
 * Validate data
 */
function mvno_validate($data) {
  $mvno = new Mvno();
  if (strpos($data['fn'], "edit")) {
    $type = "edit";
  }
  if (strpos($data['fn'], "delete")) {
    $type = "delete";
  }
  if (strpos($data['fn'], "create")) {
    $type = "create";
  }
  return $mvno->_validate($data, $type, false);
}

?>
