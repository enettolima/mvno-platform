<?php
/**
 * All methods in this class are protected
 * @access protected
 */
class Menu {
	/**
	 * Method to fecth Menu list by level
	 *
	 * Fech a list of menus 
	 * by level
	 *
	 * @url GET byLevel/{level}
	 * @smart-auto-routing false
	 * 
	 * @access public
	 * @throws 404 Menu not found for requested level
	 * @param string $level Menu to be fetched
	 * @return mixed 
	 */
  public function byLevel($menu_name = 'main', $level) {
		$db = DataConnection::readOnly();
		$m = $db->menu()
					->select("*")
					->where("status", 1)
					->and("menu_name LIKE ?", $menu_name)
					->order("position ASC");
	
		if(count($m)>0){
      $links = array();
			foreach ($m as $id => $menu){
        if ($this->menuPermission($menu, $level)) {
					 foreach ($menu as $column => $data) {
						 $links[$id][$column] = $data ;
					 }   
        }
			}
		}
      $tree = $this->menuBuildTree($links);
		  return $tree;
  }
  
  
  /**
	* @smart-auto-routing false
	* @access private
	* Builds a multi dimensional array based on the menu items.
  *
  * @param $links
  *   The links of the menu
  * @param $parent_id
  *   The parent_id (pid) of the menu item
  */
  public function menuBuildTree(array &$links, $parent_id = 0) {
    $branch = array();
    foreach ($links as $link) {
      if ($link['pid'] == $parent_id) {
        $children = $this->menuBuildTree($links, $link['id']);
        if ($children) {
          $link['children'] = $children;
        }
        $branch[$link['id']] = $link;
      }
    }
    return $branch;
  }
  
  /**
	* @smart-auto-routing false
	* @access private
	*/
  public function menuPermission($menu_item, $level) {
    $build = TRUE;
    switch ($menu_item['allow']) {
      case 'all':
        $class = '';
        $build = TRUE;
        break;
  
      case 'between':
        $range = explode('and', $menu_item['allow_value']);
        if ($range[0] < $level && $level < $range[1]) {
          $build = TRUE;
        }
        else {
          $build = FALSE;
        }
        break;
  
      case 'equal':
        if ($menu_item['allow_value'] == $level) {
          $build = TRUE;
        }
        else {
          $build = FALSE;
        }
        break;
  
      case 'higher':
        if ($menu_item['allow_value'] < $level) {
          $build = TRUE;
        }
        else {
          $build = FALSE;
        }
        break;
  
      case 'lower':
        if ($menu_item['allow_value'] > $level) {
          $build = TRUE;
        }
        else {
          $build = FALSE;
        }
        break;
  
    }
    return $build;
  }
	/**
	* Method to create a new menu
	*
	* Add a new menu
	*
	* @url POST create
	* @smart-auto-routing false
	* 
	* @access public
	*/
	function create($request_data) {
		//Validating data from the API call
		$this->_validate($request_data, "insert");
		$menu = new Menu();
		$data = array();
		unset($request_data['fn']);
		unset($request_data['id']);
		foreach ($request_data as $key => $value) {
			if ($key != "key") {
				$menu->$key = $value;
				$data[$key] = $value;
			}
		}
		
		$db = DataConnection::ReadWrite();
		$result = $db->menu()->insert($data);
		if ($result['id'] > 0) {
			//Preparing response
			$response = array();
			$response['code'] = 201;
			$response['message'] = 'Menu has been created!';
			$response['id'] = $result['id'];
			natural_set_message($response['message'], 'success');
			return $response;
		} else {
			$error_message = 'Menu could not be created!';
			natural_set_message($error_message, 'error');
			throw new Luracast\Restler\RestException(500, $error_message);
		}
	}

	/**
	* Method to fecth Menu Record by ID
	*
	* Fech a record for a specific menu
	* by ID
	*
	* @url GET byID/{id}
	* @smart-auto-routing false
	* 
	* @access public
	* @throws 404 User not found for requested id  
	* @param int $id Menu to be fetched
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
    $q = $db->menu[$id];
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
      $error_message = 'Menu not found!';
      natural_set_message($error_message, 'error');
      throw new Luracast\Restler\RestException(404, $error_message);
    }
	}

	/**
	* Method to fecth All Menus
	*
	* Fech all records from the database
	*
	* @url GET fetchAll
	* @smart-auto-routing false
	* 
	* @access public
	* @throws 404 Menu not found
	* @return mixed 
	*/
	function fetchAll() {
		$db = DataConnection::readOnly();
		$q = $db->menu();
    if(count($q) > 0) {
      foreach($q as $id => $q){
        $res[$id] = $q;
      }
      return $res;
    }else{
			natural_set_message('Menu not found', 'error');
      throw new Luracast\Restler\RestException(404, 'Menu not found');
    }
	}

	/**
	* Method to Update menu information
	*
	* Update menu on database
	*
	* @url PUT update
	* @smart-auto-routing false
	*
	* @access public
	* @throws 404 Menu not found
	* @return mixed 
	*/
	function update($request_data) {
		$this->_validate($request_data, "update");
		
		$db = DataConnection::ReadWrite();
		$menu = $db->menu[$request_data['id']];
		unset($request_data['fn']);
		foreach ($request_data as $key => $value) {
			$this->$key = $value;
		}
		if($menu){
			if($menu->update($request_data)){
        $response['code'] = 200;
        $response['message'] = 'Menu has been updated!';
        natural_set_message($response['message'], 'success');
      }else{
        //Could not update record! maybe the data is the same.
        $response['code'] = 500;
        $response['message'] = 'Could not update Menu at this time!';
        natural_set_message($response['message'], 'error');
        throw new Luracast\Restler\RestException($response['code'], $response['message']);
      }
      return $response;
		}else{
			natural_set_message('Book not found', 'error');
      throw new Luracast\Restler\RestException(404, 'Menu not found');
		}
	}

	/**
	* Method to delete a menu
	*
	* Delete menu from database
	*
	* @url DELETE delete
	* @smart-auto-routing false
	*
	* @access public
	* @throws 404 Menu not found
	* @return mixed 
	*/
	function delete($id) {
		$data['id'] = $id;
    $this->_validate($data, "delete");
    $db = DataConnection::readWrite();
    $q = $db->menu[$id];
    
    $response = array();
    if($q && $q->delete()){
      $response['code'] = 200;
      $response['message'] = 'Menu has been removed!';
      return $response;
    }else{
      $response['code'] = 404;
      $response['message'] = 'Menu not found!';
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
		if ($type == "update" || $type == "delete") {
			if (!$data['id']) {
				throw new Luracast\Restler\RestException(404, 'Parameter ID is required!');
			}
		}
		/*
		 * check if field is empty
		 * Add more fields as needed
		 */
		if ($type != "delete") {
			if (!$data['position']) {
				$error[] = 'Field position is required!';
			}
			if (!is_numeric($data['position'])) {
				$error[] = 'Field position must be numeric!';
			}
			if (!$data['element_name']) {
				$error[] = 'Field Element Name is required!';
			}
			if (!$data['label']) {
				$error[] = 'Field Label is required!';
			}
			if (!$data['func']) {
				$error[] = 'Field Function is required!';
			}
			$menu = new Menu();
			$db = DataConnection::readOnly();
			if ($type == "edit"){
				$menus = $db->menu()
				->select("*")
				->where("element_name", $data['element_name'])
				->and("id != ?",$data['id'])
				->limit(1);
				if(count($menus)>0){
					$error[] = 'Element name already in use, please try with a different element name!';
				}
			}
			
			if ($type == "create"){
				$menus = $db->menu()
				->select("*")
				->where("element_name", $data['element_name'])
				->limit(1);
				if(count($menus)){
					$error[] = 'Element name already in use, please try with a different element name!';
				}
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
