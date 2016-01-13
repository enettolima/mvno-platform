<?php 


require_once('bootstrap.php');

session_start();
$_SESSION['selected_customer_id']=$_GET['customer_id'];

if($_SESSION['log_access_level'] < 62){
 
  if($_SESSION['log_partner_id']==$customer->partner_id){
    header("Location: {$_GET['url']}");
  }else{
    // jump back
    header("Location: dashboard.php");
  }

}else{
  header("Location: {$_GET['url']}");
}


?>
