<?php
  /**
   * @file: uploader_add_file.php
   * Server Side Ajax Uplader
   */
  session_start();
  require_once('../../bootstrap.php');

  $field_id = $_GET['field_id'];

  // Based on the element id $_GET['field_id'] we can perform validations.
  $field = new DbField();
  $field->byID($field_id);
  if (!$field->affected > 0) {
    // DataManager should output the error.
    return FALSE;
  }

  // Field values attributes.
  if (!empty($field->field_values)) {
    $field_values = explode('|', $field->field_values);
    foreach ($field_values as $value) {
      $option = explode('=', $value);
      switch ($option[0]) {
        case 'limit':
          $field_limit = $option[1];
          break;
        case 'type':
          $field_type = explode(',', $option[1]);
          break;
        case 'size':
          $field_size = substr($option[1], 0, -1);
          $field_size_unit = substr($option[1], -1);
          switch($field_size_unit) {
            case 'B': // Bytes
              $field_size_limit = $field_size . ' bytes';
              break;
            case 'K': // Kilobytes
              $field_size_limit = $field_size . ' kilobytes';
              $field_size = $field_size * 1024;
              break;
            case 'M': // Megabytes
              $field_size_limit = $field_size . ' megabytes';
              $field_size = $field_size * 1048576;
              break;
            case 'G': // Gigabytes
              $field_size_limit = $field_size . ' gigabytes';
              $field_size = $field_size * 1073741824;
              break;
          }
          break;
        case 'max_height':
          $field_max_height = $option[1];
          break;
        case 'max_width':
          $field_max_width = $option[1];
          break;
        case 'min_height':
          $field_min_height = $option[1];
          break;
        case 'min_width':
          $field_min_width = $option[1];
          break;
        case 'dir':
          $field_dir = $option[1];
          break;
        case 'preview':
          $field_preview = $option[1];
          break;
      }
    }
  }
  else {
    natural_set_message('Problems to load attributes for the ' . $field->def_label . ' field.', 'error');
    return FALSE;
  }

  // Size Validation.
  if ($_FILES['myfile']['size'] > $field_size) {
    natural_set_message('File is bigger than ' . $field_size_limit . '.', 'error');
    return FALSE;
  }

  // Extension Validation.
  $ext = pathinfo( $_FILES['myfile']['name'], PATHINFO_EXTENSION);
  if(!in_array($ext, $field_type) ) {
    natural_set_message('Only ' . implode(', ', $field_type) . ' formats are accepted.', 'error');
    return FALSE;
  }

  // Upload directory.
  $upload_dir = NATURAL_ROOT_PATH . '/' . $field_dir . '/';
  $upload_file = $upload_dir . basename($_FILES['myfile']['name']);

  // Validate filename.
  if (file_exists($upload_file)) {
     natural_set_message('File "' . $_FILES['myfile']['name'] . '" already exists.', 'error');
    return FALSE;
  }

  // Create Directory if it does not exist.
  if (!is_dir($upload_dir) && !mkdir($upload_dir, 0777, TRUE)) {
    natural_set_message('Error creating folder ' . $field_dir, 'error');
    return FALSE;
  }

  if (move_uploaded_file($_FILES['myfile']['tmp_name'], $upload_file)) {
    // Test Image dimensions according to the fields attributes.
    if (isset($field_max_height) || isset($field_min_height) || isset($field_max_width) || isset($field_min_width)) {
      $file_dimensions = TRUE;
      list($file_width, $file_height) = getimagesize($upload_file);
      if (!empty($file_height) && !empty($field_max_height) && $file_height > $field_max_height) {
        natural_set_message('File height is larger than "' . $field_max_height . '" pixels.', 'error');
        $file_dimensions = FALSE;
      }
      if (!empty($file_height) && !empty($field_min_height) && $file_height < $field_min_height) {
        natural_set_message('File height is smaller than "' . $field_min_height . '" pixels.', 'error');
        $file_dimensions = FALSE;
      }
      if (!empty($file_width) && !empty($field_max_width) && $file_width > $field_max_width) {
        natural_set_message('File width is larger than "' . $field_max_width . '" pixels.', 'error');
        $file_dimensions = FALSE;
      }
      if (!empty($file_width) && !empty($field_min_width) && $file_width < $field_min_width) {
        natural_set_message('File width is smaller than "' . $field_min_width . '" pixels.', 'error');
        $file_dimensions = FALSE;
      }
      if ($file_dimensions == FALSE) {
        unlink($upload_file);
        return FALSE;
      }
    }
    // Add the file to the files table.
		$db 						 = DataConnection::readWrite();
    $file            = $db->files();
    $arr['uid']      = $_SESSION['log_id'];
    $arr['filename'] = $_FILES['myfile']['name'];
    $arr['uri']      = $field_dir . '/' . $_FILES['myfile']['name'];
    $arr['filemime'] = $_FILES['myfile']['type'];
    $arr['filesize'] = $_FILES['myfile']['size'];
    $arr['timestamp']= time();
    $res 						 = $file->insert($arr);
    if ($res['id'] > 0) {
      chmod($upload_file, 0777);
      natural_set_message('File "' . $_FILES['myfile']['name'] . '" was uploaded successfully!', 'success');
      $render = array(
        'filename'		=>  $arr['filename'],
        'preview'     => ($field_preview == 'true') ? TRUE : FALSE,
        'preview_uri' => $field_dir . '/' . $_FILES['myfile']['name'],
        'id'          => $res['id'],
        'field_id'    => $field_id,
        'field_name'  => $field->field_name,
      );
      // File item
      $file_item    = $twig->render('uploader-file-item.html', $render);
      $breaks       = array("\r\n", "\n", "\r");
      $file_item    = str_replace($breaks, "", $file_item);
      $file_item    = str_replace('"', "'", $file_item);
      $response     = array(
        'file_item' => htmlentities($file_item),
        'limit'     => $field_limit,
        'id'        => $res['id'],
      );
      print json_encode($response);
      return;
    }
    else {
      // Due to a database error we need to delete the uploaded file
      unlink($upload_file);
      natural_set_message('Problems creating uploaded file record.' , 'error');
      return FALSE;
    }

  }
  else {
    natural_set_message('File was not uploaded.', 'error');
    return FALSE;
  }

?>
