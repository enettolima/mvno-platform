<?php
/**
* NATURAL - Copyright Open Source Mind, LLC
* Last Modified: Date: 05-06-2014 17:23:02 -0500  $ @ Revision: $Rev: 11 $
* @package Natural Framework
*/

/**
* Database form management
*/

class DbForm {

  function _getSessionVar($data) {
    $data = " " . $data;
    $ini = strpos($data, "s{");
    if ($ini == 0)
      return "";
    $ini += strlen("s{");
    $len = strpos($data, "}", $ini) - $ini;
    return substr($data, $ini, $len);
  }

  function _getVar($data) {
    $data = " " . $data;
    $ini = strpos($data, "v{");
    if ($ini == 0)
      return "";
    $ini += strlen("v{");
    $len = strpos($data, "}", $ini) - $ini;
    return substr($data, $ini, $len);
  }

  function _getFieldOptions($field) {
    $options = array();
    
    if ($field['data_table'] != '') {
      $query_field_name = '';
      while (strpos($field['data_query'], "s{") > 0) {
        $query_field_name = $this->_getSessionVar($field['data_query']);
        $field['data_query'] = str_replace("s{{$query_field_name}}", "{$_SESSION[$query_field_name]}", $field['data_query']);
      }
      
      $query_select = ($field['data_value'] == $field['data_label']) ? $field['data_value'] : "{$field['data_value']},{$field['data_label']}";
      
      $db = DataConnection::readOnly();
		  $field_options = $db->{$field['data_table']}()
      ->select($query_select)
      ->where($field['data_query'])
      ->order($field['data_sort']);
      $data_value = explode(',', $field['data_value']);
      $data_label = explode(',', $field['data_label']);
      
      for ($dvf = 0; $dvf < count($data_value); $dvf++) {
        $value = $data_value[$dvf];
        $label = $data_label[$dvf];
        //echo "value is ".$value." -- label is ".$label."<br>";
        
        foreach ($field_options as $field_option) {
        //for ($y = 0; $y < count($field_options); $y++) {
					//$data = array_map('iterator_to_array', iterator_to_array($field_option));
          //$data = $field_options;
          
          //if ($data[$y][$value] == $prev_value && $data[$y][$label] == $prev_label)
            //continue;
          switch($field['html_type']) {
            case 'list':
              $status = ($field_option[$data_value[$dvf]] == $field['def_val'] ? 'selected' : '');
              break;
            case 'checkbox':
            case 'radio':
              // For Multiple Checkbox Values
              if (is_array($field['def_val'])) {
                $status = (in_array($field_option[$data_value[$dvf]], $field['def_val']) ? 'checked' : '');
              }
              else {
                $status = ($field_option[$data_value[$dvf]] == $field['def_val'] ? 'checked' : '');
              }
              break;
          }
          $options[] = array('value' => $field_option[$value], 'label' => $field_option[$label], 'status' => $status);
          $prev_value = $field_option[$value];
          $prev_label = $field_option[$label];
        }
      }
      //print_debug($options);
      
      /*for ($dvf = 0; $dvf < count($data_value); $dvf++) {
        $value = $data_value[$dvf];
        $label = $data_label[$dvf];
        //echo "value is ".$value." -- label is ".$label;
        
        //foreach ($field_options as $field_option) {
        for ($y = 0; $y < count($field_options); $y++) {
					$data = array_map('iterator_to_array', iterator_to_array($field_options));
          //$data = $field_options;
          
          
          //if ($data[$y][$value] == $prev_value && $data[$y][$label] == $prev_label)
            //continue;
          switch($field['html_type']) {
            case 'list':
              $status = ($data[$y][$data_value[$dvf]] == $field['def_val'] ? 'selected' : '');
              break;
            case 'checkbox':
            case 'radio':
              // For Multiple Checkbox Values
              if (is_array($field['def_val'])) {
                $status = (in_array($data[$y][$data_value[$dvf]], $field['def_val']) ? 'checked' : '');
              }
              else {
                $status = ($data[$y][$data_value[$dvf]] == $field['def_val'] ? 'checked' : '');
              }
              break;
          }
          $options[] = array('value' => $data[$y][$value], 'label' => $data[$y][$label], 'status' => $status);
          $prev_value = $data[$y][$value];
          $prev_label = $data[$y][$label];
        }
      }*/
      

      if ($field['field_values']) {
        $opt = explode(';', $field['field_values']);
        for ($i = 0; $i < count($opt); $i++) {
          if ($opt[$i]) {
            $values_pair = explode('=', $opt[$i]);
            switch($field['html_type']) {
              case 'list':
                $status = ($values_pair[1] == $field['def_val'] ? 'selected' : '');
                break;
              case 'checkbox':
              case 'radio':
                if (is_array($field['def_val'])) {
                  $status = (in_array($values_pair[1], $field['def_val']) ? 'checked' : '');
                }
                else {
                  $status = ($values_pair[1] == $field['def_val'] ? 'checked' : '');
                }
                break;
            }
            $options[] = array('value' => $values_pair[1], 'label' => $values_pair[0], 'status' => $status);
          }
        }
      }
    }
    else {
      $opt = explode(';', $field['field_values']);
      for ($i = 0; $i < count($opt); $i++) {
        $values_pair = explode("=", $opt[$i]);
        switch($field['html_type']) {
          case 'list':
            $status = ($values_pair[1] == $field['def_val'] ? 'selected' : '');
            break;
          case 'checkbox':
          case 'radio':
            if (is_array($field['def_val'])) {
              $status = (in_array($values_pair[1], $field['def_val']) ? 'checked' : '');
            }
            else {
              $status = ($values_pair[1] == $field['def_val'] ? 'checked' : '');
            }
            break;
        }
        $options[] = array('value' => $values_pair[1], 'label' => $values_pair[0], 'status' => $status);
      }
    }
    return $options;
  }

  /**
   * Form builder
   */
  function build($form_name, $val = NULL, $level = NULL, $modal = TRUE, $display = TRUE, $show_cancel_button = TRUE) {

    global $twig;

		$db = DataConnection::readOnly();

		$form_param = $db->{FORM_TABLE}()
    ->where('form_id', $form_name)
    ->limit(1)
    ->fetch();
    
		$form_fields = $db->{FIELD_TABLE}()
    ->where('form_template_id', $form_param['id'])
    ->order('form_field_order asc');

    $fields = array();
    $hidden_fields = array();

    $level = ($level == NULL && isset($_SESSION['log_access_level'])) ? $_SESSION['log_access_level'] : $level;

    if (count($form_param) <= 0) {
      $error_message = 'Parameters for the form ' . $form_name . ' not found!';
    }

    if (count($form_fields) <= 0) {
      $error_message = 'Form ' . $form_name . ' not found!';
    }

    // Overriding values from action
    if ($val->action) {
      $form_action = $val->action;
    }
    else {
      $form_action = str_replace("\'", "'", $form_param['form_action']);
    }

    // Start looping through fields.
    foreach($form_fields as $form_field) {

      // Verifies the form field Level and applies the ACL if needed to make this work you must set the level field in the field_template
      // to the minimum level that has access to the raw field withou the ACL being applied. for example if you set the Level to 41 and ACL to readonly
      // anyone with level 41 and below will not be able to edit the field only people with level 42 and above.
			if ($level <= $form_field['level']) {
        if ($form_field['acl'] == 'readonly') {
          $form_field['css_class'] .= 'form-readonly';
        }else {
          $form_field['html_type'] = 'hidden';
          $form_field['def_val'] = is_array($form_field['def_val']) ? implode(', ', $form_field['def_val']) : $form_field['def_val'];
        }
      }

      // Field ID
      $form_field['field_id'] = trim($form_field['field_id']);

      if (is_object($val)) {
        $fdef_val       = trim($form_field['def_val']);
        $f_values       = trim($form_field['field_values']);
        $prefix_values  = trim($form_field['prefix']);
        $suffix_values  = trim($form_field['suffix']);
        $html_options   = trim($form_field['html_options']);

        if (property_exists($val, $fdef_val)) {
          $form_field['def_val'] = $val->$fdef_val;
        }
        if (isset($val->$f_values)) {
          $form_field['field_values'] = $val->$f_values;
        }
        if (isset($val->{$prefix_values})) {
          $form_field['prefix'] = $val->$prefix_values;
        }
        if (isset($val->{$suffix_values})) {
          $form_field['suffix'] = $val->$suffix_values;
        }
        if (property_exists($val, $html_options)) {
          $form_field['html_options'] = $val->$html_options;
        }
      }


      // Preprocess some fields before sendint to the template engine.
      switch ($form_field['html_type']) {
        case 'hidden':
          // We need to put the hidden fields after all other fields.
          $hidden_fields[$form_field['id']] = $form_field;
          unset($form_field);
          // Get out of the loop
          continue;
        case 'checkbox':
        case 'radio':
				case 'list':
          $options = $this->_getFieldOptions($form_field);
          $form_field['options'] = $options;
          break;
        case 'readonly':
          if ($form_field['data_table'] != '') {
            $data_label = $form_field['data_label'];
						$dm = DataConnection::readOnly();
						$label_val = $dm->{$form_field['data_table']}()
															->select($form_field['data_label'])
															->where($form_field['data_value'], $form_field['def_val'])
															->and($form_field['data_query'])
															->order($form_field['data_sort'])
															->limit(1)
															->fetch();
            if (!$label_val) {
            	$form_field['def_val'] = "-";
						}else{
							$form_field['def_val'] = $label_val[$form_field['data_label']];
						}

						//$dm = new DataManager;
            //$query = "SELECT {$form_field['data_label']} FROM {$form_field['data_table']} WHERE {$form_field['data_value']} = '{$form_field['def_val']}' AND {$form_field['data_query']}  ORDER BY {$form_field['data_sort']} LIMIT 1";
            //$dm->dmCustomQuery($query, true);
          }
          break;
        case 'uploader':
          $form_field['file_items'] = '';
					if (!empty($form_field['def_val']) && is_array($form_field['def_val'])) {

						$files = $db->files()
												->where('id IN ( ? )', implode(',', $form_field['def_val']))
												->order('id');

            if (count($files) > 0) {
              foreach ($files as $file) {
                $render = array(
                  'filename'=> $file['filename'],
                  'preview' => (strpos($form_field['field_values'], 'preview=true') !== false) ? TRUE : FALSE,
                  'preview_uri' => $file['uri'],
                  'id' => $file['id'],
                  'field_id' => $form_field['id'],
                  'field_name'=> $form_field['field_name'],
                );
                // File item
                $form_field['file_items'] .= $twig->render('uploader-file-item.html', $render);
              }
              // Field  attributes
              $field_limit = 0;
              if (!empty($form_field['field_values'])) {
                $field_values = explode('|', $form_field['field_values']);
                foreach ($field_values as $value) {
                  $option = explode('=', $value);
                  switch ($option[0]) {
                    case 'limit':
                      $field_limit = $option[1];
                      break;
                  }
                }
              }
              if ($field_limit >= $files->affected) {
                $form_field['css_class'] .= 'hide';
              }
            }
          }
          break;
        case 'submit':
          $submit_text = $form_field['def_val'];
          break;
      }
      // Fieldset
      if (!empty($form_field['fieldset_name'])) {
        $fieldsets[$form_field['fieldset_name']]['fields'][] = $form_field;
      }
    }
    
    // Get Fieldset information
    $fieldset_clause = array();
    if (!empty($fieldsets)) {
      foreach ($fieldsets as $key => $fieldset) {
        $fieldset_clause[] = "'" . $key . "'";
      }
      $pdo = new PDO(NATURAL_PDO_DSN_READ, NATURAL_PDO_USER_READ, NATURAL_PDO_PASS_READ);
			$q = $pdo->prepare("SELECT * FROM ".FIELDSET_TABLE." WHERE name IN(".implode(', ', $fieldset_clause).")");
			$q->execute();
			$fsets = $q->fetchAll();
      
      foreach($fsets as $fs) {
        $fieldsets[$fs['name']]['id'] = $fs['id'];
        $fieldsets[$fs['name']]['name'] = $fs['name'];
        $fieldsets[$fs['name']]['label'] = $fs['label'];
        $fieldsets[$fs['name']]['css_class'] = $fs['css_class'];
      }
      $fieldsets['blank']['fields'] = $hidden_fields;
    }else{
      $fieldsets['blank']['fields'] = $form_fields;
    }
		// Render Array
    $render = array(
      'page_title' => !empty($form_param['form_title']) ? $form_param['form_title'] : '',
      'form' => $form_param,
      'fieldsets' => $fieldsets, // This includes non fieldsets fields into the blank array
      'submit_text' => !empty($submit_text) ? $submit_text : 'Save',
			'show_cancel_button' => $show_cancel_button,
      'modal' => $modal,
    );
    
    if($display){
      $template = $twig->loadTemplate('form.html');
      $template->display($render);
    }else{
      $template = $twig->render('form.html',$render);
      return $template;
    }
  }
  
  /**
  * Method to create a new form
  *
  * Add a new form
  *
  * @url POST create
  * @smart-auto-routing false
  * 
  * @access public
  */
  function create($request_data) {
    //Validating data from the API call
    $this->_validate($request_data, "insert");
    $book = new DbForm();
    $db = DataConnection::readWrite();
    //$u = $db->user();
    $data = array();
    unset($request_data['fn']);
    unset($request_data['id']);
    foreach ($request_data as $key => $value) {
      $book->$key = $value;
      $data[$key] = $value;
    }
    //$book->insert();
    $result = $db->form_templates()->insert($data);
    if ($result) {
      //Preparing response
      $response = array();
      $response['code'] = 201;
      $response['message'] = 'Form has been created!';
      $response['id'] = $result['id'];
      return $response;
    } else {
      throw new Luracast\Restler\RestException(500, 'Form could not be created!');
    }
  }

  /**
  * Method to fecth Form Record by ID
  *
  * Fech a record for a specific form
  * by ID
  *
  * @url GET byID/{id}
  * @smart-auto-routing false
  * 
  * @access public
  * @throws 404 User not found for requested id  
  * @param int $id Form to be fetched
  * @return mixed 
  */
  function byID($id) {
    //If id is null
    if (is_null($id)) {
      $error_message = 'Parameter id is missing or invalid!';
      natural_set_message($error_message, 'error');
      throw new Luracast\Restler\RestException(400, $error_message);
    }
    //Get object by id
    //$this->loadSingle("id='{$id}'");
    $db = DataConnection::readOnly();
    $q = $db->form_templates[$id];
    //If object not found throw an error
    if(count($q) > 0) {
      $result['code'] = 200;
      foreach($q as $key => $value){
        $result[$key] = $value;
        $this->$key = $value;
      }
      $this->affected 		 = 1;
      return $result;
    }else{
      $error_message = 'Form not found!';
      natural_set_message($error_message, 'error');
      throw new Luracast\Restler\RestException(404, $error_message);
    }
  }

  /**
  * Method to fecth All Forms
  *
  * Fech all records from the database
  *
  * @url GET fetchAll
  * @smart-auto-routing false
  * 
  * @access public
  * @throws 404 Book not found
  * @return mixed 
  */
  function fetchAll() {
    $db = DataConnection::readOnly();
    $q = $db->form_templates();
    if(count($q) > 0) {
      foreach($q as $id => $q){
        if(count($columns)<1){
          $columns = $db->book[$q['id']];
        }
        //setting response for api calls
        foreach($columns as $k => $v){
          $res[$id][$k] = $q[$k];
        }
      }
      return $res;
    }else{
      throw new Luracast\Restler\RestException(404, 'Form not found');
    }
  }

  /**
  * Method to Update book information
  *
  * Update book on database
  *
  * @url PUT update
  * @smart-auto-routing false
  * 
  * @access public
  * @return mixed 
  */
  function update($request_data) {
    $this->_validate($request_data, "update");
    $response = array();
    $db = DataConnection::readWrite();
    $id = $request_data['id'];
    $q  = $db->form_templates[$id];
    unset($request_data['fn']);
    foreach ($request_data as $key => $value) {
      $this->$key = $value;
    }
    
    if($q){
      if($q->update($request_data)){
        $response['code'] = 200;
        $response['message'] = 'Form has been updated!';
        natural_set_message($response['message'], 'success');
      }else{
        //Could not update record! maybe the data is the same.
        $response['code'] = 500;
        $response['message'] = 'Could not update Form at this time!';
        natural_set_message($response['message'], 'error');
        throw new Luracast\Restler\RestException($response['code'], $response['message']);
      }
      return $response;
    }else{
      natural_set_message('Form not found', 'error');
      throw new Luracast\Restler\RestException(404, 'Form not found');
    }
  }

  /**
  * Method to delete a form
  *
  * Delete form from database
  *
  * @url DELETE delete
  * @smart-auto-routing false
  *
  * @access public
  * @throws 404 Book not found
  * @return mixed 
  */
  function delete($id) {
    $data['id'] = $id;
    $this->_validate($data, "delete");
    $db = DataConnection::readWrite();
    $q = $db->form_templates[$id];
    
    $response = array();
    if($q && $q->delete()){
      $response['code'] = 200;
      $response['message'] = 'Form has been removed!';
      natural_set_message($response['message'], 'success');
      return $response;
    }else{
      $response['code'] = 404;
      $response['message'] = 'Form not found!';
      natural_set_message($response['message'], 'error');
      throw new Luracast\Restler\RestException($response['code'], $response['message']);
      return $response;
    }
  }
  
  /**
  * @smart-auto-routing false
  * @access private
  */
  function _validate($data, $type, $from_api = true) {
    //If the method called is an update, check if the id exists, otherwise return error
    $error_code = 400;
    if ($type == "update" || $type == "delete") {
      if (!$data['id']) {
        $error[] = 'Parameter ID is required!';
      }
    }
    /*
     * check if field is empty
     * Add more fields as needed
     */
    
    if ($type != "delete") {
      if (!$data['form_name']) {
        $error[] = 'Form name is required!';
      }
      if (!$data['form_id']) {
        $error[] = 'Form ID is required!';
      }
    }

    //If error exists return or throw exception if the call has been made from the API
    if (!empty($error)) {
      natural_set_message($error[0], 'error');
      if ($from_api) {
        throw new Luracast\Restler\RestException($error_code, $error[0]);
      }
      return $error;
      exit(0);
    }
  }
}

?>
