<?php
session_start();
require_once('bootstrap.php');

$ACL = new ACL();

$ACL->username = $_POST['username'];
$ACL->password = $_POST['password'];
$ACL->login();

if ($_SESSION['logged']) {
  header('Location: ' . NATURAL_WEB_ROOT . 'dashboard.php');
}
else {
  header('Location: ' . NATURAL_WEB_ROOT . 'index.php?login=error');
}
?>