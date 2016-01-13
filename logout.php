<?php
  session_start();
  require_once('bootstrap.php');

  if(isset($_SESSION['log_id'])) {
    session_destroy();
  }

	header('Location: ' . NATURAL_WEB_ROOT . 'index.php?login=expired');
?>
