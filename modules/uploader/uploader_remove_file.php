<?php
  /**
   * @file: uploader_remove_file.php
   * Server Side Ajax Uplader
   */
  session_start();
  require_once('../../bootstrap.php');


  // Load file infomartion
  $id = $_GET['id'];
  //$file = new Files();
//  $file->loadSingle('id = ' . $id);


	// Get the file from the files table.
	$db 						 = DataConnection::readWrite();
	$file            = $db->files[$id];
	$arr['uid']      = $_SESSION['log_id'];
	$arr['filename'] = $_FILES['myfile']['name'];
	$arr['uri']      = $field_dir . '/' . $_FILES['myfile']['name'];
	$arr['filemime'] = $_FILES['myfile']['type'];


  $filename = $file['filename'];
  $uri = $file['uri'];
  if ($file['id'] < 1) {
    natural_set_message('Error loading file information.', 'error');
    return FALSE;
  }

  // Remove file
  //$file->remove('id = ' . $id);

	if ($file && $file->delete()) {
		//if ($file->affected > 0) {
    unlink(NATURAL_ROOT_PATH . '/' . $uri);
    natural_set_message('File "' . $filename . '" was removed successfully.', 'success');
    $data = array('removed' => TRUE);
    print json_encode($data);
  }
  else {
    natural_set_message('Error remove file record from database.', 'error');
    return FALSE;
  }

?>
