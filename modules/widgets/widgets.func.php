<?php
/**
 * Build Multiple Select Container
 */
function multiple_select_builder($form, $field, $selected = null, $read_only = false) {
  if ($selected) {
    $items = multiple_select_items($form, $field, $selected, $read_only);
  }
  else {
    $items = '';
  }
	if($read_only){
		$resp = '<div id="' . $field . '_error" class="error multiple-select-container"></div> <div id="' . $field . '_container" class="multiple-select-container">' . $items . '</div>';
	}else{
		$resp = '<input type="button" class="button multiple-select-button" value="Add" onClick="javascript:proccess_information(\'' . $form . '\', \'multiple_select_update_field\', \'widgets\', null, \'form_field_value|' . $form . '::' . $field . '\', \'' . $field . '_error\', \'' . $field . '_container\', null, null, \''.$form.'\', this);"/> <br/> <div id="' . $field . '_error" class="error multiple-select-container"></div> <div id="' . $field . '_container" class="multiple-select-container">' . $items . '</div>';
	}
  return $resp;
}

/**
 * This Function updates the container with selected items or take off removed items
 */
function multiple_select_update_field($data) {
  $form_field_value = explode('::', $data['form_field_value']);
  $form = $form_field_value[0];
  $field = $form_field_value[1];
  if ($form_field_value[3]) {
    $remove_value = $form_field_value[2] . '::' . $form_field_value[3];
  }
  elseif ($form_field_value[2]) {
    $remove_value = $form_field_value[2];
  }
  else {
    $remove_value = '';
  }
  $selected = $field . '_selected';
  // Remove Item
	if($remove_value){
		$remove_value = build_custom_remove_value($remove_value,$data);
    foreach ($data[$selected] as $key => $value) {
      if ($value == $remove_value) {
        unset($data[$selected][$key]);
      }
    }
  }else{
    // Add items
    if (!$data[$field]) {
      return 'ERROR||You must insert a value.';
    }
		$data[$field] = filter_value($data[$field],$data);
		if ($data[$selected]) {
      if (!in_array($data[$field], $data[$selected])) {
        $data[$selected][] = $data[$field];
      }
    }else{
      $data[$selected][] = $data[$field];
    }
  }
	$field_selected = $data[$selected];
	//treating the String based on the feature
	$data[$selected] = build_custom_string($data['Feature'],$field_selected,$selected,$data);
	$resp = multiple_select_items($form, $field, $data[$selected]);
  return $resp;
}

function build_custom_remove_value($rvalue,$data){
	foreach($data as $k => $v){
		switch($k){
			default:
				$response = $rvalue;
				break;
		}	
	}
	return $response;

}

function filter_value($field,$data){
	foreach($data as $k => $v){
		switch($k){
			default:
		 		$response = preg_replace('"[^A-Za-z0-9 _:/\/;=@<>.,\-]"', '', $field); // Replaces | due the fact that pie is our javascript separator
				break;
		}	
	}
	return $response;
}
/**
 * This functions build the items Selected with a remove item
 */
function multiple_select_items($form, $field, $selected, $read_only = false) {
  $items = '';
  $remove_icon = TEMPLATE.'images/delete-16x16.gif';
  if ($selected) {
    foreach ($selected as $value) {
      // if the value has value | description
      $pos = strpos($value, '::');
      if($pos !== false){
        $value_description = explode('::', $value);
        $description = $value_description[1];
      }else{ // if there is no |, then description = value
        $description = $value;
			} 
      $escape = array("<",">");
      $replace = array("&#60","&#62");
      $pos = strrpos($value, '::');
      if ($pos !== false) {
        if (!preg_match('/[^.]+\:\:[^.]+$/', $value)) {
          return 'ERROR||The characters "::" is reserved for the system.';
        }
      }
			if($read_only){
      	$items .= str_replace($escape,$replace,$description) . ' <input type="hidden" name="' . $field . '_selected[]" value="' . $value . '" /> <br/>';
			}
      else {
      	$items .= str_replace($escape,$replace,$description) . ' <img src="'. $remove_icon . '" alt="Remove Item" class="multiple-select-remove-item" onClick="javascript:proccess_information(\'' . $form . '\', \'multiple_select_update_field\', \'widgets\', null, \'form_field_value|'. $form . '::' . $field . '::' . $value . '\', null, \'' . $field . '_container\', null, null, \''.$form.'\', this);" /> <input type="hidden" name="' . $field . '_selected[]" value="' . $value . '" /> <br/>';
			}
    }
  }
  return $items;
}

/**
 * This function build Customized String inside the selected field based on the feature
 */
function build_custom_string($feature, $pre_selected, $field_name, $data){
	//Building custom string for Call Routing
	switch($feature){
		case "time-condition":
			foreach($pre_selected as $key => $val){
				$new_string = "";
				if($pre_selected[$key]=="am" || $pre_selected[$key]=="pm"){
					$new_string = $data['DayOfWeek'].' - Start '.$data['timecondition_start_hour'].':'.$data['timecondition_start_min'].' '.$data['StartTime'].' - End '.$data['timecondition_end_hour'].':'.$data['timecondition_end_min'].' '.$data['EndTime'];
					if (!in_array($new_string,$pre_selected)) {
						$response[] = $new_string;
					}
				}else{
					$response[] = $val;
				}
			}
			break;
		default:
			$response = $pre_selected;
			break;
	}
	return $response;
}
?>
