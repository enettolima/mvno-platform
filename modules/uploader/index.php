<?php
/**
* HIVE - Copyleft Open Source Mind, GP
* Last Modified: Date: 07-18-2009 19:15:01 -0500 (Jul-Sat-2009) $ @ Revision: $Rev: 11 $
* @package Hive
*/

  session_start();
  require_once('../../bootstrap.php');
  require_once('upload.func.php');

  if(!$_SESSION['logged']) {
    echo 'LOGOUT';
		exit(0);
  }
  $fn = $_GET['fn'];
	switch($fn) {
    case 'file_remove';
		  print file_remove($_GET);
		  break;
	}
?>
