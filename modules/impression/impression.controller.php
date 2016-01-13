<?php
/**
 * List items
 */
function impression_list($row_id = NULL, $search = NULL, $sort = NULL, $page = 1) {
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
    $search_fields = array('id', 'mdn', 'id_ad');
    $exceptions = array();
    $search_query = build_search_query($search, $search_fields, $exceptions);
    
    $impressions = $db->impression()
    ->where($row_id)
    ->and($search_query)
    ->order($sort)
    ->limit($limit, $offset);
  } else {
    $impressions = $db->impression()
    ->where($row_id)
    ->order($sort)
    ->limit($limit, $offset);
  }
  $total_records = $db->impression()->count("*");
  
  $i = 0;
  if (count($impressions)) {
    // Building the header with sorter
    $headers[] = array('display' => 'Id', 'field' => 'id');
    $headers[] = array('display' => 'Mdn', 'field' => 'mdn');
    $headers[] = array('display' => 'Id Ad', 'field' => 'id_ad');
    $headers[] = array('display' => 'Edit', 'field' => NULL);
    $headers[] = array('display' => 'Delete', 'field' => NULL);
    $headers = build_sort_header('impression_list', 'impression', $headers, $sort);

    foreach( $impressions as $impression ){
      $j = $i + 1;
      //This is important for the row update/delete
      $rows[$j]['row_id']   = $impression['id'];
      /////////////////////////////////////////////
      $rows[$j]['id']       = $impression['id'];
      $rows[$j]['mdn']   = $impression['mdn'];
      $rows[$j]['id_ad'] = $impression['id_ad'];
      $rows[$j]['edit']   = theme_link_process_information('',
          'impression_edit_form',
          'impression_edit_form',
          'impression',
          array('extra_value' => 'id|' . $impression['id'],
              'response_type' => 'modal',
              'icon' => NATURAL_EDIT_ICON));
      $rows[$j]['delete'] = theme_link_process_information('',
          'impression_delete_form',
          'impression_delete_form',
          'impression', array('extra_value' => 'id|' . $impression['id'],
              'response_type' => 'modal',
              'icon' => NATURAL_REMOVE_ICON));
      $i++;
    }
  }
  
  $options = array(
    'show_headers' => TRUE,
    'page_title' => translate('Impressions List'),
    'page_subtitle' => translate('Manage Impressions'),
    'empty_message' => translate('No impression found!'),
    'table_prefix' => theme_link_process_information(translate('Create New Impression'),
      'impression_create_form',
      'impression_create_form',
      'impression',
      array('response_type' => 'modal')),
    'pager_items' => build_pager('impression_list', 'impression', $total_records, $limit, $page),
    'page' => $page,
    'sort' => $sort,
    'search' => $search,
    'show_search' => TRUE,
    'function' => 'impression_list',
    'module' => 'impression',
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
function impression_create_form() {
    $frm = new DbForm();
    return $frm->build("impression_create_form");
}

/*
 * Insert on table
 */
function impression_create_form_submit($data) {
  $error    = impression_validate($data);
  if (!empty($error)) {
    return FALSE;
  }
  $impression = new Impression();
  $response = $impression->create($data);
  if ( $response['id'] > 0 ) {
    return impression_list($response['id']);
  } else {
    return false;
  }
}

/*
 * show edit form
 */
function impression_edit_form($data) {
  $impression = new Impression();
  $impression->byID($data['id']);
  $frm = new DbForm();
  $frm->build('impression_edit_form', $impression, $_SESSION['log_access_level']);
}

/*
 * Update table
 */
function impression_edit_form_submit($data) {
  $error = impression_validate($data);
  if (!empty($error)) {
    return FALSE;
  } else {
    $impression = new Impression();
    $update = $impression->update($data);
    if ($update['code']==200) {
      return impression_list($data['id']);
    }
  }
}

/*
 * show edit form
 */
function impression_delete_form($data) {
  $impression = new Impression();
  $impression->byID($data['id']);
  //$impression->loadSingle('id='.$data['impression_id']);
  if($impression->affected>0){
    $frm = new DbForm();
    $frm->build('impression_delete_form', $impression, $_SESSION['log_access_level']);
  }else{
    return FALSE;   
  }
}

/*
 * Remove from table
 */
function impression_delete_form_submit($data) {
  $impression = new Impression();
  $delete = $impression->delete($data['id']);
  if ($delete['code']==200) {
    return $data['id'];
  } else {
    return FALSE;
  }
}

/*
 * Validate data
 */
function impression_validate($data) {
  $impression = new Impression();
  if (strpos($data['fn'], "edit")) {
    $type = "edit";
  }
  if (strpos($data['fn'], "delete")) {
    $type = "delete";
  }
  if (strpos($data['fn'], "create")) {
    $type = "create";
  }
  return $impression->_validate($data, $type, false);
}

?>
