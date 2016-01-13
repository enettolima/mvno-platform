<?php

/*
 * Just to show the form example
 */
function natural_form_example(){
	$frm = new DbForm();
	
	$frm->first_name = "System";
	$frm->last_name = "Administrator";
	$frm->username = "admin";
	$frm->password = null;
	
	//Select the properly levels to show an example of the listbox
	//Please check the table field_templates where form_reference = natural_example_form for this example
	$db = DataConnection::readOnly();
	$access_levels = $db->acl_levels()
	->select("description, level")
	->where("level <= ?",$_SESSION['log_access_level'])
	->order("description");
	
	if (count($access_levels)) {
		$items = array();
		foreach ($access_levels as $access_level) {
			$items[] = ucwords($access_level['description']) . '=' . $access_level['level'];
		}
		//Override the options and pass an array to the form class (lib/classes/forms.class.php)
		$frm->access_level_options = implode(';', $items);
	}
	$frm->build('natural_example_form', $frm, $_SESSION['log_access_level'], FALSE);
}

/*
 *Just to show how the data gets to the controller after a form submit
 */
function natural_form_example_submit($data){
	echo 'Data from the form is:<br>';
	print_debug($data);
	
	return natural_set_message('Form has been submitted!', 'success');
}
/*
 * Functions for module management
 */
function module_list($row_id = NULL, $search = NULL, $sort = NULL, $page = 1) {
		$view = new ListView();
		
		// Row Id for update only row
		if (!empty($row_id)) {
			$row_id = 'id = ' . $row_id;
		}
		else {
			$row_id = 'id != 0'; 
		}
		
		// Sort
		if (empty($sort)) {
			$sort = 'label ASC';
		}
		
		//Setting limits for the pagination
		$limit = PAGER_LIMIT;
    $offset = ($page * $limit) - $limit;
		//Openning the DB Connection
		$db = DataConnection::readOnly();
		$total_records=0;
		
		//Search On listview
		if (!empty($search)) {
			$search_fields = array('module', 'label', 'id');
			$exceptions = array();
			$search_query = build_search_query($search, $search_fields, $exceptions);
			
			$modules = $db->module()
      ->where($row_id)
      ->and($search_query)
      ->order($sort)
      ->limit($limit, $offset);
		}
		else {
			//If not a search, get everyting from table
			$modules = $db->module()
			->where($row_id)
			->order($sort)
			->limit($limit, $offset);
		}
		
		$total_records = $db->module()->count("*");
		$i = 0;
    if (count($modules)) {
			// Building the header with sorter
			$headers[] = array('display' => 'Id', 'field' => 'id');
			$headers[] = array('display' => 'Module', 'field' => 'module');
			$headers[] = array('display' => 'Label', 'field' => 'label');
			$headers[] = array('display' => 'Delete', 'field' => NULL);
			$headers = build_sort_header('module_list', 'natural', $headers, $sort);
		
			foreach( $modules as $module ){
        $j = $i + 1;
        //This is important for the row update/delete
        $rows[$j]['row_id'] = $module['id'];
        /////////////////////////////////////////////
        $rows[$j]['id']     = $module['id'];
        $rows[$j]['module'] = $module['module'];
        $rows[$j]['label'] 	= $module['label'];
        $rows[$j]['delete'] = theme_link_process_information('',
            'module_delete_form',
            'module_delete_form',
            'natural', array('extra_value' => 'id|' . $module['id'],
                'response_type' => 'modal',
                'icon' => NATURAL_REMOVE_ICON));
        $i++;
      }
    }
	$options = array(
	'show_headers' => TRUE,
	'page_title' => translate('Module List'),
	'page_subtitle' => translate('Manage Module'),
	'empty_message' => translate('No module found!'),
	'table_prefix' => theme_link_process_information(translate('Create New Module'),
		'module_create_form',
		'module_create_form',
		'natural', array('response_type' => 'modal')),
	'pager_items' => build_pager('module_list', 'natural', $total_records, $limit, $page),
	'page' => $page,
	'sort' => $sort,
	'search' => $search,
	'show_search' => TRUE,
	'function' => 'module_list',
	'module' => 'natural',
	'update_row_id' => '',
	'table_form_id' => '',
	'table_form_process' => '',
	);

  $listview = $view->build($rows, $headers, $options);

  return $listview;
}

function module_create_form() {
  $module = new Module();
	
	//Openning the DB Connection
	$pdo = new PDO(NATURAL_PDO_DSN_READ, NATURAL_PDO_USER_READ, NATURAL_PDO_PASS_READ);
	$q = $pdo->prepare("SHOW TABLES");
	$q->execute();
	$db_tables = $q->fetchAll(PDO::FETCH_COLUMN);
	
	$items = array();
	if (count($db_tables) > 0) {
		foreach ($db_tables as $table => $value) {
			$items[] = $value . '=' . $value;
		}
		$module->table_list = implode(';', $items);
		
		//Load form
		$frm = new DbForm();
		$frm->build('module_create_form', $module, $_SESSION['log_access_level']);
	}else{
		natural_set_message("No table found or schema table is unreachable!", 'error');
	}
}

function module_create_form_submit($data) {
	/*
	 * Validating information on the Database
	 */
	$error = validate_module_info($data);
	if ($error) {
		natural_set_message($error, 'error');
		return FALSE;
		exit(0);
	}
	$data['project_path'] 	= NATURAL_WEB_ROOT;
	$data['project_name'] 	= NATURAL_PLATFORM;
	$data['field_1'] 				= 'name';
	$data['field_label_1'] 	= 'Name';
	$data['field_2'] 				= 'author';
	$data['field_label_2'] 	= 'Author';
	$data['module'] 				= $data['label'];
	if (is_numeric($data['table_name'])) {
		$class_name 					= str_replace("_", " ", $data['module']);
		$data['module_name'] 	= $data['module'];
		$data['module'] 			= str_replace(" ", "_", strtolower($data['module']));
	} else {
		$class_name 					= str_replace("_", " ", $data['table_name']);
		$data['module_name'] 	= $data['table_name'];
		$data['module'] 			= str_replace(" ", "_", strtolower($data['table_name']));
		
		$query = "DESCRIBE " . "" . $data['table_name'] . "";
		
		$pdo = new PDO(NATURAL_PDO_DSN_READ, NATURAL_PDO_USER_READ, NATURAL_PDO_PASS_READ);
		$q = $pdo->prepare($query);
		$q->execute();
		$columns = $q->fetchAll(PDO::FETCH_COLUMN);
		
		if (count($columns) > 0) {
			for ($i = 1; $i < 3; $i++) {
				$key = 'field_' . $i;
				$keylabel = 'field_label_' . $i;
				//$data[key] = 'b.name';
				$data[$key] = $columns[$i];
				//$data[$keylabel] = 'Name';
				$data[$keylabel] = ucwords(str_replace("_", " ", strtolower($columns[$i])));
			}
		}
	}
	$class_name = ucwords($class_name);
	$data['class_name'] = str_replace(" ", "", $class_name);
	//$data['path'] = NATURAL_WEB_ROOT . "modules/" . $data['module'] . "/";
	$data['path'] = '../'.$data['module'].'/';
	
	//Creating directory for the module
	create_module_structure($data);
	if ($data['create_api'] == 1) {
		create_module_api($data);
	}
	if ($data['create_forms'] == 1) {
		//calling function on natural module module/natural/natural.func.php
		create_form($data['module_name']);
	}
	if ($data['create_class']) {
		//create_module_class($data);
	}
	if ($data['create_menu'] == 1) {
		create_module_menu($data);
	}

	//Saving information to the Natural Database
	$module 										= new Module();
	$submit['version'] 					= 1;
	$submit['module'] 					= strtolower(str_replace(" ", "_", $data['module']));
	$submit['label'] 						= ucwords($data['label']);
	$submit['description'] 			= ucwords($data['label']);
	$submit['license_quantity']	= 0;
	$submit['last_update'] 			= date("Y-m-d H:i:s");
	$submit['status'] 					= 1;
	$response = $module->create($submit);
	
	if ( $response['id'] > 0 ) {
		return module_list($response['id']);
	} else {
		return false;
	}
	if($module->affected){
		natural_set_message('Module '.$data['module'].' created successfully!', 'success');	
		return module_list($module->id);
	}else{
		natural_set_message('Could not save this Module at this time', 'error');
		return false;
	}
}

function module_delete_form($data){
	$module = new Module();
	//$module->loadSingle('id='.$data['id']);
	$module->byID($data['id']);
	if($module->affected>0){
		$frm = new DbForm();
		$frm->build('module_delete_form', $module, $_SESSION['log_access_level']);
	}else{
		return FALSE;   
	}
}

function module_delete_form_submit($data){
	$module = new Module();
	$delete = $module->delete($data['id']);
  if ($delete['code']==200) {
    return $data['id'];
  } else {
    return FALSE;
  }
}

/*
 * Creating Module menu
 */

function create_module_menu($data) {
  $db = DataConnection::readOnly();
	$menus = $db->menu()
	->select("*")
	->where("id > ?", 0)
	->order("position DESC")
	->limit(1);
	
	foreach($menus as $menu){
		$last_position = $menu['position'];
	}
	
	if (is_numeric($data['table_name'])) {
			$name = $data['module'];
	} else {
			$name = $data['table_name'];
	}
	
	$menu = new Menu();
	//Building array of data to pass to the menu class
	$submit['pid'] 					= '';
	$submit['menu_name'] 		= 'main';
	$submit['position']  		= $last_position + 1;
	$submit['element_name'] = $data['module'];
	$submit['label'] 				= ucwords($data['module']);
	$submit['title'] 				= ucwords($data['module']);
	$submit['func'] 				= strtolower(str_replace(" ", "_", $name.'_list'));
	$submit['module'] 			= $data['module'];
	$submit['allow'] 				= 'all';
	$submit['allow_value'] 	= '0';
	$submit['status'] 			= '1';
	$submit['icon_class'] 	= 'fa fa-edit';
	$menu->create($submit);
}

/*
 * Creating module structure
 */

function create_module_structure($data) {
    //Creating folder for the new module
    mkdir($data['path'], 0777);
    $files = array('index.php', 'class.php', 'controller.php');
    //Creating files
    create_module_file($files, $data);
}

/*
 * Creating module files
 */

function create_module_file($files, $data) {
	if (is_numeric($data['table_name'])) {
		$name = $data['module'];
	} else {
		$name = $data['table_name'];
	}
	$data['mod_name'] = $name;
	/*Array
	(
    [fn] => module_create_form_submit
    [id] => 
    [version] => 
    [table_name] => author
    [label] => Author
    [description] => 
    [license_quantity] => 
    [last_update] => 
    [status] => 
    [module] => author
    [structure] => structure
    [create_api] => 0
    [create_forms] => 0
    [create_class] => 
    [create_menu] => 0
    [project_path] => ./
    [project_name] => Natural
    [field_1] => name
    [field_label_1] => Name
    [field_2] => age
    [field_label_2] => Age
    [module_name] => author
    [class_name] => Author
    [path] => ../author/
    [mod_name] => author
	)*/
	print_debug($data);
	foreach ($files as $k => $v) {
		if ($v == "index.php") {
			$file = file_get_contents("template/index.php");
		} else {
			$file = file_get_contents("template/template_book." . $v);
		}
		// Do tag replacements or whatever you want
		$file = str_replace("template_book", $name, $file);
		$file = str_replace("book", $name, $file);
		$file = str_replace("TemplateBook", $data['class_name'], $file);
		$file = str_replace("Book", $data['class_name'], $file);
		$file = str_replace("_name_", $data['field_1'], $file);
		$file = str_replace("_Name_", $data['field_label_1'], $file);
		$file = str_replace("_author_", $data['field_2'], $file);
		$file = str_replace("_Author_", $data['field_label_2'], $file);
		//save it back:
		if ($v == "index.php") {
			file_put_contents($data['path'] . "index.php", $file);
		} else {
			file_put_contents($data['path'] . $name . "." . $v, $file);
		}
	}
	//Composer Json update not required anymore
	//update_composer_dependencies($data);
}

/*
 *Adding module to the composer dependencies
 */
function update_composer_dependencies($data){
	$file = '../../composer.json';
	$json = json_decode(file_get_contents($file), true);
	
	array_push($json['autoload']['classmap'], 'modules/'.$data['mod_name']);
	file_put_contents($file, json_encode($json));
}

/*
 * Validating module information
 */
function validate_module_info($data, $edit = false) {
	if ($data['label']=='') {
		return 'Field Label is required!';
	}
	if (file_exists($data['path'])) {
		return 'The directory <i>' . $data['module'] . '</i> already exists!<br>Please try a different name or remove the current module!';
	}
	$module = new Module();
	//$module->loadSingle('module="'.$data['module'].'" LIMIT 1');
	$response = $module->byName($data['module']);
	if($response['code']==200){
		return 'Module <i>' . $data['module'] . '</i> already exists!';
	}
	
	//Setup database connection
	$db = DataConnection::readOnly();
	if ($data['create_forms'] == 1) {
		/*$query = "SELECT * FROM " . "form_parameters WHERE form_id = '" . $data['table_name'] . "_new' 
		OR form_id = '" . $data['table_name'] . "_edit' 
		OR form_id = '" . $data['table_name'] . "_view'";
		*/
		
		//$modules = $db->form_parameters()
		$modules = $db->form_templates()
		->where("form_name",$data['table_name'] ."_create_form")
		->or("form_name",$data['table_name'] ."_edit_form")
		->or("form_name",$data['table_name'] ."_delete_form")
		->order("form_id")
		->limit(1);
		if (count($modules) > 0) {
			return 'The form for the module <i>' . $data['module'] . '</i> already exists!';
		}
	}
	if ($data['create_menu'] == 1) {
		//$query = "SELECT * FROM " . "menu WHERE element_name = '" . $data['module'] . "_main'";
		//$form = new DataManager();
		//$form->dmLoadCustomList($query, 'ASSOC');
		
		$menus = $db->menu()
		->where("element_name",$data['module'] ."_main")
		->order("element_name")
		->limit(1);
		if (count($menus) > 0) {
			return 'Menu for the module <i>' . $data['module'] . '</i> already exists!';
		}
	}
	return false;
}

/*
 * Create API reference on api/index.php inside of the project
 */
function create_module_api($data) {
	//Creating strings to add to the api/index.php inside 
	//$new_api = "require_once('SimpleAuth.php');\nrequire_once('../modules/" . $data['module_name'] . "/" . $data['module_name'] . ".model.php');";
	$set_api = "\$r->addAPIClass('" . $data['class_name'] . "');\n\$r->handle();";
	$file = file_get_contents('../../api/index.php');
	if (!strpos(file_get_contents('../../api/index.php'), "\$r->addAPIClass('" . $data['class_name'] . "');") !== false) {
		//If string of the API not found, include t the api/index.php
		// Do tag replacements or whatever you want
		//$file = str_replace("require_once('SimpleAuth.php');", $new_api, $file);
		$file = str_replace('$r->handle();', $set_api, $file);
		//save it back
		file_put_contents('../../api/index.php', $file);
	}
}

/**
 * Module Remove
 */
function module_remove($data) {
	$module = new DataManager();
	$module->dm_load_single("" . MODULES_TABLE,"id='{$data['module_id']}'");
	//$module->dm_load_single($table, $search_str)
	$name = $module->name;
	if (!$module->affected) {
		return "ERROR|19109|Module Not Found, Please contact your system administrator!";
		exit(0);
	}
	$module->dm_remove("" . MODULES_TABLE,"id='{$data['module_id']}'");
	if ($module->affected) {
		return "Module {$name} was removed successfully!<br>NOTE: Database and module structure was not removed!";
	} else {
		return "We could not remove the Module {$name} at this time, please try again!<br>If the problem persists, contact your system administrator!";
	}
}

/*
 * START OF THE FORM MANAGEMENT
 */

function form_list($row_id = NULL, $search = NULL, $sort = NULL, $page = 1) {
	$view = new ListView();
	// Row Id for update only row
	if (!empty($row_id)) {
		$row_id = 'id = ' . $row_id;
	} else {
		$row_id = 'id != 0';
	}
	
	// Sort
	if (empty($sort)) {
		$sort = 'form_name ASC';
	}
	
	$limit = PAGER_LIMIT;
	$offset = ($page * $limit) - $limit;
	$db = DataConnection::readOnly();
	$total_records = 0;
	
	// Search
	if (!empty($search)) {
		$search_fields = array('id', 'form_name', 'form_title');
		$exceptions = array();
		$search_query = build_search_query($search, $search_fields, $exceptions);
		
		$forms = $db->form_templates()
		->where($row_id)
		->and($search_query)
		->order($sort)
		->limit($limit, $offset);
	} else {
		$forms = $db->form_templates()
		->where($row_id)
		->order($sort)
		->limit($limit, $offset);
	}
	$total_records = $db->form_templates()->count("*");
	$i = 0;
	if (count($forms)) {
		// Building the header with sorter
		$headers[] = array('display' => 'Id', 'field' => 'id');
		$headers[] = array('display' => 'Name', 'field' => 'form_name');
		$headers[] = array('display' => 'Title', 'field' => 'form_title');
		$headers[] = array('display' => 'Edit', 'field' => NULL);
		$headers[] = array('display' => 'Delete', 'field' => NULL);
		$headers = build_sort_header('form_list', 'natural', $headers, $sort);

		$total = 0;
		foreach( $forms as $form ){
			$j = $i + 1;
			//This is important for the row update
			$rows[$j]['row_id'] = $form['id'];
			//////////////////////////////////////
			$rows[$j]['id'] = $form['id'];
			$rows[$j]['form_name'] = $form['form_name'];
			$rows[$j]['form_title'] = $form['form_title'];
			
			if($form['system']==1){
					$disabled = 'disabled';
			}else{
					$disabled = '';
			}
			$rows[$j]['edit']   = theme_link_process_information('',
				'form_edit_form',
				'form_edit_form',
				'natural',
				array('extra_value' => 'id|' . $form['id'],
					'response_type' => 'modal',
					'icon' => NATURAL_EDIT_ICON,
					'class' => $disabled));
			$rows[$j]['delete'] = theme_link_process_information('',
				'form_delete_form',
				'form_delete_form',
				'natural',
				array('extra_value' => 'id|' . $form['id'],
					'response_type' => 'modal',
					'icon' => NATURAL_REMOVE_ICON,
					'class' => $disabled));
			$i++;
		}
	}

  $options = array(
		'show_headers' => TRUE,
		'page_title' => translate('Form List'),
		'page_subtitle' => translate('Manage Forms'),
		'empty_message' => translate('No form found!'),
		'table_prefix' => theme_link_process_information(translate('Create New Form'),
			'form_create_form',
			'form_create_form',
			'natural',
			array('response_type' => 'modal')),
		'pager_items' => build_pager('form_list', 'natural', $total_records, $limit, $page),
		'page' => $page,
		'sort' => $sort,
		'search' => $search,
		'show_search' => TRUE,
		'function' => 'form_list',
		'module' => 'natural',
		'update_row_id' => '',
	  'table_form_id' => '',
		'table_form_process' => '',
	);

  $listview = $view->build($rows, $headers, $options);

  return $listview;
}

function form_create_form() {
	$frm = new DbForm();
	return $frm->build("form_create_form");
}

function form_create_form_submit($data) {
	$db = DataConnection::readOnly();
	$check = $db->form_templates()
	->select("*")
	->where("form_name", $data['form_name'])
	->limit(1);
	if(count($check)>0){
		natural_set_message('Sorry but this form name already exist, please try again with different name!', 'error');
		return false;
		exit(0);
	}
	
	$form = new DbForm();
	//$save = $form->creat($submit);
	$response = $form->create($data);
	if ( $response['id'] > 0 ) {
		natural_set_message('Form '.$data['form_name'].' saved successfully!', 'success');
		return form_list($response['id']);
	} else {
		natural_set_message('Form '.$data['form_name'].' could not be saved!', 'error');
		return false;
	}
}

function form_edit_form($data){
	$form = new DbForm();
	//$form->dmLoadSingle("" . FORM_TABLE, 'id='.$data['id']);
	$form->byID($data['id']);
	$frm = new DbForm();
	$frm->build("form_edit_form", $form, $_SESSION['log_access_level']);
}

function form_edit_form_submit($data){
	$form = new DbForm();
	$response = $form->update($data);
	if($response['code']==200){
		return form_list($data['id']);
	}
}

function form_delete_form($data){
	$form = new DbForm();
	$form->byID($data['id']);
	if($form->affected>0){
		$frm = new DbForm();
		$frm->build('form_delete_form', $form, $_SESSION['log_access_level']);
	}else{
		return FALSE;
	}
}

function form_delete_form_submit($data){
	$form = new DbForm();
  $delete = $form->delete($data['id']);
  if ($delete['code']==200) {
		
		$sql = "DELETE FROM ".FIELD_TABLE." WHERE form_template_id =  :formID";
		$pdo = new PDO(NATURAL_PDO_DSN_WRITE, NATURAL_PDO_USER_WRITE, NATURAL_PDO_PASS_WRITE);
		$stmt = $pdo->prepare($sql);
		$stmt->bindParam(':formID', $data['id'], PDO::PARAM_INT);   
		$stmt->execute();
		
    return $data['id'];
  } else {
    return FALSE;
  }
}

/*
 * FIELD MANAGEMENT
 */
function field_list($row_id = NULL, $search = NULL, $sort = NULL, $page = 1) {
	$view = new ListView();
	// Row Id for update only row
  if (!empty($row_id)) {
    $row_id = 'id = ' . $row_id;
  } else {
    $row_id = 'id != 0';
  }
  
  // Sort
  if (empty($sort)) {
    $sort = 'form_reference ASC';
  }
  
  $limit = PAGER_LIMIT;
  $offset = ($page * $limit) - $limit;
  $db = DataConnection::readOnly();
  $total_records = 0;
  
  // Search
  if (!empty($search)) {
    $search_fields = array('id', 'field_name', 'form_reference', 'html_type', 'def_label');
    $exceptions = array();
    $search_query = build_search_query($search, $search_fields, $exceptions);
    
    $fields = $db->field_templates()
    ->where($row_id)
    ->and($search_query)
    ->order($sort)
    ->limit($limit, $offset);
  } else {
    $fields = $db->field_templates()
    ->where($row_id)
    ->order($sort)
    ->limit($limit, $offset);
  }
  $total_records = $db->field_templates()->count("*");
	$i = 0;
  if (count($fields)) {
		// Building the header with sorter
		$headers[] = array('display' => 'Id', 'field' => 'id');
		$headers[] = array('display' => 'Form Reference', 'field' => 'form_reference');
		$headers[] = array('display' => 'Position', 'field' => 'form_field_order');
		$headers[] = array('display' => 'Name', 'field' => 'field_name');
		$headers[] = array('display' => 'HTML Type', 'field' => 'html_type');
		$headers[] = array('display' => 'Label', 'field' => 'def_label');
		$headers[] = array('display' => 'Edit', 'field' => NULL);
		$headers[] = array('display' => 'Delete', 'field' => NULL);
		$headers = build_sort_header('field_list', 'natural', $headers, $sort);

		$total = 0;
		foreach( $fields as $field ){
			$j = $i + 1;
			//This is important for the row update
			$rows[$j]['row_id'] = $field['id'];
			//////////////////////////////////////
			$rows[$j]['id'] = $field['id'];
			$rows[$j]['form_reference'] = $field['form_reference'];
			$rows[$j]['form_field_order'] = $field['form_field_order'];
			$rows[$j]['field_name'] = $field['field_name'];
			$rows[$j]['html_type'] = $field['html_type'];
			$rows[$j]['def_label'] = $field['def_label'];
			$rows[$j]['edit']   = theme_link_process_information('',
				'field_edit_form',
				'field_edit_form',
				'natural',
				array('extra_value' => 'id|' . $field['id'],
					'response_type' => 'modal',
					'icon' => NATURAL_EDIT_ICON));
			$rows[$j]['delete'] = theme_link_process_information('',
				'field_delete_form',
				'field_delete_form',
				'natural',
				array('extra_value' => 'id|' . $field['id'],
					'response_type' => 'modal',
					'icon' => NATURAL_REMOVE_ICON));
			$i++;
		}
	}

    $options = array(
		'show_headers' => TRUE,
		'page_title' => translate('Field List'),
		'page_subtitle' => translate('Manage Fields'),
		'empty_message' => translate('No field found!'),
		'table_prefix' => theme_link_process_information(translate('Create New Field'),
			'field_create_form',
			'field_create_form',
			'natural',
			array('response_type' => 'modal')),
		'pager_items' => build_pager('field_list', 'natural', $total_records, $limit, $page),
		'page' => $page,
		'sort' => $sort,
		'search' => $search,
		'show_search' => TRUE,
		'function' => 'field_list',
		'module' => 'natural',
		'update_row_id' => '',
	  'table_form_id' => '',
		'table_form_process' => '',
	);

  $listview = $view->build($rows, $headers, $options);

  return $listview;
}

function field_create_form(){
	$frm = new DbForm();
	$frm->build('field_create_form', null, $_SESSION['log_access_level']);
}

function field_create_form_submit($data){
	$field = new DbField();
	
	foreach ($data as $key => $value) {
		if ($key != "fn") {
			$submit[$key] = mysql_real_escape_string($value);
		}
	}
	
	//Getting form name
	$form = new DbForm();
	$form->byID($data['form_reference']);
	$submit['form_reference'] = $form->form_name;
	$submit['form_template_id'] = $data['form_reference'];
	$response = $field->create($submit);
  if ( $response['id'] > 0 ) {
    natural_set_message('Field has been created!', 'success');
    return field_list($response['id']);
  } else {
    natural_set_message('Could not save this Field at this time', 'error');
    return false;
  }
}

function field_edit_form($data){
	$ff = new DbField();
	$ff->byID($data['id']);
	$ff->form_reference = $ff->form_template_id;
	$form = new DbForm();
	$form->build("field_edit_form", $ff, $_SESSION['log_access_level']);
}

function field_edit_form_submit($data){
	//$ff = new FieldTemplates();
	$ff = new DbField();
	$ff->byID($data['id']);
	foreach ($data as $key => $value) {
		if ($key != 'fn' && $key!='fieldset_name') {
			$ff->$key = mysql_real_escape_string($value);
			$submit[$key] = mysql_real_escape_string($value);
		}
	}
	
	//Getting form name
	$form = new DbForm();
	$form->byID($data['form_reference']);
	$submit['form_reference'] = $form->form_name;
	$submit['form_template_id'] = $data['form_reference'];
	
	$field = new DbField();
	$response = $field->update($submit);
	if($response['code']==200){
		return field_list($data['id']);
	}
}

function field_delete_form($data){
	$field = new DbField();
	$field->byID($data['id']);
	$frm = new DbForm();
	$frm->build('field_delete_form', $field, $_SESSION['log_access_level']);
}

function field_delete_form_submit($data){
	$field = new DbField();
  $delete = $field->delete($data['id']);
  if ($delete['code']==200) {
    return $data['id'];
  } else {
    return FALSE;
  }
}

function class_form_creator_form(){
	$frm = new DbForm();
	$query = "SHOW TABLES FROM " . NATURAL_DBNAME . "";
	$dm = new DataManager();
	$dm->dmLoadCustomList($query, 'ASSOC');
	if ($dm->affected) {
		foreach ($dm->data as $k => $v) {
			foreach ($v as $key => $value) {
				$items[] = $value . '=' . $value;
			}
		}
		$dm->table_options = implode(';', $items);
	}
	$dm->type = array('class', 'form');
	$frm->build('class_form_creator_form', $dm, $_SESSION['log_access_level'], FALSE);
}

function class_form_creator_form_submit($data){
	if(count($data['type'])<1){
		natural_set_message('Please select at least one type and try again!', 'error');
		exit(0);
	}
	
	foreach($data['type'] as $k => $v){
		if($v=='class'){
			class_creator($data['table_name']);
		}
		if($v=='form'){
			create_form($data['table_name']);
		}
	}
}

function class_creator($table_name){
	$data['project_path'] = NATURAL_WEB_ROOT;
	$data['project_name'] = NATURAL_PLATFORM;
	$data['field_1'] = 'b.name';
	$data['field_label_1'] = 'Name';
	$data['field_2'] = 'b.author';
	$data['field_label_2'] = 'Author';

	//$query = "DESCRIBE " . "" . $table_name . "";
	//$fields = new DataManager();
	//$fields->dmLoadCustomList($query, 'ASSOC');
	
	$query = "DESCRIBE " . "" . $table_name . "";
		
	$pdo = new PDO(NATURAL_PDO_DSN_READ, NATURAL_PDO_USER_READ, NATURAL_PDO_PASS_READ);
	$q = $pdo->prepare($query);
	$q->execute();
	$columns = $q->fetchAll(PDO::FETCH_COLUMN);
	
	if (count($columns) > 0) {
		for ($i = 1; $i < 3; $i++) {
			//$key = 'field_' . $i;
			//$keylabel = 'field_label_' . $i;
			////$data[key] = 'b.name';
			//$data[$key] = $columns[$i];
			////$data[$keylabel] = 'Name';
			//$data[$keylabel] = ucwords(str_replace("_", " ", strtolower($columns[$i])));
			$key = 'field_' . $i;
			$keylabel = 'field_label_' . $i;
			//$data[key] = 'b.name';
			$data[$key] = $columns[$i];
			$data[$keylabel] = ucwords(str_replace("_", " ", strtolower($columns[$i])));
		}
	}
		
	/*if ($fields->affected > 0) {
		for ($i = 1; $i < 3; $i++) {
			$key = 'field_' . $i;
			$keylabel = 'field_label_' . $i;
			//$data[key] = 'b.name';
			$data[$key] = $fields->data[$i]['Field'];
			//$data[$keylabel] = 'Name';
			$data[$keylabel] = ucwords(str_replace("_", " ", strtolower($fields->data[$i]['Field'])));
		}
	}
	*/
	$name = $table_name;
	$class_name = str_replace("_", " ", $table_name);
	
	$class_name = ucwords($class_name);
	$data['class_name'] = str_replace(" ", "", $class_name);
	$data['path'] = NATURAL_WEB_ROOT . "modules/natural/";
	
	$file = file_get_contents("template/book.class.php");
	// Do tag replacements or whatever you want
	$file = str_replace("template_book", $name, $file);
	$file = str_replace("book", $name, $file);
	$file = str_replace("Book", $data['class_name'], $file);
	$file = str_replace("name", $data['field_1'], $file);
	$file = str_replace("Name", $data['field_label_1'], $file);
	$file = str_replace("author", $data['field_2'], $file);
	$file = str_replace("Author", $data['field_label_2'], $file);
	//save it back:
	$write = file_put_contents($name . ".class.php", $file);
	natural_set_message('Done creating the class for the table '.$data['table_name'].'!', 'success');		
}

function create_form($table_name) {
	//$ft = new DataManager;
	//$ff = new DataManager;
	$db = DataConnection::readOnly();
	$dbform = new DbForm();
	$dbfield= new DbField();
	$param= "";
	$fnm 	= "";

	$param['form_method'] = "POST";
	$form_add 		= $table_name.'_create_form';
	$form_edit 		= $table_name.'_edit_form';
	$form_delete 	= $table_name.'_delete_form';
	
	//Saving form parameters for the create form
	$param['form_id'] 		= $form_add;
	$param['form_name'] 	= $form_add;
	$param['form_title'] 	= 'Add New '.ucwords(str_replace("_", " ", strtolower($table_name)));
	$param['form_action'] = "javascript:process_information('" . $table_name . "_create_form', '" . $table_name . "_create_form_submit', '" . $table_name . "', null, null, null, null, 'create_row');";
	//$ft->dmInsert("" . FORM_TABLE, $param);
	$create = $dbform->create($param);
	$form_add_id = $create['id'];
	
	//Saving form parameters for edit form
	$param['form_id'] 		= $form_edit;
	$param['form_name'] 	= $form_edit;
	$param['form_title'] 	= 'Edit '.ucwords(str_replace("_", " ", strtolower($table_name)));
	$param['form_action'] = "javascript:process_information('" . $table_name . "_edit_form', '" . $table_name . "_edit_form_submit', '" . $table_name . "', null, null, null, null, 'edit_row');";
	//$ft->dmInsert("" . FORM_TABLE, $param);
	$edit = $dbform->create($param);
	$form_edit_id = $edit['id'];
	
	//Saving form parameters for delete form
	$param['form_id'] 		= $form_delete;
	$param['form_name'] 	= $form_delete;
	$param['form_title'] 	= 'Delete '.ucwords(str_replace("_", " ", strtolower($table_name)));
	$param['form_action'] = "javascript:process_information('" . $table_name . "_delete_form', '" . $table_name . "_delete_form_submit', '" . $table_name . "', null, null, null, null, 'delete_row');";
	//$ft->dmInsert("" . FORM_TABLE, $param);
	$delete = $dbform->create($param);
	$form_delete_id = $delete['id'];

	//$dblink = mysql_connect(NATURAL_DBHOST, NATURAL_DBUSER, NATURAL_DBPASS);

	/*if (!$dblink) {
			//die('Could not connect: ' . mysql_error());
			natural_set_message('Failed to connect with the database '.NATURAL_DBNAME.'!', 'error');		
	}*/
	$today = date("m-d-Y H:i:s");
	$now = date("M-D-Y");
	$query = 'SHOW COLUMNS FROM ' . NATURAL_DBNAME . '.'.$table_name;
	$query_result = mysql_query($query, $dblink);
	
	
	$pdo = new PDO(NATURAL_PDO_DSN_READ, NATURAL_PDO_USER_READ, NATURAL_PDO_PASS_READ);
	$q = $pdo->prepare('SHOW COLUMNS FROM ' . NATURAL_DBNAME . '.'.$table_name);
	$q->execute();
	$columns = $q->fetchAll(PDO::FETCH_COLUMN);
	$i = 0;
	if(count($columns)>0){
		foreach($columns as $key => $val){
			$label = "";
			$nam_ar = explode("_", $val);
			if (is_array($nam_ar)) {
				for ($x = 0; $x < count($nam_ar); $x++) {
					if ($nam_ar[$x] != "id") {
						$label .= ucfirst($nam_ar[$x]) . " ";
					}
				}
				$label = substr($label, 0, -1);
			} else {
				$label = ucfirst($val);
			}
			$field['form_reference'] = $form_add;
			$field['form_template_id'] = $form_add_id;
			$field['field_id'] = $val;
			$field['field_name'] = $val;
			$field['form_field_order'] = $i;
			if ($val == "id") {
					$field['html_type'] = "hidden";
			} else {
					$field['html_type'] = "text";
			}
			$field['def_val'] = "";
			$field['def_label'] = $label;
			//Insert template new
			//$ff->dmInsert("" . FIELD_TABLE, $field);
			$dbfield->create($field);
			//$form_add_id = $create['id'];
			//Insert template edit
			$field['form_reference'] = $form_edit;
			$field['form_template_id'] = $form_edit_id;
			$field['def_val'] = "{$val}";
			$dbfield->create($field);
			if($val=='id'){
				//Insert delete id
				$field['form_reference'] 	= $form_delete;
				$field['form_template_id']= $form_delete_id;
				$field['def_val'] 				= "{$val}";
				$field['html_type'] 			= "hidden";
				$field['def_label'] 			= 'ID';
				$dbfield->create($field);
			}
			if($i==1){
				//Insert delete message
				$field['form_reference'] 	= $form_delete;
				$field['form_template_id']= $form_delete_id;
				$field['field_id'] 				= 'message';
				$field['field_name'] 			= 'message';
				$field['form_field_order']= $i;
				$field['def_label'] 			= '';
				$field['def_val'] 				= 'Are you sure you want to delete this '.$table_name.'?';
				$field['html_type'] 			= 'message';
				$dbfield->create($field);
				
				//Insert delete object
				$field['form_reference'] 	= $form_delete;
				$field['form_template_id']= $form_delete_id;
				$field['field_id'] 				= "{$val}";
				$field['field_name'] 			= "{$val}";
				$field['form_field_order']= $i + 1;
				$field['def_label'] 			= '';
				$field['def_val'] 				= "{$val}";
				$field['html_type'] 			= 'message';
				$dbfield->create($field);
			}
			$i++;
		}
		
		$field['form_reference'] 	= $form_add;
		$field['form_template_id']= $form_add_id;
		$field['field_id'] 				= "sub";
		$field['field_name'] 			= "sub";
		$field['form_field_order']= $i;
		$field['def_label'] 			= '';
		$field['def_val'] 				= '';
		$field['html_type'] 			= 'submit';
		$dbfield->create($field);
		
		$field['form_reference'] 	= $form_edit;
		$field['form_template_id']= $form_edit_id;
		$dbfield->create($field);
		
		$field['form_reference'] 	= $form_delete;
		$field['form_template_id']= $form_delete_id;
		$dbfield->create($field);
	}
	natural_set_message('Done creating the form for the table '.$table_name.'!', 'success');	
}


/*
 * END OF THE FORM MANAGEMENT
 */
function support_info(){
	global $twig;
	// Twig Base
	$template = $twig->loadTemplate('content.html');
	$template->display(array(
		// Dashboard - Passing default variables to content.html
		'page_title' => 'Support',
		'page_subtitle' => 'Natural',
		'content' => 'Thank you for using natural framework,
		<br>for documentation or questions please visit www.opensourcemind.net or email us at devteam@opensourcemind.net.'
	));
}

?>
