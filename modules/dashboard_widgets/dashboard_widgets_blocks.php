<?php

/**
 * This file is used to organize and display the dashboard widgets
 * Dashboard Widget - Server Properties
 */
function render_widget_graph($data) {
  global $twig;
  $pdo = new PDO(NATURAL_PDO_DSN_READ, NATURAL_PDO_USER_READ, NATURAL_PDO_PASS_READ);
  $getquery = $pdo->prepare("select * from dashboard_widgets where id = ".$data['id']);
  $getquery->execute();
  $query = $getquery->fetch();


  switch ($_SESSION['log_access_level']) {
    case 81: // Super Admin
      # Este level vai ver todas as opções do painel de administrador e do painel de customer**/
      $query['query'] = str_replace("{{user}}"," != -1",$query['query']);
      break;
    case 51: // Customer
      # Este level quando logar no sistema ja vai entrar no painel do customer e verá todas as opções do menu
      $query['query'] = str_replace("{{user}}"," = ".$_SESSION['log_id'],$query['query']);
      break;
    default:
      # code...
      $query['query'] = str_replace("{{user}}"," = ".$_SESSION['log_id'],$query['query']);
      break;
  }

  $command = $pdo->prepare($query['query']);
  $command->execute();
  $d = $command->fetchAll();
  if (count($d) <= 0){
    $return['data'] = null;
    $return['response_nodata']  = $query['response_nodata'];
  }else{
    //we only return response_nodata if data is null so we save the json size a littow.
    $return['response_nodata']  = null;
    if($query['graph_type'] == 'Template'){
      global $twigwidgets;
      $return['data'] = $twigwidgets->render($query['widget_template_file'],
                          array(
                              'rows' => $d
                          ));

    }else {
      $return['data'] = $d;
    }
  }
  //$errors = $command->errorInfo();
  //print_debug($errors);
  $return['id']               = $query['id'];
  $return['fn']               = $query['widget_function'];
  $return['update_seconds']   = $query['update_seconds'];
  $return['graph_type']       = $query['graph_type'];
  $return['graph_options']    = unserialize($query['graph_options']);
  $return['data-gs-x']        = $query['data_gs_x'];
  $return['data_gs_y']        = $query['data_gs_y'];
  $return['data_gs_width']    = $query['data_gs_width'];
  $return['data_gs_height']   = $query['data_gs_height'];
  $return['html'] = $twig->render('dashboard-widget.html',
                      array(
                          'icon' => $query['icon'],
                          'widget_id' => $query['id'],
                          'widget_title' => $query['title'],
                          'widget_function' => $query['widget_function'],
                          'x'       => $query['data_gs_x'],
                          'y'       => $query['data_gs_y'],
                          'width'   => $query['data_gs_width'],
                          'height'  => $query['data_gs_height']
                      ));
     header('Content-Type: application/json');
     echo json_encode($return);
 }

function custom_graph_example($data) {
    global $twig;
    $pdo = new PDO(NATURAL_PDO_DSN_READ, NATURAL_PDO_USER_READ, NATURAL_PDO_PASS_READ);
    $getquery = $pdo->prepare("select * from dashboard_widgets where id = ".$data['id']);
    $getquery->execute();
    $query = $getquery->fetch();
    //$errors = $command->errorInfo();
    //print_debug($errors);
    $resp_data[0]['x'] = '2011 Q1';
    $resp_data[0]['y'] = 3;
    $resp_data[0]['z'] = 2;
    $resp_data[0]['a'] = 3;

    $resp_data[1]['x'] = '2011 Q2';
    $resp_data[1]['y'] = 2;
    $resp_data[1]['z'] = null;
    $resp_data[1]['a'] = 1;

    $resp_data[2]['x'] = '2011 Q3';
    $resp_data[2]['y'] = 0;
    $resp_data[2]['z'] = 2;
    $resp_data[2]['a'] = 4;

    $resp_data[3]['x'] = '2011 Q4';
    $resp_data[3]['y'] = 2;
    $resp_data[3]['z'] = 4;
    $resp_data[3]['a'] = 3;


    $return['data-gs-x']        = 0; //this value is render after the html is placed, so if is different than template x value, it will resize to it.
    $return['data-gs-y']        = 0; //this value is render after the html is placed, so if is different than template y value, it will resize to it.
    $return['data-gs-width']    = 3; //this value is render after the html is placed, so if is different than template width value, it will resize to it.
    $return['data-gs-height']   = 3; //this value is render after the html is placed, so if is different than template height value, it will resize to it.
    $return['data']             = $resp_data; //this will arrive at the custom_graph_example function inside the dashboard.js as chart_data['data']
    $return['html'] = $twig->render('dashboard-widget.html',
                        array(
                            'icon' => $query['icon'],
                            'widget_id' => $query['id'],
                            'widget_title' => $query['title'],
                            'widget_function' => $query['widget_function'],
                            'x'       => 0, //this is the defaul row
                            'y'       => 0, //this is the defaul column
                            'width'   => 3, //this is the defaul width in column size
                            'height'  => 3  //this is the defaul height in column size
                        ));

    header('Content-Type: application/json');
    echo json_encode($return);
}
?>
